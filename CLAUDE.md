# CLAUDE.md — Feedback Survey Project

## Project Overview

HR Personnel Service feedback collection and analytics system. Allows employees to rate HR personnel via a public survey form, with an admin dashboard for management and analytics.

The "Agent" model/table names are retained internally for backwards compatibility with existing data; all user-facing labels say "HR Personnel".

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12 |
| Admin Panel | Filament 3.3 |
| Database | SQLite (dev), Eloquent ORM |
| Frontend | Blade templates, Tailwind CSS 4, Vite 7 |
| Testing | PHPUnit 11 |
| Code Quality | Laravel Pint |
| Container | Docker (compose.yaml) |

## Key Architecture

### Public Routes (`routes/web.php`)
- `GET /` → Feedback form (`FeedbackController@show`)
- `POST /feedback` → Submit feedback (`FeedbackController@store`)
- `GET /feedback/thanks` → Thank you page (`FeedbackController@thanks`)

### Admin Panel (`/admin`) — Filament Resources
- `AgentResource` — Manage HR personnel (CRUD, active/inactive toggle) — labeled "HR Personnel" in UI
- `QuestionResource` — Manage survey questions (rating or text type, sort order)
- `FeedbackResource` — View submitted feedback (read + delete, filter by HR personnel)
- `Settings` page — Admin-configurable branding: primary color (default orange `#f97316`) + brand name. Stored in the `settings` key/value table.

### Dashboard Widgets
- `FeedbackStatsOverview` — KPI cards (total, avg rating, satisfaction rate, etc.)
- `RatingTrendChart` — 30-day daily average rating line chart
- `AgentPerformanceTable` — Per-HR-personnel leaderboard sorted by feedback volume

### Database Schema
- `agents` — name, employee_id, department, email, is_active (stores HR personnel; table name retained)
- `questions` — question_text, type (rating|text), sort_order, is_active
- `feedbacks` — respondent_name, position, overall_rating
- `feedback_responses` — feedback_id, question_id, rating_value (1–5), text_value
- `feedback_agent` — pivot table (many-to-many between feedbacks and HR personnel; table name retained)
- `settings` — key/value table for branding (`primary_color`, `brand_name`)

### Key Models & Relationships
- `Feedback` belongsToMany `Agent` (via `feedback_agent`)
- `Feedback` hasMany `FeedbackResponse`
- `Question` hasMany `FeedbackResponse`
- `FeedbackResponse` belongsTo `Feedback` + `Question`
- `Setting` — simple key/value store with cached `Setting::get()` / `Setting::set()`.

## Important Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/FeedbackController.php` | Core feedback submission logic |
| `resources/views/feedback/form.blade.php` | Public form (standalone, dark theme, dynamically themed) |
| `resources/views/feedback/thanks.blade.php` | Post-submission confirmation |
| `app/Models/Feedback.php` | Feedback model + overall rating calculation |
| `app/Models/Agent.php` | HR personnel model + avg rating calculation |
| `app/Models/Setting.php` | Key/value branding store |
| `app/Support/Branding.php` | Reads/normalizes the branding (color + name) from settings |
| `app/Filament/Pages/Settings.php` | Admin branding/appearance page |
| `app/Filament/Resources/` | All Filament admin resources |
| `app/Filament/Widgets/` | Dashboard analytics widgets |
| `database/migrations/` | All migration files |

## Development Commands

```bash
# Install and set up from scratch
composer setup

# Start all dev processes (server + queue + logs + vite hot reload)
composer dev

# Run tests
composer test

# Format code
./vendor/bin/pint
```

## Development Notes

- The feedback form (`form.blade.php`) is a large standalone HTML/CSS/JS file with embedded styles and vanilla JS — it is NOT using Blade components or Livewire.
- The form uses a dark theme. Primary color (default orange) is admin-configurable via the Settings page and injected as CSS custom properties (`--primary`, `--primary-dark`, `--primary-light`, `--primary-rgb`).
- `App\Support\Branding` is the helper that reads `primary_color` and `brand_name` from the `settings` table (with cache + safe defaults). Use `Branding::primaryHex()`, `Branding::primaryRgb()`, `Branding::primaryDark()`, `Branding::primaryLight()`, `Branding::brandName()`, `Branding::filamentPalette()`.
- Filament panel primary color is generated from the configured hex via `\Filament\Support\Colors\Color::hex()` in both `AdminPanelProvider` and `AgentPanelProvider`.
- `overall_rating` on `feedbacks` is computed as the average of all `rating`-type question responses at submission time.
- The admin panel uses Filament's built-in auth — access via `/admin`.
- SQLite database file lives at `database/database.sqlite`.
