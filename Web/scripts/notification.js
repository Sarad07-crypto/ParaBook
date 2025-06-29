// Notification System Variables
let notifications = [];
let messages = [];
let favorites = [];

// ENVELOPE (MESSAGES) FUNCTIONS
function toggleEnvelopeNotifications(event) {
  event.preventDefault();
  event.stopPropagation();

  const dropdown = document.getElementById("envelope-dropdown");
  const envelopeIcon = document.getElementById("envelope-icon");
  const envelope = document.getElementById("envelope-bell");
  const isVisible = dropdown && dropdown.classList.contains("show");

  // Close other notification panels first
  closeAllNotificationPanels();
  closeNotifications();
  closeHeartNotifications();

  if (isVisible) {
    closeEnvelopeNotifications();
  } else {
    openEnvelopeNotifications();
  }
}

function openEnvelopeNotifications() {
  const dropdown = document.getElementById("envelope-dropdown");
  const envelopeIcon = document.getElementById("envelope-icon");
  const envelope = document.getElementById("envelope-bell");

  if (dropdown) dropdown.classList.add("show");
  if (envelopeIcon) envelopeIcon.className = "fas fa-envelope"; // Switch to filled envelope
  if (envelope) envelope.classList.add("active");

  // Load messages when opening
  loadEnvelopeNotifications();
}

function closeEnvelopeNotifications() {
  const dropdown = document.getElementById("envelope-dropdown");
  const envelopeIcon = document.getElementById("envelope-icon");
  const envelope = document.getElementById("envelope-bell");

  if (dropdown) dropdown.classList.remove("show");
  if (envelopeIcon) envelopeIcon.className = "far fa-envelope"; // Switch back to outline envelope
  if (envelope) envelope.classList.remove("active");
}

// Load messages from API
async function loadEnvelopeNotifications() {
  const loadingElement = document.getElementById("loading-envelope");
  const listElement = document.getElementById("envelope-list");

  try {
    if (loadingElement) loadingElement.style.display = "block";

    // Replace with your actual API endpoint for messages
    const response = await fetch(
      "Web/php/AJAX/messageAPI.php?action=get&limit=20"
    );
    const data = await response.json();

    if (loadingElement) loadingElement.style.display = "none";

    if (data.success) {
      messages = data.messages || [];
      renderEnvelopeNotifications();
      updateEnvelopeBadge();
    } else {
      showEnvelopeError("Failed to load messages");
    }
  } catch (error) {
    if (loadingElement) loadingElement.style.display = "none";
    console.error("Error loading messages:", error);
    showEnvelopeError("Error loading messages");
  }
}

// Render messages in the dropdown
function renderEnvelopeNotifications() {
  const listElement = document.getElementById("envelope-list");
  const markAllBtn = document.getElementById("envelope-mark-all-btn");

  if (!listElement) return;

  if (messages.length === 0) {
    listElement.innerHTML = `
      <div class="no-notifications">
        <i class="far fa-envelope"></i>
        <p>No messages yet</p>
      </div>
    `;
    if (markAllBtn) markAllBtn.style.display = "none";
    return;
  }

  const hasUnread = messages.some((m) => m.is_read == 0);
  if (markAllBtn)
    markAllBtn.style.display = hasUnread ? "inline-block" : "none";

  const messageHTML = messages
    .map((message) => {
      const isUnread = message.is_read == 0;
      const timeAgo = formatRelativeTime(message.created_at);

      return `
      <div class="notification-item ${isUnread ? "unread" : ""}" 
           data-message-id="${message.id}"
           onclick="markEnvelopeAsRead(${message.id})">
        <div class="notification-content">
          <div class="notification-icon message">
            <i class="fas fa-envelope"></i>
          </div>
          <div class="notification-text">
            <p class="notification-message">${
              message.message || message.content
            }</p>
            <span class="notification-time">${timeAgo}</span>
          </div>
        </div>
      </div>
    `;
    })
    .join("");

  listElement.innerHTML = messageHTML;
}

// Mark message as read
async function markEnvelopeAsRead(messageId) {
  try {
    const response = await fetch(
      "Web/php/AJAX/messageAPI.php?action=mark_read",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `message_id=${messageId}`,
      }
    );

    const data = await response.json();

    if (data.success) {
      // Update message in local array
      const message = messages.find((m) => m.id == messageId);
      if (message) {
        message.is_read = 1;
      }

      // Update UI
      const messageElement = document.querySelector(
        `[data-message-id="${messageId}"]`
      );
      if (messageElement) {
        messageElement.classList.remove("unread");
      }

      updateEnvelopeBadge();

      // Hide mark all button if no unread messages
      const hasUnread = messages.some((m) => m.is_read == 0);
      const markAllBtn = document.getElementById("envelope-mark-all-btn");
      if (markAllBtn)
        markAllBtn.style.display = hasUnread ? "inline-block" : "none";
    }
  } catch (error) {
    console.error("Error marking message as read:", error);
  }
}

