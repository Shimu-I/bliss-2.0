# Bliss Day Care (PHP + MySQL)

Vanilla PHP 7.4+ (8.x fine), MySQL/MariaDB, PDO, no frameworks, no JavaScript dependencies.

## Deploy (any shared host, e.g. InfinityFree)
1. Create a MySQL database in the host's control panel. Note host, name, user, password.
2. Edit `config.php` with those values.
3. Upload everything in this folder to `htdocs/` (or your web root).
4. Open `https://your-site/install.php`, create the admin account (tick demo data only for a trial).
5. **Delete `install.php`** from the server. Sign in at `/login.php`.

## Run locally
`php -S localhost:8000` in this folder, with a local MySQL database and `config.php` set up.

## Roles
- **Parent**: registers children, sees attendance/activity/meals/learning, bills, notifications.
- **Caregiver**: marks attendance, logs records for assigned children (parents are notified).
- **Admin**: assigns caregivers, manages caregivers, creates bills, marks them paid.

## Security built in
Prepared statements everywhere, bcrypt passwords, CSRF tokens on every POST, session ID regeneration,
HttpOnly/SameSite cookies, login throttling, role checks + ownership checks on every action,
output escaping, CSP and security headers, no file uploads, config/SQL/includes blocked by .htaccess.
Use HTTPS in production.
# bliss-2.0
