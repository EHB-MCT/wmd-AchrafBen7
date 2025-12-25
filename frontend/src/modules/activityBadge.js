export function initActivityBadge() {
  const badge = document.querySelector("[data-activity-badge]");
  const countEl = document.querySelector("[data-activity-count]");
  const dotEl = document.querySelector("[data-activity-dot]");

  if (!badge || !countEl || !dotEl) {
    return;
  }

  const formatter = new Intl.NumberFormat("nl-NL");

  const refresh = async () => {
    try {
      const response = await fetch("/api/frontend-metrics");
      if (!response.ok) {
        throw new Error(`Request failed: ${response.status}`);
      }
      const metrics = await response.json();
      const active = Number(metrics.active_sessions ?? 0);

      countEl.textContent = formatter.format(active);

      dotEl.classList.remove("is-idle", "is-active", "is-busy");
      if (active >= 5) {
        dotEl.classList.add("is-busy");
        dotEl.title = "Hoge activiteit";
      } else if (active > 0) {
        dotEl.classList.add("is-active");
        dotEl.title = "Actief";
      } else {
        dotEl.classList.add("is-idle");
        dotEl.title = "Geen activiteit";
      }
    } catch (error) {
      dotEl.classList.remove("is-active", "is-busy");
      dotEl.classList.add("is-idle");
      dotEl.title = "Geen data";
    }
  };

  refresh();
  setInterval(refresh, 10000);
}
