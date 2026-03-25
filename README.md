# IT Feedback Survey

IT Technical Support Service feedback collection and analytics system. Employees rate IT support agents via a public survey form; admins manage agents, questions, and view performance analytics via a dashboard.

**Production URL:** https://itsurvey.bfcgroup.ph

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12 |
| Admin Panel | Filament 3.3 |
| Database | SQLite |
| Frontend | Blade, Tailwind CSS 4, Vite 7 |
| Web Server | Nginx + PHP 8.4-FPM |
| Bot Protection | Cloudflare Turnstile |
| Backups | spatie/laravel-backup → Google Drive |

---

## Production Setup

### 1. System Requirements

- PHP 8.4+ with extensions: `pdo`, `pdo_sqlite`, `sqlite3`, `mbstring`, `xml`, `curl`, `zip`, `gd`, `intl`
- Composer 2+
- Node.js 20+ & npm 10+
- Nginx
- Git

```bash
# Install PHP 8.4 and required extensions
sudo apt install php8.4-fpm php8.4-cli php8.4-sqlite3 php8.4-mbstring \
    php8.4-xml php8.4-curl php8.4-zip php8.4-gd php8.4-intl -y
```

### 2. Clone the Repository

```bash
cd /var/www
sudo git clone <repository-url> it-feedback-survey
sudo chown -R root:www-data it-feedback-survey
sudo chmod -R 755 it-feedback-survey
sudo chmod -R 775 it-feedback-survey/storage it-feedback-survey/bootstrap/cache
```

### 3. Install Dependencies

```bash
cd /var/www/it-feedback-survey

sudo composer install --no-dev --optimize-autoloader
sudo npm install
sudo npm run build
```

### 4. Environment Configuration

```bash
sudo cp .env.example .env
sudo nano .env
```

Set the following values:

```env
APP_NAME="IT Feedback Survey"
APP_ENV=production
APP_KEY=                        # generated in step 5
APP_DEBUG=false
APP_URL=https://itsurvey.bfcgroup.ph

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=sqlite

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

# Cloudflare Turnstile (https://dash.cloudflare.com → Turnstile)
TURNSTILE_SITE_KEY=your_site_key_here
TURNSTILE_SECRET_KEY=your_secret_key_here

# Google Drive Backup
GOOGLE_DRIVE_CLIENT_ID=
GOOGLE_DRIVE_CLIENT_SECRET=
GOOGLE_DRIVE_REFRESH_TOKEN=
GOOGLE_DRIVE_FOLDER_ID=
GOOGLE_DRIVE_FOLDER_PATH=Backup/PDE

# Admin notification email (for backup alerts)
ADMIN_NOTIFICATION_EMAIL=admin@example.com
```

### 5. Application Key & Database

```bash
# Generate app key
sudo php artisan key:generate

# Create the SQLite database file
sudo touch database/database.sqlite
sudo chown www-data:www-data database/database.sqlite

# Run migrations
sudo php artisan migrate --force
```

### 6. Create the First Admin User

```bash
sudo php artisan make:filament-user
```

Follow the prompts for name, email, and password.

### 7. Optimize for Production

```bash
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
sudo php artisan event:cache
sudo php artisan icons:cache
```

### 8. Nginx Configuration

Create `/etc/nginx/sites-available/it-feedback-survey`:

