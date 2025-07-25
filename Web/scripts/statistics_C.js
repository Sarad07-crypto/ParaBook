// Global variables to store current data
let currentData = null;
let currentFilter = "today";

// Initialize dashboard when page loads
document.addEventListener("DOMContentLoaded", () => {
  console.log("Company Statistics Dashboard Loaded");

  // Add fade-in animation to cards
  const cards = document.querySelectorAll(
    ".stat-card, .chart-card, .instructor-card"
  );
  cards.forEach((card, index) => {
    card.style.opacity = "0";
    card.style.transform = "translateY(20px)";

    setTimeout(() => {
      card.style.transition = "opacity 0.6s ease, transform 0.6s ease";
      card.style.opacity = "1";
      card.style.transform = "translateY(0)";
    }, index * 100);
  });

  // Load initial data
  loadStatistics("today");
});

// Time filter function
function setTimeFilter(period) {
  // Remove active class from all buttons
  document.querySelectorAll(".filter-btn").forEach((btn) => {
    btn.classList.remove("active");
  });

  // Add active class to clicked button
  event.target.classList.add("active");
  currentFilter = period;

  // Add loading animation
  showLoadingAnimation(period);

  // Load data from API
  loadStatistics(period);
}

// Show loading animation
function showLoadingAnimation(period) {
  const charts = document.querySelectorAll(".chart-placeholder");
  charts.forEach((chart) => {
    chart.innerHTML = `
            <div class="loading-animation"></div>
            <br>Loading ${period} data...
        `;
  });

  // Show loading on stat cards
  document.querySelectorAll(".stat-value").forEach((element) => {
    element.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  });
}

// Load statistics from API
async function loadStatistics(timeFilter) {
  try {
    const response = await fetch(
      `Web/php/AJAX/statistics_C.php?time_filter=${timeFilter}`
    );
    const result = await response.json();

    if (result.success) {
      currentData = result.data;
      updateDashboard(result.data, timeFilter);
    } else {
      console.error("API Error:", result.error);
      showError("Failed to load statistics: " + result.error);
    }
  } catch (error) {
    console.error("Network Error:", error);
    showError("Network error occurred while loading data");
  }
}

// Update dashboard with new data
function updateDashboard(data, timeFilter) {
  // Update main statistics
  updateMainStats(data.statistics);

  // Update services list (replaces revenue trends)
  updateServicesList(data.services);

  // Update flight types chart
  updateFlightTypesChart(data.flight_types);

  // Update today's flights (always shows current date)
  updateTodaysFlights(data.todays_flights);

  // Add completion animation
  setTimeout(() => {
    showCompletionMessage(timeFilter);
  }, 1000);
}

// Update main statistics cards
function updateMainStats(stats) {
  const statElements = document.querySelectorAll(".stat-value");
  const changes = stats.changes || {};

  // Total Bookings
  if (statElements[0]) {
    animateValue(statElements[0], 0, stats.total_bookings, 1000);
    updateChangeIndicator(
      statElements[0].parentElement,
      changes.bookings || 0,
      "bookings"
    );
  }

  // Total Revenue
  if (statElements[1]) {
    animateValue(statElements[1], 0, stats.total_revenue, 1000, true);
    updateChangeIndicator(
      statElements[1].parentElement,
      changes.revenue || 0,
      "revenue"
    );
  }

  // Total Flights
  if (statElements[2]) {
    animateValue(statElements[2], 0, stats.total_flights, 1000);
    updateChangeIndicator(
      statElements[2].parentElement,
      changes.flights || 0,
      "flights"
    );
  }

  // Average Rating
  if (statElements[3]) {
    animateValue(statElements[3], 0, stats.avg_rating, 1000, false, 1);
    updateChangeIndicator(
      statElements[3].parentElement,
      changes.rating || 0,
      "rating"
    );
  }
}

