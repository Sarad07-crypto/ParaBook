// Notification System Variables
let notifications = [];
let messages = [];
let favorites = [];
let conversations = []; // Store conversations data

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

  // Load conversations when opening
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

// Load conversations from your existing API
/*
async function loadEnvelopeNotifications() {
  const loadingElement = document.getElementById("loading-envelope");
  const listElement = document.getElementById("envelope-list");

  try {
    if (loadingElement) loadingElement.style.display = "block";

    // Use your existing get_conversations.php API
    const response = await fetch(
      "Web/php/chat/api/get_conversations.php?limit=20"
    );
    const data = await response.json();

    if (loadingElement) loadingElement.style.display = "none";

    if (data.success) {
      conversations = data.conversations || [];
      renderEnvelopeNotifications();
      updateEnvelopeBadge(data.total_unread || 0);
    } else {
      showEnvelopeError(data.error || "Failed to load conversations");
    }
  } catch (error) {
    if (loadingElement) loadingElement.style.display = "none";
    console.error("Error loading conversations:", error);
    showEnvelopeError("Error loading conversations");
  }
}
*/
// Render conversations in the dropdown
function renderEnvelopeNotifications() {
  const listElement = document.getElementById("envelope-list");
  const markAllBtn = document.getElementById("envelope-mark-all-btn");

  if (!listElement) return;

  if (conversations.length === 0) {
    listElement.innerHTML = `
      <div class="no-notifications">
        <i class="far fa-envelope"></i>
        <p>No conversations yet</p>
      </div>
    `;
    if (markAllBtn) markAllBtn.style.display = "none";
    return;
  }

  const hasUnread = conversations.some((conv) => conv.unread_count > 0);
  if (markAllBtn)
    markAllBtn.style.display = hasUnread ? "inline-block" : "none";

  const conversationHTML = conversations
    .map((conversation) => {
      const isUnread = conversation.unread_count > 0;
      const timeAgo = formatRelativeTime(conversation.last_message_time);
      const lastMessage = conversation.last_message || "No messages yet";
      const truncatedMessage =
        lastMessage.length > 50
          ? lastMessage.substring(0, 50) + "..."
          : lastMessage;

      return `
      <div class="notification-item ${isUnread ? "unread" : ""}" 
           data-conversation-id="${conversation.id}"
           onclick="openConversation(${conversation.id})">
        <div class="notification-content">
          <div class="notification-icon message">
            <i class="fas fa-envelope"></i>
            ${
              isUnread
                ? `<span class="unread-badge">${conversation.unread_count}</span>`
                : ""
            }
          </div>
          <div class="notification-text">
            <div class="notification-header">
              <strong>${conversation.other_participant_name}</strong>
              <span class="service-name">${conversation.service_name}</span>
            </div>
            <p class="notification-message">${truncatedMessage}</p>
            <span class="notification-time">${timeAgo}</span>
          </div>
        </div>
      </div>
    `;
    })
    .join("");

  listElement.innerHTML = conversationHTML;
}

// Open a specific conversation (you'll need to implement this based on your chat interface)
function openConversation(conversationId) {
  // Close the dropdown
  closeEnvelopeNotifications();

  // Here you would typically navigate to the chat interface
  // This depends on how your chat system is structured

  // Example implementations:
  // Option 1: Redirect to chat page
  // window.location.href = `chat.php?conversation_id=${conversationId}`;

  // Option 2: Open modal/sidebar chat
  // openChatModal(conversationId);

  // Option 3: Navigate within SPA
  // navigateToChat(conversationId);

  console.log(`Opening conversation ${conversationId}`);

  // For now, you can replace this with your actual chat opening logic
  alert(`Opening conversation ${conversationId}`);
}

// Mark all conversations as read (optional - you might want to implement this)
async function markAllEnvelopeAsRead() {
  try {
    // You would need to create an endpoint to mark all conversations as read
    // For now, we'll just refresh the data
    await loadEnvelopeNotifications();
    showToast("Refreshed conversations", "success");
  } catch (error) {
    console.error("Error refreshing conversations:", error);
    showToast("Error refreshing conversations", "error");
  }
}

// Refresh conversations
async function refreshEnvelopeNotifications() {
  await loadEnvelopeNotifications();
  showToast("Conversations refreshed", "success");
}