// Mark all messages as read
async function markAllEnvelopeAsRead() {
  try {
    const response = await fetch(
      "Web/php/AJAX/messageAPI.php?action=mark_all_read",
      {
        method: "POST",
      }
    );

    const data = await response.json();

    if (data.success) {
      // Update all messages in local array
      messages.forEach((m) => (m.is_read = 1));

      // Update UI
      document.querySelectorAll(".notification-item.unread").forEach((item) => {
        item.classList.remove("unread");
      });

      updateEnvelopeBadge();
      const markAllBtn = document.getElementById("envelope-mark-all-btn");
      if (markAllBtn) markAllBtn.style.display = "none";

      showToast("All messages marked as read", "success");
    }
  } catch (error) {
    console.error("Error marking all messages as read:", error);
    showToast("Error updating messages", "error");
  }
}

// Refresh messages
async function refreshEnvelopeNotifications() {
  await loadEnvelopeNotifications();
  showToast("Messages refreshed", "success");
}

// Update envelope badge
function updateEnvelopeBadge() {
  const badge = document.getElementById("envelope-badge");
  if (!badge) return;

  const unreadCount = messages.filter((m) => m.is_read == 0).length;

  if (unreadCount > 0) {
    badge.textContent = unreadCount > 99 ? "99+" : unreadCount;
    badge.classList.add("show");
  } else {
    badge.classList.remove("show");
  }
}

// Show envelope error message
function showEnvelopeError(message) {
  const listElement = document.getElementById("envelope-list");
  if (!listElement) return;

  listElement.innerHTML = `
    <div class="no-notifications">
      <i class="fas fa-exclamation-triangle"></i>
      <p>${message}</p>
      <button onclick="loadEnvelopeNotifications()" style="margin-top: 10px; padding: 8px 16px; background: #007BFF; color: white; border: none; border-radius: 4px; cursor: pointer;">
        Try Again
      </button>
    </div>
  `;
}

// HEART (FAVORITES) FUNCTIONS
function toggleHeartNotifications(event) {
  event.preventDefault();
  event.stopPropagation();

  const dropdown = document.getElementById("heart-dropdown");
  const heartIcon = document.getElementById("heart-icon");
  const heart = document.getElementById("heart-bell");
  const isVisible = dropdown && dropdown.classList.contains("show");

  // Close other notification panels first
  closeAllNotificationPanels();
  closeNotifications();
  closeEnvelopeNotifications();

  if (isVisible) {
    closeHeartNotifications();
  } else {
    openHeartNotifications();
  }
}

function openHeartNotifications() {
  const dropdown = document.getElementById("heart-dropdown");
  const heartIcon = document.getElementById("heart-icon");
  const heart = document.getElementById("heart-bell");

  if (dropdown) dropdown.classList.add("show");
  if (heartIcon) heartIcon.className = "fas fa-heart"; // Switch to filled heart
  if (heart) heart.classList.add("active");

  // Load favorites when opening
  loadHeartNotifications();
}

function closeHeartNotifications() {
  const dropdown = document.getElementById("heart-dropdown");
  const heartIcon = document.getElementById("heart-icon");
  const heart = document.getElementById("heart-bell");

  if (dropdown) dropdown.classList.remove("show");
  if (heartIcon) heartIcon.className = "far fa-heart"; // Switch back to outline heart
  if (heart) heart.classList.remove("active");
}

// Load favorites from API
async function loadHeartNotifications() {
  const loadingElement = document.getElementById("loading-heart");
  const listElement = document.getElementById("heart-list");

  try {
    if (loadingElement) loadingElement.style.display = "block";

    // Replace with your actual API endpoint for favorites
    const response = await fetch(
      "Web/php/AJAX/favoritesAPI.php?action=get&limit=20"
    );
    const data = await response.json();

    if (loadingElement) loadingElement.style.display = "none";

    if (data.success) {
      favorites = data.favorites || [];
      renderHeartNotifications();
      updateHeartBadge();
    } else {
      showHeartError("Failed to load favorites");
    }
  } catch (error) {
    if (loadingElement) loadingElement.style.display = "none";
    console.error("Error loading favorites:", error);
    showHeartError("Error loading favorites");
  }
}

