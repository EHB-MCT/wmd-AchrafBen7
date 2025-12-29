# NIOS Analytics WMD

## Quick Start
1) Copy the template and adjust if needed:
```
cp .env.template .env
```

2) Build and run everything:
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

## Notes
- Everything runs locally in Docker.
- No external API keys required.
