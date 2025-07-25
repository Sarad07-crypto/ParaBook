<?php
  require 'avatar.php';
  require 'partials/header.php';
  require 'partials/nav_C.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Statistics</title>
    <link rel="stylesheet" href="Web/css/views.css?v=1.0" />
    <link rel="stylesheet" href="Web/css/statistics_C.css?v=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <!-- Add your existing CSS file here -->
    <style>
    /* Updated service item layout */
    .services-list {
        width: 100%;
        padding: 40px;
    }

    .service-item {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        margin: 8px 0;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        opacity: 0;
        animation: slideIn 0.5s ease forwards;
    }

    .service-item:not(:last-child) {
        border-bottom: 1px solid #ddd;
    }

    .service-info {
        flex: 1;
        margin-right: 15px;
        /* Add some space between service info and rating */
    }

    .service-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 4px;
    }

    .service-company {
        font-size: 0.9em;
        color: #7f8c8d;
        margin-bottom: 4px;
    }

    /* Rating styles - positioned on the right */
    .service-rating {
        flex-shrink: 0;
        /* Prevent rating from shrinking */
        text-align: right;
    }

    .service-rating .fas.fa-star,
    .service-rating .fas.fa-star-half-alt {
        color: #f1c40f;
        /* Yellow color for filled stars */
    }

    .service-rating .far.fa-star {
        color: #bdc3c7;
        /* Light gray for empty stars */
    }

    .service-rating .rating-text {
        color: #7f8c8d;
        font-size: 0.9em;
        margin-left: 5px;
    }

    .service-item,
    .flight-type-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        margin: 8px 0;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        opacity: 0;
        animation: slideIn 0.5s ease forwards;
    }

    .service-info,
    .flight-type-info {
        flex: 1;
    }

    .service-title,
    .flight-type-name {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 4px;
    }

    .service-company,
    .flight-type-details {
        font-size: 0.9em;
        color: #7f8c8d;
    }

    .flight-type-stats {
        text-align: right;
    }

    .flight-type-percentage {
        font-size: 1.2em;
        font-weight: 600;
        color: #27ae60;
    }

    .flight-type-total {
        font-size: 0.9em;
        color: #7f8c8d;
    }

    .no-flights {
        text-align: center;
        opacity: 0.7;
    }

    .status-info {
        background: #3498db;
        color: white;
    }

    @keyframes slideIn {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .loading-animation {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
    </style>
</head>

<body>
    <div class="stats-container">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-chart-line"></i> Statistics Dashboard
            </h1>
            <p class="page-subtitle">Real-time insights into your paragliding operations</p>

            <div class="time-filter">
                <button class="filter-btn active" onclick="setTimeFilter('today')">Today</button>
                <button class="filter-btn" onclick="setTimeFilter('week')">This Week</button>
                <button class="filter-btn" onclick="setTimeFilter('month')">This Month</button>
                <button class="filter-btn" onclick="setTimeFilter('year')">This Year</button>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <div class="stat-value">0</div>
                <div class="stat-label">Total Bookings</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
                <div class="stat-value">Rs.0</div>
                <div class="stat-label">Total Revenue</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-plane"></i>
                    </div>
                </div>
                <div class="stat-value">0</div>
                <div class="stat-label">Total Completed Flights</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="stat-value">0.0</div>
                <div class="stat-label">Average Rating</div>
            </div>
        </div>

        <!-- Charts Section: Display horizontally -->
        <div class="charts-section">
            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="fas fa-list"></i> All Services
                </h3>
                <div class="chart-placeholder">
                    <div>
                        <i class="fas fa-spinner fa-spin"
                            style="font-size: 3rem; margin-bottom: 10px; display: block;"></i>
                        Loading Services...
                        <br><small>Your company services will appear here</small>
                    </div>
                </div>
            </div>
            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="fas fa-chart-pie"></i> Flight Types & Prices
                </h3>
                <div class="chart-placeholder">
                    <div>
                        <i class="fas fa-spinner fa-spin"
                            style="font-size: 3rem; margin-bottom: 10px; display: block;"></i>
                        Loading Flight Data...
                        <br><small>Flight types with pricing information</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Flight Status -->
        <div class="flight-status-section">
            <h3 class="section-title">
                <i class="fas fa-plane-departure"></i> Today's Flight Status
            </h3>
            <div class="flight-list">
                <div class="flight-item">
                    <div class="flight-info">
                        <div class="flight-name">Loading today's flights...</div>
                        <div class="flight-details">
                            <i class="fas fa-spinner fa-spin"></i> Please wait while we fetch current data
                        </div>
                    </div>
                    <div class="flight-status status-pending">Loading</div>
                </div>
            </div>
        </div>
    </div>


    <script src="Web/scripts/statistics_C.js?v=1.0"></script>
</body>

</html>

<?php
  require 'partials/footer.php';
?>