
# NÏOS Analytics Engine – Development Standards


NÏOS Analytics Engine is a behavioral analytics platform designed to collect real-time user interaction data from the companion iOS application.
The system identifies patterns, builds individual profiles, and generates insights to influence UI decisions and optimize user engagement

## 1️⃣ Project Stack
- **Frontend:** Vue.js 3 (Composition API)
- **Backend:** PHP 8+ (Laravel)
- **Database:** PostgreSQL 15+
- **API Communication:** RESTful JSON
- **Version Control:** Git

---

## 2️⃣ Code Style & Structure

### 🔹 Backend (PHP)
- PSR-12 coding standard (https://www.php-fig.org/psr/psr-12/)
- **Indentation:** 4 spaces
- **Naming convention:** camelCase for variables/functions, PascalCase for classes

/backend
├── /public → entry point (index.php)
├── /app → controllers, services, models
├── /routes → API routes
├── /config → DB connection, environment variables
├── /tests → unit & integration tests
└── composer.json

- **Security:**
- Always use prepared statements for DB queries
- Input validation before saving data

### 🔹 Frontend (Vue.js)
- Vue 3 + Vite setup
- **Naming conventions:**
- Components in PascalCase (e.g., `LineStatusCard.vue`)
- Props in camelCase
- **Folders:**

/frontend
├── /src
│ ├── /components → reusable UI components
│ ├── /views → dashboard pages
│ ├── /store → Pinia (state management)
│ ├── /services → API calls
│ ├── /assets → images, icons, styles
│ └── App.vue
└── vite.config.js

## 3️⃣ Database Standards (PostgreSQL)
- **Naming:** all lowercase, use underscores (e.g., `user_reports`, `alert_logs`)
- **Primary keys:** always `id SERIAL PRIMARY KEY`
- **Timestamps:** use `created_at` and `updated_at` (default `NOW()`)
- **Foreign keys:** always with `ON DELETE CASCADE`
- **Indexing:** add indexes on foreign keys and high-query columns
- **Sample structure:**
```sql
CREATE TABLE alerts (
    id SERIAL PRIMARY KEY,
    line_id INT NOT NULL,
    type VARCHAR(50),
    description TEXT,
    status VARCHAR(20) DEFAULT 'unconfirmed',
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

4) Git Workflow

Branches:

main → production-ready

dev → active development

feature/ → new feature (e.g. feature/dashboard-ui)

JSON format:

{
  "id": 42,
  "type": "delay",
  "status": "confirmed",
  "created_at": "2025-10-13T09:23:00Z"
}

5) Security & Privacy

HTTPS enforced for all requests

.env file used for secrets (DB credentials, API keys)

No user data stored without consent

Validation on all inputs (client & server side)

Protection against:

- SQL Injection 

- XSS 

- CSRF 
