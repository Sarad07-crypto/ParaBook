<?php
  require 'avatar.php';
  require 'partials/header_C.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="Web/css/header_C.css?v=1.0" />
    <style>
        :root {
            --primary-color: #007bff;
            --gradient: linear-gradient(90deg, #1e90ff, #0056b3);
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
        }

        

        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            color: #333;
            
        }

        .stats-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .page-subtitle {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 20px;
        }

        .time-filter {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .filter-btn {
            padding: 8px 16px;
            border: 2px solid var(--primary-color);
            background: white;
            color: var(--primary-color);
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }

        .filter-btn.active,
        .filter-btn:hover {
            background: var(--primary-color);
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-icon {
            font-size: 2rem;
            color: var(--primary-color);
            opacity: 0.8;
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.95rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-change {
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 8px;
        }

        .positive {
            color: #28a745;
        }

        .negative {
            color: #dc3545;
        }

        .charts-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .chart-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--card-shadow);
        }

        .chart-title {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .chart-placeholder {
            height: 300px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 1.1rem;
            border: 2px dashed #ddd;
        }

        .weather-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .weather-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--card-shadow);
            text-align: center;
        }

        .weather-location {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .weather-temp {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .weather-condition {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }

        .weather-details {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #777;
        }

        .flight-status-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--card-shadow);
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 1.4rem;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .flight-list {
            display: grid;
            gap: 15px;
        }

        .flight-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }

        .flight-info {
            flex: 1;
        }

        .flight-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .flight-details {
            font-size: 0.9rem;
            color: #666;
        }

        .flight-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .instructor-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .instructor-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--card-shadow);
            text-align: center;
        }

        .instructor-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 2rem;
            font-weight: 700;
        }

        .instructor-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .instructor-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 15px;
        }

        .instructor-stat {
            text-align: center;
        }

        .instructor-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .instructor-stat-label {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
        }

        @media (max-width: 768px) {
            .stats-container {
                padding: 20px 10px;
            }

            .page-title {
                font-size: 2rem;
            }

            .charts-section {
                grid-template-columns: 1fr;
            }

            .time-filter {
                flex-wrap: wrap;
            }

            .flight-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }

        .loading-animation {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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

    <script>
        function setTimeFilter(period) {
            // Remove active class from all buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to clicked button
            event.target.classList.add('active');
            
            // Add loading animation
            const charts = document.querySelectorAll('.chart-placeholder');
            charts.forEach(chart => {
                chart.innerHTML = '<div class="loading-animation"></div><br>Loading ' + period + ' data...';
                
                // Simulate data loading
                setTimeout(() => {
                    const originalContent = chart.getAttribute('data-original') || chart.innerHTML;
                    if (period === 'today') {
                        chart.innerHTML = '<div><i class="fas fa-chart-line" style="font-size: 3rem; margin-bottom: 10px; display: block;"></i>Today\'s Data<br><small>Real-time statistics</small></div>';
                    } else if (period === 'week') {
                        chart.innerHTML = '<div><i class="fas fa-chart-area" style="font-size: 3rem; margin-bottom: 10px; display: block;"></i>Weekly Trends<br><small>7-day overview</small></div>';
                    } else if (period === 'month') {
                        chart.innerHTML = '<div><i class="fas fa-chart-bar" style="font-size: 3rem; margin-bottom: 10px; display: block;"></i>Monthly Analysis<br><small>30-day summary</small></div>';
                    } else {
                        chart.innerHTML = '<div><i class="fas fa-chart-pie" style="font-size: 3rem; margin-bottom: 10px; display: block;"></i>Yearly Overview<br><small>Annual performance</small></div>';
                    }
                }, 1000);
            });
            
            // Update stats based on period (simplified simulation)
            updateStats(period);
        }
        
        function updateStats(period) {
            const multipliers = {
                'today': 1,
                'week': 7,
                'month': 30,
                'year': 365
            };
            
            const baseStats = [147, 24850, 89, 23];
            const statElements = document.querySelectorAll('.stat-value');
            
            statElements.forEach((element, index) => {
                if (index < baseStats.length) {
                    const newValue = Math.floor(baseStats[index] * (multipliers[period] || 1) * (0.8 + Math.random() * 0.4));
                    if (index === 1) {
                        element.textContent = '$' + newValue.toLocaleString();
                    } else {
                        element.textContent = newValue.toLocaleString();
                    }
                }
            });
        }
        
        // Add hover effects to cards
        document.querySelectorAll('.stat-card, .chart-card, .instructor-card').forEach(card => {
            card.style.transform = 'translateY(0)';
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-5px)';
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
            });
        });
        
        // Simulate real-time updates
        setInterval(() => {
            const flightCount = document.querySelector('.stat-value');
            if (flightCount && flightCount.textContent !== '$24,850') {
                const currentValue = parseInt(flightCount.textContent);
                const newValue = currentValue + Math.floor(Math.random() * 3);
                flightCount.textContent = newValue;
            }
        }, 30000); // Update every 30 seconds
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Paragliding Statistics Dashboard Loaded');
            
            // Add fade-in animation to cards
            const cards = document.querySelectorAll('.stat-card, .chart-card, .weather-card, .instructor-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>

<?php
  require 'partials/footer.php';
?>