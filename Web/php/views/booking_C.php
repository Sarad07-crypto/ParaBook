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
                        <option value="">All Status</option>
                        <option value="confirmed">confirmed</option>
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
                <div class="stat-label">confirmed</div>
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


    <script src="Web/scripts/booking_C.js?v=1.0"></script>

</body>

</html>


<?php
  require 'partials/footer.php';
?>