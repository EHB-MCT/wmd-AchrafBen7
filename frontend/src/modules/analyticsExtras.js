import { postJson } from "./apiClient.js";

const FUNNEL_STORAGE_KEY = "nios_funnel_steps";

export function createAnalyticsExtras(analytics) {
  const sentSteps = loadSentSteps();

  const recordSearchQuery = async (query, resultCount) => {
    if (!query) {
      return;
    }

    await analytics.ready;
    const userId = analytics.getUserId();
    if (!userId) {
      return;
    }

    await postJson("/api/search-queries", {
      user_id: userId,
      query,
      result_count: resultCount,
      timestamp: new Date().toISOString(),
    });
  };

  const recordFunnelStep = async (step, order) => {
    if (!step || sentSteps.has(step)) {
      return;
    }

    await analytics.ready;
    const userId = analytics.getUserId();
    if (!userId) {
      return;
    }

    await postJson("/api/funnels", {
      user_id: userId,
      step,
      step_order: order,
      timestamp: new Date().toISOString(),
    });

    sentSteps.add(step);
    persistSentSteps(sentSteps);
  };

  const recordProviderView = async (providerId, durationSeconds) => {
    if (!providerId) {
      return;
    }

    await analytics.ready;
    const userId = analytics.getUserId();
    if (!userId) {
      return;
    }

    await postJson("/api/provider-views", {
      user_id: userId,
      provider_id: providerId,
      view_duration: Math.max(1, Math.round(durationSeconds)),
      timestamp: new Date().toISOString(),
    });
  };

  return {
    recordSearchQuery,
    recordFunnelStep,
    recordProviderView,
  };
}

function loadSentSteps() {
  try {
    const raw = sessionStorage.getItem(FUNNEL_STORAGE_KEY);
    if (!raw) {
      return new Set();
    }
    return new Set(JSON.parse(raw));
  } catch {
    return new Set();
  }
}

function persistSentSteps(steps) {
  try {
    sessionStorage.setItem(FUNNEL_STORAGE_KEY, JSON.stringify([...steps]));
  } catch {
    // ignore storage errors
  }
}
