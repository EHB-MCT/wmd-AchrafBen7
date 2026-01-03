export function setActiveHomeFilter(target) {
  document.querySelectorAll(".pill").forEach((pill) => {
    pill.classList.remove("is-active");
  });
  target.classList.add("is-active");
}

export function applyHomeFilter(filter) {
  if (!filter || filter === "more") {
    return;
  }

  document.querySelectorAll(".card").forEach((card) => {
    const category = card.dataset.category;
    const shouldShow = filter === "all" || filter === category;
    card.classList.toggle("is-hidden", !shouldShow);
  });
}
