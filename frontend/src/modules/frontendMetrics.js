import { postJson } from "./apiClient.js";

export function sendFrontendMetric(type, name, payload = {}) {
  const sessionId = localStorage.getItem("nios_session_id");
  const uid = localStorage.getItem("nios_user_uid");

  if (!uid) {
    return;
  }

  return postJson("/api/frontend-metrics/event", {
    session_id: sessionId,
    uid,
    type,
    name,
    payload,
    timestamp: new Date().toISOString(),
  });
}
