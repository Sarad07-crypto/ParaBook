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

// FIXED: Load statistics from API with better error handling and debugging
async function loadStatistics(timeFilter) {
  try {
    console.log(`Loading statistics for filter: ${timeFilter}`);

    const response = await fetch(
      `Web/php/AJAX/statistics_C.php?time_filter=${timeFilter}`
    );

    // Check if response is ok
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    // FIXED: Enhanced debugging - log the entire response
    console.log("API Response:", result);

    if (result.success) {
      currentData = result.data;

      // FIXED: Log the statistics data specifically
      console.log("Statistics data:", result.data.statistics);
      console.log(
        "Total cancelled:",
        result.data.statistics?.total_cancelled,
        typeof result.data.statistics?.total_cancelled
      );

      updateDashboard(result.data, timeFilter);
    } else {
      console.error("API Error:", result.error);
      if (result.debug_error) {
        console.error("Debug Error:", result.debug_error);
      }
      showError("Failed to load statistics: " + result.error);
    }
  } catch (error) {
    console.error("Network Error:", error);
    showError("Network error occurred while loading data: " + error.message);
  }
}

// Update dashboard with new data
function updateDashboard(data, timeFilter) {
  console.log("Updating dashboard with data:", data);

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

// FIXED: Update main statistics cards with better error handling
function updateMainStats(stats) {
  console.log("Updating main stats with:", stats);

  if (!stats) {
    console.error("No statistics data provided");
    return;
  }

  const statElements = document.querySelectorAll(".stat-value");
  const changes = stats.changes || {};

  console.log("Found stat elements:", statElements.length);
  console.log("Stats object:", {
    total_bookings: stats.total_bookings,
    total_revenue: stats.total_revenue,
    total_flights: stats.total_flights,
    total_cancelled: stats.total_cancelled,
  });

  // FIXED: Total Bookings - ensure it's a valid number
  if (statElements[0]) {
    const bookings = parseInt(stats.total_bookings) || 0;
    console.log("Animating bookings:", bookings);
    animateValue(statElements[0], 0, bookings, 1000);
    updateChangeIndicator(
      statElements[0].parentElement,
      changes.bookings || 0,
      "bookings"
    );
  }

  // FIXED: Total Revenue - ensure it's a valid number
  if (statElements[1]) {
    const revenue = parseFloat(stats.total_revenue) || 0;
    console.log("Animating revenue:", revenue);
    animateValue(statElements[1], 0, revenue, 1000, true);
    updateChangeIndicator(
      statElements[1].parentElement,
      changes.revenue || 0,
      "revenue"
    );
  }

  // FIXED: Total Flights - ensure it's a valid number
  if (statElements[2]) {
    const flights = parseInt(stats.total_flights) || 0;
    console.log("Animating flights:", flights);
    animateValue(statElements[2], 0, flights, 1000);
    updateChangeIndicator(
      statElements[2].parentElement,
      changes.flights || 0,
      "flights"
    );
  }

  // FIXED: Total Cancelled Bookings - with extensive debugging
  if (statElements[3]) {
    const cancelledRaw = stats.total_cancelled;
    console.log(
      "Raw cancelled value:",
      cancelledRaw,
      "Type:",
      typeof cancelledRaw
    );

    // FIXED: Handle various data types and edge cases
    let cancelled = 0;
    if (cancelledRaw === null || cancelledRaw === undefined) {
      cancelled = 0;
      console.log("Cancelled was null/undefined, set to 0");
    } else if (typeof cancelledRaw === "string") {
      if (cancelledRaw.trim() === "" || cancelledRaw.toLowerCase() === "nan") {
        cancelled = 0;
        console.log("Cancelled was empty string or NaN, set to 0");
      } else {
        cancelled = parseInt(cancelledRaw) || 0;
        console.log("Parsed cancelled from string:", cancelled);
      }
    } else if (typeof cancelledRaw === "number") {
      if (isNaN(cancelledRaw)) {
        cancelled = 0;
        console.log("Cancelled was NaN number, set to 0");
      } else {
        cancelled = Math.floor(cancelledRaw);
        console.log("Used cancelled as number:", cancelled);
      }
    } else {
      cancelled = 0;
      console.log("Cancelled was unknown type, set to 0");
    }

    console.log("Final cancelled value to animate:", cancelled);
    animateValue(statElements[3], 0, cancelled, 1000);
    updateChangeIndicator(
      statElements[3].parentElement,
      changes.cancelled || 0,
      "cancelled"
    );
  } else {
    console.error("Fourth stat element not found for cancelled bookings");
  }
}

// FIXED: Animate number values with better error handling
function animateValue(
  element,
  start,
  end,
  duration,
  isCurrency = false,
  decimals = 0
) {
  console.log(
    `Animating value from ${start} to ${end}, currency: ${isCurrency}`
  );

  // FIXED: Validate inputs
  if (!element) {
    console.error("Element not provided to animateValue");
    return;
  }

  const startValue = parseFloat(start) || 0;
  const endValue = parseFloat(end) || 0;

  if (isNaN(startValue) || isNaN(endValue)) {
    console.error("Invalid start or end values:", start, end);
    // Fallback to direct assignment
    if (isCurrency) {
      element.textContent = "Rs." + Math.floor(endValue || 0).toLocaleString();
    } else {
      element.textContent = Math.floor(endValue || 0).toLocaleString();
    }
    return;
  }

  const startTime = Date.now();

  function updateValue() {
    const currentTime = Date.now();
    const elapsed = currentTime - startTime;
    const progress = Math.min(elapsed / duration, 1);

    const current = startValue + (endValue - startValue) * progress;

    try {
      if (isCurrency) {
        element.textContent = "Rs." + Math.floor(current).toLocaleString();
      } else if (decimals > 0) {
        element.textContent = current.toFixed(decimals);
      } else {
        element.textContent = Math.floor(current).toLocaleString();
      }
    } catch (error) {
      console.error("Error updating element text:", error);
      // Fallback
      element.textContent = Math.floor(current);
    }

    if (progress < 1) {
      requestAnimationFrame(updateValue);
    }
  }

  updateValue();
}

// FIXED: Update change indicator with validation
function updateChangeIndicator(cardElement, change, type) {
  if (!cardElement) {
    console.error("Card element not provided to updateChangeIndicator");
    return;
  }

  const changeElement = cardElement.querySelector(".stat-change");
  if (!changeElement) {
    console.log("Change element not found for type:", type);
    return;
  }

  // FIXED: Ensure change is a valid number
  const changeValue = parseFloat(change) || 0;
  const isPositive = changeValue >= 0;
  const changeText = Math.abs(changeValue);

  changeElement.className = `stat-change ${
    isPositive ? "positive" : "negative"
  }`;

  let changeString = "";
  if (type === "cancelled") {
    // For cancelled bookings, we might want to show it differently
    changeString = `${
      isPositive ? "+" : ""
    }${changeText} from last ${currentFilter}`;
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

  if (!servicesChart) {
    console.error("Services chart container not found");
    return;
  }

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

// FIXED: Updated flight types chart function with better error handling
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

  // FIXED: Calculate total for percentages with better validation
  const totalFlights = flightTypes.reduce((sum, type) => {
    const count = parseInt(type.count) || 0;
    return sum + count;
  }, 0);

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

  // Create centered chart container with proper padding
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

    // FIXED: Prepare data for Chart.js with better validation
    const validData = [];
    const validLabels = [];
    const validFlightTypes = [];

    flightTypes.forEach((type) => {
      const count = parseInt(type.count) || 0;
      if (count > 0 && type.type) {
        validData.push(count);
        validLabels.push(type.type);
        validFlightTypes.push({
          ...type,
          count: count,
          avg_price: parseFloat(type.avg_price) || 0,
          total_amount: parseFloat(type.total_amount) || 0,
        });
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
              hoverBackgroundColor: colors.map((color) => color + "CC"),
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: {
              display: false,
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

                  if (flightType.avg_price > 0) {
                    lines.push(
                      `Avg Price: Rs.${flightType.avg_price.toFixed(2)}`
                    );
                  }

                  if (flightType.total_amount > 0) {
                    lines.push(
                      `Total Revenue: Rs.${Math.floor(
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
            padding: 20,
          },
        },
      });
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
  }, 100);
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
      const hue = (i * 137.508) % 360;
      colors.push(`hsl(${hue}, 70%, 50%)`);
    }
  }
  return colors;
}

// Update today's flights
function updateTodaysFlights(flights) {
  const flightList = document.querySelector(".flight-list");
  if (!flightList) {
    console.error("Flight list container not found");
    return;
  }

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
  console.log(`Dashboard updated for period: ${period}`);
}

// FIXED: Show error message with better error handling
function showError(message) {
  console.error("Dashboard Error:", message);

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
    console.log("Auto-refreshing dashboard data");
    loadStatistics("today");
  }
}, 300000); // 5 minutes

// Refresh today's flights every 30 seconds when on today filter
setInterval(() => {
  if (currentFilter === "today" && currentData) {
    console.log("Auto-refreshing today's flights");
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
