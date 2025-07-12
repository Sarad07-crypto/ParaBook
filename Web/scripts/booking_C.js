console.log("=== JAVASCRIPT STARTING ===");

$(document).ready(function () {
  console.log("Document ready - starting data fetch...");
  fetchData();
});

function fetchData() {
  console.log("Fetching data from fetchBookings.php...");

  $.ajax({
    url: "Web/php/AJAX/fetchBookings.php",
    method: "GET",
    dataType: "json",
    timeout: 10000,
    beforeSend: function () {
      console.log("AJAX request starting...");
      $("#loadingIndicator").show();
    },
    success: function (response) {
      console.log("SUCCESS! Response received:", response);
      $("#loadingIndicator").hide();

      if (response.success && response.bookings) {
        console.log(`Found ${response.bookings.length} bookings`);

        // Auto-complete bookings with past dates
        autoCompleteExpiredBookings(response.bookings);

        // Display bookings
        renderBookings(response.bookings);

        // Display statistics if available
        if (response.statistics) {
          displayStatistics(response.statistics);
        }

        console.log("Data rendered successfully");
      } else {
        console.error("Invalid response format:", response);
        showError("Invalid response from server");
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX ERROR!");
      console.error("Status:", status);
      console.error("Error:", error);
      console.error("Status Code:", xhr.status);
      console.error("Response Text:", xhr.responseText);

      $("#loadingIndicator").hide();
      showError(`Failed to load data: ${error}`);
    },
  });
}

function autoCompleteExpiredBookings(bookings) {
  console.log("Checking for expired bookings to auto-complete...");
  const today = new Date();
  today.setHours(0, 0, 0, 0); // Set to start of day for comparison

  bookings.forEach(function (booking) {
    const normalizedStatus = booking.status
      ? booking.status.toLowerCase().trim()
      : "pending";

    if (normalizedStatus === "pending" && booking.date) {
      const bookingDate = new Date(booking.date);
      bookingDate.setHours(0, 0, 0, 0);

      // If booking date has passed, auto-complete it
      if (bookingDate < today) {
        console.log(`Auto-completing expired booking: ${booking.booking_id}`);
        completeBooking(booking.booking_id, true); // Pass true for auto-complete
      }
    }
  });
}

function renderBookings(bookings) {
  console.log("Rendering bookings...");
  const tbody = $("#bookingsTableBody");
  tbody.empty();

  if (!bookings || bookings.length === 0) {
    tbody.html(
      '<tr><td colspan="9" style="text-align:center;">No bookings found</td></tr>'
    );
    return;
  }

  bookings.forEach(function (booking, index) {
    const sequentialNumber = index + 1;

    // Status class logic - normalize the status value
    let normalizedStatus = booking.status
      ? booking.status.toLowerCase().trim()
      : "pending";

    let statusClass = "status-pending"; // default
    if (normalizedStatus === "completed") {
      statusClass = "status-completed";
    } else if (normalizedStatus === "confirmed") {
      statusClass = "status-confirmed";
    }

    let statusButton;

    // Debug log to check the actual status value
    console.log(
      `Booking ${booking.booking_id} status: "${booking.status}" (normalized: "${normalizedStatus}")`
    );

    // Show complete button only if status is NOT completed
    if (normalizedStatus === "completed") {
      statusButton =
        '<span style="color: green;"><i class="fas fa-check-circle"></i> Completed</span>';
    } else {
      // Show Complete button for pending, confirmed, or any other status
      statusButton = `<button class="complete-btn" onclick="completeBooking(${booking.booking_id})" data-booking-id="${booking.booking_id}" style="background:#0659e7; color:white; border:none; padding:8px 16px; border-radius:4px; cursor:pointer; font-weight:bold;">Complete</button>`;
    }

    const row = `
            <tr id="booking-row-${booking.booking_id}">
                <td>${sequentialNumber}</td>
                <td>
                    ${booking.user_name}<br>
                    <small style="color:#666;">${booking.user_email}</small>
                </td>
                <td>
                    ${booking.formatted_date}<br>
                    <small style="color:#666;">${booking.formatted_created_at}</small>
                </td>
                <td>${booking.user_phone}</td>
                <td>${booking.pickup}</td>
                <td>${booking.flight_type}</td>
                <td>
                    <small>
                        Weight: ${booking.weight}<br>
                        Age: ${booking.age}<br>
                        Condition: ${booking.medical_condition}
                    </small>
                </td>
                <td><span class="${statusClass}">${normalizedStatus}</span></td>
                <td class="action-cell">
                    ${statusButton}
                </td>
            </tr>`;
    tbody.append(row);
  });

  console.log(`Rendered ${bookings.length} bookings`);
}

function completeBooking(bookingId, isAutoComplete = false) {
  console.log(
    "Completing booking:",
    bookingId,
    isAutoComplete ? "(auto-complete)" : "(manual)"
  );

  // Disable the button to prevent multiple clicks (only for manual completion)
  if (!isAutoComplete) {
    const button = $(`.complete-btn[data-booking-id="${bookingId}"]`);
    button
      .prop("disabled", true)
      .html('<i class="fas fa-spinner fa-spin"></i> Processing...');
  }

  $.ajax({
    url: "Web/php/AJAX/fetchBookings.php",
    method: "POST",
    dataType: "json",
    contentType: "application/json",
    data: JSON.stringify({
      action: "complete",
      booking_id: parseInt(bookingId),
    }),
    success: function (response) {
      console.log("Complete booking response:", response);

      if (response.success) {
        // Update the UI immediately
        updateBookingStatus(bookingId, "completed");
        console.log(`Booking ${bookingId} marked as completed`);

        // Show success message only for manual completion
        if (!isAutoComplete) {
          let message = "Booking completed successfully!";
          if (response.passenger_name) {
            message += ` Notification sent to ${response.passenger_name}.`;
          }

          if (response.notification_error) {
            message += " (Note: Notification delivery may have failed)";
          }

          showSuccessMessage(message);
        }

        // Update statistics without re-rendering entire table
        updateStatisticsAfterCompletion();
      } else {
        console.error("Failed to complete booking:", response.message);

        if (!isAutoComplete) {
          showError(response.message || "Failed to complete booking");

          // Re-enable button on failure
          const button = $(`.complete-btn[data-booking-id="${bookingId}"]`);
          button
            .prop("disabled", false)
            .html('<i class="fas fa-check"></i> Complete');
        }
      }
    },
    error: function (xhr, status, error) {
      console.error("Error completing booking:", error);
      console.error("Response:", xhr.responseText);

      if (!isAutoComplete) {
        showError("Failed to complete booking. Please try again.");

        // Re-enable button on error
        const button = $(`.complete-btn[data-booking-id="${bookingId}"]`);
        button
          .prop("disabled", false)
          .html('<i class="fas fa-check"></i> Complete');
      }
    },
  });
}

function updateBookingStatus(bookingId, newStatus) {
  const row = $(`#booking-row-${bookingId}`);

  // Normalize the new status
  const normalizedStatus = newStatus.toLowerCase().trim();

  // Update status cell (8th column)
  const statusCell = row.find("td:nth-child(8) span");
  statusCell
    .text(normalizedStatus)
    .removeClass("status-pending status-confirmed")
    .addClass("status-completed");

  // Update action cell - Replace the button with non-clickable completed text
  const actionCell = row.find(".action-cell");
  actionCell.html(
    '<span style="color: green;"><i class="fas fa-check-circle"></i> Completed</span>'
  );
}

function displayStatistics(stats) {
  console.log("Displaying statistics:", stats);

  $("#totalBookings").text(stats.total || 0);
  $("#confirmedBookings").text(stats.confirmed || 0);
  $("#pendingBookings").text(stats.pending || 0);
  $("#todayBookings").text(stats.today || 0);

  // Add completed bookings display if element exists
  if ($("#completedBookings").length) {
    $("#completedBookings").text(stats.completed || 0);
  }
}

function showError(message) {
  const tbody = $("#bookingsTableBody");
  tbody.html(`
        <tr>
            <td colspan="9" style="text-align:center; color:red; padding:20px;">
                <strong>Error:</strong> ${message}<br>
                <button onclick="fetchData()" style="margin-top:10px; padding:5px 10px; background:#007bff; color:white; border:none; border-radius:3px; cursor:pointer;">Try Again</button>
            </td>
        </tr>
    `);
}

function updateStatisticsAfterCompletion() {
  $.ajax({
    url: "Web/php/AJAX/fetchBookings.php",
    method: "GET",
    dataType: "json",
    success: function (response) {
      if (response.success && response.statistics) {
        displayStatistics(response.statistics);
      }
    },
    error: function (xhr, status, error) {
      console.error("Error updating statistics:", error);
    },
  });
}

function showSuccessMessage(message) {
  // Remove any existing success messages
  $(".success-message").remove();

  const successDiv = $(
    '<div class="success-message" style="position:fixed;top:20px;right:20px;background:#4CAF50;color:white;padding:15px;border-radius:5px;z-index:1000;max-width:300px;box-shadow:0 2px 8px rgba(0,0,0,0.3);">' +
      message +
      "</div>"
  );
  $("body").append(successDiv);

  setTimeout(function () {
    successDiv.fadeOut(500, function () {
      successDiv.remove();
    });
  }, 5000);
}

function refreshData() {
  console.log("Manual refresh triggered");
  fetchData();
}

// Filter bookings function (if needed)
function filterBookings() {
  // This function would implement filtering logic
  console.log("Filter function called");
  // You can implement filtering logic here based on your requirements
}

// Global error handler
window.onerror = function (message, source, lineno, colno, error) {
  console.error("JavaScript Error:", message, "at", source + ":" + lineno);
  return false;
};

console.log("=== JAVASCRIPT LOADED ===");
