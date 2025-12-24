import "mapbox-gl/dist/mapbox-gl.css";
import "./styles.css";

import { initAnalytics } from "./modules/analytics.js";
import { createAnalyticsExtras } from "./modules/analyticsExtras.js";
import { switchView } from "./modules/view.js";
import { applyHomeFilter, setActiveHomeFilter } from "./modules/homeFilters.js";
import { initDetailerModal } from "./modules/detailerModal.js";
import {
  initServicesMap,
  resizeServicesMap,
  setServiceFilter,
  setServicesContext,
} from "./modules/servicesMap.js";

const analytics = initAnalytics();
const analyticsExtras = createAnalyticsExtras(analytics);
const { openDetailerModal, detailerFromCard } = initDetailerModal();

let currentView = "home";
let lastPointer = null;
let lastScrollY = 0;

setServicesContext({
  trackEvent: analytics.trackEvent,
  openDetailerModal,
  recordSearchQuery: analyticsExtras.recordSearchQuery,
  recordProviderView: analyticsExtras.recordProviderView,
  recordFunnelStep: analyticsExtras.recordFunnelStep,
});

switchView("home");
analytics.trackEvent("view", "page.home", { page: "home" });
analyticsExtras.recordFunnelStep("Ontdekking", 1);
currentView = "home";

document.addEventListener("mousemove", (event) => {
  lastPointer = { x: event.clientX, y: event.clientY };
});

document.addEventListener("touchmove", (event) => {
  const touch = event.touches?.[0];
  if (touch) {
    lastPointer = { x: touch.clientX, y: touch.clientY };
  }
});

document.addEventListener("scroll", () => {
  lastScrollY = window.scrollY || 0;
});

setInterval(() => {
  const heartbeatPayload = {
    view: currentView,
    scroll_y: lastScrollY,
  };

  const coordsEvent = lastPointer
    ? { clientX: lastPointer.x, clientY: lastPointer.y }
    : null;

  analytics.trackEvent("view", "heartbeat", heartbeatPayload, coordsEvent);
}, 10000);

document.addEventListener("click", (event) => {
  const target = event.target.closest("[data-event]");
  if (!target) {
    return;
  }

  const eventName = target.dataset.event;
  const metadata = {
    label: target.textContent?.trim() || undefined,
  };

  if (eventName === "promo.close") {
    document.querySelector(".promo")?.classList.add("is-hidden");
  }

  if (eventName === "top.menu") {
    document.querySelector(".app")?.classList.toggle("is-collapsed");
    requestAnimationFrame(() => resizeServicesMap());
  }

  if (target.dataset.view) {
    switchView(target.dataset.view);
    currentView = target.dataset.view;
    analytics.trackEvent("view", `page.${target.dataset.view}`, {
      page: target.dataset.view,
    });

    if (target.dataset.view === "services") {
      initServicesMap();
      requestAnimationFrame(() => resizeServicesMap());
      analyticsExtras.recordFunnelStep("Ontdekking", 1);
    }
  }

  if (target.dataset.servicesFilter) {
    setServiceFilter(target.dataset.servicesFilter);
  }

  if (target.classList.contains("pill")) {
    setActiveHomeFilter(target);
    applyHomeFilter(target.dataset.filter);
  }

  const card = target.closest(".card");
  if (card) {
    const detailer = detailerFromCard(card);
    openDetailerModal(detailer);
    metadata.provider = card.dataset.provider;
    analyticsExtras.recordFunnelStep("Intenties", 2);
  }

  if (eventName === "cta.search") {
    analyticsExtras.recordFunnelStep("Intenties", 2);
  }

  if (eventName === "detailer.message" || eventName === "cta.offer") {
    analyticsExtras.recordFunnelStep("Offertes", 3);
  }

  if (eventName?.startsWith("book") || eventName?.includes(".book")) {
    analyticsExtras.recordFunnelStep("Boekingen", 4);
    analytics.trackEvent("conversion", eventName, metadata, event);
  } else {
    analytics.trackEvent("click", eventName, metadata, event);
  }
});

document.addEventListener("visibilitychange", () => {
  if (document.visibilityState === "hidden") {
    analytics.endSession();
  }
});

window.addEventListener("beforeunload", () => {
  analytics.endSession();
});

document.addEventListener("detailer:closed", (event) => {
  const detailer = event.detail?.detailer;
  const durationSeconds = event.detail?.durationSeconds;
  analyticsExtras.recordProviderView(detailer?.name, durationSeconds);
});
