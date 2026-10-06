<?php
// Production database settings.
//
// Copy this file to config.local.php in the same folder and fill in the real
// values. db.php loads config.local.php when it exists, and .gitignore keeps
// it out of the repository so the password is never uploaded.
//
// Use a dedicated MySQL user that only has rights on this one database,
// not the root account.
$servername = 'localhost';
$username   = 'nyumbani_app';
$password   = 'change-me';
$dbname     = 'nyumbani_db';