// Update envelope badge with total unread count
function updateEnvelopeBadge(totalUnread = null) {
  const badge = document.getElementById("envelope-badge");
  if (!badge) return;

  // If totalUnread is not provided, calculate from conversations
  if (totalUnread === null) {
    totalUnread = conversations.reduce(
      (sum, conv) => sum + conv.unread_count,
      0
    );
  }

  if (totalUnread > 0) {
    badge.textContent = totalUnread > 99 ? "99+" : totalUnread;
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

// Auto-refresh conversations every 30 seconds (optional)
function startEnvelopeAutoRefresh() {
  setInterval(() => {
    // Only refresh if the dropdown is closed to avoid disrupting user interaction
    const dropdown = document.getElementById("envelope-dropdown");
    if (!dropdown || !dropdown.classList.contains("show")) {
      loadEnvelopeNotifications();
    }
  }, 30000); // 30 seconds
}

// Helper function to format relative time
function formatRelativeTime(dateString) {
  if (!dateString) return "Unknown";

  const date = new Date(dateString);
  const now = new Date();
  const diffInSeconds = Math.floor((now - date) / 1000);

  if (diffInSeconds < 60) {
    return "Just now";
  } else if (diffInSeconds < 3600) {
    const minutes = Math.floor(diffInSeconds / 60);
    return `${minutes}m ago`;
  } else if (diffInSeconds < 86400) {
    const hours = Math.floor(diffInSeconds / 3600);
    return `${hours}h ago`;
  } else if (diffInSeconds < 2592000) {
    const days = Math.floor(diffInSeconds / 86400);
    return `${days}d ago`;
  } else {
    return date.toLocaleDateString();
  }
}

function showToast(message, type = "info") {
  // Implement based on your toast notification system
  console.log(`Toast: ${message} (${type})`);
}

// Initialize auto-refresh when the page loads
document.addEventListener("DOMContentLoaded", function () {
  // Load initial data
  // loadEnvelopeNotifications();
  // Start auto-refresh (optional)
  // startEnvelopeAutoRefresh();
});

// HEART (FAVORITES) FUNCTIONS
function toggleHeartNotifications(event) {
  event.preventDefault();
  event.stopPropagation();

  const dropdown = document.getElementById("heart-dropdown");
  const heartIcon = document.getElementById("heart-icon");
  const heart = document.getElementById("heart-bell");
  const isVisible = dropdown && dropdown.classList.contains("show");

  // Close other notification panels first (if these functions exist)
  try {
    if (typeof closeAllNotificationPanels === "function")
      closeAllNotificationPanels();
    if (typeof closeNotifications === "function") closeNotifications();
    if (typeof closeEnvelopeNotifications === "function")
      closeEnvelopeNotifications();
  } catch (e) {
    // Functions don't exist, that's okay
  }

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

  // Show dropdown
  if (dropdown) dropdown.classList.add("show");

  // Change icon to filled heart
  if (heartIcon) heartIcon.className = "fas fa-heart";

  // Add active state to heart bell
  if (heart) heart.classList.add("active");

  // Load favorites when opening
  loadFavoritesList();

  // Close when clicking outside
  document.addEventListener("click", handleOutsideClick);
}

function closeHeartNotifications() {
  const dropdown = document.getElementById("heart-dropdown");
  const heartIcon = document.getElementById("heart-icon");
  const heart = document.getElementById("heart-bell");

  // Hide dropdown
  if (dropdown) dropdown.classList.remove("show");

  // Change icon to outline heart
  if (heartIcon) heartIcon.className = "far fa-heart";

  // Remove active state from heart bell
  if (heart) heart.classList.remove("active");

  // Remove outside click listener
  document.removeEventListener("click", handleOutsideClick);
}

function handleOutsideClick(event) {
  const heartContainer = document.querySelector(".heart-container");
  if (!heartContainer.contains(event.target)) {
    closeHeartNotifications();
  }
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

// ENHANCED NOTIFICATION FUNCTIONS WITH CLICK HANDLING
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
      // Setup click handlers after rendering notifications
      setupNotificationClickHandlers();
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
           data-notification-type="${notification.type || ""}"
           data-booking-id="${notification.booking_id || ""}"
           style="cursor: pointer;">
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

// Function to handle notification clicks and redirect to service description
async function handleNotificationClick(notificationElement) {
  console.log("Notification clicked:", notificationElement);

  const notificationId = notificationElement.dataset.notificationId;
  const notificationType = notificationElement.dataset.notificationType;
  const bookingId = notificationElement.dataset.bookingId;

  console.log("Notification ID:", notificationId);
  console.log("Notification Type:", notificationType);
  console.log("Booking ID:", bookingId);

  if (!notificationId) {
    console.error("No notification ID found");
    return;
  }

  try {
    // Show loading indicator
    console.log("Fetching notification details...");

    // Get notification details from your API
    const response = await fetch(
      `Web/php/AJAX/reviewNotificationAPI.php?action=get_notification_details&notification_id=${notificationId}`,
      {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
        credentials: "same-origin",
      }
    );

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    console.log("API Response:", data);

    if (data.success) {
      const notification = data.notification;
      console.log("Notification details:", notification);

      // Check if this is a completed booking notification for passenger
      if (
        notification.type === "completed" &&
        notification.recipient_type !== "company" &&
        notification.service_id
      ) {
        console.log(
          "Navigating to service review for service_id:",
          notification.service_id
        );

        // Navigate to service description page with review section
        navigateToServiceReview(notification.service_id);

        // Mark notification as read after successful navigation
        await markNotificationAsRead(notificationId);

        // Update the UI to show as read
        notificationElement.classList.remove("unread");
        updateNotificationBadge();

        // Close notification dropdown after navigation
        closeNotifications();
      } else {
        console.log("Notification does not match criteria for navigation");

        // Still mark as read even if not navigating
        await markAsRead(notificationId);
        notificationElement.classList.remove("unread");
        updateNotificationBadge();

        // Show message if it's not a reviewable notification
        if (notification.type !== "completed") {
          showToast("Notification marked as read", "info");
        }
      }
    } else {
      console.error("Error fetching notification details:", data.message);
      showToast("Error loading notification details", "error");
    }
  } catch (error) {
    console.error("Error handling notification click:", error);
    showToast("Error processing notification", "error");
  }
}

// Function to navigate to service review section
function navigateToServiceReview(serviceId) {
  if (serviceId) {
    const url = `/serviceDescription?service_id=${encodeURIComponent(
      serviceId
    )}#reviewSection`;
    console.log("Navigating to:", url);
    window.location.href = url;
  } else {
    console.error("No service ID provided for navigation");
  }
}

// Function to mark notification as read using the review API
async function markNotificationAsRead(notificationId) {
  try {
    const response = await fetch("Web/php/AJAX/notificationAPI.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=mark_as_read&notification_id=${notificationId}`,
      credentials: "same-origin",
    });

    const data = await response.json();
    if (data.success) {
      console.log("Notification marked as read");
      return true;
    } else {
      console.error("Failed to mark notification as read:", data.message);
      return false;
    }
  } catch (error) {
    console.error("Error marking notification as read:", error);
    return false;
  }
}

// Setup click handlers for notifications
function setupNotificationClickHandlers() {
  const notificationList = document.getElementById("notification-list");

  if (notificationList) {
    console.log("Setting up notification click handlers");

    // Remove existing click handlers to avoid duplicates
    const existingHandler = notificationList.getAttribute("data-click-handler");
    if (existingHandler) {
      return; // Already has handler
    }

    // Mark that handler is set
    notificationList.setAttribute("data-click-handler", "true");

    // Add event listener using event delegation
    notificationList.addEventListener("click", function (event) {
      console.log("Click event triggered on:", event.target);

      const notificationItem = event.target.closest(".notification-item");
      if (notificationItem) {
        console.log("Found notification item:", notificationItem);

        // Prevent default action and stop propagation
        event.preventDefault();
        event.stopPropagation();

        // Handle the notification click
        handleNotificationClick(notificationItem);
      } else {
        console.log("No notification item found for click");
      }
    });

    console.log("Notification click handlers setup complete");
  } else {
    console.error("Notification list element not found");
  }
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
    case "completed":
      return "fas fa-star"; // Star icon for completed bookings
    case "payment":
      return "fas fa-credit-card";
    case "system":
      return "fas fa-info-circle";
    default:
      return "fas fa-bell";
  }
}

// Enhanced mark as read function for individual notifications
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

      return true;
    } else {
      console.error("Failed to mark notification as read:", data.message);
      return false;
    }
  } catch (error) {
    console.error("Error marking notification as read:", error);
    return false;
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

// Enhanced load all notification counts function
async function loadAllNotificationCounts() {
  try {
    // Load notification count with better error handling
    try {
      const notificationResponse = await fetch(
        "Web/php/AJAX/bookingNotificationAPI.php?action=unread_count"
      );

      if (!notificationResponse.ok) {
        console.error(
          `Notification API error: ${notificationResponse.status} ${notificationResponse.statusText}`
        );
      } else {
        const responseText = await notificationResponse.text();
        console.log("Notification API raw response:", responseText);

        let notificationData;
        try {
          notificationData = JSON.parse(responseText);
        } catch (parseError) {
          console.error(
            "Failed to parse notification response as JSON:",
            parseError
          );
          console.error("Response was:", responseText);
          return;
        }

        if (notificationData.success) {
          const badge = document.getElementById("notification-badge");
          const unreadCount = notificationData.unread_count || 0;

          if (badge && unreadCount > 0) {
            badge.textContent = unreadCount > 99 ? "99+" : unreadCount;
            badge.classList.add("show");
          } else if (badge) {
            badge.classList.remove("show");
          }
        }
      }
    } catch (error) {
      console.error("Error loading notification count:", error);
    }

    // Load favorites count with better error handling
    try {
      const favoritesResponse = await fetch(
        "Web/php/AJAX/favoritesAPI.php?action=count"
      );

      if (!favoritesResponse.ok) {
        console.error(
          `Favorites API error: ${favoritesResponse.status} ${favoritesResponse.statusText}`
        );
      } else {
        const responseText = await favoritesResponse.text();
        console.log("Favorites API raw response:", responseText);

        let favoritesData;
        try {
          favoritesData = JSON.parse(responseText);
        } catch (parseError) {
          console.error(
            "Failed to parse favorites response as JSON:",
            parseError
          );
          console.error("Response was:", responseText);
          return;
        }

        if (favoritesData.success) {
          const badge = document.getElementById("heart-badge");
          const favoriteCount = favoritesData.count || 0;

          if (badge && favoriteCount > 0) {
            badge.textContent = favoriteCount > 99 ? "99+" : favoriteCount;
            badge.classList.add("show");
          } else if (badge) {
            badge.classList.remove("show");
          }
        }
      }
    } catch (error) {
      console.error("Error loading favorites count:", error);
    }
  } catch (error) {
    console.error("Error in loadAllNotificationCounts:", error);
  }
}

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
  console.log("DOM loaded, initializing notification system");

  // Load all notification counts on page load
  loadAllNotificationCounts();

  // Auto-refresh notification count every 30 seconds
  setInterval(loadAllNotificationCounts, 30000);

  // Initialize notification system
  console.log("Notification system initialized");
});

