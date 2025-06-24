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
    // Use index + 1 for sequential numbering starting from 1
    const sequentialNumber = index + 1;

    // Determine status class and button
    const statusClass =
      booking.status === "confirmed" ? "status-confirmed" : "status-pending";
    const statusButton =
      booking.status === "confirmed"
        ? '<span style="color: green;">✓ confirmed</span>'
        : `<button class="complete-btn" onclick="completeBooking(${booking.booking_id})" data-booking-id="${booking.booking_id}">Complete</button>`;

    const row = `
            <tr id="booking-row-${booking.booking_id}">
                <td>${sequentialNumber}</td>
                <td>
                    ${booking.user_name}<br>
                    <small style="color:#666;">${booking.user_email}</small>
                </td>
                <td>
                    ${booking.formatted_date}<br>
                    <small style="color:#666;">${booking.created_at}</small>
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
                <td><span class="${statusClass}">${booking.status}</span></td>
                <td class="action-cell">
                    ${statusButton}
                </td>
            </tr>`;
    tbody.append(row);
  });

  console.log(`Rendered ${bookings.length} bookings`);
}

function completeBooking(bookingId) {
  console.log("Completing booking:", bookingId);

  // Disable the button to prevent multiple clicks
  const button = $(`.complete-btn[data-booking-id="${bookingId}"]`);
  button.prop("disabled", true).text("Processing...");

  $.ajax({
    url: "Web/php/AJAX/fetchBookings.php",
    method: "POST",
    dataType: "json",
    contentType: "application/json",
    data: JSON.stringify({
      action: "complete",
      booking_id: bookingId,
    }),
    success: function (response) {
      console.log("Complete booking response:", response);

      if (response.success) {
        // Update the UI immediately
        updateBookingStatus(bookingId, "confirmed");
        console.log(`Booking ${bookingId} marked as confirmed`);

        // Refresh statistics
        fetchData();

        // Show success message
        showSuccessMessage("Booking confirmed successfully!");
      } else {
        console.error("Failed to complete booking:", response.message);
        showError(response.message || "Failed to complete booking");

        // Re-enable button on failure
        button.prop("disabled", false).text("Complete");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error completing booking:", error);
      console.error("Response:", xhr.responseText);

      showError("Failed to complete booking. Please try again.");

      // Re-enable button on error
      button.prop("disabled", false).text("Complete");
    },
  });
}

function updateBookingStatus(bookingId, newStatus) {
  const row = $(`#booking-row-${bookingId}`);

  // Update status cell (now 8th column instead of 7th)
  const statusCell = row.find("td:nth-child(8) span");
  statusCell
    .text(newStatus)
    .removeClass("status-pending")
    .addClass("status-confirmed");

  // Update action cell
  const actionCell = row.find(".action-cell");
  actionCell.html('<span style="color: green;">✓ confirmed</span>');
}

function displayStatistics(stats) {
  console.log("Displaying statistics:", stats);

  $("#totalBookings").text(stats.total || 0);
  $("#confirmedBookings").text(stats.confirmed || 0);
  $("#pendingBookings").text(stats.pending || 0);
  $("#todayBookings").text(stats.today || 0);
}

function showError(message) {
  const tbody = $("#bookingsTableBody");
  tbody.html(`
        <tr>
            <td colspan="9" style="text-align:center; color:red; padding:20px;">
                <strong>Error:</strong> ${message}<br>
                <button onclick="fetchData()">Try Again</button>
            </td>
        </tr>
    `);
}

function showSuccessMessage(message) {
  const successDiv = $(
    '<div class="success-message" style="position:fixed;top:20px;right:40%;background:#4CAF50;color:white;padding:15px;border-radius:5px;z-index:1000;">' +
      message +
      "</div>"
  );
  $("body").append(successDiv);

  setTimeout(function () {
    successDiv.fadeOut(500, function () {
      successDiv.remove();
    });
  }, 3000);
}

function refreshData() {
  console.log("Manual refresh triggered");
  fetchData();
}

// Global error handler
window.onerror = function (message, source, lineno, colno, error) {
  console.error("JavaScript Error:", message, "at", source + ":" + lineno);
  return false;
};

console.log("=== JAVASCRIPT LOADED ===");
