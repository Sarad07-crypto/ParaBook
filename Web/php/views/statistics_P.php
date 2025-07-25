<?php
  require 'avatar.php';
  require 'partials/header.php';
  require 'partials/nav_P.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics</title>
    <link rel="stylesheet" href="Web/css/statistics_P.css?v=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="container">
        <!-- Statistics Overview -->
        <div class="stats-overview">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-plane"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalFlights">0</h3>
                    <p>Total Flights</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalSpent">$0</h3>
                    <p>Total Spent</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-info">
                    <h3 id="avgRating">0.0</h3>
                    <p>Average Rating</p>
                </div>
            </div>


        </div>

        <!-- Filter Options -->
        <div class="filter-section">
            <div class="filter-group">
                <label for="dateFilter">Filter by Date:</label>
                <select id="dateFilter">
                    <option value="all">All Time</option>
                    <option value="thisMonth">This Month</option>
                    <option value="lastMonth">Last Month</option>
                    <option value="thisYear">This Year</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="statusFilter">Filter by Status:</label>
                <select id="statusFilter">
                    <option value="all">All Bookings</option>
                    <option value="completed">Completed</option>
                    <option value="upcoming">Upcoming</option>
                </select>
            </div>

            <div class="search-group">
                <input type="text" id="searchInput" placeholder="Search flights...">
                <i class="fas fa-search"></i>
            </div>
        </div>

        <!-- Booking History -->
        <div class="booking-history">
            <h2>Booking History</h2>
            <div class="table-container">
                <table id="bookingTable">
                    <thead>
                        <tr>
                            <th>Flight Date</th>
                            <th>Flight Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Rating</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bookingTableBody">
                        <!-- Dynamic content will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>


    </div>

    <!-- Rating Modal -->
    <div id="ratingModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Rate Your Flight</h3>
            <div class="rating-stars">
                <i class="fas fa-star" data-rating="1"></i>
                <i class="fas fa-star" data-rating="2"></i>
                <i class="fas fa-star" data-rating="3"></i>
                <i class="fas fa-star" data-rating="4"></i>
                <i class="fas fa-star" data-rating="5"></i>
            </div>
            <textarea id="reviewText" placeholder="Share your experience..."></textarea>
            <button id="submitRating">Submit Rating</button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="Web/scripts/statistics_P.js"></script>
</body>

</html>

<?php
  require 'partials/footer.php';
?>