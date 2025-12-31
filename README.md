# NIOS Analytics WMD

Weapon of Math Destruction-project dat elke interactie logt, gebruikers profielt en het admin-dashboard realtime voedt. Analyses over bias, tekortkomingen en AI-hulp staan in `REPORT.md` en `SOURCES.md`.

---

## 1. Doel
NIOS Analytics verzamelt zoveel mogelijk gebruikersinteracties om een gedragsprofiel op te bouwen. Dat profiel beïnvloedt copy, promoties en CTA's in de user-frontend en ondersteunt admin-beslissingen.

## 2. Opstarten (Docker)
1) Environment klaarzetten:
```
cp .env.template .env
```
2) Stack builden en starten:
```
docker compose up --build
```

3) Migrations + seed data:
```
docker compose exec app php artisan migrate --seed
```

## 3. URLs
- User frontend: `http://localhost:5173`
- Admin dashboard: `http://localhost:8100/dashboard`

## 4. Structuur
```
wmd-AchrafBen7/
├── docker-compose.yml
├── Backend/ (Laravel + Inertia)
│   ├── app/                 # Controllers, Services, DTOs, Models
│   ├── database/            # migrations + seeders
│   ├── resources/js         # admin dashboard (Vue)
│   └── routes               # api + web routes
└── frontend/ (Vite, vanilla JS)
    ├── src/modules          # tracking + influence
    ├── src/data             # providers data
    ├── src/config           # env config
    └── src/styles.css
```

## 5. Belangrijkste flows
| Flow | Beschrijving |
| --- | --- |
| **Tracking layer** | Hovers, clicks, scroll depth, input-timing, file metadata en heartbeats worden gelogd. |
| **Profiel & nudging** | Profielsignalen sturen promo's, CTA's en uitgelichte kaarten. |
| **Admin dashboard** | Overzicht, sessies, events en tijdlijn met filters en realtime updates. |

## 6. Opschoning en datakwaliteit
- Laravel validators controleren elke payload voordat data wordt opgeslagen.
- Metadata wordt beperkt in grootte en type (arrays, strings, integers).
- Sessies worden automatisch afgesloten via `sessions/end`.

## 7. Docker services
| Service | Beschrijving |
| --- | --- |
| `app` | Laravel backend + build van admin assets |
| `nginx` | Reverse proxy op poort 8100 |
| `postgres` | Persistente database |
| `redis` | Cache/queue |
| `frontend` | Vite dev server op poort 5173 |

Stoppen:
```
docker compose down
```

## 8. API-overzicht (kern)
- `POST /api/users/identify` (UID + device/locale)
- `POST /api/sessions/start` / `POST /api/sessions/end`
- `POST /api/events` (click, hover, scroll, etc.)
- `GET /api/stats/*` (admin visualisaties)

## 9. Tests en kwaliteit
- Geen automatische tests toegevoegd.
- Code volgt PSR-12 (backend) en ES module-structuur (frontend).

## 10. Notes
- Alles draait lokaal via Docker.
- Geen externe API-keys nodig.
