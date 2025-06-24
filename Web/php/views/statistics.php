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
    <title>Statistics</title>
    <link rel="stylesheet" href="Web/css/header.css?v=1.0" />
    <link rel="stylesheet" href="Web/css/statistics.css?v=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
                <div class="stat-value">147</div>
                <div class="stat-label">Total Bookings</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> +12% from last month
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
                <div class="stat-value">$24,850</div>
                <div class="stat-label">Revenue</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> +18% from last month
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-value">89</div>
                <div class="stat-label">Active Customers</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> +7% from last month
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-plane"></i>
                    </div>
                </div>
                <div class="stat-value">23</div>
                <div class="stat-label">Flights Today</div>
                <div class="stat-change negative">
                    <i class="fas fa-arrow-down"></i> -3% from yesterday
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="stat-value">4.8</div>
                <div class="stat-label">Average Rating</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> +0.2 from last month
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                </div>
                <div class="stat-value">94%</div>
                <div class="stat-label">Safety Rate</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> Maintained
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="fas fa-chart-area"></i> Revenue Trends
                </h3>
                <div class="chart-placeholder">
                    <div>
                        <i class="fas fa-chart-line" style="font-size: 3rem; margin-bottom: 10px; display: block;"></i>
                        Interactive Revenue Chart
                        <br><small>Click time filters above to update data</small>
                    </div>
                </div>
            </div>

            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="fas fa-chart-pie"></i> Booking Types
                </h3>
                <div class="chart-placeholder">
                    <div>
                        <i class="fas fa-chart-pie" style="font-size: 3rem; margin-bottom: 10px; display: block;"></i>
                        Booking Distribution
                        <br><small>Tandem, Solo, Course</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weather Conditions -->
        <div class="weather-section">
            <div class="weather-card">
                <div class="weather-location">
                    <i class="fas fa-map-marker-alt"></i> Launch Site A
                </div>
                <div class="weather-temp">22°C</div>
                <div class="weather-condition">
                    <i class="fas fa-sun"></i> Clear Skies
                </div>
                <div class="weather-details">
                    <span><i class="fas fa-wind"></i> 8 km/h NE</span>
                    <span><i class="fas fa-eye"></i> 15km</span>
                </div>
            </div>

            <div class="weather-card">
                <div class="weather-location">
                    <i class="fas fa-map-marker-alt"></i> Launch Site B
                </div>
                <div class="weather-temp">19°C</div>
                <div class="weather-condition">
                    <i class="fas fa-cloud"></i> Partly Cloudy
                </div>
                <div class="weather-details">
                    <span><i class="fas fa-wind"></i> 12 km/h SW</span>
                    <span><i class="fas fa-eye"></i> 12km</span>
                </div>
            </div>

            <div class="weather-card">
                <div class="weather-location">
                    <i class="fas fa-map-marker-alt"></i> Landing Zone
                </div>
                <div class="weather-temp">24°C</div>
                <div class="weather-condition">
                    <i class="fas fa-sun"></i> Perfect
                </div>
                <div class="weather-details">
                    <span><i class="fas fa-wind"></i> 5 km/h E</span>
                    <span><i class="fas fa-eye"></i> 20km</span>
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
                        <div class="flight-name">Tandem Flight #001 - Sarah Johnson</div>
                        <div class="flight-details">
                            <i class="fas fa-clock"></i> 09:30 - 11:00 | Instructor: Mike Thompson
                        </div>
                    </div>
                    <div class="flight-status status-active">Completed</div>
                </div>

                <div class="flight-item">
                    <div class="flight-info">
                        <div class="flight-name">Solo Flight #002 - Alex Rodriguez</div>
                        <div class="flight-details">
                            <i class="fas fa-clock"></i> 11:30 - 13:00 | Advanced Level
                        </div>
                    </div>
                    <div class="flight-status status-active">In Progress</div>
                </div>

                <div class="flight-item">
                    <div class="flight-info">
                        <div class="flight-name">Course Flight #003 - Emma Davis</div>
                        <div class="flight-details">
                            <i class="fas fa-clock"></i> 14:00 - 16:00 | Instructor: Lisa Park
                        </div>
                    </div>
                    <div class="flight-status status-pending">Scheduled</div>
                </div>

                <div class="flight-item">
                    <div class="flight-info">
                        <div class="flight-name">Tandem Flight #004 - John Wilson</div>
                        <div class="flight-details">
                            <i class="fas fa-clock"></i> 10:00 - 11:30 | Instructor: Mark Brown
                        </div>
                    </div>
                    <div class="flight-status status-cancelled">Weather Cancelled</div>
                </div>
            </div>
        </div>

        <!-- Instructor Performance -->
        <div class="instructor-section">
            <div class="instructor-card">
                <div class="instructor-avatar">MT</div>
                <div class="instructor-name">Mike Thompson</div>
                <div class="instructor-stats">
                    <div class="instructor-stat">
                        <div class="instructor-stat-value">47</div>
                        <div class="instructor-stat-label">Flights</div>
                    </div>
                    <div class="instructor-stat">
                        <div class="instructor-stat-value">4.9</div>
                        <div class="instructor-stat-label">Rating</div>
                    </div>
                </div>
            </div>

            <div class="instructor-card">
                <div class="instructor-avatar">LP</div>
                <div class="instructor-name">Lisa Park</div>
                <div class="instructor-stats">
                    <div class="instructor-stat">
                        <div class="instructor-stat-value">38</div>
                        <div class="instructor-stat-label">Flights</div>
                    </div>
                    <div class="instructor-stat">
                        <div class="instructor-stat-value">4.8</div>
                        <div class="instructor-stat-label">Rating</div>
                    </div>
                </div>
            </div>

            <div class="instructor-card">
                <div class="instructor-avatar">MB</div>
                <div class="instructor-name">Mark Brown</div>
                <div class="instructor-stats">
                    <div class="instructor-stat">
                        <div class="instructor-stat-value">52</div>
                        <div class="instructor-stat-label">Flights</div>
                    </div>
                    <div class="instructor-stat">
                        <div class="instructor-stat-value">4.7</div>
                        <div class="instructor-stat-label">Rating</div>
                    </div>
                </div>
            </div>

            <div class="instructor-card">
                <div class="instructor-avatar">JS</div>
                <div class="instructor-name">James Smith</div>
                <div class="instructor-stats">
                    <div class="instructor-stat">
                        <div class="instructor-stat-value">29</div>
                        <div class="instructor-stat-label">Flights</div>
                    </div>
                    <div class="instructor-stat">
                        <div class="instructor-stat-value">4.9</div>
                        <div class="instructor-stat-label">Rating</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="Web/scripts/statistics.js?v=1.0"></script>
</body>

</html>

<?php
  require 'partials/footer.php';
?>