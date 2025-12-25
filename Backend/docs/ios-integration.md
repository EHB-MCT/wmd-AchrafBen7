# Web Analytics Integratiegids (NIOS Frontend)

## 1. Doel

Deze versie vervangt de iOS-integratie door een webinterface die het bestaande analytics-dashboard voedt. De map `frontend/` bevat een homepage die events naar de `/api/*`-endpoints van de Laravel-backend stuurt.

## 2. Snel starten

1. Start backend + infra: `docker compose up -d` in de repo-root.
2. Start de Vite-frontend:
   - `cd frontend`
   - `npm install`
   - `npm run dev`

> Configureer `frontend/.env` met `VITE_API_BASE` en `VITE_MAPBOX_TOKEN` voor je start.

## 3. Gebruikte endpoints

| Doel | Methode + Route | Payload/Response |
| --- | --- | --- |
| Identificeer gebruiker | `POST /api/users/identify` | Body: `{ uid, device_type?, os_version?, app_version?, locale?, country? }` → `{ user_id }` |
| Start sessie | `POST /api/sessions/start` | Body: `{ user_id: uuid, platform: 'web', network_type?: 'wifi' }` → `{ session: { id, start_time, ... } }` |
| Eindig sessie | `POST /api/sessions/end` | Body: `{ session_id: uuid, duration_seconds: 420 }` |
| Event registreren | `POST /api/events` | Body: `{ session_id, user_id?, type: 'click'|'view'|'conversion'|..., name: 'cta.search', value?: {}, device_x?: 120, device_y?: 420, timestamp: ISO8601 }` |
| Zoekopdracht registreren | `POST /api/search-queries` | Body: `{ user_id, query, result_count, timestamp }` |
| Funnel stap registreren | `POST /api/funnels` | Body: `{ user_id, step, step_order, timestamp }` |
| Provider view registreren | `POST /api/provider-views` | Body: `{ user_id, provider_id, view_duration, timestamp }` |
| Dashboard data | `GET /api/stats/overview|events|sessions|search|conversions?range=24h|7d|30d&compare=1` | `compare=1` geeft vorige periode |
| Heatmap data | `GET /api/stats/heatmap?range=24h` | Retourneert `(device_x/device_y)` |
| Tijdlijn | `GET /api/stats/timeline?user_id=<uuid>&range=7d` | Volledige user journey |
| KPI export | `GET /api/export/kpis.csv` / `GET /api/export/kpis.pdf` | CSV of PDF export |

## 4. JS voorbeeld (frontend)

```js
const API_BASE = "http://localhost:8100";
const session = await fetch(`${API_BASE}/api/sessions/start`, {
  method: "POST",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify({ user_id, platform: "web" }),
}).then((res) => res.json());

await fetch(`${API_BASE}/api/events`, {
  method: "POST",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify({
    session_id: session.session.id,
    user_id,
    type: "click",
    name: "cta.search",
    value: { page: "home" },
    device_x: 240,
    device_y: 640,
    timestamp: new Date().toISOString(),
  }),
});
```

## 5. Events vanuit `frontend/`

- Sidebar navigatie (`nav.*`)
- Hero CTA's (`cta.search`, `cta.offer`)
- Service filters (`filter.*`)
- Reserveringen (`book.*`)
- Eerste page view (`page.home`)

Deze interacties voeden sessies, events, heatmap, funnel en zoekstatistieken.