// Animate number values
function animateValue(
  element,
  start,
  end,
  duration,
  isCurrency = false,
  decimals = 0
) {
  const startTime = Date.now();
  const startValue = start;
  const endValue = end;

  function updateValue() {
    const currentTime = Date.now();
    const elapsed = currentTime - startTime;
    const progress = Math.min(elapsed / duration, 1);

    const current = startValue + (endValue - startValue) * progress;

    if (isCurrency) {
      element.textContent = "$" + Math.floor(current).toLocaleString();
    } else if (decimals > 0) {
      element.textContent = current.toFixed(decimals);
    } else {
      element.textContent = Math.floor(current).toLocaleString();
    }

    if (progress < 1) {
      requestAnimationFrame(updateValue);
    }
  }

  updateValue();
}

// Update change indicator
function updateChangeIndicator(cardElement, change, type) {
  const changeElement = cardElement.querySelector(".stat-change");
  if (!changeElement) return;

  const isPositive = change >= 0;
  const changeText = Math.abs(change);

  changeElement.className = `stat-change ${
    isPositive ? "positive" : "negative"
  }`;

  let changeString = "";
  if (type === "rating") {
    changeString = `${isPositive ? "+" : ""}${change.toFixed(
      1
    )} from last ${currentFilter}`;
  } else {
    changeString = `${
      isPositive ? "+" : ""
    }${changeText}% from last ${currentFilter}`;
  }

  changeElement.innerHTML = `
        <i class="fas fa-arrow-${
          isPositive ? "up" : "down"
        }"></i> ${changeString}
    `;
}

// Update services list (replaces revenue trends chart)
function updateServicesList(services) {
  const chartElement = document.querySelector(".chart-card .chart-placeholder");
  if (!chartElement) return;

  if (services.length === 0) {
    chartElement.innerHTML = `
            <div style="text-align: center; color: #666;">
                <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                No services found
                <br><small>Add services to see them here</small>
            </div>
        `;
    return;
  }

  let servicesList = '<div class="services-list">';
  services.forEach((service, index) => {
    // Generate star rating HTML
    const starRating = generateStarRating(
      service.avg_rating,
      service.rating_count
    );

    servicesList += `
            <div class="service-item" style="animation-delay: ${index * 0.1}s">
                <div class="service-info">
                    <div class="service-title">${service.title}</div>
                    <div class="service-company">${service.company}</div>
                    ${starRating}
                </div>
                <div class="service-id">#${service.id}</div>
            </div>
        `;
  });
  servicesList += "</div>";

  chartElement.innerHTML = servicesList;
}

// Generate star rating HTML
function generateStarRating(rating, ratingCount) {
  if (ratingCount === 0) {
    return '<div class="service-rating">No ratings yet</div>';
  }

  let starsHTML = '<div class="service-rating">';

  // Generate 5 stars
  for (let i = 1; i <= 5; i++) {
    if (i <= Math.floor(rating)) {
      // Full star
      starsHTML += '<i class="fas fa-star"></i>';
    } else if (i === Math.ceil(rating) && rating % 1 !== 0) {
      // Half star
      starsHTML += '<i class="fas fa-star-half-alt"></i>';
    } else {
      // Empty star
      starsHTML += '<i class="far fa-star"></i>';
    }
  }

  starsHTML += ` <span class="rating-text">${rating.toFixed(
    1
  )} (${ratingCount} review${ratingCount !== 1 ? "s" : ""})</span>`;
  starsHTML += "</div>";

  return starsHTML;
}

// Update flight types chart
function updateFlightTypesChart(flightTypes) {
  const chartElements = document.querySelectorAll(
    ".chart-card .chart-placeholder"
  );
  const flightTypesChart = chartElements[1]; // Second chart for flight types

  if (!flightTypesChart) return;

  if (flightTypes.length === 0) {
    flightTypesChart.innerHTML = `
            <div style="text-align: center; color: #666;">
                <i class="fas fa-chart-pie" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                No flight data available
                <br><small>Bookings will appear here</small>
            </div>
        `;
    return;
  }

  let chartContent = '<div class="flight-types-chart">';
  flightTypes.forEach((type, index) => {
    const percentage =
      flightTypes.length > 0
        ? (
            (type.count / flightTypes.reduce((sum, t) => sum + t.count, 0)) *
            100
          ).toFixed(1)
        : 0;

    chartContent += `
            <div class="flight-type-item" style="animation-delay: ${
              index * 0.1
            }s">
                <div class="flight-type-info">
                    <div class="flight-type-name">${type.type}</div>
                    <div class="flight-type-details">
                        ${type.count} flights • $${type.avg_price.toFixed(
      2
    )} avg
                    </div>
                </div>
                <div class="flight-type-stats">
                    <div class="flight-type-percentage">${percentage}%</div>
                    <div class="flight-type-total">$${type.total_amount.toLocaleString()}</div>
                </div>
            </div>
        `;
  });
  chartContent += "</div>";

  flightTypesChart.innerHTML = chartContent;
}

