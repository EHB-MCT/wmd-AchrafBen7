const INPUT_STATE = new WeakMap();

export function initInteractionTracking({ analytics, sendFrontendMetric, getView }) {
  const hoverCooldownMs = 1500;
  const hoverMemory = new Map();
  const scrollThresholds = [0.25, 0.5, 0.75, 1];
  const firedThresholds = new Set();
  let scrollTicking = false;

  const record = (type, name, value = {}, event) => {
    analytics.trackEvent(type, name, value, event);
    if (sendFrontendMetric) {
      sendFrontendMetric(type, name, value);
    }
  };

  record("device", "viewport.sample", {
    viewport_w: window.innerWidth,
    viewport_h: window.innerHeight,
    screen_w: window.screen?.width ?? null,
    screen_h: window.screen?.height ?? null,
    dpr: window.devicePixelRatio ?? 1,
  });

  document.addEventListener("visibilitychange", () => {
    record("visibility", "page.visibility", { state: document.visibilityState });
  });

  document.addEventListener("mouseover", (event) => {
    const target = event.target.closest("[data-event], .card, .pill, .chip, .nav-link, .btn");
    if (!target) {
      return;
    }

    const label = target.dataset.event || target.dataset.provider || target.textContent?.trim();
    if (!label) {
      return;
    }

    const key = `${target.dataset.event ?? "hover"}:${label}`;
    const now = Date.now();
    const last = hoverMemory.get(key);
    if (last && now - last < hoverCooldownMs) {
      return;
    }
    hoverMemory.set(key, now);

    record("hover", "ui.hover", {
      label,
      event: target.dataset.event ?? null,
      view: typeof getView === "function" ? getView() : null,
    });
  });

  window.addEventListener("scroll", () => {
    if (scrollTicking) {
      return;
    }
    scrollTicking = true;

    requestAnimationFrame(() => {
      scrollTicking = false;
      const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
      const percent = maxScroll > 0 ? window.scrollY / maxScroll : 1;

      scrollThresholds.forEach((threshold) => {
        if (percent >= threshold && !firedThresholds.has(threshold)) {
          firedThresholds.add(threshold);
          record("engagement", "scroll.depth", {
            threshold: Math.round(threshold * 100),
            current: Math.round(percent * 100),
          });
        }
      });
    });
  });

  document.addEventListener("focusin", (event) => {
    if (!isTextInput(event.target)) {
      return;
    }

    INPUT_STATE.set(event.target, {
      startedAt: Date.now(),
      firstInputAt: null,
    });
  });

  document.addEventListener("input", (event) => {
    if (!isTextInput(event.target)) {
      return;
    }

    const state = INPUT_STATE.get(event.target);
    if (state && !state.firstInputAt) {
      state.firstInputAt = Date.now();
    }
  });

  document.addEventListener("focusout", (event) => {
    if (!isTextInput(event.target)) {
      return;
    }

    const state = INPUT_STATE.get(event.target);
    if (!state) {
      return;
    }

    const totalMs = Date.now() - state.startedAt;
    const firstInputMs = state.firstInputAt ? state.firstInputAt - state.startedAt : null;
    const length = typeof event.target.value === "string" ? event.target.value.length : 0;

    record("input", "input.timing", {
      field: describeField(event.target),
      total_ms: totalMs,
      first_input_ms: firstInputMs,
      length,
    });

    INPUT_STATE.delete(event.target);
  });

  document.addEventListener("change", (event) => {
    if (event.target?.type !== "file") {
      return;
    }

    const files = Array.from(event.target.files ?? []);
    if (!files.length) {
      return;
    }

    record("upload", "file.metadata", {
      field: describeField(event.target),
      count: files.length,
      files: files.map((file) => ({
        name: file.name,
        size: file.size,
        type: file.type,
        last_modified: file.lastModified,
      })),
    });
  });
}

function isTextInput(target) {
  if (!target || !(target instanceof HTMLElement)) {
    return false;
  }

  if (target.tagName === "TEXTAREA") {
    return true;
  }

  if (target.tagName !== "INPUT") {
    return false;
  }

  const type = target.getAttribute("type")?.toLowerCase() ?? "text";
  return ["text", "search", "email", "tel", "url", "password"].includes(type);
}

function describeField(target) {
  return (
    target.getAttribute("aria-label") ||
    target.getAttribute("placeholder") ||
    target.getAttribute("name") ||
    target.getAttribute("id") ||
    "input"
  );
}