// Also setup handlers if DOM is already loaded
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", function () {
    console.log("Setting up notification handlers on DOM ready");
  });
} else {
  console.log("DOM already loaded, setting up notification handlers");
}

// Enhanced error handling for API calls
async function loadAllNotificationCounts() {
  try {
    // Load notification count with better error handling
    try {
      const notificationResponse = await fetch(
        "Web/php/AJAX/bookingNotificationAPI.php?action=unread_count"
      );

      // Check if response is OK
      if (!notificationResponse.ok) {
        console.error(
          `Notification API error: ${notificationResponse.status} ${notificationResponse.statusText}`
        );
      } else {
        const responseText = await notificationResponse.text();
        console.log("Notification API raw response:", responseText); // Debug log

        let notificationData;
        try {
          notificationData = JSON.parse(responseText);
        } catch (parseError) {
          console.error(
            "Failed to parse notification response as JSON:",
            parseError
          );
          console.error("Response was:", responseText);
          return;
        }

        if (notificationData.success) {
          const badge = document.getElementById("notification-badge");
          const unreadCount = notificationData.unread_count || 0;

          if (badge && unreadCount > 0) {
            badge.textContent = unreadCount > 99 ? "99+" : unreadCount;
            badge.classList.add("show");
          }
        }
      }
    } catch (error) {
      console.error("Error loading notification count:", error);
    }

    // Load message count with better error handling
    /*
    try {
      const messageResponse = await fetch(
        "Web/php/AJAX/messageAPI.php?action=unread_count"
      );

      if (!messageResponse.ok) {
        console.error(
          `Message API error: ${messageResponse.status} ${messageResponse.statusText}`
        );
      } else {
        const responseText = await messageResponse.text();
        console.log("Message API raw response:", responseText); // Debug log

        let messageData;
        try {
          messageData = JSON.parse(responseText);
        } catch (parseError) {
          console.error(
            "Failed to parse message response as JSON:",
            parseError
          );
          console.error("Response was:", responseText);
          return;
        }

        if (messageData.success) {
          const badge = document.getElementById("envelope-badge");
          const unreadCount = messageData.unread_count || 0;

          if (badge && unreadCount > 0) {
            badge.textContent = unreadCount > 99 ? "99+" : unreadCount;
            badge.classList.add("show");
          }
        }
      }
    } catch (error) {
      console.error("Error loading message count:", error);
    }
*/
    // Load favorites count with better error handling
    try {
      const favoritesResponse = await fetch(
        "Web/php/AJAX/favoritesAPI.php?action=count"
      );

      if (!favoritesResponse.ok) {
        console.error(
          `Favorites API error: ${favoritesResponse.status} ${favoritesResponse.statusText}`
        );
      } else {
        const responseText = await favoritesResponse.text();
        console.log("Favorites API raw response:", responseText); // Debug log

        let favoritesData;
        try {
          favoritesData = JSON.parse(responseText);
        } catch (parseError) {
          console.error(
            "Failed to parse favorites response as JSON:",
            parseError
          );
          console.error("Response was:", responseText);
          return;
        }

        if (favoritesData.success) {
          const badge = document.getElementById("heart-badge");
          const favoriteCount = favoritesData.count || 0;

          if (badge && favoriteCount > 0) {
            badge.textContent = favoriteCount > 99 ? "99+" : favoriteCount;
            badge.classList.add("show");
          }
        }
      }
    } catch (error) {
      console.error("Error loading favorites count:", error);
    }
  } catch (error) {
    console.error("Error in loadAllNotificationCounts:", error);
  }
}
