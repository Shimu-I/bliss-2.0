# Bliss Day Care

A small, friendly day care management system built with **vanilla PHP, MySQL and CSS**: no frameworks, no build step.
Parents follow their child's day, caregivers log it, and the admin runs the centre.

## Features
- **Parents**: register children, see attendance / activities / meals / learning notes, view bills, read notifications.
- **Caregivers**: mark attendance in one tap, log activities, meals, learning notes and pick-up / drop-off. Parents are notified automatically.
- **Admin**: dashboard stats, assign caregivers to children, manage caregivers, create bills and mark them paid.
- **Built in**: bcrypt passwords, CSRF protection, role checks, login throttling, one-time web installer.

## Folder structure
```
bliss-php/
├── index.php          Landing page
├── login.php          Sign in
├── register.php       Parent registration
├── logout.php         Sign out (POST only)
├── app.php            Dashboard router (loads a page from pages/)
├── install.php        One-time installer (delete after use)
├── config.php         Database + timezone + currency settings
├── schema.sql         Database tables
├── inc/bootstrap.php  DB connection, session, security + helper functions
├── pages/             home, children, records, bills, inbox, staff, parents
├── assets/            style.css, app.js
├── README.md          This file
├── DOCS.md            Technical documentation
└── USER_MANUAL.md     Guide for parents, caregivers and admins
```

## Requirements
PHP 7.4+ (with `pdo_mysql` and `mbstring`), MySQL 5.7+ or MariaDB 10.3+, Apache with `.htaccess` support.

## Quick start
1. Create an empty MySQL database (e.g. `day_care`).
2. Edit `config.php` with your database host, name, user and password.
3. Put the folder in your web root (XAMPP: `C:\xampp\htdocs\`, or upload to your host).
4. Open `/install.php`, create the admin account (tick *demo data* to try it out).
5. **Delete `install.php`**, then sign in at `/login.php`.

Demo accounts (only if you loaded demo data), password `password123`:
`john.doe@example.com` (parent), `emma.green@example.com` (caregiver).

## More
- Technical details, database, permissions, troubleshooting: [DOCS.md](DOCS.md)
- How to use each role: [USER_MANUAL.md](USER_MANUAL.md)
