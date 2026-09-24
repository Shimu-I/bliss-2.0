# Technical Documentation

## 1. Architecture
Plain PHP with PDO. Every entry file starts with `require inc/bootstrap.php`, which loads `config.php`,
sets security headers, starts the session and opens the database connection.
`app.php` is a tiny router: it checks the user's role, whitelists the requested page against that role's
tab list (`tabs()` in bootstrap), then includes `pages/<page>.php`. Pages read and write with prepared
statements and redirect after every successful POST (Post/Redirect/Get) with a one-time flash message.

## 2. Configuration (`config.php`)
| Key | Env variable | Default | Meaning |
|---|---|---|---|
| host / port | `DB_HOST` / `DB_PORT` | localhost / 3306 | MySQL server |
| name | `DB_NAME` | day_care | Database name |
| user / pass | `DB_USER` / `DB_PASS` | root / (empty) | Credentials |
| tz | `APP_TZ` | Asia/Dhaka | Used by PHP **and** MySQL so "today" matches |
| currency | `CURRENCY` | ৳ | Symbol shown on bills |

## 3. Database
| Table | Purpose |
|---|---|
| users | Everyone who can sign in (`role`: Parent, Caregiver, Admin), bcrypt `password` |
| caregivers | Caregiver profile (qualification, experience, training), 1:1 with a user |
| child | Child details; `user_id` = parent, `caregiver_id` = assigned caregiver |
| attendance | One row per child per day (unique key) |
| activity, learning, meal, pickdrop | Daily records per child |
| bill_payment | Bills per parent; status Pending / Paid (Overdue is computed from `due_date`) |
| notification | Messages to a user, Unread / Read |

Relationships: a parent has many children; a caregiver has many children; a child has many attendance,
activity, learning, meal and pickdrop rows; a user has many bills and notifications. Deleting a parent or
child cascades to their data. Deleting a caregiver un-assigns their children (`SET NULL`).

## 4. Permissions
| Action | Parent | Caregiver | Admin |
|---|---|---|---|
| View children | own | assigned | all |
| Register / remove child | own | no | remove any |
| Assign caregiver | no | no | yes |
| Mark attendance, log records | no | assigned children | no |
| View records | own children | assigned children | no (by design) |
| View bills | own | no | all |
| Create bill / mark paid | no | no | yes |
| Manage caregivers | no | no | yes |
| Inbox | yes | no | no |

Every action re-checks role and ownership on the server. Hiding a button is never the only protection.

## 5. Security measures
Prepared statements only; bcrypt via `password_hash`; CSRF token on every POST; `session_regenerate_id` on login;
HttpOnly + SameSite cookies (Secure over HTTPS); login lock (5 failures = 60 s); all output escaped with `h()`;
CSP, `X-Frame-Options`, `nosniff`; no file uploads; `.htaccess` blocks `config.php`, `.sql`, `.md`, `inc/`, `pages/`.
The CSP forbids inline styles and scripts, so all styling lives in `assets/style.css`.

## 6. Smoke test (5 minutes, do this after setup)
1. Open `install.php`, install with demo data, delete `install.php`.
2. Sign in as `emma.green@example.com`: mark Alice present, add an activity record.
3. Sign out, sign in as `john.doe@example.com`: Inbox shows unread messages; Activity lists the record.
4. Register a child: a caregiver is assigned automatically.
5. Sign in as your admin: create a bill for John, mark it paid; John gets a notification.
6. As John, try `app.php?p=staff`: you should land back on the overview (not allowed).

## 7. Troubleshooting
| Symptom | Likely cause / fix |
|---|---|
| "Database connection failed" | Wrong values in `config.php`, MySQL not running, wrong port |
| Blank page | PHP error is logged, not shown: check the PHP/Apache error log |
| 500 error right away | Apache < 2.4 or `.htaccess` overrides disabled: remove the `Require` lines |
| "Invalid request" on a form | Session expired; reload the page and retry |
| Installer says "Already installed" | Users exist; drop the tables to reinstall |
| Times look off | Set `APP_TZ` / `tz` in `config.php` |

## 8. Known limitations
- No online payment: admin records payments manually.
- No password reset by email (needs mail setup); admin can add a new caregiver account, parents must re-register.
- No profile photos, no editing of a child once registered (remove and re-add).
- Registration is open to anyone (parents only).
- Not yet tested on a live server by the author of this document; run the smoke test above.

## 9. Ideas for next steps
Payment gateway (bKash / SSLCommerz), password reset by email, weekly attendance chart, caregiver-to-parent
messages, edit child details, CSV export of attendance and bills, admin view of all records.
