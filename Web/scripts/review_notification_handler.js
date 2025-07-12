// Fixed handleReviewNotificationClick function
function handleReviewNotificationClick(notificationElement) {
  console.log("Review notification clicked"); // Debug log

  // Get the booking ID from the notification data attribute
  const bookingId = notificationElement.getAttribute("data-booking-id");
  const notificationType = notificationElement.getAttribute("data-type");
  const notificationId = notificationElement.getAttribute(
    "data-notification-id"
  );

  console.log(
    "Booking ID:",
    bookingId,
    "Type:",
    notificationType,
    "Notification ID:",
    notificationId
  ); // Debug log

  // Only handle review notifications
  if (notificationType === "review") {
    // Check if booking ID exists
    if (!bookingId) {
      console.error("No booking ID found for review notification");
      showToast("Error: No booking information found", "error");
      return;
    }

    // Mark as read when clicked (using the correct function)
    if (notificationId) {
      markNotificationAsReadOnly(notificationId);
    }

    // Get service ID from booking
    getServiceIdFromBooking(bookingId)
      .then((serviceId) => {
        console.log("Service ID retrieved:", serviceId); // Debug log
        if (serviceId) {
          // Redirect to service description page for review
          goToServiceDescriptionForReview(serviceId);
        } else {
          console.error("No service ID returned");
          showToast("Unable to find service for review", "error");
        }
      })
      .catch((error) => {
        console.error("Error getting service ID:", error);
        showToast("Error loading review page", "error");
      });
  }
}

// Fixed markAsRead function
async function markAsRead(notificationId) {
  try {
    // First, get the notification element to check its type
    const notificationElement = document.querySelector(
      `[data-notification-id="${notificationId}"]`
    );

    if (!notificationElement) {
      console.error("Notification element not found");
      return;
    }

    const notificationType = notificationElement.getAttribute("data-type");
    console.log("Notification type:", notificationType); // Debug log

    // IMPORTANT: Handle review notifications BEFORE marking as read
    if (notificationType === "review") {
      console.log("Review notification detected, calling review handler");

      // Check if the review handler function exists
      if (typeof handleReviewNotificationClick === "function") {
        // Call the review handler function
        handleReviewNotificationClick(notificationElement);
        return; // Exit here - the review handler will mark as read
      } else {
        console.error("handleReviewNotificationClick function not found");
        showToast(
          "Review handler not loaded. Please refresh the page.",
          "error"
        );
        return;
      }
    }

    // For non-review notifications, proceed with normal flow
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
      notificationElement.classList.remove("unread");
      updateNotificationBadge();

      // Hide mark all button if no unread notifications
      const hasUnread = notifications.some((n) => n.is_read == 0);
      const markAllBtn = document.getElementById("mark-all-btn");
      if (markAllBtn)
        markAllBtn.style.display = hasUnread ? "inline-block" : "none";
    }
  } catch (error) {
    console.error("Error marking notification as read:", error);
    showToast("Error updating notification", "error");
  }
}

// Enhanced getServiceIdFromBooking with better error handling
async function getServiceIdFromBooking(bookingId) {
  try {
    console.log("Fetching service ID for booking:", bookingId); // Debug log

    const response = await fetch(
      `Web/php/AJAX/getServiceFromBooking.php?booking_id=${encodeURIComponent(
        bookingId
      )}`
    );

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const responseText = await response.text();
    console.log("Raw response:", responseText); // Debug log

    let data;
    try {
      data = JSON.parse(responseText);
    } catch (parseError) {
      console.error("Failed to parse response as JSON:", parseError);
      console.error("Response was:", responseText);
      return null;
    }

    if (data.success) {
      console.log("Service ID found:", data.service_id); // Debug log
      return data.service_id;
    } else {
      console.error(
        "Error getting service ID:",
        data.message || "Unknown error"
      );
      return null;
    }
  } catch (error) {
    console.error("Error fetching service ID:", error);
    return null;
  }
}

// Enhanced goToServiceDescriptionForReview function
function goToServiceDescriptionForReview(serviceId) {
  console.log(
    "Redirecting to service description for review, service ID:",
    serviceId
  ); // Debug log

  // Add review parameter to indicate user should leave a review
  const url = `/serviceDescription?service_id=${encodeURIComponent(
    serviceId
  )}&action=review`;
  console.log("Redirect URL:", url); // Debug log

  // Try multiple redirect methods for better compatibility
  try {
    window.location.href = url;
  } catch (error) {
    console.error("Error with window.location.href:", error);
    // Fallback method
    window.location.assign(url);
  }
}

// Enhanced renderNotifications function to ensure booking_id is included
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

      // Debug log for review notifications
      if (notification.type === "review") {
        console.log("Review notification data:", {
          id: notification.id,
          type: notification.type,
          booking_id: notification.booking_id,
          message: notification.message,
        });
      }

      return `
      <div class="notification-item ${isUnread ? "unread" : ""}" 
           data-notification-id="${notification.id}"
           data-type="${notification.type}"
           data-booking-id="${notification.booking_id || ""}"
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
