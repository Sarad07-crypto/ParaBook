// Function to handle notification clicks and redirect to service description
async function handleNotificationClick(notificationElement) {
  console.log("Notification clicked:", notificationElement);

  const notificationId = notificationElement.dataset.notificationId;
  console.log("Notification ID:", notificationId);

  if (!notificationId) {
    console.error("No notification ID found");
    return;
  }

  try {
    console.log("Fetching notification details...");

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
    console.log("API Response:", data); // Debug log

    if (data.success) {
      const notification = data.notification;
      console.log("Notification details:", notification); // Debug log

      // Check if this is a completed booking notification for passenger (review notification)
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
        markNotificationAsRead(notificationId);
      } else {
        console.log("Notification does not match criteria for navigation");
        console.log("Type:", notification.type);
        console.log("Recipient type:", notification.recipient_type);
        console.log("Service ID:", notification.service_id);
      }
    } else {
      console.error("Error fetching notification details:", data.message);
    }
  } catch (error) {
    console.error("Error handling notification click:", error);
  }
}

// Function to navigate to service review section
function navigateToServiceReview(serviceId) {
  if (serviceId) {
    const url = `/serviceDescription?service_id=${encodeURIComponent(
      serviceId
    )}#reviewSection`;
    console.log("Navigating to:", url); // Debug log
    window.location.href = url;
  } else {
    console.error("No service ID provided for navigation");
  }
}

// Function to mark notification as read
async function markNotificationAsRead(notificationId) {
  try {
    const response = await fetch("Web/php/AJAX/bookingNotificationAPI.php", {
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
    } else {
      console.error("Failed to mark notification as read:", data.message);
    }
  } catch (error) {
    console.error("Error marking notification as read:", error);
  }
}

// Setup click handlers for notifications
function setupNotificationClickHandlers() {
  const notificationList = document.getElementById("notification-list");

  if (notificationList) {
    console.log("Setting up notification click handlers"); // Debug log

    // Remove existing event listeners to avoid duplicates
    const newNotificationList = notificationList.cloneNode(true);
    notificationList.parentNode.replaceChild(
      newNotificationList,
      notificationList
    );

    // Add event listener to the new element
    newNotificationList.addEventListener("click", function (event) {
      console.log("Click event triggered:", event.target); // Debug log

      const notificationItem = event.target.closest(".notification-item");
      if (notificationItem) {
        console.log("Found notification item:", notificationItem); // Debug log

        // Prevent default action and stop propagation
        event.preventDefault();
        event.stopPropagation();

        handleNotificationClick(notificationItem);
      } else {
        console.log("No notification item found for click"); // Debug log
      }
    });
  } else {
    console.error("Notification list element not found");
  }
}

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
  console.log("DOM loaded, initializing notification handlers"); // Debug log
  setupNotificationClickHandlers();
});

// Also initialize if the script loads after DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", setupNotificationClickHandlers);
} else {
  setupNotificationClickHandlers();
}
