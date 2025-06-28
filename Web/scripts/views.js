// Show initial for avatar fallback
function showInitial(img, initial) {
  const container = img.parentNode;
  img.remove();
  const fallback = document.createElement("div");
  fallback.className = "initial-avatar";
  fallback.textContent = initial;
  container.appendChild(fallback);
}

// Sidebar functions
function openSidebar() {
  document.getElementById("sidebar").classList.add("active");
  document.body.classList.add("sidebar-open");
  document.getElementById("sidebar-backdrop").classList.add("active");
}

function closeSidebar() {
  document.getElementById("sidebar").classList.remove("active");
  document.body.classList.remove("sidebar-open");
  document.getElementById("sidebar-backdrop").classList.remove("active");
}

// Mobile search toggle
function toggleMobileSearch() {
  var box = document.getElementById("mobile-search-box");
  if (box.classList.contains("active")) {
    box.classList.remove("active");
  } else {
    box.classList.add("active");
    box.querySelector("input").focus();
  }
}

// Dark mode toggle
function darkModeToggle() {
  document.body.classList.toggle("dark-mode");
  if (document.body.classList.contains("dark-mode")) {
    localStorage.setItem("theme", "dark");
  } else {
    localStorage.setItem("theme", "light");
  }
}

// Event listeners - MODIFIED to work with notifications
document.addEventListener("click", function (e) {
  const sidebar = document.getElementById("sidebar");
  const hamburger = document.querySelector(".hamburger");

  // Close sidebar when clicking outside
  if (
    sidebar.classList.contains("active") &&
    !sidebar.contains(e.target) &&
    !hamburger.contains(e.target)
  ) {
    closeSidebar();
  }

  // Close avatar dropdown when clicking outside - CHECK if notification system exists
  if (!e.target.closest(".avatar-dropdown")) {
    const dropdownMenu = document.getElementById("dropdownMenu");
    if (dropdownMenu) dropdownMenu.style.display = "none";
  }

  // DON'T interfere with notification system - let notification.js handle it
  // The notification.js already has proper click handlers for notifications
});

// Close sidebar automatically when resizing from mobile to desktop
window.addEventListener("resize", function () {
  if (window.innerWidth > 600) {
    closeSidebar();
  }
});

// ESC key closes all panels - MODIFIED to work with notifications
document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") {
    // Check if notification functions exist before calling them
    if (typeof closeAllNotificationPanels === "function") {
      closeAllNotificationPanels();
    }
    if (typeof closeNotifications === "function") {
      closeNotifications();
    }

    // Close avatar dropdown
    const dropdownMenu = document.getElementById("dropdownMenu");
    if (dropdownMenu) dropdownMenu.style.display = "none";

    // Close sidebar
    closeSidebar();
  }
});

// Load theme on page load
window.onload = function () {
  if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark-mode");
  }
};

// Pagination functionality
const cardsPerPage = 10;
const cards = Array.from(document.querySelectorAll(".company-card"));
const pagination = document.getElementById("pagination");
let currentPage = 1;

function showPage(page) {
  currentPage = page;
  const start = (page - 1) * cardsPerPage;
  const end = start + cardsPerPage;
  cards.forEach((card, i) => {
    card.style.display = i >= start && i < end ? "" : "none";
  });
  renderPagination();
}

function renderPagination() {
  if (!pagination) return;
  const pageCount = Math.ceil(cards.length / cardsPerPage);
  pagination.innerHTML = "";
  for (let i = 1; i <= pageCount; i++) {
    const btn = document.createElement("button");
    btn.textContent = i;
    if (i === currentPage) btn.classList.add("active");
    btn.onclick = () => showPage(i);
    pagination.appendChild(btn);
  }
}

if (cards.length > 0) {
  showPage(1);
}

function logout() {
  alert("Logout clicked");
}

// Initialize view components when DOM is loaded - MODIFIED
document.addEventListener("DOMContentLoaded", function () {
  // Card reveal animation
  const cards = document.querySelectorAll(".company-card");

  function revealCards() {
    const triggerBottom = window.innerHeight * 0.95;
    cards.forEach((card) => {
      const cardTop = card.getBoundingClientRect().top;
      if (cardTop < triggerBottom) {
        card.classList.add("visible");
      }
    });
  }

  window.addEventListener("scroll", revealCards);
  revealCards(); // Initial check

  // Initialize theme
  if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark-mode");
  }
});
