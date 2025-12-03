# iOS Analytics Integration Guide

## 1. Préparer l'environnement et charger les mock data

1. `cd Backend`
2. Copier votre fichier d'environnement (`cp .env.example .env`) puis ajuster `DB_` si nécessaire.
3. Installer les dépendances backend : `composer install` et `npm install && npm run build` si l'UI doit être recompilée.
4. Lancer l'infrastructure (`docker compose up -d` à la racine du repo) ou votre stack locale.
5. Appliquer le schéma + jeux de données réalistes : `php artisan migrate:fresh --seed`.

La commande de seed appelle `MockAnalyticsSeeder` qui génère users, sessions, events, funnels, provider views et search queries cohérents. Les graphiques Vue consomment ensuite ces données via les endpoints `/api/stats/...`.

## 2. Endpoints utiles

| Objectif | Méthode + Route | Payload/Réponse |
| --- | --- | --- |
| Démarrer une session utilisateur | `POST /api/sessions/start` | Body : `{ user_id: uuid, platform: 'iOS', network_type?: 'wifi', battery_level?: 80 }` → Retourne `{ session: { id, start_time, ... } }` |
| Clôturer une session | `POST /api/sessions/end` | Body : `{ session_id: uuid, duration_seconds: 420 }` |
| Tracer un évènement | `POST /api/events` | Body : `{ session_id, user_id?, type: 'click'|'view'|'conversion'|..., name: 'booking.completed', value?: {}, device_x?: 120, device_y?: 420, timestamp: ISO8601 }` |
| Datas pour l'UI | `GET /api/stats/overview|events|sessions|search|conversions?range=24h|7d|30d&compare=1` | `compare=1` renvoie la période précédente pour activer le mode « Compare Range ». |
| Heatmap interactions | `GET /api/stats/heatmap?range=24h` | Retourne la liste des points `(device_x/device_y)` + intensité pour dessiner la carte thermique. |
| Timeline utilisateur | `GET /api/stats/timeline?user_id=<uuid>&range=7d` | Permet d'afficher un parcours complet (session_start → events → conversion). |
| Export KPI | `GET /api/export/kpis.csv` / `GET /api/export/kpis.pdf` | Génère un export (CSV ou PDF) avec sessions, conversions, top pages, search performance. |

> Tous les timestamps attendent un format ISO 8601 (`Date().ISO8601Format()` côté Swift) afin que Carbon puisse les parser.

## 3. Exemple Swift léger

```swift
struct AnalyticsClient {
    let baseURL = URL(string: "https://analytics.nios.app")!
    let decoder = JSONDecoder()

    func startSession(userId: UUID, platform: String = "iOS") async throws -> UUID {
        var request = URLRequest(url: baseURL.appending(path: "/api/sessions/start"))
        request.httpMethod = "POST"
        request.addValue("application/json", forHTTPHeaderField: "Content-Type")
        let body: [String: Any] = [
            "user_id": userId.uuidString,
            "platform": platform,
            "network_type": "5g",
            "battery_level": 90
        ]
        request.httpBody = try JSONSerialization.data(withJSONObject: body)

        let (data, _) = try await URLSession.shared.data(for: request)
        let payload = try decoder.decode(StartSessionResponse.self, from: data)
        return payload.session.id
    }

    func trackEvent(_ event: AnalyticsEvent, sessionId: UUID, userId: UUID?) async throws {
        var request = URLRequest(url: baseURL.appending(path: "/api/events"))
        request.httpMethod = "POST"
        request.addValue("application/json", forHTTPHeaderField: "Content-Type")
        let body: [String: Any?] = [
            "session_id": sessionId.uuidString,
            "user_id": userId?.uuidString,
            "type": event.type,
            "name": event.name,
            "value": event.metadata,
            "device_x": event.position?.x,
            "device_y": event.position?.y,
            "timestamp": ISO8601DateFormatter().string(from: Date())
        ]
        request.httpBody = try JSONSerialization.data(withJSONObject: body.compactMapValues { $0 })
        _ = try await URLSession.shared.data(for: request)
    }

    func endSession(id: UUID, duration: Int) async throws {
        var request = URLRequest(url: baseURL.appending(path: "/api/sessions/end"))
        request.httpMethod = "POST"
        request.addValue("application/json", forHTTPHeaderField: "Content-Type")
        request.httpBody = try JSONSerialization.data(withJSONObject: [
            "session_id": id.uuidString,
            "duration_seconds": duration
        ])
        _ = try await URLSession.shared.data(for: request)
    }
}
```

`AnalyticsEvent` peut encapsuler `type`, `name`, `metadata` (payload libre) et optionnellement la position (pour la heatmap). Dans votre application :

1. Appelez `startSession` à l'ouverture de l'app et conservez l'`id` en mémoire.
2. À chaque interaction notable (vue d'écran, tap sur CTA, recherche, réservation), appelez `trackEvent` avec un `timestamp` précis.
3. Lorsque l'utilisateur quitte ou passe en background prolongé, appelez `endSession` et transmettez la durée réelle.

En gardant le même `session_id` pour toute la durée d'utilisation (ou jusqu'à expiration), les tableaux de bord web reflètent en quasi temps réel les comportements captés sur iOS.

## 4. Nouveautés côté dashboard

- **Heatmap d'interaction** : les événements envoyés avec `device_x` / `device_y` alimentent `/api/stats/heatmap`. Chaque `POST /api/events` contenant ces valeurs positionne un point dans la vue Heatmap.
- **User Timeline** : en envoyant `session_id` cohérent + `type`/`name`, l'endpoint `/api/stats/timeline` reconstitue automatiquement `session_start → view → click → scroll → conversion`.
- **Export KPI** : utilisez les routes `/api/export/kpis.csv|pdf` avec `?range=30d` pour récupérer un snapshot offline (utile pour reporting hebdo ou mobile share sheet).
- **Compare Range** : tous les endpoints `overview/events/sessions` acceptent `compare=1` pour superposer la période précédente. Sur iOS, vous pouvez réutiliser ces données pour afficher des badges « vs last week ».
