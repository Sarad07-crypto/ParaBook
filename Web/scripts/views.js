// Updated views.js with notification functionality

// Notification System Variables
let notificationsVisible = false;
let notifications = [];
let notificationUpdateInterval;

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

  if (panel) panel.classList.remove("show");
  if (icon) {
    // Switch back to outline icon
    icon.className = `far fa-${type}`;
    icon.classList.remove("icon-active");
  }
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

// Notification System Functions
function toggleNotifications(event) {
  event.preventDefault();

  const dropdown = document.getElementById("notification-dropdown");
  const bellIcon = document.getElementById("bell-icon");
  const bell = document.getElementById("notification-bell");

  if (notificationsVisible) {
    closeNotifications();
  } else {
    openNotifications();
  }
}

function openNotifications() {
  const dropdown = document.getElementById("notification-dropdown");
  const bellIcon = document.getElementById("bell-icon");
  const bell = document.getElementById("notification-bell");

  if (dropdown) dropdown.classList.add("show");
  if (bellIcon) bellIcon.className = "fas fa-bell"; // Switch to filled bell
  if (bell) bell.classList.add("active");
  notificationsVisible = true;

  // Load notifications when opening
  loadNotifications();
}

function closeNotifications() {
  const dropdown = document.getElementById("notification-dropdown");
  const bellIcon = document.getElementById("bell-icon");
  const bell = document.getElementById("notification-bell");

  if (dropdown) dropdown.classList.remove("show");
  if (bellIcon) bellIcon.className = "far fa-bell"; // Switch back to outline bell
  if (bell) bell.classList.remove("active");
  notificationsVisible = false;
}

// Load notifications from API
async function loadNotifications() {
  const loadingElement = document.getElementById("loading-notifications");
  const listElement = document.getElementById("notification-list");

  try {
    if (loadingElement) loadingElement.style.display = "block";

    const response = await fetch(
      "Web/php/AJAX/bookingNotificationAPI.php?action=get&limit=20"
    );
    const data = await response.json();

    if (loadingElement) loadingElement.style.display = "none";

    if (data.success) {
      notifications = data.notifications || [];
      renderNotifications();
      updateNotificationBadge();
    } else {
      showError("Failed to load notifications");
    }
  } catch (error) {
    if (loadingElement) loadingElement.style.display = "none";
    console.error("Error loading notifications:", error);
    showError("Error loading notifications");
  }
}

// Render notifications in the dropdown
function renderNotifications() {
  const listElement = document.getElementById("notification-list");
  const markAllBtn = document.getElementById("mark-all-btn");

  if (!listElement) return;

  if (notifications.length === 0) {
    listElement.innerHTML = `
      <div class="no-notifications">
        <i class="far fa-bell"></i>
        <p>No notifications yet</p>
      </div>
    `;
    if (markAllBtn) markAllBtn.style.display = "none";
    return;
  }

  const hasUnread = notifications.some((n) => n.is_read == 0);
  if (markAllBtn)
    markAllBtn.style.display = hasUnread ? "inline-block" : "none";

  const notificationHTML = notifications
    .map((notification) => {
      const isUnread = notification.is_read == 0;
      const iconClass = getNotificationIcon(notification.type);
      const timeAgo = formatRelativeTime(notification.created_at);

      return `
      <div class="notification-item ${isUnread ? "unread" : ""}" 
           data-notification-id="${notification.id}"
           onclick="markAsRead(${notification.id})">
        <div class="notification-content">
          <div class="notification-icon ${notification.type}">
            <i class="${iconClass}"></i>
          </div>
          <div class="notification-text">
            <p class="notification-message">${notification.message}</p>
            <span class="notification-time">${timeAgo}</span>
          </div>
        </div>
      </div>
    `;
    })
    .join("");

  listElement.innerHTML = notificationHTML;
}

// Get icon based on notification type
function getNotificationIcon(type) {
  switch (type) {
    case "booking_created":
    case "booking":
      return "fas fa-plane";
    case "booking_approved":
    case "booking_confirmed":
      return "fas fa-check-circle";
    case "booking_rejected":
    case "booking_cancelled":
      return "fas fa-times-circle";
    case "payment":
      return "fas fa-credit-card";
    case "system":
      return "fas fa-info-circle";
    default:
      return "fas fa-bell";
  }
}

// Mark notification as read
async function markAsRead(notificationId) {
  try {
    const response = await fetch(
      "Web/php/AJAX/bookingNotificationAPI.php?action=mark_read",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `notification_id=${notificationId}`,
      }
    );

    const data = await response.json();

    if (data.success) {
      // Update notification in local array
      const notification = notifications.find((n) => n.id == notificationId);
      if (notification) {
        notification.is_read = 1;
      }

      // Update UI
      const notificationElement = document.querySelector(
        `[data-notification-id="${notificationId}"]`
      );
      if (notificationElement) {
        notificationElement.classList.remove("unread");
      }

      updateNotificationBadge();

      // Hide mark all button if no unread notifications
      const hasUnread = notifications.some((n) => n.is_read == 0);
      const markAllBtn = document.getElementById("mark-all-btn");
      if (markAllBtn)
        markAllBtn.style.display = hasUnread ? "inline-block" : "none";
    }
  } catch (error) {
    console.error("Error marking notification as read:", error);
  }
}

