Role:
You are now my Technical Co-Founder. Your job is to help me build a real product I can
use, share, or launch. Handle all the building, but keep me in the loop and in control.
Use php and mysql tech stack.

My Idea:
Student Organization Recruitment Management System
Supports recruitment campaigns, screening, and membership approvals

Project Framework
Phase 1: Discovery
Ask questions to understand what I actually need (not just what I said)
Challenge my assumptions if something doesn’t make sense
Help me separate “must have now” from “add later”
Tell me if my idea is too big and suggest a smarter starting point.

Phase 2: Planning
Propose exactly what we’ll build in version 1
Explain the technical approach in plain language
Estimate complexity (simple, medium, ambitious)
Identify everything I’ll need (accounts, services, decisions)
Show me the outline of the finished product

Phase 3: Building
Build things I can see and react to
Let me watch you work or join in (I want to learn)
Stop and check before moving on
Test everything and demo key points
Help me understand the options instead of just picking one

Phase 4: Polish
Make it look professional, not like a hackathon project
Use nice UI/UX
Make sure it’s fast and works on different devices if relevant
Don’t call it done until it feels “finished”

Phase 5: Handoff
Launch it if I want it online
Give me everything I need to use it, maintain it, and make changes
Document your process so it’s independent on this version

Phase 6: Support
I’m the product owner. I make the decisions, you make them happen.
Help me keep communicating or organizing if I get busy
Don’t disappear without telling me
Don’t be emotionally attached to the idea – it might change, be disappointing, be
abandoned

Rules:
I don’t just want to learn – I want it to be something I’m proud to show people
This is not a throw-away MVP. Not a prototype. A working product.
Keep me in control and in the loop at all times.
Do not use emojies or symbols on buttons

## Architecture and product walkthrough

This application stays on its existing PHP and MySQL stack (PHP 8.0+ and MySQL 5.7.8+ for JSON columns). The public homepage presents live recruitment information, system capabilities, an accessible tutorial, and links to the existing CSRF-protected login and registration forms. PHP renders the page and API responses; PDO performs parameterized MySQL queries; account passwords use PHP's `password_hash` and `password_verify`; authenticated state uses server-side sessions rather than browser-stored JWTs.

### Analytics and portfolio download

- `POST /api/analytics/log-action.php` records page views and portfolio download intent after checking the session CSRF token. It accepts only those event types and validates page paths and optional screen-resolution values.
- `GET /api/portfolio/download.php` streams `public/docs/portfolio.pdf` as an attachment named `portfolio.pdf`; add `?view=1` to open it inline in the browser. Add the PDF at that path; if it is missing, the endpoint responds with HTTP 404 and `{"success":false,"message":"File not found"}`. Direct downloads are logged on the server; downloads preceded by the telemetry POST are logged once.
- Login attempts are written to the existing `login_logs` table and the new `activity_logs` table. Registration attempts and successful account creation are written to `activity_logs`.
- Activity timestamps are stored in UTC. Browser, OS, device type, referrer path, and optional screen resolution are logged. IPs are anonymized to IPv4 /24 or IPv6 /64 by default. Country, region, and city lookup is disabled by default.
- The database migration is `db/migrations/2026_11_01_activity_logs.sql`. Apply it to an existing installation after selecting the application database, for example: `mysql -u root -p soroms_db < db/migrations/2026_11_01_activity_logs.sql`. Fresh installations can use the updated `db/schema.sql`.
- The admin-only Analytics Dashboard is at `src/report/analytics.php`. It refreshes its KPIs and charts every minute and supports daily, weekly, and monthly traffic views. Page views are recorded by the first-party browser script using the existing CSRF-protected analytics endpoint.
- Dashboard statistics, Excel exports, and PDF exports require the signed-in administrator role and return HTTP 403 to guests and other roles. The Excel workbook contains summary metrics and raw telemetry sheets; XLSX archives are generated with PHP's built-in zlib support. The PDF report contains KPI blocks and visual traffic, device, and operating-system summaries.
- Visitor conversion is calculated from distinct anonymized network addresses with a recorded page view and portfolio download; it is an estimate, since one anonymized address can represent multiple people. Login and signup rates compare successful events with the recorded login/signup attempts.

### Reports, audit logs, and backups

- The admin Reports page includes a print-friendly layout, applicant gender counts (including unknown values), and rule-based decision-support guidance. Recommendations are informational and do not make applicant decisions.
- The Login Activity report combines status, user, date-range, and search filters. The admin Activity Log at `src/report/activity.php` supports combined event, user, and date filters and exports up to 10,000 matching events as CSV. CSV values are escaped to reduce spreadsheet formula injection risk.
- Admins can create and restore ZIP backups at `src/report/backup.php`. Archives contain database rows in JSON Lines format and files under private `storage/`; restore requires an exact schema match, a CSRF token, and typing `RESTORE`. A pre-restore safety archive is kept under `storage/backups/`. Keep that private directory protected and store an additional backup copy off the server.
- Backup and restore require PHP's `zip` extension enabled for the web server (in XAMPP, enable `extension=zip` in `php.ini` and restart Apache). The restore upload is limited to 100 MB and the archive to 512 MB uncompressed; adjust PHP upload limits only if larger backups are needed. Restore replaces current records and private uploads and should be used only with a verified backup.

### Configuration

Configure these environment variables in the PHP/Apache/FPM service rather than committing deployment credentials:

| Variable | Default | Purpose |
| --- | --- | --- |
| `SORMS_DB_HOST` | `localhost` | MySQL host |
| `SORMS_DB_NAME` | `soroms_db` | MySQL database |
| `SORMS_DB_USER` | `root` | MySQL user; use a least-privilege application user in production |
| `SORMS_DB_PASS` | empty | MySQL password |
| `SORMS_DEBUG` | `false` | Enables PHP error display for local development only |
| `SORMS_ANONYMIZE_IP` | `true` | Anonymizes IP addresses in application and login logs |
| `SORMS_ENABLE_IP_GEOLOCATION` | `false` | Enables optional IP geolocation lookups through ipwho.is |
| `SORMS_TRUSTED_PROXIES` | empty | Comma-separated exact proxy IPs allowed to supply forwarding headers |

The app applies sliding-window limits per session and per anonymized network to login, registration, analytics, and downloads. Network-wide limits are approximate when IP anonymization is enabled and may group users on a shared IPv4 subnet. Add reverse-proxy/WAF limits for production, especially for credential-stuffing protection. Forwarded IP and HTTPS headers are ignored unless `REMOTE_ADDR` matches `SORMS_TRUSTED_PROXIES`; configure the trusted proxy to overwrite, not append untrusted, forwarding headers.

Do not enable geolocation without reviewing the provider's privacy terms and documenting the disclosure for users. No third-party analytics script is used. The analytics endpoint is same-origin and does not enable cross-origin access; keep production CORS restricted to the app origin. Serve the app over HTTPS, use a dedicated database account, disable debug display, back up the database, and configure retention/deletion for activity logs to meet applicable privacy requirements.

## Landing page verification

After applying the migration and configuring the database, verify the homepage, switch the light/dark theme, use all three tutorial tabs, test both account links, and download the PDF. Confirm that activity events appear in `activity_logs`, IP values are anonymized with the default settings, and each download generates one event. Login and registration remain on the existing server-validated flows.