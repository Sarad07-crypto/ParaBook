<?php
  require 'avatar.php';
  require 'partials/header.php';
  require 'partials/nav_C.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bookings</title>
    <link rel="stylesheet" href="Web/css/booking_C.css?v=1.0" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 600px;
        border-radius: 8px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
    }

    .modal-header {
        border-bottom: 1px solid #ddd;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .detail-row {
        display: flex;
        margin-bottom: 15px;
        padding: 10px;
        background-color: #f9f9f9;
        border-radius: 5px;
    }

    .detail-label {
        font-weight: bold;
        width: 150px;
        color: #333;
    }

    .detail-value {
        flex: 1;
        color: #666;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .btn {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .btn-view {
        background-color: #17a2b8;
        color: white;
    }

    .btn-update {
        background-color: #ffc107;
        color: #212529;
    }

    .btn-complete {
        background-color: #0659e7;
        color: white;
    }

    .btn:hover {
        opacity: 0.8;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .form-group input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .modal-footer {
        border-top: 1px solid #ddd;
        padding-top: 15px;
        margin-top: 20px;
        text-align: right;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
        margin-right: 10px;
    }

    .status-cancelled {
        background-color: #dc3545;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
    }
    </style>
</head>

<body>
    <div class="container">

        <!-- Statistics Section -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number" id="totalBookings">0</div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="cancelledBookings">0</div>
                <div class="stat-label">Cancelled</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="pendingBookings">0</div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="todayBookings">0</div>
                <div class="stat-label">Today's Bookings</div>
            </div>
        </div>

        <!-- Bookings Table Section -->
        <div class="bookings-container">
            <div class="bookings-header">
                <h2>📋 All Bookings</h2>
                <div class="pagination-info" id="paginationInfo">
                    Showing 1-10 of 0 bookings
                </div>
            </div>

            <div id="loadingIndicator" class="loading" style="display: none">
                <p>Loading bookings...</p>
            </div>

            <table class="booking-table" id="bookingsTable">
                <thead>
                    <tr>
                        <th>S.N.</th>
                        <th>Customer</th>
                        <th>Flight Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="bookingsTableBody">
                </tbody>
            </table>

            <div id="noBookings" class="no-bookings" style="display: none">
                <p>📭 No bookings found matching your criteria.</p>
            </div>

            <div class="pagination" id="paginationControls">
                <button onclick="goToPage(1)" id="firstBtn">« First</button>
                <button onclick="previousPage()" id="prevBtn">‹ Previous</button>
                <span id="pageNumbers"></span>
                <button onclick="nextPage()" id="nextBtn">Next ›</button>
                <button onclick="goToPage(totalPages)" id="lastBtn">Last »</button>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeViewModal()">&times;</span>
                <h2>Booking Details</h2>
            </div>
            <div id="viewModalBody">
                <!-- Details will be populated here -->
            </div>
        </div>
    </div>

    <!-- Update Flight Date Modal -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeUpdateModal()">&times;</span>
                <h2>Update Flight Date</h2>
            </div>
            <div id="updateModalBody">
                <form id="updateForm">
                    <div class="form-group">
                        <label for="newFlightDate">New Flight Date:</label>
                        <input type="date" id="newFlightDate" name="newFlightDate" required>
                    </div>
                    <input type="hidden" id="updateBookingId" name="bookingId">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeUpdateModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveFlightDate()">Update Date</button>
            </div>
        </div>
    </div>


    <script src="Web/scripts/booking_C.js?v=1.0"></script>
    <script>
    console.log("=== JAVASCRIPT STARTING ===");

    $(document).ready(function() {
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
            beforeSend: function() {
                console.log("AJAX request starting...");
                $("#loadingIndicator").show();
            },
            success: function(response) {
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
            error: function(xhr, status, error) {
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
        today.setHours(0, 0, 0, 0);

        bookings.forEach(function(booking) {
            const normalizedStatus = booking.status ?
                booking.status.toLowerCase().trim() :
                "pending";

            if (normalizedStatus === "pending" && booking.date) {
                const bookingDate = new Date(booking.date);
                bookingDate.setHours(0, 0, 0, 0);

                if (bookingDate < today) {
                    console.log(`Auto-completing expired booking: ${booking.booking_id}`);
                    completeBooking(booking.booking_id, true);
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
                '<tr><td colspan="5" style="text-align:center;">No bookings found</td></tr>'
            );
            return;
        }

        bookings.forEach(function(booking, index) {
            const sequentialNumber = index + 1;

            let normalizedStatus = booking.status ?
                booking.status.toLowerCase().trim() :
                "pending";

            let statusClass = "status-pending";
            if (normalizedStatus === "completed") {
                statusClass = "status-completed";
            } else if (normalizedStatus === "confirmed") {
                statusClass = "status-confirmed";
            } else if (normalizedStatus === "cancelled") {
                statusClass = "status-cancelled";
            }

            console.log(
                `Booking ${booking.booking_id} status: "${booking.status}" (normalized: "${normalizedStatus}")`
            );

            // Action buttons with proper state management
            let actionButtons = `
        <div class="action-buttons">
            <button class="btn btn-view" onclick="viewBookingDetails(${booking.booking_id})">View</button>`;

            // Update button - disable if booking is completed or cancelled
            if (normalizedStatus === "completed" || normalizedStatus === "cancelled") {
                actionButtons += `
            <button class="btn btn-update" 
                    onclick="openUpdateModal(${booking.booking_id}, '${booking.date}')" 
                    disabled 
                    style="opacity: 0.5; cursor: not-allowed; pointer-events: none;" 
                    title="Cannot update ${normalizedStatus} booking">Update</button>`;
            } else {
                actionButtons +=
                    `<button class="btn btn-update" onclick="openUpdateModal(${booking.booking_id}, '${booking.date}')">Update</button>`;
            }

            // Cancel button - disable if booking is completed or cancelled
            if (normalizedStatus === "completed" || normalizedStatus === "cancelled") {
                actionButtons += `
            <button class="btn btn-cancel" 
                    disabled 
                    style="opacity: 0.5; cursor: not-allowed; pointer-events: none; background-color: #dc3545; color: white;" 
                    title="Cannot cancel ${normalizedStatus} booking">Cancel</button>`;
            } else {
                actionButtons +=
                    `<button class="btn btn-cancel" onclick="confirmCancelBooking(${booking.booking_id})" data-booking-id="${booking.booking_id}" style="background-color: #dc3545; color: white;">Cancel</button>`;
            }

            // Complete button or status display
            if (normalizedStatus === "completed") {
                actionButtons +=
                    '<span style="color: green;"><i class="fas fa-check-circle"></i> Completed</span>';
            } else if (normalizedStatus === "cancelled") {
                actionButtons +=
                    '<span style="color: red;"><i class="fas fa-times-circle"></i> Cancelled</span>';
            } else {
                actionButtons +=
                    `<button class="btn btn-complete" onclick="confirmCompleteBooking(${booking.booking_id})" data-booking-id="${booking.booking_id}">Complete</button>`;
            }

            actionButtons += '</div>';

            const row = `
                <tr id="booking-row-${booking.booking_id}" data-booking='${JSON.stringify(booking)}'>
                    <td>${sequentialNumber}</td>
                    <td>
                        ${booking.user_name}<br>
                        <small style="color:#666;">${booking.user_email}</small>
                    </td>
                    <td>
                        ${booking.formatted_date}<br>
                        <small style="color:#666;">${booking.formatted_created_at}</small>
                    </td>
                    <td><span class="${statusClass}">${normalizedStatus}</span></td>
                    <td class="action-cell">
                        ${actionButtons}
                    </td>
                </tr>`;
            tbody.append(row);
        });

        console.log(`Rendered ${bookings.length} bookings`);
    }

    function viewBookingDetails(bookingId) {
        const row = $(`#booking-row-${bookingId}`);
        const bookingData = JSON.parse(row.attr('data-booking'));

        const detailsHtml = `
                <div class="detail-row">
                    <div class="detail-label">Contact No:</div>
                    <div class="detail-value">${bookingData.user_phone}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Pickup Location:</div>
                    <div class="detail-value">${bookingData.pickup}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Flight Type:</div>
                    <div class="detail-value">${bookingData.flight_type}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Weight:</div>
                    <div class="detail-value">${bookingData.weight}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Age:</div>
                    <div class="detail-value">${bookingData.age}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Medical Condition:</div>
                    <div class="detail-value">${bookingData.medical_condition}</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Gender:</div>
                    <div class="detail-value">${bookingData.gender}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Country:</div>
                    <div class="detail-value">${bookingData.country}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date of Birth:</div>
                    <div class="detail-value">${bookingData.dob}</div>
                </div>
            `;

        $('#viewModalBody').html(detailsHtml);
        $('#viewModal').show();
    }

    function closeViewModal() {
        $('#viewModal').hide();
    }

    function openUpdateModal(bookingId, currentDate) {
        $('#updateBookingId').val(bookingId);
        $('#newFlightDate').val(currentDate);
        $('#updateModal').show();
    }

    function closeUpdateModal() {
        $('#updateModal').hide();
        $('#updateForm')[0].reset();
    }

    function saveFlightDate() {
        const bookingId = $('#updateBookingId').val();
        const newDate = $('#newFlightDate').val();

        if (!newDate) {
            alert('Please select a new flight date');
            return;
        }

        $.ajax({
            url: "Web/php/AJAX/fetchBookings.php",
            method: "POST",
            dataType: "json",
            contentType: "application/json",
            data: JSON.stringify({
                action: "update_date",
                booking_id: parseInt(bookingId),
                new_date: newDate
            }),
            success: function(response) {
                if (response.success) {
                    showSuccessMessage('Flight date updated successfully!');
                    closeUpdateModal();
                    fetchData(); // Refresh the data
                } else {
                    alert('Error: ' + (response.message || 'Failed to update flight date'));
                }
            },
            error: function(xhr, status, error) {
                console.error("Error updating flight date:", error);
                alert('Failed to update flight date. Please try again.');
            }
        });
    }

    function completeBooking(bookingId, isAutoComplete = false) {
        console.log(
            "Completing booking:",
            bookingId,
            isAutoComplete ? "(auto-complete)" : "(manual)"
        );

        if (!isAutoComplete) {
            const button = $(`.btn-complete[data-booking-id="${bookingId}"]`);
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
            success: function(response) {
                console.log("Complete booking response:", response);

                if (response.success) {
                    updateBookingStatus(bookingId, "completed");
                    console.log(`Booking ${bookingId} marked as completed`);

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

                    updateStatisticsAfterCompletion();
                } else {
                    console.error("Failed to complete booking:", response.message);

                    if (!isAutoComplete) {
                        showError(response.message || "Failed to complete booking");

                        const button = $(`.btn-complete[data-booking-id="${bookingId}"]`);
                        button
                            .prop("disabled", false)
                            .html('Complete');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error("Error completing booking:", error);
                console.error("Response:", xhr.responseText);

                if (!isAutoComplete) {
                    showError("Failed to complete booking. Please try again.");

                    const button = $(`.btn-complete[data-booking-id="${bookingId}"]`);
                    button
                        .prop("disabled", false)
                        .html('Complete');
                }
            },
        });
    }


    // Add confirmation function for completing booking
    function confirmCompleteBooking(bookingId) {
        if (confirm('Are you sure you want to mark this booking as completed? This action cannot be undone.')) {
            completeBooking(bookingId);
        }
    }

    // Add confirmation function for cancelling booking
    function confirmCancelBooking(bookingId) {
        if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
            cancelBooking(bookingId);
        }
    }

    // Add cancelBooking function
    function cancelBooking(bookingId) {
        console.log("Cancelling booking:", bookingId);

        const button = $(`.btn-cancel[data-booking-id="${bookingId}"]`);
        button
            .prop("disabled", true)
            .html('<i class="fas fa-spinner fa-spin"></i> Cancelling...');

        $.ajax({
            url: "Web/php/AJAX/fetchBookings.php",
            method: "POST",
            dataType: "json",
            contentType: "application/json",
            data: JSON.stringify({
                action: "cancel",
                booking_id: parseInt(bookingId),
            }),
            success: function(response) {
                console.log("Cancel booking response:", response);

                if (response.success) {
                    updateBookingStatus(bookingId, "cancelled");
                    console.log(`Booking ${bookingId} marked as cancelled`);

                    let message = "Booking cancelled successfully!";
                    if (response.passenger_name) {
                        message += ` Notification sent to ${response.passenger_name}.`;
                    }

                    if (response.notification_error) {
                        message += " (Note: Notification delivery may have failed)";
                    }

                    showSuccessMessage(message);
                    updateStatisticsAfterCompletion();
                } else {
                    console.error("Failed to cancel booking:", response.message);
                    showError(response.message || "Failed to cancel booking");

                    const button = $(`.btn-cancel[data-booking-id="${bookingId}"]`);
                    button
                        .prop("disabled", false)
                        .html('Cancel');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error cancelling booking:", error);
                console.error("Response:", xhr.responseText);

                showError("Failed to cancel booking. Please try again.");

                const button = $(`.btn-cancel[data-booking-id="${bookingId}"]`);
                button
                    .prop("disabled", false)
                    .html('Cancel');
            },
        });
    }

    function updateBookingStatus(bookingId, newStatus) {
        const row = $(`#booking-row-${bookingId}`);
        const normalizedStatus = newStatus.toLowerCase().trim();

        // Update status display
        const statusCell = row.find("td:nth-child(4) span");
        statusCell
            .text(normalizedStatus)
            .removeClass("status-pending status-confirmed status-completed status-cancelled")
            .addClass(`status-${normalizedStatus}`);

        const actionCell = row.find(".action-cell");
        const currentButtons = actionCell.find('.action-buttons');

        if (normalizedStatus === "completed") {
            // Replace the complete button with completed status
            currentButtons.find('.btn-complete').replaceWith(
                '<span style="color: green;"><i class="fas fa-check-circle"></i> Completed</span>'
            );

            // Disable the update and cancel buttons
            const updateButton = currentButtons.find('.btn-update');
            const cancelButton = currentButtons.find('.btn-cancel');

            updateButton
                .prop('disabled', true)
                .css({
                    'opacity': '0.5',
                    'cursor': 'not-allowed',
                    'pointer-events': 'none'
                })
                .attr('title', 'Cannot update completed booking');

            cancelButton
                .prop('disabled', true)
                .css({
                    'opacity': '0.5',
                    'cursor': 'not-allowed',
                    'pointer-events': 'none'
                })
                .attr('title', 'Cannot cancel completed booking');

        } else if (normalizedStatus === "cancelled") {
            // Replace the cancel button with cancelled status
            currentButtons.find('.btn-cancel').replaceWith(
                '<span style="color: red;"><i class="fas fa-times-circle"></i> Cancelled</span>'
            );

            // Disable the update and complete buttons
            const updateButton = currentButtons.find('.btn-update');
            const completeButton = currentButtons.find('.btn-complete');

            updateButton
                .prop('disabled', true)
                .css({
                    'opacity': '0.5',
                    'cursor': 'not-allowed',
                    'pointer-events': 'none'
                })
                .attr('title', 'Cannot update cancelled booking');

            completeButton
                .prop('disabled', true)
                .css({
                    'opacity': '0.5',
                    'cursor': 'not-allowed',
                    'pointer-events': 'none'
                })
                .attr('title', 'Cannot complete cancelled booking');
        }
    }

    function displayStatistics(stats) {
        console.log("Displaying statistics:", stats);

        $("#totalBookings").text(stats.total || 0);
        $("#confirmedBookings").text(stats.confirmed || 0);
        $("#pendingBookings").text(stats.pending || 0);
        $("#todayBookings").text(stats.today || 0);

        if ($("#completedBookings").length) {
            $("#completedBookings").text(stats.completed || 0);
        }

        if ($("#cancelledBookings").length) {
            $("#cancelledBookings").text(stats.cancelled || 0);
        }
    }

    function showError(message) {
        const tbody = $("#bookingsTableBody");
        tbody.html(`
                <tr>
                    <td colspan="5" style="text-align:center; color:red; padding:20px;">
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
            success: function(response) {
                if (response.success && response.statistics) {
                    displayStatistics(response.statistics);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error updating statistics:", error);
            },
        });
    }

    function showSuccessMessage(message) {
        $(".success-message").remove();

        const successDiv = $(
            '<div class="success-message" style="position:fixed;top:20px;right:20px;background:#4CAF50;color:white;padding:15px;border-radius:5px;z-index:1000;max-width:300px;box-shadow:0 2px 8px rgba(0,0,0,0.3);">' +
            message +
            "</div>"
        );
        $("body").append(successDiv);

        setTimeout(function() {
            successDiv.fadeOut(500, function() {
                successDiv.remove();
            });
        }, 5000);
    }

    function refreshData() {
        console.log("Manual refresh triggered");
        fetchData();
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        const viewModal = document.getElementById('viewModal');
        const updateModal = document.getElementById('updateModal');

        if (event.target == viewModal) {
            viewModal.style.display = "none";
        }
        if (event.target == updateModal) {
            updateModal.style.display = "none";
        }
    }

    window.onerror = function(message, source, lineno, colno, error) {
        console.error("JavaScript Error:", message, "at", source + ":" + lineno);
        return false;
    };

    console.log("=== JAVASCRIPT LOADED ===");
    </script>
</body>

</html>


<?php
  require 'partials/footer.php';
?>