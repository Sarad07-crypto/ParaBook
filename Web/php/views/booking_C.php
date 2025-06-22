<?php
  require 'avatar.php';
  require 'partials/header_C.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bookings Management</title>
    <link rel="stylesheet" href="Web/css/booking_C.css?v=1.0" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container">

        <div class="controls">
            <div class="controls-grid">
                <div class="control-group">
                    <label for="search">🔍 Search Bookings</label>
                    <input type="text" id="search" placeholder="Search by booking number, email, or location..."
                        onkeyup="filterBookings()" />
                </div>
                <div class="control-group">
                    <label for="statusFilter">📊 Filter by Status</label>
                    <select id="statusFilter" onchange="filterBookings()">
                        <option value="">All Statuses</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="pending">Pending</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="control-group">
                    <label for="dateFrom">📅 From Date</label>
                    <input type="date" id="dateFrom" onchange="filterBookings()" />
                </div>
                <div class="control-group">
                    <label for="dateTo">📅 To Date</label>
                    <input type="date" id="dateTo" onchange="filterBookings()" />
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number" id="totalBookings">0</div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="confirmedBookings">0</div>
                <div class="stat-label">Confirmed</div>
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
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Flight Date</th>
                        <th>Contact no.</th>
                        <th>Pickup Location</th>
                        <th>Flight Type</th>
                        <th>Passenger Info</th>
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

    <script>
    console.log("=== JAVASCRIPT STARTING ===");

    $(document).ready(function() {
        console.log("Document ready - starting data fetch...");
        fetchData();
    });

    function fetchData() {
        console.log("Fetching data from fetchBookings.php...");

        $.ajax({
            url: 'Web/php/AJAX/fetchBookings.php',
            method: 'GET',
            dataType: 'json',
            timeout: 10000,
            beforeSend: function() {
                console.log("AJAX request starting...");
                $('#loadingIndicator').show();
            },
            success: function(response) {
                console.log("SUCCESS! Response received:", response);
                $('#loadingIndicator').hide();

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
            error: function(xhr, status, error) {
                console.error("AJAX ERROR!");
                console.error("Status:", status);
                console.error("Error:", error);
                console.error("Status Code:", xhr.status);
                console.error("Response Text:", xhr.responseText);

                $('#loadingIndicator').hide();
                showError(`Failed to load data: ${error}`);
            }
        });
    }

    function renderBookings(bookings) {
        console.log("Rendering bookings...");
        const tbody = $('#bookingsTableBody');
        tbody.empty();

        if (!bookings || bookings.length === 0) {
            tbody.html('<tr><td colspan="9" style="text-align:center;">No bookings found</td></tr>');
            return;
        }

        bookings.forEach(function(booking) {
            // Determine status class and button
            const statusClass = booking.status === 'confirmed' ? 'status-confirmed' : 'status-pending';
            const statusButton = booking.status === 'confirmed' ?
                '<span style="color: green;">✓ Completed</span>' :
                `<button class="complete-btn" onclick="completeBooking(${booking.booking_id})" data-booking-id="${booking.booking_id}">Complete</button>`;

            const row = `
            <tr id="booking-row-${booking.booking_id}">
                <td>${booking.booking_no}</td>
                <td>
                    ${booking.user_name}<br>
                    <small style="color:#666;">${booking.user_email}</small>
                </td>
                <td>${booking.formatted_date}</td>
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
        button.prop('disabled', true).text('Processing...');

        $.ajax({
            url: 'Web/php/AJAX/fetchBookings.php',
            method: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'complete',
                booking_id: bookingId
            }),
            success: function(response) {
                console.log("Complete booking response:", response);

                if (response.success) {
                    // Update the UI immediately
                    updateBookingStatus(bookingId, 'confirmed');
                    console.log(`Booking ${bookingId} marked as confirmed`);

                    // Refresh statistics
                    fetchData();

                    // Show success message
                    showSuccessMessage("Booking completed successfully!");
                } else {
                    console.error("Failed to complete booking:", response.message);
                    showError(response.message || "Failed to complete booking");

                    // Re-enable button on failure
                    button.prop('disabled', false).text('Complete');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error completing booking:", error);
                console.error("Response:", xhr.responseText);

                showError("Failed to complete booking. Please try again.");

                // Re-enable button on error
                button.prop('disabled', false).text('Complete');
            }
        });
    }

    function updateBookingStatus(bookingId, newStatus) {
        const row = $(`#booking-row-${bookingId}`);

        // Update status cell (now 8th column instead of 7th)
        const statusCell = row.find('td:nth-child(8) span');
        statusCell.text(newStatus).removeClass('status-pending').addClass('status-confirmed');

        // Update action cell
        const actionCell = row.find('.action-cell');
        actionCell.html('<span style="color: green;">✓ Completed</span>');
    }

    function displayStatistics(stats) {
        console.log("Displaying statistics:", stats);

        $('#totalBookings').text(stats.total || 0);
        $('#confirmedBookings').text(stats.confirmed || 0);
        $('#pendingBookings').text(stats.pending || 0);
        $('#todayBookings').text(stats.today || 0);
    }

    function showError(message) {
        const tbody = $('#bookingsTableBody');
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
            message + '</div>');
        $('body').append(successDiv);

        // Remove after 3 seconds
        setTimeout(function() {
            successDiv.fadeOut(500, function() {
                successDiv.remove();
            });
        }, 3000);
    }

    function refreshData() {
        console.log("Manual refresh triggered");
        fetchData();
    }

    // Global error handler
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