// Mark all notifications as read
async function markAllAsRead() {
  try {
    const response = await fetch(
      "Web/php/AJAX/bookingNotificationAPI.php?action=mark_all_read",
      {
        method: "POST",
      }
    );

    const data = await response.json();

    if (data.success) {
      // Update all notifications in local array
      notifications.forEach((n) => (n.is_read = 1));

      // Update UI
      document.querySelectorAll(".notification-item.unread").forEach((item) => {
        item.classList.remove("unread");
      });

      updateNotificationBadge();
      const markAllBtn = document.getElementById("mark-all-btn");
      if (markAllBtn) markAllBtn.style.display = "none";

      showToast("All notifications marked as read", "success");
    }
  } catch (error) {
    console.error("Error marking all notifications as read:", error);
    showToast("Error updating notifications", "error");
  }
}

// Refresh notifications
async function refreshNotifications() {
  await loadNotifications();
  showToast("Notifications refreshed", "success");
}

// Update notification badge
function updateNotificationBadge() {
  const badge = document.getElementById("notification-badge");
  if (!badge) return;

  const unreadCount = notifications.filter((n) => n.is_read == 0).length;

  if (unreadCount > 0) {
    badge.textContent = unreadCount > 99 ? "99+" : unreadCount;
    badge.classList.add("show");
  } else {
    badge.classList.remove("show");
  }
}

// Format relative time
function formatRelativeTime(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diffInSeconds = Math.floor((now - date) / 1000);
  const diffInMinutes = Math.floor(diffInSeconds / 60);
  const diffInHours = Math.floor(diffInMinutes / 60);
  const diffInDays = Math.floor(diffInHours / 24);

  if (diffInSeconds < 60) {
    return "Just now";
  } else if (diffInMinutes < 60) {
    return `${diffInMinutes}m ago`;
  } else if (diffInHours < 24) {
    return `${diffInHours}h ago`;
  } else if (diffInDays < 7) {
    return `${diffInDays}d ago`;
  } else {
    return date.toLocaleDateString();
  }
}

// Show toast notification
function showToast(message, type = "info") {
  const toast = document.createElement("div");
  toast.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 12px 20px;
    border-radius: 6px;
    color: white;
    font-weight: 500;
    z-index: 10000;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
    max-width: 300px;
  `;

  switch (type) {
    case "success":
      toast.style.backgroundColor = "#28a745";
      break;
    case "error":
      toast.style.backgroundColor = "#dc3545";
      break;
    default:
      toast.style.backgroundColor = "#007BFF";
  }

  toast.textContent = message;
  document.body.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = "0";
    toast.style.transform = "translateY(-20px)";
    setTimeout(() => {
      if (document.body.contains(toast)) {
        document.body.removeChild(toast);
      }
    }, 300);
  }, 3000);
}

// Show error message
function showError(message) {
  const listElement = document.getElementById("notification-list");
  if (!listElement) return;

  listElement.innerHTML = `
    <div class="no-notifications">
      <i class="fas fa-exclamation-triangle"></i>
      <p>${message}</p>
      <button onclick="loadNotifications()" style="margin-top: 10px; padding: 8px 16px; background: #007BFF; color: white; border: none; border-radius: 4px; cursor: pointer;">
        Try Again
      </button>
    </div>
  `;
}

// Load initial notification count
async function loadNotificationCount() {
  try {
    const response = await fetch(
      "Web/php/AJAX/bookingNotificationAPI.php?action=unread_count"
    );
    const data = await response.json();

    if (data.success) {
      const badge = document.getElementById("notification-badge");
      const unreadCount = data.unread_count || 0;

      if (badge && unreadCount > 0) {
        badge.textContent = unreadCount > 99 ? "99+" : unreadCount;
        badge.classList.add("show");
      }
    }
  } catch (error) {
    console.error("Error loading notification count:", error);
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
    closeNotifications();
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
  const notificationContainer = document.querySelector(
    ".notification-container"
  );

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
    const dropdownMenu = document.getElementById("dropdownMenu");
    if (dropdownMenu) dropdownMenu.style.display = "none";
  }

  // Close notification panels when clicking outside
  const notificationPanels = document.querySelectorAll(".notification-panel");
  const rightSection = document.querySelector(".right-section");
  if (!rightSection || !rightSection.contains(e.target)) {
    closeAllNotificationPanels();
  }

  // Close notifications when clicking outside
  if (
    notificationsVisible &&
    notificationContainer &&
    !notificationContainer.contains(e.target)
  ) {
    closeNotifications();
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
    closeNotifications();
    const dropdownMenu = document.getElementById("dropdownMenu");
    if (dropdownMenu) dropdownMenu.style.display = "none";
  }
});

// Load theme on page load
window.onload = function () {
  if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark-mode");
  }
};

// Initialize notifications when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  // Load notification count on page load
  loadNotificationCount();

  // Auto-refresh notification count every 30 seconds
  setInterval(loadNotificationCount, 30000);

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