// Update today's flights
function updateTodaysFlights(flights) {
  const flightList = document.querySelector(".flight-list");
  if (!flightList) return;

  if (flights.length === 0) {
    flightList.innerHTML = `
            <div class="flight-item no-flights">
                <div class="flight-info">
                    <div class="flight-name">No flights scheduled for today</div>
                    <div class="flight-details">
                        <i class="fas fa-calendar"></i> Check back tomorrow for new bookings
                    </div>
                </div>
                <div class="flight-status status-info">No Data</div>
            </div>
        `;
    return;
  }

  let flightHTML = "";
  flights.forEach((flight, index) => {
    const statusClass = getStatusClass(flight.status);
    const statusText = getStatusText(flight.status);

    flightHTML += `
            <div class="flight-item" style="animation-delay: ${index * 0.1}s">
                <div class="flight-info">
                    <div class="flight-name">
                        ${flight.flight_type} #${flight.booking_no} - ${
      flight.customer_name
    }
                    </div>
                    <div class="flight-details">
                        <i class="fas fa-clock"></i> ${flight.booking_time} | 
                        Service: ${flight.service_title}
                        ${flight.pickup ? ` | Pickup: ${flight.pickup}` : ""}
                    </div>
                </div>
                <div class="flight-status ${statusClass}">${statusText}</div>
            </div>
        `;
  });

  flightList.innerHTML = flightHTML;
}

// Get status class for styling
function getStatusClass(status) {
  switch (status.toLowerCase()) {
    case "completed":
      return "status-active";
    case "confirmed":
      return "status-active";
    case "pending":
      return "status-pending";
    case "cancelled":
      return "status-cancelled";
    default:
      return "status-pending";
  }
}

// Get display text for status
function getStatusText(status) {
  switch (status.toLowerCase()) {
    case "completed":
      return "Completed";
    case "confirmed":
      return "Confirmed";
    case "pending":
      return "Pending";
    case "cancelled":
      return "Cancelled";
    default:
      return status.charAt(0).toUpperCase() + status.slice(1);
  }
}

// Show completion message
function showCompletionMessage(period) {
  const charts = document.querySelectorAll(".chart-placeholder");
  // This function can be used to show any final animations or confirmations
}

// Show error message
function showError(message) {
  // Reset loading animations
  document.querySelectorAll(".stat-value").forEach((element) => {
    element.textContent = "0";
  });

  document.querySelectorAll(".chart-placeholder").forEach((chart) => {
    chart.innerHTML = `
            <div style="text-align: center; color: #e74c3c;">
                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                Error loading data
                <br><small>${message}</small>
            </div>
        `;
  });

  console.error("Dashboard Error:", message);
}

// Add hover effects to cards
document
  .querySelectorAll(".stat-card, .chart-card, .instructor-card")
  .forEach((card) => {
    card.style.transform = "translateY(0)";
    card.addEventListener("mouseenter", () => {
      card.style.transform = "translateY(-5px)";
    });
    card.addEventListener("mouseleave", () => {
      card.style.transform = "translateY(0)";
    });
  });

// Auto-refresh data every 5 minutes for today's filter
setInterval(() => {
  if (currentFilter === "today") {
    loadStatistics("today");
  }
}, 300000); // 5 minutes

// Refresh today's flights every 30 seconds when on today filter
setInterval(() => {
  if (currentFilter === "today" && currentData) {
    // Only refresh today's flights, not all data
    fetch(`Web/php/AJAX/statistics_C.php?time_filter=today`)
      .then((response) => response.json())
      .then((result) => {
        if (result.success) {
          updateTodaysFlights(result.data.todays_flights);
        }
      })
      .catch((error) => console.log("Auto-refresh error:", error));
  }
}, 30000); // 30 seconds
