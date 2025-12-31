import { API_BASE } from "../config/env.js";
import { postJson } from "./apiClient.js";

const SESSION_KEY = "nios_session_id";
const SESSION_START_KEY = "nios_session_start";
const USER_ID_KEY = "nios_user_id";
const USER_UID_KEY = "nios_user_uid";

export function initAnalytics() {
  const state = {
    sessionId: localStorage.getItem(SESSION_KEY),
    sessionStart: Number(localStorage.getItem(SESSION_START_KEY)) || Date.now(),
    userId: localStorage.getItem(USER_ID_KEY),
    userUid: localStorage.getItem(USER_UID_KEY),
    ready: null,
    retryTimer: null,
  };

  if (!state.userUid) {
    state.userUid = generateUUID();
    localStorage.setItem(USER_UID_KEY, state.userUid);
  }

  state.ready = bootstrap(state);
  scheduleRetry(state);

  return {
    trackEvent: (type, name, value = {}, event) =>
      trackEvent(state, type, name, value, event),
    endSession: () => endSession(state),
    getUserId: () => state.userId,
    ready: state.ready,
  };
}

async function bootstrap(state) {
  try {
    await identifyUser(state);
    await ensureSession(state);
  } catch (error) {
    console.warn("NIOS analytics bootstrap failed", error);
  }
}

function scheduleRetry(state) {
  if (state.retryTimer) {
    return;
  }

  state.retryTimer = setInterval(() => {
    if (!state.userId || !state.sessionId) {
      bootstrap(state);
    }
  }, 10000);
}

async function identifyUser(state) {
  const payload = await postJson("/api/users/identify", {
    uid: state.userUid,
    device_type: navigator.platform ?? null,
    os_version: navigator.userAgent ?? null,
    app_version: null,
    locale: navigator.language ?? null,
    country: null,
  });

  if (payload?.user_id) {
    const previousUserId = state.userId;
    state.userId = payload.user_id;
    localStorage.setItem(USER_ID_KEY, state.userId);

    if (previousUserId && previousUserId !== state.userId) {
      state.sessionId = null;
      localStorage.removeItem(SESSION_KEY);
      localStorage.removeItem(SESSION_START_KEY);
    }
  }

  if (payload?.session_id && !state.sessionId) {
    state.sessionId = payload.session_id;
    localStorage.setItem(SESSION_KEY, state.sessionId);
    state.sessionStart = Date.now();
    localStorage.setItem(SESSION_START_KEY, String(state.sessionStart));
  }
}

async function ensureSession(state) {
  if (state.sessionId) {
    return;
  }

  const payload = await postJson("/api/sessions/start", {
    user_id: state.userId ?? undefined,
    uid: state.userUid ?? undefined,
    platform: "web",
    network_type: getNetworkType(),
    battery_level: null,
  });

  state.sessionId = payload.session?.id;
  if (!state.userId && payload.session?.user_id) {
    state.userId = payload.session.user_id;
    localStorage.setItem(USER_ID_KEY, state.userId);
  }
  state.sessionStart = Date.now();
  if (state.sessionId) {
    localStorage.setItem(SESSION_KEY, state.sessionId);
    localStorage.setItem(SESSION_START_KEY, String(state.sessionStart));
  }
}

async function trackEvent(state, type, name, value = {}, event) {
  await state.ready;
  if (!state.sessionId || state.sessionId === "null" || state.sessionId === "undefined") {
    state.sessionId = null;
    await ensureSession(state);
  }
  if (!state.sessionId) {
    return;
  }

  const coords = getEventCoords(event);

  try {
    await postJson("/api/events", {
      session_id: state.sessionId,
      user_id: state.userId,
      type,
      name,
      value,
      device_x: coords?.x ?? null,
      device_y: coords?.y ?? null,
      timestamp: new Date().toISOString(),
    });
  } catch (error) {
    console.warn("NIOS analytics event failed", error);
  }
}

function endSession(state) {
  if (!state.sessionId) {
    return;
  }

  const durationSeconds = Math.max(
    1,
    Math.floor((Date.now() - state.sessionStart) / 1000),
  );

  const payload = JSON.stringify({
    session_id: state.sessionId,
    duration_seconds: durationSeconds,
  });

  const blob = new Blob([payload], { type: "application/json" });
  navigator.sendBeacon(`${API_BASE}/api/sessions/end`, blob);
}

function getEventCoords(event) {
  if (!event || typeof event.clientX !== "number") {
    return null;
  }

  return {
    x: Math.round(event.clientX),
    y: Math.round(event.clientY),
  };
}

function getNetworkType() {
  const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
  return connection?.effectiveType || null;
}

function generateUUID() {
  if (crypto?.randomUUID) {
    return crypto.randomUUID();
  }

  return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, (char) => {
    const rand = Math.random() * 16;
    const value = char === "x" ? rand : (rand % 4) + 8;
    return Math.floor(value).toString(16);
  });
}
