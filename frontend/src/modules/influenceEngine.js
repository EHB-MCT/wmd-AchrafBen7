import { API_BASE } from "../config/env.js";

export async function applyUserInfluence(analytics) {
  await analytics.ready;
  const topProvider = await fetchTopProvider();
  if (topProvider?.provider_id) {
    addTopProviderBadge(topProvider.provider_id);
  }

  try {
    const userId = analytics.getUserId();
    if (!userId) {
      return;
    }

    const insight = await fetchInsight(userId);
    if (!insight) {
      return;
    }

    applyInfluence(insight);
    updateWhyBlock(insight, topProvider);
  } catch {
    // Ignore influence failures to keep UX stable.
  }
}

async function fetchInsight(userId) {
  const response = await fetch(`${API_BASE}/api/users/${userId}/insights`);
  if (!response.ok) {
    return null;
  }

  const insights = await response.json();
  return Array.isArray(insights) ? insights[0] : null;
}

async function fetchTopProvider() {
  try {
    const response = await fetch(`${API_BASE}/api/provider-views/top`);
    if (!response.ok) {
      return null;
    }
    return response.json();
  } catch {
    return null;
  }
}

function applyInfluence(insight) {
  const promoEl = document.querySelector("[data-promo]");
  const promoTitle = document.querySelector("[data-promo-title]");
  const promoSub = document.querySelector("[data-promo-sub]");
  const heroEyebrow = document.querySelector("[data-hero-eyebrow]");
  const heroTitle = document.querySelector("[data-hero-title]");
  const heroSub = document.querySelector("[data-hero-sub]");
  const heroPrimary = document.querySelector("[data-hero-primary]");
  const heroSecondary = document.querySelector("[data-hero-secondary]");

  const premium = toNumber(insight.premium_tendency);
  const hesitation = toNumber(insight.hesitation_score);
  const impulsive = toNumber(insight.impulsivity_score);
  const likelyToBook = Boolean(insight.likely_to_book);
  const churnRisk = Boolean(insight.risk_churn);
  const nightUser = Boolean(insight.night_user);

  if (churnRisk && promoEl) {
    promoEl.classList.add("is-urgent");
    setText(promoTitle, "We willen je terug – €15 korting");
    setText(promoSub, "Nog 48u geldig voor je volgende reservering");
  } else if (impulsive > 0.6 && promoEl) {
    setText(promoTitle, "Laatste plekken voor vandaag");
    setText(promoSub, "Snelle service bij jou in de buurt");
  }

  if (nightUser) {
    setText(heroEyebrow, "Laatavond beschikbaar");
  }

  if (hesitation > 0.6) {
    setText(heroTitle, "Twijfel? Plan eerst een advies");
    setText(heroSub, "We helpen je de juiste service te kiezen zonder druk.");
    setText(heroSecondary, "Vraag advies");
  }

  if (likelyToBook && heroPrimary) {
    heroPrimary.classList.add("is-priority");
    setText(heroPrimary, "Reserveer direct");
  }

  if (premium > 0.6) {
    highlightProvider("Shine Masters", "Premium keuze");
  }
}

function updateWhyBlock(insight, topProvider) {
  const block = document.querySelector("[data-why-block]");
  const list = document.querySelector("[data-why-list]");
  const title = document.querySelector("[data-why-title]");
  if (!block || !list) {
    return;
  }

  const reasons = [];
  const premium = toNumber(insight.premium_tendency);
  const hesitation = toNumber(insight.hesitation_score);
  const impulsive = toNumber(insight.impulsivity_score);

  if (premium > 0.6) {
    reasons.push("Je kiest vaker premium diensten.");
  }
  if (hesitation > 0.6) {
    reasons.push("Je vergelijkt langer voordat je beslist.");
  }
  if (impulsive > 0.6) {
    reasons.push("Je klikt snel door naar reserveren.");
  }
  if (insight.night_user) {
    reasons.push("Je bekijkt vooral in de avond.");
  }
  if (insight.likely_to_book) {
    reasons.push("Je boekt vaker dan gemiddeld.");
  }
  if (insight.risk_churn) {
    reasons.push("Je haakt soms af zonder te boeken.");
  }

  if (topProvider?.provider_id) {
    reasons.push(`Deze detailer is het vaakst gekozen: ${topProvider.provider_id}.`);
  }

  if (!reasons.length) {
    return;
  }

  block.classList.remove("is-hidden");
  list.innerHTML = reasons.map((reason) => `<li>${reason}</li>`).join("");
  if (topProvider?.provider_id && title) {
    title.textContent = `Waarom ${topProvider.provider_id}?`;
  }
}

function highlightProvider(name, tagLabel) {
  const card = document.querySelector(`[data-provider="${name}"]`);
  if (!card) {
    return;
  }

  card.classList.add("is-featured");
  const body = card.querySelector(".card-body");
  if (!body || body.querySelector(".card-tag")) {
    return;
  }

  const tag = document.createElement("span");
  tag.className = "card-tag";
  tag.textContent = tagLabel;
  body.prepend(tag);
}

function addTopProviderBadge(name) {
  const card = document.querySelector(`[data-provider="${name}"]`);
  if (!card) {
    return;
  }

  const body = card.querySelector(".card-body");
  if (!body || body.querySelector(".card-badge")) {
    return;
  }

  const badge = document.createElement("span");
  badge.className = "card-badge";
  badge.textContent = "Meest gekozen";
  body.prepend(badge);
}

function setText(el, text) {
  if (el && text) {
    el.textContent = text;
  }
}

function toNumber(value) {
  const parsed = Number(value);
  return Number.isFinite(parsed) ? parsed : 0;
}
