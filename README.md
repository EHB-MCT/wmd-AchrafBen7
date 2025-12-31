# 🚗 NIOS Analytics WMD

Weapon of Math Destruction-project dat elke interactie logt, gebruikers profielt en het admin‑dashboard realtime voedt. De analyses over bias, tekortkomingen en AI‑hulp staan in `REPORT.md` en `SOURCES.md`.

---

## 1. Doel
NIOS Analytics verzamelt zoveel mogelijk gebruikersinteracties om een gedragsprofiel op te bouwen. Dat profiel beïnvloedt de copy, promoties en CTA’s in de user‑frontend en ondersteunt admin‑beslissingen.

## 2. Opstarten
1) **Environment klaarzetten**
```
cp .env.template .env
```

2) **Docker starten**
```
docker compose up --build
```

3) **Migrations + seed data**
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
├── Backend/ (Laravel)
│   ├── app/                 # controllers, services, models
│   ├── database/            # migrations + seeders
│   └── routes/              # api + web routes
└── frontend/ (Vite)
    ├── src/modules          # tracking + influence
    ├── src/data             # providers data
    └── src/styles.css
```

## 5. Belangrijkste flows
| Flow | Beschrijving |
| --- | --- |
| **Tracking** | Hovers, clicks, scroll depth, input‑timing, file metadata en heartbeats worden gelogd. |
| **Profiel & nudging** | Profielsignalen beïnvloeden promo’s, CTA’s en featured cards. |
| **Admin dashboard** | Overzicht, sessies, events en tijdlijn met filters en realtime updates. |

## 6. Docker services
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

## 7. Notes
- Alles draait lokaal via Docker.
- Geen externe API‑keys nodig.
