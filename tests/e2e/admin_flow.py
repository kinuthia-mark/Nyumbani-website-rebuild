"""End-to-end test of the admin panel and the public site.

Drives a real browser (Playwright) through the admin: sign in, publish and
delete blog posts, upload gallery photos, reports and a newsletter, post jobs,
read a contact message, and confirm that forged or GET-based actions are
refused. Each step is checked against the database.

Run it against a running copy of the site:

    pip install playwright && python -m playwright install chromium
    export BASE_URL=http://127.0.0.1:8080
    export MYSQL_CMD="mysql -h 127.0.0.1 -uroot -psecret nyumbani_db"
    python tests/e2e/admin_flow.py

Set SCREENSHOTS_DIR to also save screenshots of the main pages.
The admin password is the starter one from database/nyumbani_db.sql.
"""
import os, shlex, subprocess, sys, tempfile, time
from playwright.sync_api import sync_playwright

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
IMG = os.path.join(ROOT, "images")
OUT = os.environ.get("SCREENSHOTS_DIR")
BASE = os.environ.get("BASE_URL", "http://127.0.0.1:8080").rstrip("/")
MYSQL = shlex.split(os.environ.get("MYSQL_CMD", "mysql -h 127.0.0.1 -uroot nyumbani_db"))
ADMIN_PASSWORD = os.environ.get("ADMIN_PASSWORD", "ChangeMe123!")

# The smallest valid PDF, so the upload's MIME check accepts it.
PDF = os.path.join(tempfile.gettempdir(), "nyumbani-e2e.pdf")
with open(PDF, "wb") as fh:
    fh.write(
        b"%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n"
        b"2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n"
        b"3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]>>endobj\n"
        b"trailer<</Root 1 0 R>>\n%%EOF\n"
    )


def sql(q):
    return subprocess.run(MYSQL + ["-N", "-e", q], capture_output=True, text=True).stdout.strip()


def shot(page, name):
    if OUT:
        os.makedirs(OUT, exist_ok=True)
        page.screenshot(path=os.path.join(OUT, name))


errors = []
def check(cond, msg):
    print(("PASS " if cond else "FAIL ") + msg)
    if not cond:
        errors.append(msg)


