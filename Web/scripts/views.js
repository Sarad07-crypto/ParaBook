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

function showInitial(img, initial) {
  const container = img.parentNode;

  // Remove broken image
  img.remove();

  // Create a fallback element with the initial
  const fallback = document.createElement("div");
  fallback.className = "initial-avatar";
  fallback.textContent = initial;

  container.appendChild(fallback);
}
function toggleDropdown() {
    var menu = document.getElementById('dropdownMenu');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

// Hide dropdown when clicking outside
window.onclick = function(event) {
    if (!event.target.closest('.avatar-dropdown')) {
        document.getElementById('dropdownMenu').style.display = 'none';
    }
}
function darkModeToggle() {
    document.body.classList.toggle('dark-mode');
    // Save preference
    if(document.body.classList.contains('dark-mode')) {
        localStorage.setItem('theme', 'dark');
    } else {
        localStorage.setItem('theme', 'light');
    }
}
window.onload = function() {
    if(localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
    }
};