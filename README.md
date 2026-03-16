# IT Feedback Survey

A feedback collection and analytics system for IT Technical Support. Employees can rate IT support agents through a public survey form, while admins manage agents, questions, and view performance analytics via a dashboard.

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+ & npm
- SQLite (included with PHP)

## Setup

### Quick Setup (recommended)

```bash
composer setup
```

This single command will:
1. Install PHP dependencies
2. Create `.env` from `.env.example`
3. Generate the application key
4. Run database migrations
5. Install Node.js dependencies
6. Build frontend assets

### Manual Setup

If you prefer to run each step individually:

```bash
# 1. Install PHP dependencies
composer install

# 2. Create environment file
cp .env.example .env

# 3. Generate application key
php artisan key:generate

# 4. Create the SQLite database file
touch database/database.sqlite

# 5. Run migrations
php artisan migrate

# 6. Install frontend dependencies
npm install

# 7. Build frontend assets
npm run build
```

## Create Admin User

After setup, create an admin account to access the dashboard:

```bash
php artisan make:filament-user
```

Follow the prompts to set a name, email, and password.

## Running the Application

```bash
composer dev
```

This starts all development processes concurrently:
- **Laravel server** — http://localhost:8000
- **Queue listener** — processes background jobs
- **Log viewer** — streams application logs
- **Vite** — hot-reloads frontend assets

## Usage

| URL | Description |
|-----|-------------|
| `http://localhost:8000` | Public feedback form |
| `http://localhost:8000/feedback/thanks` | Post-submission confirmation |
| `http://localhost:8000/admin` | Admin dashboard (requires login) |

### Admin Dashboard Features

- **Agents** — Add and manage IT support agents
- **Questions** — Create survey questions (rating 1–5 or free text), set display order
- **Feedback** — View all submitted responses, filter by agent
- **Dashboard** — KPI overview, 30-day rating trend chart, per-agent leaderboard

## Running Tests

```bash
composer test
```

## Code Formatting

```bash
./vendor/bin/pint
```

## Tech Stack

- **Backend:** Laravel 12
- **Admin Panel:** Filament 3.3
- **Database:** SQLite
- **Frontend:** Blade, Tailwind CSS 4, Vite 7
