import "mapbox-gl/dist/mapbox-gl.css";
import "./styles.css";

import { initAnalytics } from "./modules/analytics.js";
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
const { openDetailerModal, detailerFromCard } = initDetailerModal();

setServicesContext({
  trackEvent: analytics.trackEvent,
  openDetailerModal,
});

switchView("home");
analytics.trackEvent("view", "page.home", { page: "home" });

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
    analytics.trackEvent("view", `page.${target.dataset.view}`, {
      page: target.dataset.view,
    });

    if (target.dataset.view === "services") {
      initServicesMap();
      requestAnimationFrame(() => resizeServicesMap());
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
  }

  analytics.trackEvent("click", eventName, metadata, event);
});

document.addEventListener("visibilitychange", () => {
  if (document.visibilityState === "hidden") {
    analytics.endSession();
  }
});

window.addEventListener("beforeunload", () => {
  analytics.endSession();
});
