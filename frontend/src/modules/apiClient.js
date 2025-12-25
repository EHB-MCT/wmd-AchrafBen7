import { API_BASE } from "../config/env.js";

export async function postJson(endpoint, data) {
  const url = `${API_BASE}${endpoint}`;
  dispatchApiEvent({
    endpoint,
    url,
    status: "pending",
  });

  try {
    const response = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    });

    if (!response.ok) {
      dispatchApiEvent({
        endpoint,
        url,
        status: "error",
        code: response.status,
      });
      throw new Error(`Request failed: ${response.status}`);
    }

    dispatchApiEvent({
      endpoint,
      url,
      status: "ok",
      code: response.status,
    });

    return response.json();
  } catch (error) {
    dispatchApiEvent({
      endpoint,
      url,
      status: "error",
      message: error?.message ?? "Network error",
    });
    throw error;
  }
}

function dispatchApiEvent(detail) {
  document.dispatchEvent(new CustomEvent("nios:api", { detail }));
}