```nginx
server {
    listen 80 default_server;
    listen [::]:80 default_server;

    server_name itsurvey.bfcgroup.ph _;

    root /var/www/it-feedback-survey/public;
    index index.php;

    # Cloudflare real IP passthrough
    real_ip_header CF-Connecting-IP;
    set_real_ip_from 103.21.244.0/22;
    set_real_ip_from 103.22.200.0/22;
    set_real_ip_from 103.31.4.0/22;
    set_real_ip_from 104.16.0.0/13;
    set_real_ip_from 104.24.0.0/14;
    set_real_ip_from 108.162.192.0/18;
    set_real_ip_from 131.0.72.0/22;
    set_real_ip_from 141.101.64.0/18;
    set_real_ip_from 162.158.0.0/15;
    set_real_ip_from 172.64.0.0/13;
    set_real_ip_from 173.245.48.0/20;
    set_real_ip_from 188.114.96.0/20;
    set_real_ip_from 190.93.240.0/20;
    set_real_ip_from 197.234.240.0/22;
    set_real_ip_from 198.41.128.0/17;
    set_real_ip_from 2400:cb00::/32;
    set_real_ip_from 2606:4700::/32;
    set_real_ip_from 2803:f800::/32;
    set_real_ip_from 2405:b500::/32;
    set_real_ip_from 2405:8100::/32;
    set_real_ip_from 2a06:98c0::/29;
    set_real_ip_from 2c0f:f248::/32;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/it-feedback-survey /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 9. Queue Worker (systemd)

The app uses a database queue for background jobs (backups, notifications). Create a systemd service:

```bash
sudo nano /etc/systemd/system/it-survey-worker.service
```

```ini
[Unit]
Description=IT Feedback Survey Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/it-feedback-survey/artisan queue:work --sleep=3 --tries=3 --timeout=90

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable it-survey-worker
sudo systemctl start it-survey-worker
```

### 10. Scheduled Backups (cron)

The app uses `spatie/laravel-backup` to back up the SQLite database to Google Drive. Add the Laravel scheduler to cron:

```bash
sudo crontab -e -u www-data
```

Add:
```
* * * * * php artisan schedule:run >> /dev/null 2>&1
```

To run a manual backup at any time:

```bash
php artisan backup:run
```

#### Google Drive Backup Setup

1. Create a project in [Google Cloud Console](https://console.cloud.google.com)
2. Enable the **Google Drive API**
3. Create OAuth 2.0 credentials (Desktop app type)
4. Visit `https://itsurvey.bfcgroup.ph/refresh-token` while logged into the server to complete the OAuth flow and obtain the refresh token
5. Copy the `refresh_token` value from the JSON response into `.env`

---

## Deploying Updates

```bash
cd /var/www/it-feedback-survey

# Pull latest code
sudo git pull origin master

# Install/update dependencies
sudo composer install --no-dev --optimize-autoloader
sudo npm install
sudo npm run build

# Run any new migrations
sudo php artisan migrate --force

# Re-cache optimized config
sudo php artisan optimize

# Restart queue worker to pick up code changes
sudo systemctl restart it-survey-worker
```

---

## Application Structure

### Public Routes

| URL | Description |
|-----|-------------|
| `/` | Cloudflare Turnstile verification page |
| `/form` | IT support feedback survey form |
| `/feedback/thanks` | Post-submission confirmation |

### Admin Panels

| URL | Description |
|-----|-------------|
| `/admin` | Super-admin dashboard (agents, questions, all feedback, analytics) |
| `/agent` | Agent panel (own feedback view only) |

### Admin Dashboard Features

- **Agents** — Add/manage IT support agents (active/inactive)
- **Questions** — Create rating (1–5) or free-text survey questions, set display order
- **Issue Types** — Manage issue/request categories shown on the form
- **Locations** — Manage location/department options
- **Feedback** — View all submissions, filter by agent
- **Analytics** — KPI cards, 30-day rating trend, per-agent leaderboard, issue breakdown charts

### Security

- **Cloudflare Turnstile** — Bot challenge shown before the public form and on both admin login pages
- **Rate limiting** — `POST /feedback` throttled to 5 requests/minute per IP; Turnstile verify throttled to 10/minute
- **Cloudflare proxy** — Real visitor IPs forwarded via `CF-Connecting-IP` header (configured in Nginx)

---

## Development Setup

```bash
# Quick install (from scratch)
composer setup

# Start all dev processes (server + queue + logs + Vite HMR)
composer dev
```

```bash
# Run tests
composer test

# Format code
./vendor/bin/pint
```

---

## Database

SQLite database file: `database/database.sqlite`

| Table | Purpose |
|-------|---------|
| `users` | Admin and agent accounts |
| `agents` | IT support agent profiles |
| `questions` | Survey questions (rating or text) |
| `issue_types` | Issue/request categories |
| `locations` | Location/department options |
| `feedbacks` | Submitted feedback records |
| `feedback_responses` | Per-question responses |
| `feedback_agent` | Pivot — feedback ↔ agents |
| `feedback_location` | Pivot — feedback ↔ locations |
