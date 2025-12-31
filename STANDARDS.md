
# NIOS Analytics WMD - Development Standards

NIOS Analytics is a behavioral analytics platform that collects real-time user interaction data from a web frontend, builds user profiles, and influences UI decisions based on those profiles.

## 1. Project Stack
- Frontend (user): Vite + vanilla JavaScript (ES modules)
- Admin: Vue 3 + Inertia (Laravel)
- Backend: Laravel (PHP 8.3)
- Database: PostgreSQL
- Cache: Redis
- API: REST JSON
- Docker: docker compose for local stack

## 2. Code Style and Structure

### Backend (Laravel, PHP)
- PSR-12 coding standard.
- Indentation: 4 spaces.
- Naming: camelCase for methods/vars, PascalCase for classes, snake_case for DB columns.
- Validation happens in controllers before persistence.
- Services and repositories contain business logic.

Structure (actual):
```
Backend/
├── app/                 # Controllers, Services, DTOs, Models
├── database/            # Migrations, seeders
├── routes/              # api.php + web.php
├── resources/           # Inertia Vue admin pages
└── public/              # entry point + built assets
```

### Frontend (Vite, JS)
- ES modules, no framework for the user frontend.
- UI hooks use data-attributes (e.g. data-event, data-view-section).
- Shared styles in `frontend/src/styles.css`.

Structure (actual):
```
frontend/
├── index.html
├── src/
│   ├── modules/         # tracking, analytics, influence
│   ├── data/            # providers data
│   ├── config/          # env config
│   └── styles.css
```

## 3. Data and API Conventions
- UUID primary keys for users, sessions, events, insights.
- snake_case for DB columns.
- API timestamps are ISO 8601 strings.
- Payload validation uses Laravel validators before writes.
- Metadata fields are bounded and sanitized in controllers.

## 4. Git Workflow
- `main` is the stable branch.
- Work is done on `feature/*` branches and merged into `main`.
- Merge commits preserved for feature grouping.

## 5. Security and Hygiene
- `.env` files are not committed.
- Inputs are validated server-side.
- Only required metadata is stored; no external API keys needed.

## 6. Documentation
- `README.md` for setup and usage.
- `REPORT.md` for findings and shortcomings.
- `SOURCES.md` for sources and AI conversations.
