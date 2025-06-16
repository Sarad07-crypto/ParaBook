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
// Optional: close sidebar when clicking outside
document.addEventListener("click", function (e) {
  const sidebar = document.getElementById("sidebar");
  const hamburger = document.querySelector(".hamburger");
  if (
    sidebar.classList.contains("active") &&
    !sidebar.contains(e.target) &&
    !hamburger.contains(e.target)
  ) {
    closeSidebar();
  }
});
// Optional: close menu when clicking outside (for better UX)
document.addEventListener("click", function (e) {
  const topBar = document.querySelector(".top-bar");
  const hamburger = document.querySelector(".hamburger");
  if (!topBar.contains(e.target) && topBar.classList.contains("active")) {
    topBar.classList.remove("active");
  }
});

// Close sidebar automatically when resizing from mobile to desktop
window.addEventListener("resize", function () {
  if (window.innerWidth > 600) {
    closeSidebar();
  }
});

function logout() {
  // Add your logout logic here
  alert("Logout clicked");
}
function toggleMobileSearch() {
  var box = document.getElementById("mobile-search-box");
  if (box.classList.contains("active")) {
    box.classList.remove("active");
  } else {
    box.classList.add("active");
    box.querySelector("input").focus();
  }
}

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

const cardsPerPage = 10; // Change this for more/less per page
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

// Initialize
showPage(1);
