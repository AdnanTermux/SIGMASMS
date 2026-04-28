# Sigma SMS A2P OTP Panel

A multi-tenant PHP/MySQL web application for managing virtual phone numbers, receiving real OTP messages, calculating profit per SMS, and providing hierarchical user management.

---

## Requirements

- PHP 8.0+ with PDO, cURL, JSON extensions
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx with mod_rewrite (or equivalent)
- Internet access to CDN (Bootstrap, DataTables, ApexCharts, etc.)

---

## Quick Install

### Option A — Web Installer (Recommended)

1. Upload all files to your web server root (e.g. `/var/www/html/sigma_sms/`)
2. Visit: `http://yoursite.com/sigma_sms/install.php`
3. Fill in database credentials, app URL, and admin account
4. Click **Install Now**
5. **Delete `install.php` after installation**

### Option B — Manual

1. Create a MySQL database: `sigma_sms_a2p`
2. Import `schema.sql`: `mysql -u root -p sigma_sms_a2p < schema.sql`
3. Edit `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'sigma_sms_a2p');
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_password');
   define('APP_URL', 'http://yoursite.com/sigma_sms');
   ```
4. Default login: `admin` / `password` — **change immediately**

---

## Directory Structure

```
sigma_sms/
├── ajax/               # AJAX/API endpoints
│   ├── cron_fetch.php  # OTP ingestion trigger
│   ├── dashboard_stats.php
│   ├── dashboard_charts.php
│   ├── dt_sms_reports.php
│   ├── dt_profit_reports.php
│   ├── dt_numbers.php
│   ├── dt_users.php
│   ├── aj_numbers.php
│   ├── aj_users.php
│   ├── aj_services.php
│   └── aj_countries.php
├── api/
│   └── otps.php        # Public REST API endpoint
├── assets/
│   └── css/app.css     # Custom styles
│   └── js/app.js       # Custom JS helpers
├── includes/
│   ├── header.php
│   └── footer.php
├── config.php
├── functions.php
├── schema.sql
├── install.php         # DELETE after install
├── index.php
├── login.php
├── logout.php
├── dashboard.php
├── sms_reports.php
├── profit_stats.php
├── numbers.php
├── my_numbers.php
├── users.php
├── profile.php
├── notifications.php
├── news_master.php
├── credit_notes.php
├── payment_requests.php
├── bank_accounts.php
└── statements.php
```

---

## User Roles

| Role           | Description |
|----------------|-------------|
| `admin`        | Full system control |
| `manager`      | Manages own users and numbers |
| `reseller`     | Views assigned numbers, creates sub-resellers |
| `sub_reseller` | Uses assigned numbers only |

---

## OTP Fetching

OTPs are fetched from `https://tempnum.net/api/public/otps`

- **Manual**: Click "Fetch OTPs Now" button on the dashboard (admin/manager only)
- **Cron**: Add to crontab for automatic fetching:
  ```
  * * * * * php /var/www/html/sigma_sms/ajax/cron_fetch.php
  ```
- Respects a **60-second** minimum interval between fetches

---

## REST API

Retrieve OTPs via API token:

```
GET /api/otps.php?token=YOUR_TOKEN&from=2026-01-01&to=2026-12-31
```

**Optional parameters:**
- `from` / `to` — date range (YYYY-MM-DD)
- `service` — filter by service name
- `country` — filter by country code (e.g. MM)
- `number` — filter by phone number
- `page` / `limit` — pagination (default limit: 100, max: 500)

**Response:**
```json
{
  "status": "success",
  "total": 42,
  "page": 1,
  "limit": 100,
  "data": [
    {
      "number": "+959661902830",
      "service": "viber",
      "country": "MM",
      "otp": "685102",
      "message": "Your viber verification code is: 685102",
      "received_at": "2026-04-27 12:36:57",
      "rate": "0.005500",
      "profit": "0.005500"
    }
  ]
}
```

Generate your token at: `Profile & API Token` page in the panel.

---

## Security Notes

- All queries use **PDO prepared statements**
- Passwords hashed with `password_hash()` (bcrypt)
- Session-based authentication with role checks on every page
- CSRF token protection on all forms
- API tokens are 64-char hex random strings
- Change the default admin password immediately after install
- Delete `install.php` after installation
- Use HTTPS in production

---

## License

MIT — Free to use and modify.
