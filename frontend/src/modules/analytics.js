import { API_BASE } from "../config/env.js";

const SESSION_KEY = "nios_session_id";
const SESSION_START_KEY = "nios_session_start";
const USER_KEY = "nios_user_id";

export function initAnalytics() {
  const state = {
    sessionId: localStorage.getItem(SESSION_KEY),
    sessionStart: Number(localStorage.getItem(SESSION_START_KEY)) || Date.now(),
    userId: localStorage.getItem(USER_KEY),
  };

  if (!state.userId) {
    state.userId = generateUUID();
    localStorage.setItem(USER_KEY, state.userId);
  }

  ensureSession(state).catch((error) => {
    console.warn("NIOS analytics session start failed", error);
  });

  return {
    trackEvent: (type, name, value = {}, event) =>
      trackEvent(state, type, name, value, event),
    endSession: () => endSession(state),
  };
}

async function ensureSession(state) {
  if (state.sessionId) {
    return;
  }

  const payload = await postJson(`${API_BASE}/api/sessions/start`, {
    user_id: state.userId,
    platform: "web",
    network_type: getNetworkType(),
    battery_level: null,
  });

  state.sessionId = payload.session?.id;
  state.sessionStart = Date.now();
  if (state.sessionId) {
    localStorage.setItem(SESSION_KEY, state.sessionId);
    localStorage.setItem(SESSION_START_KEY, String(state.sessionStart));
  }
}

async function trackEvent(state, type, name, value = {}, event) {
  if (!state.sessionId) {
    return;
  }

  const coords = getEventCoords(event);

  try {
    await postJson(`${API_BASE}/api/events`, {
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

async function postJson(url, data) {
  const response = await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  });

  if (!response.ok) {
    throw new Error(`Request failed: ${response.status}`);
  }

  return response.json();
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