with sync_playwright() as p:
    b = p.chromium.launch()
    page = b.new_page(viewport={"width": 1366, "height": 860}, device_scale_factor=1.5)
    page.on("dialog", lambda d: d.accept())

    # Login
    page.goto(BASE + "/admin/login.php")
    page.fill("input[name=user]", "admin"); page.fill("input[name=pass]", ADMIN_PASSWORD)
    page.click("button[name=login_submit]"); page.wait_for_load_state("networkidle")
    check("admin.php" in page.url, "admin can log in")

    # Blog posts (two published, one draft)
    posts = [
        ("A new classroom block for Nyumbani Village", "village.jpg",
         "Thanks to our partners, children at Nyumbani Village in Kitui now learn in a bright new classroom block with space for 120 pupils. The building has solar lighting and rainwater harvesting, so lessons continue through power cuts and dry spells.", "published"),
        ("Lea Toto reaches 3,000 children in Nairobi", "leakids.jpg",
         "Our community programme Lea Toto now supports more than 3,000 children living with HIV across eight Nairobi communities, with home visits, treatment support and school fees.", "published"),
        ("Draft: Annual fun day plans", "kids.jpg", "Planning notes for the fun day.", "draft"),
    ]
    for title, img, body, status in posts:
        page.goto(BASE + "/admin/manage_blog.php")
        page.fill("#inTitle", title); page.fill("#inDesc", body)
        page.set_input_files("#inThumb", os.path.join(IMG, img))
        page.click("button.btn-publish" if status == "published" else "button.btn-draft")
        page.wait_for_load_state("networkidle")
    check(sql("select count(*) from blog_posts") == "3", "three blog posts created")
    check(sql("select count(*) from blog_posts where status='published'") == "2", "two published, one draft")

    # Publish the draft with the new POST button, then delete it
    page.goto(BASE + "/admin/manage_blog.php")
    page.locator("form.inline-action button.mc-btn-go-live").first.click(); page.wait_for_load_state("networkidle")
    check(sql("select count(*) from blog_posts where status='published'") == "3", "Go Live button publishes via POST")
    did = sql("select id from blog_posts where title like 'Draft:%'")
    page.goto(BASE + "/admin/manage_blog.php")
    page.locator(f"form.inline-action:has(input[name=delete][value='{did}']) button").first.click(); page.wait_for_load_state("networkidle")
    check(sql("select count(*) from blog_posts") == "2", "Delete button removes via POST")

    # A GET to the old-style link must not delete anything any more
    keep = sql("select id from blog_posts limit 1")
    page.goto(f"{BASE}/admin/manage_blog.php?delete={keep}&t=whatever")
    check(sql("select count(*) from blog_posts") == "2", "old GET delete link does nothing")

    # Gallery
    for cap, img in [("Morning assembly at the Children's Home", "home.jpg"), ("Harvest at Nyumbani Village farm", "farm.jpg"),
                     ("Lab technicians at the diagnostic laboratory", "lab main.jpg"), ("Lea Toto community day", "community.jpg"),
                     ("Vocational training workshop", "training.jpg"), ("The village from above", "Nyumbani Village aerial view.jpg")]:
        page.goto(BASE + "/admin/manage_gallery.php")
        page.fill("input[name=caption]", cap); page.set_input_files("input[name=gallery_img]", os.path.join(IMG, img))
        page.click("button[name=submit_gallery]"); page.wait_for_load_state("networkidle")
    check(int(sql("select count(*) from gallery")) >= 6, "gallery uploads work")

    # Jobs
    for title, loc, cat in [("Registered Nurse", "Karen, Nairobi", "medical"), ("Social Worker", "Kitui", "social"), ("Accounts Assistant", "Karen, Nairobi", "admin")]:
        page.goto(BASE + "/admin/manage_jobs.php")
        page.fill("input[name=title]", title); page.fill("input[name=location]", loc)
        page.select_option("select[name=category]", cat)
        page.click("button[name=add_job]"); page.wait_for_load_state("networkidle")
    check(sql("select count(*) from job_openings") == "3", "jobs created")

    # Reports and newsletter
    page.goto(BASE + "/admin/manage_annual_reports.php")
    page.fill("#inTitle", "2025 Annual Report"); page.fill("#inDesc", "Programme reach, finances and stories from the year.")
    page.set_input_files("input[name=pdf_file]", PDF); page.click("button.btn-publish"); page.wait_for_load_state("networkidle")
    page.goto(BASE + "/admin/manage_audit_reports.php")
    page.fill("#inTitle", "2025 Audited Financial Statements"); page.fill("#inDesc", "Independent audit of the 2025 accounts.")
    page.set_input_files("input[name=pdf_file]", PDF); page.click("button.btn-publish"); page.wait_for_load_state("networkidle")
    page.goto(BASE + "/admin/manage_newsletters.php")
    page.fill("#inTitle", "September 2025 Newsletter"); page.fill("#inDesc", "News from the Home, the Village and Lea Toto.")
    page.fill("#inDate", "2025-09-30")
    page.set_input_files("input[name=pdf_file]", PDF); page.set_input_files("input[name=thumb_file]", os.path.join(IMG, "kids.jpg"))
    page.click("button.btn-publish"); page.wait_for_load_state("networkidle")
    check(sql("select count(*) from annual_reports") == "1" and sql("select count(*) from newsletters") == "1", "report and newsletter uploads work")

    # Contact form (public) then mark as read in the admin
    pub = b.new_page(viewport={"width": 1366, "height": 860}, device_scale_factor=1.5)
    pub.goto(BASE + "/contact.php")
    pub.fill("#name", "Amina Yusuf"); pub.fill("#email", "amina@example.com"); pub.fill("#message", "I would like to volunteer at the Children's Home during the December holidays.")
    pub.click("button.btn-primary"); pub.wait_for_load_state("networkidle")
    check(sql("select count(*) from messages where status='unread'") == "1", "contact form stores an unread message")
    page.goto(BASE + "/admin/manage_messages.php")
    page.locator("form.inline-action button.btn-read").first.click(); page.wait_for_load_state("networkidle")
    check(sql("select status from messages limit 1") == "read", "Mark as Read works via POST")

    # A forged POST without the token is rejected
    resp = page.request.post(BASE + "/admin/manage_jobs.php", form={"delete": sql("select id from job_openings limit 1")})
    check(resp.status == 403 and sql("select count(*) from job_openings") == "3", "POST without CSRF token is refused")

    # Screenshots
    page.goto(BASE + "/admin/admin.php"); page.wait_for_load_state("networkidle"); time.sleep(0.5)
    shot(page, "admin-dashboard.png")
    page.goto(BASE + "/admin/manage_blog.php"); page.wait_for_load_state("networkidle"); time.sleep(0.5)
    shot(page, "admin-blog.png")
    for name in ["index", "programs", "blog", "gallery", "resources", "careers", "donate", "contact"]:
        pub.goto(f"{BASE}/{name}.php"); pub.wait_for_load_state("networkidle"); time.sleep(0.8)
        shot(pub, f"{name}.png")
    m = b.new_page(viewport={"width": 390, "height": 844}, device_scale_factor=2, is_mobile=True)
    m.goto(BASE + "/index.php"); m.wait_for_load_state("networkidle"); time.sleep(0.8)
    shot(m, "home-mobile.png")
    b.close()

print("ERRORS:", errors)
sys.exit(1 if errors else 0)
