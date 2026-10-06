# Nyumbani Website Rebuild

A PHP and MySQL website for **Nyumbani Children's Home and the Children of God Relief Institute (COGRI)** in Kenya. It presents the organisation's programmes to the public, accepts messages and donations enquiries, and includes an admin panel so staff can publish blog posts, reports, newsletters, gallery photos and job openings without touching code.

[![CI](https://github.com/kinuthia-mark/Nyumbani-website-rebuild/actions/workflows/ci.yml/badge.svg)](https://github.com/kinuthia-mark/Nyumbani-website-rebuild/actions/workflows/ci.yml)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-10.4-003545?logo=mariadb&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-ready-2496ED?logo=docker&logoColor=white)
![Playwright](https://img.shields.io/badge/tested%20with-Playwright-2EAD33?logo=playwright&logoColor=white)

![Nyumbani home page](docs/screenshots/index.png)

## Screenshots

| Blog | Admin: create a post |
|---|---|
| ![Blog page](docs/screenshots/blog.png) | ![Admin blog editor](docs/screenshots/admin-blog.png) |
| **Gallery** | **Careers** |
| ![Gallery](docs/screenshots/gallery.png) | ![Careers](docs/screenshots/careers.png) |

<details>
<summary>More: programmes, resources, donate, contact, admin dashboard, mobile</summary>

| | |
|---|---|
| ![Programmes](docs/screenshots/programs.png) | ![Resources](docs/screenshots/resources.png) |
| ![Donate](docs/screenshots/donate.png) | ![Contact](docs/screenshots/contact.png) |
| ![Admin dashboard](docs/screenshots/admin-dashboard.png) | <img src="docs/screenshots/home-mobile.png" alt="Home page on a phone" width="260"> |

</details>

Screenshots were taken by the automated browser test, so the blog posts, jobs and gallery captions in them are sample content entered by the test.


## Table of Contents

1. [Features](#features)
2. [Architecture](#architecture)
3. [Site Map](#site-map)
4. [Request Flow](#request-flow)
5. [Admin Panel](#admin-panel)
6. [Database Schema](#database-schema)
7. [Tech Stack](#tech-stack)
8. [Project Structure](#project-structure)
9. [Getting Started](#getting-started) (Docker or XAMPP)
10. [Security](#security)
11. [Automated Checks](#automated-checks)
12. [Roadmap](#roadmap)
13. [Contributing](#contributing)

## Features

**Public site**

- Pages for the Children's Home, Lea Toto community programme, Nyumbani Village (Kitui) and an overview of all programmes
- Blog / news with individual post pages
- Resources hub: newsletters, annual reports and audit reports (PDF downloads)
- Photo gallery managed from the database
- Careers page with categorised job openings (medical, social, admin)
- Contact form that stores messages in the database
- Donation page with M-Pesa and bank transfer details
- Shared header and footer for consistent navigation

**Admin panel** (`/admin`)

- Session-based login
- Dashboard with counts of newsletters, reports and jobs
- Create, edit, publish or delete blog posts, newsletters, annual reports and audit reports (draft / published workflow)
- Upload and delete gallery photos
- Manage job openings
- Inbox for contact-form messages (unread / read)

## Architecture

A classic server-rendered PHP application. Each page is a PHP script that includes a shared header and footer, queries MariaDB through `mysqli`, and returns HTML. Uploaded files live on disk, and their paths are stored in the database.

```mermaid
flowchart TD
    V["Visitor"] -->|HTTP| Web["Apache + PHP 8.2"]
    A["Admin staff"] -->|HTTP + session| Web

    subgraph App["Nyumbani application"]
        Pub["Public pages<br/>index, about, blog, gallery ..."]
        Adm["Admin panel<br/>admin/*.php"]
        Inc["Shared includes<br/>header.php, footer.php"]
        Conn["admin/db.php<br/>mysqli connection"]
    end

    Web --> Pub
    Web --> Adm
    Pub --> Inc
    Pub --> Conn
    Adm --> Conn
    Conn --> DB[("MariaDB<br/>nyumbani_db")]
    Adm -->|store PDFs and images| FS[("uploads/ folder")]
    Pub -->|serve files| FS
    Pub --> CDN["External: Font Awesome,<br/>Google Fonts, Google Maps"]
```

## Site Map

```mermaid
flowchart TD
    Home["index.php<br/>Home"]
    Home --> About["about.php"]
    Home --> Programs["programs.php"]
    Home --> Contact["contact.php"]
    Home --> Gallery["gallery.php"]
    Home --> Res["resources.php"]
    Home --> Careers["careers.php"]
    Home --> Donate["donate.php"]

    Programs --> CH["childrens-home.php"]
    Programs --> LT["lea-toto.php"]
    Programs --> NV["nyumbani-village.php"]

    Res --> News["newsletter.php"]
    Res --> AR["annual-report.php"]
    Res --> AU["audit-report.php"]
    Res --> Blog["blog.php"]
    Blog --> Post["view_post.php"]

    Contact -.->|POST| Send["send_message.php"]
```

## Request Flow

### Contact form

```mermaid
sequenceDiagram
    actor Visitor
    participant C as contact.php
    participant S as send_message.php
    participant DB as MariaDB
    actor Admin
    participant M as admin/manage_messages.php

    Visitor->>C: Opens contact page
    Visitor->>S: Submits name, email, subject, message
    S->>DB: INSERT into messages (status = unread)
    S-->>Visitor: Redirect to contact.php?success=1
    Admin->>M: Opens inbox
    M->>DB: SELECT messages
    M-->>Admin: List of unread and read messages
```

### Publishing content

```mermaid
sequenceDiagram
    actor Admin
    participant L as admin/login.php
    participant P as Admin manage page
    participant FS as uploads folder
    participant DB as MariaDB
    actor Visitor
    participant W as Public page

    Admin->>L: Enters username and password
    L->>DB: Look up user
    L-->>Admin: Session started, redirect to dashboard
    Admin->>P: Fills form and attaches PDF or image
    P->>FS: Save file with timestamp prefix
    P->>DB: INSERT record with file path and status
    Visitor->>W: Requests resource page
    W->>DB: SELECT where status is published
    W-->>Visitor: Rendered list with download links
```

## Admin Panel

```mermaid
flowchart LR
    Login["login.php"] -->|valid credentials| Dash["admin.php<br/>Dashboard"]
    Dash --> Msg["manage_messages.php"]
    Dash --> Blog["manage_blog.php"]
    Dash --> NL["manage_newsletters.php"]
    Dash --> AR["manage_annual_reports.php"]
    Dash --> AU["manage_audit_reports.php"]
    Dash --> Gal["manage_gallery.php"]
    Dash --> Jobs["manage_jobs.php"]
    Dash --> Out["logout.php"]
```

## Database Schema

Database name: `nyumbani_db`. The tables are independent (no foreign keys), and each content table has a `status` column so drafts stay hidden from the public site.

```mermaid
erDiagram
    users {
        int id PK
        varchar username
        varchar password
    }
    blog_posts {
        int id PK
        varchar title
        text content
        varchar image_path
        varchar author
        enum status "draft or published"
        timestamp created_at
    }
    newsletters {
        int id PK
        varchar title
        text description
        date publish_date
        varchar pdf_path
        varchar thumbnail_path
        enum status "draft or published"
    }
    annual_reports {
        int id PK
        varchar title
        int report_year
        text description
        varchar pdf_path
        enum status "draft or published"
    }
    audit_reports {
        int id PK
        varchar title
        int report_year
        text description
        varchar pdf_path
        enum status "draft or published"
    }
    gallery {
        int id PK
        varchar caption
        varchar image_path
        timestamp upload_date
    }
    job_openings {
        int id PK
        varchar title
        varchar location
        varchar job_type
        enum category "medical, social, admin"
    }
    messages {
        int id PK
        varchar name
        varchar email
        varchar subject
        text message
        enum status "unread or read"
        timestamp created_at
    }
```

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.2, `mysqli` |
| Database | MariaDB 10.4 (MySQL compatible) |
| Frontend | HTML5 and plain CSS: shared styles in `css/style.css`, page and admin styles in `css/pages/` and `css/admin/` |
| Icons and fonts | Font Awesome (CDN), Google Fonts (Poppins) |
| Maps | Google Maps embed on the contact page |
| Local environment | XAMPP (Apache + MariaDB + phpMyAdmin) |

## Project Structure

```text
Nyumbani-website-rebuild/
├── index.php                 # Home page
├── about.php
├── programs.php              # Programmes overview
├── childrens-home.php
├── lea-toto.php
├── nyumbani-village.php
├── blog.php, view_post.php   # Blog list and single post
├── newsletter.php
├── annual-report.php
├── audit-report.php
├── resources.php             # Resources hub
├── gallery.php
├── careers.php
├── contact.php, send_message.php
├── donate.php
├── header.php, footer.php    # Shared layout
├── css/
│   ├── style.css             # Shared site styles
│   ├── pages/                # Styles for individual public pages (blog, resources, ...)
│   └── admin/                # Admin panel styles: sidebar, dashboard, one file per screen
├── images/                   # Logos and static photos
├── uploads/                  # Admin-uploaded files (.htaccess blocks script execution, contents git-ignored)
├── admin/
│   ├── db.php                # Database connection (reads config.local.php if present)
│   ├── config.example.php    # Template for production DB credentials
│   ├── helpers.php           # Escaping, CSRF tokens, action_button(), safe file uploads
│   ├── login.php, login_process.php, logout.php
│   ├── admin.php             # Dashboard
│   ├── manage_*.php          # Content management screens
│   └── blog_handler.php, gallery_handler.php, delete_photo.php
├── database/
│   └── nyumbani_db.sql       # Schema and starter data
├── tests/e2e/admin_flow.py   # Playwright browser test of the admin panel
├── docs/screenshots/         # Images used in this README
├── Dockerfile                # PHP 8.2 + Apache + mysqli
└── docker-compose.yml        # Site + MariaDB with named volumes
```

## Getting Started

### Quick start with Docker

```bash
git clone https://github.com/kinuthia-mark/Nyumbani-website-rebuild.git
cd Nyumbani-website-rebuild
docker compose up --build
```

- Site: <http://localhost:8080>
- Admin: <http://localhost:8080/admin/login.php> (starter login `admin` / `ChangeMe123!`, change it straight away)

Compose starts PHP 8.2 on Apache and MariaDB 10.11. The database is created from `database/nyumbani_db.sql` the first time, using a dedicated `nyumbani` database user rather than root. The database and uploaded files live in named volumes, so they survive restarts.

### Local setup with XAMPP

#### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (or any Apache + PHP 8.x + MariaDB/MySQL stack)
- Git

#### Installation

1. **Clone the repository into your web root** (`htdocs` for XAMPP):

   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/kinuthia-mark/Nyumbani-website-rebuild.git
   ```

2. **Create the database.** Start Apache and MySQL in XAMPP, open phpMyAdmin, create an empty database named `nyumbani_db` (collation `utf8mb4_general_ci`), then import `database/nyumbani_db.sql` into it.

3. **Check the connection settings.** The defaults in `admin/db.php` suit XAMPP (`root`, empty password, database `nyumbani_db`). They can be overridden in two ways:
   - environment variables `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME` (this is what Docker uses)
   - `admin/config.local.php`: copy `admin/config.example.php` and enter your real values. The file is git-ignored, so your password is never uploaded.

4. **Open the site** at `http://localhost/Nyumbani-website-rebuild/` and the admin panel at `http://localhost/Nyumbani-website-rebuild/admin/login.php`.

5. **Sign in and change the starter password immediately.** The sample database has one admin account: username `admin`, password `ChangeMe123!`. Passwords are stored hashed. To set a new one, generate a hash:

   ```bash
   php -r "echo password_hash('your-new-password', PASSWORD_DEFAULT);"
   ```

   then run in phpMyAdmin: `UPDATE users SET password = '<paste hash>' WHERE username = 'admin';`
   (Plain-text passwords from older installs still work once and are converted to hashes automatically on first login.)

Upload folders are created automatically on first upload. On Linux hosting, make sure `uploads/` is writable by the web server.

## Security

| Area | How it is handled |
|------|-------------------|
| Passwords | Hashed with `password_hash()` and checked with `password_verify()`; legacy plain-text passwords are upgraded on first login |
| Sessions | Session ID regenerated on login, cookie is `HttpOnly` and `SameSite=Lax`, logout clears the session |
| SQL injection | Prepared statements for login, contact form and every insert; status and category fields are whitelisted |
| XSS | Visitor and admin content is escaped with `htmlspecialchars()` before display (`e()` helper in the admin area) |
| File uploads | Extension whitelist, real MIME type check, 10 MB limit, random file names; `uploads/.htaccess` blocks script execution |
| Access control | Every admin page and handler calls `require_admin()` |
| CSRF | Every admin form (create, publish, delete, mark as read, upload) sends a per-session token, checked with `hash_equals()`. Requests without it get a 403 |
| State-changing actions | Delete, publish and mark-as-read are POST forms built by `action_button()`, never plain links, so a crawler, a link preview or a reopened URL cannot trigger them |
| Secrets | Production DB credentials come from environment variables or `admin/config.local.php`, which is git-ignored |
| Uploaded files | Kept out of git by `.gitignore`; in Docker they live in their own volume. CI checks that a `.php` file dropped into `uploads/` is not executed |
| Errors | Database errors are logged, not shown to visitors |

**Still recommended before a large public launch**

- Serve the site over HTTPS (then also enable `session.cookie_secure`)
- Use a dedicated MySQL user with limited rights instead of `root` (the Docker setup already does)
- Add stronger login attempt limiting (currently a 1-second delay on failure) and a password-reset flow if more staff are added
- Take regular backups of the database and the `uploads/` folder

## Automated Checks

GitHub Actions (`.github/workflows/ci.yml`) runs on every push and pull request:

| Check | What it does |
|-------|--------------|
| Syntax | `php -l` on every PHP file, on PHP 8.2 and 8.3 |
| Browser test | `tests/e2e/admin_flow.py` drives Chromium through the admin with Playwright: sign in, publish and delete posts, upload photos, reports and a newsletter, post jobs, send and read a contact message. Every step is checked in the database, and it confirms that an old-style GET delete link and a POST without the CSRF token both do nothing. Screenshots are uploaded as a build artifact |
| Docker | Builds the image, starts the Compose stack with MariaDB, loads the main pages, and checks that a PHP file placed in `uploads/` is not run |
| Smoke test | Starts MariaDB, imports `database/nyumbani_db.sql`, serves the site with PHP's built-in server and requests every public page. Each page must return 200 with no PHP warnings or errors |
| Access control | Checks that `admin/admin.php` redirects a signed-out visitor to the login page |



- [x] Public pages for programmes, resources, gallery, careers and donations
- [x] Admin panel with draft / publish workflow
- [x] Contact form with admin inbox
- [x] Security hardening (hashed passwords, prepared statements, safe uploads, CSRF tokens)
- [x] Remove duplicated code and the old `public --- copy` folder
- [ ] Add pagination to blog and resource lists
- [x] Automated syntax check and page smoke test on every push
- [x] Admin actions moved from links to CSRF-protected POST forms
- [x] End-to-end browser test of the admin panel
- [x] Docker Compose setup
- [ ] Deployment guide for production hosting

## Contributing

1. Fork the repository
2. Create a branch: `git checkout -b feature/your-feature`
3. Commit your changes: `git commit -m "Describe your change"`
4. Push and open a Pull Request

## Author

**Mark Kinuthia** - [github.com/kinuthia-mark](https://github.com/kinuthia-mark)
