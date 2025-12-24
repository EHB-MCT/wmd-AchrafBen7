import mapboxgl from "mapbox-gl";
import { MAPBOX_TOKEN } from "../config/env.js";
import { serviceProviders } from "../data/providers.js";

const state = {
  map: null,
  markers: [],
  filter: "offers",
  query: "",
  searchBound: false,
  context: {
    trackEvent: () => {},
    openDetailerModal: () => {},
  },
};

export function setServicesContext(context) {
  state.context = context;
}

export function initServicesMap() {
  const container = document.getElementById("services-map");
  if (!container || state.map) {
    return;
  }

  if (!MAPBOX_TOKEN) {
    container.classList.add("map-placeholder");
    container.textContent = "Mapbox token manquant.";
    return;
  }

  mapboxgl.accessToken = MAPBOX_TOKEN;
  state.map = new mapboxgl.Map({
    container,
    style: "mapbox://styles/mapbox/light-v11",
    center: [4.3517, 50.8503],
    zoom: 11,
  });

  state.map.addControl(new mapboxgl.NavigationControl(), "bottom-right");
  state.map.on("load", () => {
    updateServiceMarkers();
  });

  bindServicesSearch();
}

export function resizeServicesMap() {
  if (state.map) {
    state.map.resize();
  }
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
  });

  state.searchBound = true;
}

function updateServiceMarkers() {
  if (!state.map) {
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

  filtered.forEach((provider) => {
    const markerEl = document.createElement("button");
    markerEl.type = "button";
    markerEl.className = "map-marker";
    markerEl.textContent = "•";

    markerEl.addEventListener("click", () => {
      state.context.trackEvent("click", "services.marker", { provider: provider.name });
      state.context.openDetailerModal({
        name: provider.name,
        service: provider.service,
        rating: provider.rating,
      });
    });

    const marker = new mapboxgl.Marker(markerEl)
      .setLngLat(provider.coords)
      .addTo(state.map);

    state.markers.push(marker);
  });

  const countEl = document.querySelector("[data-services-count]");
  if (countEl) {
    countEl.textContent = filtered.length.toString();
  }
}
