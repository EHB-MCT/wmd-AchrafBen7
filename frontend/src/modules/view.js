export function switchView(viewName) {
  document.querySelectorAll("[data-view-section]").forEach((section) => {
    const isActive = section.dataset.viewSection === viewName;
    section.classList.toggle("is-active", isActive);
    section.style.display = isActive ? section.dataset.display || "block" : "none";
  });
}