// Render favorites in the dropdown
function renderHeartNotifications() {
  const listElement = document.getElementById("heart-list");
  const clearAllBtn = document.getElementById("heart-clear-btn");

  if (!listElement) return;

  if (favorites.length === 0) {
    listElement.innerHTML = `
      <div class="no-notifications">
        <i class="far fa-heart"></i>
        <p>No favorites yet</p>
      </div>
    `;
    if (clearAllBtn) clearAllBtn.style.display = "none";
    return;
  }

  if (clearAllBtn) clearAllBtn.style.display = "inline-block";

  const favoriteHTML = favorites
    .map((favorite) => {
      const timeAgo = formatRelativeTime(favorite.created_at);

      return `
      <div class="notification-item" 
           data-favorite-id="${favorite.id}"
           onclick="removeFavorite(${favorite.id})">
        <div class="notification-content">
          <div class="notification-icon favorite">
            <i class="fas fa-heart"></i>
          </div>
          <div class="notification-text">
            <p class="notification-message">${
              favorite.title || favorite.name
            }</p>
            <span class="notification-time">${timeAgo}</span>
          </div>
        </div>
      </div>
    `;
    })
    .join("");

  listElement.innerHTML = favoriteHTML;
}

// Remove favorite
async function removeFavorite(favoriteId) {
  try {
    const response = await fetch(
      "Web/php/AJAX/favoritesAPI.php?action=remove",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `favorite_id=${favoriteId}`,
      }
    );

    const data = await response.json();

    if (data.success) {
      // Remove favorite from local array
      favorites = favorites.filter((f) => f.id != favoriteId);

      // Update UI
      renderHeartNotifications();
      updateHeartBadge();

      showToast("Removed from favorites", "success");
    }
  } catch (error) {
    console.error("Error removing favorite:", error);
    showToast("Error removing favorite", "error");
  }
}

// Clear all favorites
async function clearAllFavorites() {
  try {
    const response = await fetch(
      "Web/php/AJAX/favoritesAPI.php?action=clear_all",
      {
        method: "POST",
      }
    );

    const data = await response.json();

    if (data.success) {
      favorites = [];
      renderHeartNotifications();
      updateHeartBadge();

      showToast("All favorites cleared", "success");
    }
  } catch (error) {
    console.error("Error clearing favorites:", error);
    showToast("Error clearing favorites", "error");
  }
}

// Refresh favorites
async function refreshHeartNotifications() {
  await loadHeartNotifications();
  showToast("Favorites refreshed", "success");
}

// Update heart badge
function updateHeartBadge() {
  const badge = document.getElementById("heart-badge");
  if (!badge) return;

  const favoriteCount = favorites.length;

  if (favoriteCount > 0) {
    badge.textContent = favoriteCount > 99 ? "99+" : favoriteCount;
    badge.classList.add("show");
  } else {
    badge.classList.remove("show");
  }
}

// Show heart error message
function showHeartError(message) {
  const listElement = document.getElementById("heart-list");
  if (!listElement) return;

  listElement.innerHTML = `
    <div class="no-notifications">
      <i class="fas fa-exclamation-triangle"></i>
      <p>${message}</p>
      <button onclick="loadHeartNotifications()" style="margin-top: 10px; padding: 8px 16px; background: #e91e63; color: white; border: none; border-radius: 4px; cursor: pointer;">
        Try Again
      </button>
    </div>
  `;
}

