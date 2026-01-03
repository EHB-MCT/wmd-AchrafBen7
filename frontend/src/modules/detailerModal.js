export function initDetailerModal() {
  const modal = document.getElementById("detailer-modal");
  let currentDetailer = null;
  let openedAt = null;
  if (!modal) {
    return {
      openDetailerModal: () => {},
      closeDetailerModal: () => {},
      detailerFromCard: () => null,
    };
  }

  modal.addEventListener("click", (event) => {
    if (event.target.id === "detailer-modal" || event.target.closest(".modal-close")) {
      closeDetailerModal(modal);
    }
  });

  return {
    openDetailerModal: (detailer) => {
      currentDetailer = detailer;
      openedAt = Date.now();
      openDetailerModal(modal, detailer);
    },
    closeDetailerModal: () => closeDetailerModal(modal, currentDetailer, openedAt),
    detailerFromCard,
  };
}

function openDetailerModal(modal, detailer) {
  if (!detailer) {
    return;
  }

  const nameEl = document.getElementById("detailer-name");
  const serviceEl = document.getElementById("detailer-service");
  const ratingEl = document.getElementById("detailer-rating");
  const bookButton = modal.querySelector("[data-event=\"detailer.book\"]");

  if (nameEl) {
    nameEl.textContent = detailer.name || "Dienstverlener";
  }

  if (serviceEl) {
    serviceEl.textContent = detailer.service || "Dienst";
  }

  if (ratingEl) {
    ratingEl.textContent = `★ ${detailer.rating ?? "-"}`;
  }

  if (bookButton) {
    bookButton.dataset.provider = detailer.name || "";
  }

  modal.classList.add("is-open");
  modal.setAttribute("aria-hidden", "false");
}

function closeDetailerModal(modal, detailer, openedAt) {
  modal.classList.remove("is-open");
  modal.setAttribute("aria-hidden", "true");

  if (detailer && openedAt) {
    const durationSeconds = Math.max(1, (Date.now() - openedAt) / 1000);
    document.dispatchEvent(
      new CustomEvent("detailer:closed", {
        detail: { detailer, durationSeconds },
      }),
    );
  }
}

function detailerFromCard(card) {
  if (!card) {
    return null;
  }

  const name = card.querySelector("h3")?.textContent?.trim();
  const service = card.querySelector("p")?.textContent?.trim();
  const rating = card.dataset.rating || card.querySelector(".rating")?.textContent?.trim();

  return {
    name,
    service,
    rating: rating?.replace("★", "").trim() || rating,
  };
}
