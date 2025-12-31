# NIOS Analytics WMD

## Wat is dit?
Een lokale WMD-analytics stack met:
- Een user-facing frontend die gedrag verzamelt en subtiel beïnvloedt.
- Een backend (Laravel) die data valideert, opslaat en analyseert.
- Een admin dashboard om data te bekijken en trends te volgen.

## Snel starten (vereist Docker)
1) Maak een `.env` op basis van de template:
```
cp .env.template .env
```

2) Build en start alles:
```
docker compose up --build
```

3) Run migrations + seed data:
```
docker compose exec app php artisan migrate --seed
```

## URLs
- User frontend: `http://localhost:5173`
- Admin dashboard: `http://localhost:8100/dashboard`

## Belangrijk
- Alles draait lokaal via Docker.
- Er zijn geen externe API keys nodig.
