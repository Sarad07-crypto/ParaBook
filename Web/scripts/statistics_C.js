// Global variables to store current data
let currentData = null;
let currentFilter = "today";
let flightChart = null; // Store chart instance globally

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

  // Total Revenue - FIXED: Changed to Rs. instead of $
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

// FIXED: Animate number values with Rs. instead of $
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
      // FIXED: Changed from $ to Rs.
      element.textContent = "Rs." + Math.floor(current).toLocaleString();
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

// Updated services list function
function updateServicesList(services) {
  const chartElements = document.querySelectorAll(
    ".chart-card .chart-placeholder"
  );
  const servicesChart = chartElements[0]; // First chart for services

  if (!servicesChart) return;

  if (!services || services.length === 0) {
    servicesChart.innerHTML = `
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
      service.avg_rating || 0,
      service.rating_count || 0
    );

    servicesList += `
      <div class="service-item" style="animation-delay: ${index * 0.1}s">
        <div class="service-info">
          <div class="service-title">${service.title || "Unknown Service"}</div>
          <div class="service-company">${
            service.company || "Unknown Company"
          }</div>
        </div>
        ${starRating}
      </div>
    `;
  });
  servicesList += "</div>";

  servicesChart.innerHTML = servicesList;
}

// Generate star rating HTML
function generateStarRating(rating, ratingCount) {
  if (!ratingCount || ratingCount === 0) {
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

// FIXED: Updated flight types chart function with centered layout (no legend)
function updateFlightTypesChart(flightTypes) {
  const chartElements = document.querySelectorAll(
    ".chart-card .chart-placeholder"
  );
  const flightTypesChart = chartElements[1]; // Second chart for flight types

  if (!flightTypesChart) {
    console.error("Flight types chart container not found");
    return;
  }

  // Destroy existing chart if it exists
  if (flightChart) {
    flightChart.destroy();
    flightChart = null;
  }

  if (!flightTypes || flightTypes.length === 0) {
    flightTypesChart.innerHTML = `
      <div style="text-align: center; color: #666; padding: 50px;">
        <i class="fas fa-chart-pie" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
        No flight data available
        <br><small>Bookings will appear here</small>
      </div>
    `;
    return;
  }

  // Calculate total for percentages
  const totalFlights = flightTypes.reduce(
    (sum, type) => sum + (type.count || 0),
    0
  );

  if (totalFlights === 0) {
    flightTypesChart.innerHTML = `
      <div style="text-align: center; color: #666; padding: 50px;">
        <i class="fas fa-chart-pie" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
        No flight bookings found
        <br><small>Flight data will appear when bookings are made</small>
      </div>
    `;
    return;
  }

  // FIXED: Create centered chart container with proper padding
  flightTypesChart.innerHTML = `
    <div style="
      display: flex; 
      justify-content: center; 
      align-items: center; 
      height: 400px; 
      padding: 30px;
      box-sizing: border-box;
    ">
      <div style="
        width: 350px; 
        height: 350px; 
        position: relative;
      ">
        <canvas id="flightTypesChart" style="
          max-height: 350px; 
          max-width: 350px;
          display: block;
        "></canvas>
      </div>
    </div>
  `;

  // Wait for DOM to update, then create chart
  setTimeout(() => {
    const canvas = document.getElementById("flightTypesChart");
    if (!canvas) {
      console.error("Canvas element not found");
      return;
    }

    // Prepare data for Chart.js - ensure all values are numbers
    const labels = flightTypes.map((type) => type.type || "Unknown");
    const data = flightTypes.map((type) => parseInt(type.count) || 0);

    // Filter out zero values
    const validData = [];
    const validLabels = [];
    const validFlightTypes = [];

    flightTypes.forEach((type, index) => {
      const count = parseInt(type.count) || 0;
      if (count > 0) {
        validData.push(count);
        validLabels.push(type.type || "Unknown");
        validFlightTypes.push(type);
      }
    });

    if (validData.length === 0) {
      flightTypesChart.innerHTML = `
        <div style="text-align: center; color: #666; padding: 50px;">
          <i class="fas fa-chart-pie" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
          No valid flight data to display
        </div>
      `;
      return;
    }

    // Generate colors for each slice
    const colors = generateColors(validData.length);

    // Get canvas context
    const ctx = canvas.getContext("2d");

    try {
      // Create new pie chart
      flightChart = new Chart(ctx, {
        type: "pie",
        data: {
          labels: validLabels,
          datasets: [
            {
              data: validData,
              backgroundColor: colors,
              borderColor: "#fff",
              borderWidth: 2,
              hoverBorderWidth: 3,
              hoverBorderColor: "#fff",
              hoverBackgroundColor: colors.map((color) => color + "CC"), // Add transparency on hover
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: {
              display: false, // Hide legend completely
            },
            tooltip: {
              enabled: true,
              callbacks: {
                label: function (context) {
                  const label = context.label || "";
                  const value = context.parsed;
                  const percentage = (
                    (value / validData.reduce((a, b) => a + b, 0)) *
                    100
                  ).toFixed(1);
                  const flightType = validFlightTypes[context.dataIndex];

                  const lines = [`${label}: ${value} flights (${percentage}%)`];

                  if (flightType.avg_price) {
                    lines.push(
                      `Avg Price: Rs.${parseFloat(flightType.avg_price).toFixed(
                        2
                      )}`
                    );
                  }

                  if (flightType.total_amount) {
                    lines.push(
                      `Total Revenue: Rs.${parseInt(
                        flightType.total_amount
                      ).toLocaleString()}`
                    );
                  }

                  return lines;
                },
              },
              backgroundColor: "rgba(0, 0, 0, 0.8)",
              titleColor: "#fff",
              bodyColor: "#fff",
              borderColor: "#ddd",
              borderWidth: 1,
              cornerRadius: 5,
              displayColors: true,
            },
          },
          animation: {
            animateRotate: true,
            animateScale: true,
            duration: 1500,
            easing: "easeInOutQuart",
          },
          layout: {
            padding: 20, // Add padding inside the chart
          },
        },
      });

      // No legend creation since we removed it
    } catch (error) {
      console.error("Error creating chart:", error);
      flightTypesChart.innerHTML = `
        <div style="text-align: center; color: #e74c3c; padding: 50px;">
          <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
          Error creating chart
          <br><small>Please check the console for details</small>
        </div>
      `;
    }
  }, 100); // Small delay to ensure DOM is ready
}

// Generate colors for pie chart slices
function generateColors(count) {
  const baseColors = [
    "#3498db",
    "#e74c3c",
    "#2ecc71",
    "#f39c12",
    "#9b59b6",
    "#1abc9c",
    "#34495e",
    "#e67e22",
    "#95a5a6",
    "#16a085",
    "#f1c40f",
    "#8e44ad",
    "#27ae60",
    "#d35400",
    "#2c3e50",
  ];

  const colors = [];
  for (let i = 0; i < count; i++) {
    if (i < baseColors.length) {
      colors.push(baseColors[i]);
    } else {
      // Generate additional colors using HSL if needed
      const hue = (i * 137.508) % 360; // Golden angle approximation for good color distribution
      colors.push(`hsl(${hue}, 70%, 50%)`);
    }
  }
  return colors;
}

// FIXED: Create custom legend with flight type details - beside chart
function createCustomLegend(flightTypes, colors, totalFlights) {
  const legendContainer = document.getElementById("chartLegend");
  if (!legendContainer) return;

  let legendHTML =
    '<div class="flight-types-legend" style="display: flex; flex-direction: row; gap: 10px; height: 100%;">';
  legendHTML +=
    '<h4 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 1.1em;">Flight Distribution</h4>';

  flightTypes.forEach((type, index) => {
    const count = parseInt(type.count) || 0;
    const percentage =
      totalFlights > 0 ? ((count / totalFlights) * 100).toFixed(1) : 0;
    const avgPrice = parseFloat(type.avg_price) || 0;
    const totalAmount = parseInt(type.total_amount) || 0;

    legendHTML += `
      <div class="legend-item" style="
        display: flex; 
        align-items: center; 
        padding: 12px; 
        background: rgba(255,255,255,0.5); 
        border-radius: 8px;
        animation: slideIn 0.5s ease forwards;
        animation-delay: ${index * 0.1}s;
        opacity: 0;
        border-left: 4px solid ${colors[index]};
      ">
        <div class="legend-color" style="
          width: 16px; 
          height: 16px; 
          background-color: ${colors[index]}; 
          border-radius: 50%; 
          margin-right: 12px;
          flex-shrink: 0;
        "></div>
        <div class="legend-info" style="flex: 1;">
          <div class="legend-title" style="font-weight: 600; color: #2c3e50; margin-bottom: 4px; font-size: 0.95em;">
            ${type.type || "Unknown"}
          </div>
          <div class="legend-details" style="font-size: 0.8em; color: #7f8c8d; line-height: 1.3;">
            ${count} flights (${percentage}%)
            ${avgPrice > 0 ? `<br>Avg: Rs.${avgPrice.toFixed(2)}` : ""}
            ${
              totalAmount > 0
                ? `<br>Total: Rs.${totalAmount.toLocaleString()}`
                : ""
            }
          </div>
        </div>
      </div>
    `;
  });

  legendHTML += "</div>";
  legendContainer.innerHTML = legendHTML;
}

// Update today's flights
function updateTodaysFlights(flights) {
  const flightList = document.querySelector(".flight-list");
  if (!flightList) return;

  if (!flights || flights.length === 0) {
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
                        ${flight.flight_type || "Unknown Flight"} - ${
      flight.customer_name || "Unknown Customer"
    }
                    </div>
                    <div class="flight-details">
                        <i class="fas fa-clock"></i> ${
                          flight.booking_time || "Time TBD"
                        } | 
                        Service: ${flight.service_title || "Unknown Service"}
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
  if (!status) return "status-pending";

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
  if (!status) return "Pending";

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
  // This function can be used to show any final animations or confirmations
  console.log(`Dashboard updated for period: ${period}`);
}

// Show error message
function showError(message) {
  // Reset loading animations
  document.querySelectorAll(".stat-value").forEach((element) => {
    element.textContent = "0";
  });

  document.querySelectorAll(".chart-placeholder").forEach((chart) => {
    chart.innerHTML = `
            <div style="text-align: center; color: #e74c3c; padding: 50px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                Error loading data
                <br><small>${message}</small>
            </div>
        `;
  });

  console.error("Dashboard Error:", message);
}

// Add hover effects to cards when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
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
