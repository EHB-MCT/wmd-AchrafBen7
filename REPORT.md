# Report – NIOS Analytics WMD

## Doel
Een user-level analytics systeem bouwen dat gedrag verzamelt, profileert en de UI subtiel bijstuurt op basis van die profielen.

## Uitkomsten
- Frontend stuurt events (clicks, hovers, scroll depth, input timing, file metadata) naar de backend.
- Backend slaat sessies, events, funnels, searches, provider-views en insights op in PostgreSQL.
- Admin dashboard visualiseert KPI's, heatmap, funnels, timeline en realtime events.
- UI wordt beïnvloed door profielsignalen (promoties, CTA's, card highlight).

## Wat wordt verzameld
- Identiteit: `uid`, device/platform, locale, user agent.
- Gedrag: clicks, hover, scroll depth, visibility, funnels, search queries.
- Context: viewport/screen, time-to-first-input, input length, file metadata.
- Sessies: start/einde, duur, realtime heartbeat.

## Invloed op de gebruiker
- Promoties worden urgenter bij churn-risico.
- Primary CTA wordt agressiever bij hoge booking score.
- Premium kaart wordt uitgelicht bij premium-tendens.
- Copy past zich aan bij hoge twijfel of nachtgebruik.

## Shortcomings / pitfalls
- Data is ruisgevoelig (adblockers, privacy settings, offline events).
- UID is browser-based; shared devices vervuilen profielen.
- Scroll/hover events kunnen over- of onderrapporteren.
- Insights zijn heuristiek en kunnen bias introduceren.
- UX beïnvloeding kan manipulatief aanvoelen zonder transparantie.

## Wat ik leerde
- Kleine UI-aanpassingen kunnen een grote impact hebben op gedrag.
- Datakwaliteit bepaalt de waarde van dashboards meer dan visualisatie.
- Transparantie en ethiek blijven cruciaal, zelfs in een "WMD"-context.
