// Toggle notification panels and icon states
function toggleNotificationPanel(event, type) {
  event.preventDefault();

  const panel = document.getElementById(`${type}-panel`);
  const icon = document.getElementById(`${type}-icon`);

  // Close all other panels first
  closeAllNotificationPanels(type);

  // Toggle current panel
  if (panel.classList.contains("show")) {
    panel.classList.remove("show");
    // Switch back to outline icon
    icon.className = `far fa-${type}`;
    icon.classList.remove("icon-active");
  } else {
    panel.classList.add("show");
    // Switch to filled icon
    icon.className = `fas fa-${type}`;
    icon.classList.add("icon-active");
  }
}

// Close specific notification panel
function closeNotificationPanel(type) {
  const panel = document.getElementById(`${type}-panel`);
  const icon = document.getElementById(`${type}-icon`);

  panel.classList.remove("show");
  // Switch back to outline icon
  icon.className = `far fa-${type}`;
  icon.classList.remove("icon-active");
}

// Close all notification panels except the specified one
function closeAllNotificationPanels(except = null) {
  const types = ["bell", "envelope", "heart"];
  types.forEach((type) => {
    if (type !== except) {
      closeNotificationPanel(type);
    }
  });
}

// Toggle icon function for headphones
function toggleIcon(element, iconName) {
  if (element.classList.contains("fa-solid")) {
    // Switch to outline version
    element.classList.remove("fa-solid");
    element.classList.add("far");
    element.classList.remove("icon-active");
  } else {
    // Switch to filled version
    element.classList.remove("far");
    element.classList.add("fa-solid");
    element.classList.add("icon-active");
  }
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

// Avatar dropdown - Updated to work with your existing CSS
function toggleDropdown() {
  var menu = document.getElementById("dropdownMenu");
  if (menu.style.display === "block") {
    menu.style.display = "none";
  } else {
    menu.style.display = "block";
    // Close notification panels
    closeAllNotificationPanels();
  }
}

// Show initial for avatar fallback
function showInitial(img, initial) {
  const container = img.parentNode;
  img.remove();
  const fallback = document.createElement("div");
  fallback.className = "initial-avatar";
  fallback.textContent = initial;
  container.appendChild(fallback);
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

// Event listeners
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

  // Close avatar dropdown when clicking outside
  if (!e.target.closest(".avatar-dropdown")) {
    document.getElementById("dropdownMenu").style.display = "none";
  }

  // Close notification panels when clicking outside
  const notificationPanels = document.querySelectorAll(".notification-panel");
  const rightSection = document.querySelector(".right-section");
  if (!rightSection || !rightSection.contains(e.target)) {
    closeAllNotificationPanels();
  }
});

// Close sidebar automatically when resizing from mobile to desktop
window.addEventListener("resize", function () {
  if (window.innerWidth > 600) {
    closeSidebar();
  }
});

// ESC key closes all panels
document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") {
    closeAllNotificationPanels();
    document.getElementById("dropdownMenu").style.display = "none";
  }
});

// Load theme on page load
window.onload = function () {
  if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark-mode");
  }
};

// Your existing functions from the original JavaScript
document.addEventListener("DOMContentLoaded", function () {
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
});

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
  if (!pagination) return; // Check if pagination element exists
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

// Initialize pagination if cards exist
if (cards.length > 0) {
  showPage(1);
}

function logout() {
  alert("Logout clicked");
}
