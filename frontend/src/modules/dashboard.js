export function initDashboard() {
  const kpiContainer = document.querySelector("[data-dashboard-kpis]");
  const realtimeContainer = document.querySelector("[data-dashboard-realtime]");
  const chartContainer = document.querySelector("[data-dashboard-activity]");
  const updatedEl = document.querySelector("[data-dashboard-updated]");
  const signalEl = document.querySelector("[data-dashboard-signal]");

  if (!kpiContainer) {
    return;
  }

  const refresh = async () => {
    const metrics = await fetchJson("/api/frontend-metrics");

    renderKpis(kpiContainer, metrics);
    renderRealtime(realtimeContainer, metrics.last_events ?? []);
    renderChart(chartContainer, metrics.activity ?? []);

    if (signalEl) {
      signalEl.textContent = String(metrics.total_events ?? 0);
    }

    if (updatedEl) {
      updatedEl.textContent = `Update: ${new Date().toLocaleTimeString("nl-NL")}`;
    }
  };

  refresh();
  setInterval(refresh, 10000);
}

async function fetchJson(url) {
  const response = await fetch(url);
  if (!response.ok) {
    throw new Error(`Request failed: ${response.status}`);
  }
  return response.json();
}

function renderKpis(container, metrics) {
  const rows = [
    { label: "Actieve sessies", value: metrics.active_sessions ?? 0 },
    { label: "Totale events", value: metrics.total_events ?? 0 },
    { label: "Top event", value: metrics.top_event ?? "-" },
    { label: "Gemiddelde duur", value: metrics.average_duration ?? "0m 00s" },
    { label: "Frontend signaal", value: metrics.total_events ?? 0 },
  ];

  container.innerHTML = rows
    .map(
      (kpi) => `
        <div class="dashboard-card">
          <p>${kpi.label}</p>
          <h3>${kpi.value}</h3>
        </div>
      `,
    )
    .join("");
}

function renderRealtime(container, items) {
  if (!container) {
    return;
  }

  if (!items.length) {
    container.innerHTML = "<li>Geen recente events</li>";
    return;
  }

  container.innerHTML = items
    .slice(0, 6)
    .map(
      (item) => `
        <li>
          <span>${item.name ?? "event"}</span>
          <span>${item.time_ago ?? ""}</span>
        </li>
      `,
    )
    .join("");
}

function renderChart(container, activity) {
  if (!container) {
    return;
  }

  const items = Array.isArray(activity) ? activity : [];
  const normalized = items.map((item, index) => {
    if (typeof item === "number") {
      return { count: item, date: `Dag ${index + 1}` };
    }
    return {
      count: Number(item.count ?? 0),
      date: String(item.date ?? `Dag ${index + 1}`),
    };
  });

  const counts = normalized.map((item) => item.count);
  const max = Math.max(...counts, 1);

  const bars = normalized
    .map((item) => {
      const height = Math.max(8, Math.round((item.count / max) * 160));
      const label = formatShortDate(item.date);
      return `
        <div class="dashboard-bar" style="height:${height}px" title="${label}: ${item.count}">
          <span>${item.count}</span>
        </div>
      `;
    })
    .join("");

  const labels = normalized
    .map((item) => `<span>${formatShortDate(item.date)}</span>`)
    .join("");

  container.innerHTML = `
    <div class="dashboard-bars">${bars}</div>
    <div class="dashboard-labels">${labels}</div>
  `;
}

function formatShortDate(value) {
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) {
    return value;
  }
  return parsed.toLocaleDateString("nl-NL", {
    weekday: "short",
    day: "2-digit",
    month: "short",
  });
}
