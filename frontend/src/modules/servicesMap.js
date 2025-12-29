import { serviceProviders } from "../data/providers.js";

const state = {
  mapEl: null,
  markers: [],
  filter: "offers",
  query: "",
  searchBound: false,
  searchTimer: null,
  bounds: null,
  context: {
    trackEvent: () => {},
    openDetailerModal: () => {},
    recordSearchQuery: () => {},
    recordProviderView: () => {},
    recordFunnelStep: () => {},
  },
};

export function setServicesContext(context) {
  state.context = context;
}

export function initServicesMap() {
  const container = document.getElementById("services-map");
  if (!container || state.mapEl) {
    return;
  }

  state.mapEl = container;
  container.classList.add("map-static");
  updateServiceMarkers();

  bindServicesSearch();
}

export function resizeServicesMap() {
  if (!state.mapEl) {
    return;
  }
  updateServiceMarkers();
}

export function setServiceFilter(filter) {
  if (!filter) {
    return;
  }

  state.filter = filter;
  document.querySelectorAll("[data-services-filter]").forEach((chip) => {
    chip.classList.toggle("is-active", chip.dataset.servicesFilter === filter);
  });

  updateServiceMarkers();
  state.context.trackEvent("click", `services.filter.${filter}`, { filter });
}

export function setServiceQuery(query) {
  state.query = query.toLowerCase().trim();
  updateServiceMarkers();
}

function bindServicesSearch() {
  if (state.searchBound) {
    return;
  }

  const searchInput = document.querySelector("[data-services-search]");
  if (!searchInput) {
    return;
  }

  searchInput.addEventListener("input", (event) => {
    setServiceQuery(event.target.value);
    state.context.trackEvent("search", "services.query", { query: state.query });

    if (state.searchTimer) {
      clearTimeout(state.searchTimer);
    }

    state.searchTimer = setTimeout(() => {
      state.context.recordSearchQuery(state.query, currentResultCount());
    }, 400);
  });

  state.searchBound = true;
}

function updateServiceMarkers() {
  if (!state.mapEl) {
    return;
  }

  state.markers.forEach((marker) => marker.remove());
  state.markers = [];

  const filtered = serviceProviders.filter((provider) => {
    const matchesFilter = provider.tags.includes(state.filter);
    const matchesQuery =
      !state.query ||
      `${provider.name} ${provider.city} ${provider.service}`
        .toLowerCase()
        .includes(state.query);
    return matchesFilter && matchesQuery;
  });

  const bounds = getMapBounds();

  filtered.forEach((provider) => {
    const markerEl = document.createElement("button");
    markerEl.type = "button";
    markerEl.className = "map-marker";
    markerEl.textContent = "•";
    markerEl.title = `${provider.name} • ${provider.city}`;

    const { x, y } = projectCoords(provider.coords, bounds);
    markerEl.style.left = `${(x * 100).toFixed(2)}%`;
    markerEl.style.top = `${(y * 100).toFixed(2)}%`;

    markerEl.addEventListener("click", () => {
      state.context.trackEvent("click", "services.marker", { provider: provider.name });
      state.context.openDetailerModal({
        name: provider.name,
        service: provider.service,
        rating: provider.rating,
      });
      state.context.recordFunnelStep("Intenties", 2);
    });

    state.mapEl.appendChild(markerEl);
    state.markers.push(markerEl);
  });

  const countEl = document.querySelector("[data-services-count]");
  if (countEl) {
    countEl.textContent = filtered.length.toString();
  }
}

function currentResultCount() {
  const visibleMarkers = state.markers.length;
  return visibleMarkers;
}

function getMapBounds() {
  if (state.bounds) {
    return state.bounds;
  }

  const longs = serviceProviders.map((provider) => provider.coords[0]);
  const lats = serviceProviders.map((provider) => provider.coords[1]);
  const minLon = Math.min(...longs);
  const maxLon = Math.max(...longs);
  const minLat = Math.min(...lats);
  const maxLat = Math.max(...lats);

  state.bounds = { minLon, maxLon, minLat, maxLat };
  return state.bounds;
}

function projectCoords(coords, bounds) {
  const [lon, lat] = coords;
  const x = (lon - bounds.minLon) / Math.max(0.0001, bounds.maxLon - bounds.minLon);
  const y = 1 - (lat - bounds.minLat) / Math.max(0.0001, bounds.maxLat - bounds.minLat);
  return { x, y };
}
