# CLAUDE.md — Feedback Survey Project

## Project Overview

IT Technical Support Service feedback collection and analytics system. Allows employees to rate IT support agents via a public survey form, with an admin dashboard for management and analytics.

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
- `AgentResource` — Manage IT support agents (CRUD, active/inactive toggle)
- `QuestionResource` — Manage survey questions (rating or text type, sort order)
- `FeedbackResource` — View submitted feedback (read + delete, filter by agent)

### Dashboard Widgets
- `FeedbackStatsOverview` — KPI cards (total, avg rating, satisfaction rate, etc.)
- `RatingTrendChart` — 30-day daily average rating line chart
- `AgentPerformanceTable` — Per-agent leaderboard sorted by feedback volume

### Database Schema
- `agents` — name, employee_id, department, email, is_active
- `questions` — question_text, type (rating|text), sort_order, is_active
- `feedbacks` — respondent_name, position, overall_rating
- `feedback_responses` — feedback_id, question_id, rating_value (1–5), text_value
- `feedback_agent` — pivot table (many-to-many between feedbacks and agents)

### Key Models & Relationships
- `Feedback` belongsToMany `Agent` (via `feedback_agent`)
- `Feedback` hasMany `FeedbackResponse`
- `Question` hasMany `FeedbackResponse`
- `FeedbackResponse` belongsTo `Feedback` + `Question`

## Important Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/FeedbackController.php` | Core feedback submission logic |
| `resources/views/feedback/form.blade.php` | Public form (740-line standalone, dark theme) |
| `resources/views/feedback/thanks.blade.php` | Post-submission confirmation |
| `app/Models/Feedback.php` | Feedback model + overall rating calculation |
| `app/Models/Agent.php` | Agent model + avg rating calculation |
| `app/Filament/Resources/` | All Filament admin resources |
| `app/Filament/Widgets/` | Dashboard analytics widgets |
| `database/migrations/` | All 9 migration files |

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

- The feedback form (`form.blade.php`) is a 740-line standalone HTML/CSS/JS file with embedded styles and vanilla JS — it is NOT using Blade components or Livewire.
- The form uses a dark theme with cyan accents; CSS custom properties control theming.
- `overall_rating` on `feedbacks` is computed as the average of all `rating`-type question responses at submission time.
- The admin panel uses Filament's built-in auth — access via `/admin`.
- SQLite database file lives at `database/database.sqlite`.