// ORIGINAL NOTIFICATION FUNCTIONS (Keep existing)
function toggleNotifications(event) {
  event.preventDefault();
  event.stopPropagation();

  const dropdown = document.getElementById("notification-dropdown");
  const isVisible = dropdown && dropdown.classList.contains("show");

  // Close other notification panels first
  closeAllNotificationPanels();
  closeEnvelopeNotifications();
  closeHeartNotifications();

  if (isVisible) {
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
}

// Check if notification dropdown is currently open
function isNotificationDropdownOpen() {
  const dropdown = document.getElementById("notification-dropdown");
  return dropdown && dropdown.classList.contains("show");
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

// UTILITY FUNCTIONS
function closeAllNotificationPanels() {
  closeNotifications();
  closeEnvelopeNotifications();
  closeHeartNotifications();
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
    right: 40%;
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

// Load initial notification counts
async function loadAllNotificationCounts() {
  try {
    // Load notification count
    const notificationResponse = await fetch(
      "Web/php/AJAX/bookingNotificationAPI.php?action=unread_count"
    );
    const notificationData = await notificationResponse.json();

    if (notificationData.success) {
      const badge = document.getElementById("notification-badge");
      const unreadCount = notificationData.unread_count || 0;

      if (badge && unreadCount > 0) {
        badge.textContent = unreadCount > 99 ? "99+" : unreadCount;
        badge.classList.add("show");
      }
    }

    // Load message count
    const messageResponse = await fetch(
      "Web/php/AJAX/messageAPI.php?action=unread_count"
    );
    const messageData = await messageResponse.json();

    if (messageData.success) {
      const badge = document.getElementById("envelope-badge");
      const unreadCount = messageData.unread_count || 0;

      if (badge && unreadCount > 0) {
        badge.textContent = unreadCount > 99 ? "99+" : unreadCount;
        badge.classList.add("show");
      }
    }

    // Load favorites count
    const favoritesResponse = await fetch(
      "Web/php/AJAX/favoritesAPI.php?action=count"
    );
    const favoritesData = await favoritesResponse.json();

    if (favoritesData.success) {
      const badge = document.getElementById("heart-badge");
      const favoriteCount = favoritesData.count || 0;

      if (badge && favoriteCount > 0) {
        badge.textContent = favoriteCount > 99 ? "99+" : favoriteCount;
        badge.classList.add("show");
      }
    }
  } catch (error) {
    console.error("Error loading notification counts:", error);
  }
}

// Toggle icon function for headphones
// function toggleIcon(element, iconName) {
//   if (element.classList.contains("fa-solid")) {
//     // Switch to outline version
//     element.classList.remove("fa-solid");
//     element.classList.add("far");
//     element.classList.remove("icon-active");
//   } else {
//     // Switch to filled version
//     element.classList.remove("far");
//     element.classList.add("fa-solid");
//     element.classList.add("icon-active");
//   }
// }

// Avatar dropdown functionality
function toggleDropdown() {
  const menu = document.getElementById("dropdownMenu");
  if (menu.style.display === "block") {
    menu.style.display = "none";
  } else {
    menu.style.display = "block";
    // Close notification panels when opening avatar dropdown
    closeAllNotificationPanels();
  }
}

// Mobile search functionality
function toggleMobileSearch() {
  const mobileSearchBox = document.getElementById("mobile-search-box");
  if (mobileSearchBox) {
    mobileSearchBox.classList.toggle("show");
  }
}

// Sidebar functionality
function openSidebar() {
  // Add your sidebar opening logic here
  console.log("Opening sidebar...");
}

// Dark mode toggle
function darkModeToggle() {
  document.body.classList.toggle("dark-mode");
  const icon = document.querySelector(".darkmode i");
  if (document.body.classList.contains("dark-mode")) {
    icon.className = "fas fa-sun";
  } else {
    icon.className = "fas fa-moon";
  }
}

// Avatar image error handling
function showInitial(img, initial) {
  const avatarDiv = img.parentElement;
  img.style.display = "none";
  avatarDiv.innerHTML = `<div class="avatar-initial">${initial}</div>`;
}

// Global click handler for closing dropdowns and notifications
document.addEventListener("click", function (e) {
  // Close avatar dropdown when clicking outside
  if (!e.target.closest(".avatar-dropdown")) {
    const dropdownMenu = document.getElementById("dropdownMenu");
    if (dropdownMenu && dropdownMenu.style.display === "block") {
      dropdownMenu.style.display = "none";
    }
  }

  // Close notification dropdowns when clicking outside
  if (!e.target.closest(".notification-container")) {
    if (isNotificationDropdownOpen()) {
      closeNotifications();
    }
  }

  if (!e.target.closest(".envelope-container")) {
    const envelopeDropdown = document.getElementById("envelope-dropdown");
    if (envelopeDropdown && envelopeDropdown.classList.contains("show")) {
      closeEnvelopeNotifications();
    }
  }

  if (!e.target.closest(".heart-container")) {
    const heartDropdown = document.getElementById("heart-dropdown");
    if (heartDropdown && heartDropdown.classList.contains("show")) {
      closeHeartNotifications();
    }
  }

  // Close mobile search when clicking outside
  if (
    !e.target.closest(".search-icon-mobile") &&
    !e.target.closest(".mobile-search-box")
  ) {
    const mobileSearchBox = document.getElementById("mobile-search-box");
    if (mobileSearchBox && mobileSearchBox.classList.contains("show")) {
      mobileSearchBox.classList.remove("show");
    }
  }
});

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  // Load all notification counts on page load
  loadAllNotificationCounts();

  // Auto-refresh notification count every 30 seconds
  setInterval(loadNotificationCount, 30000);

  // Initialize other components if needed
  console.log("Notification system initialized");
});
