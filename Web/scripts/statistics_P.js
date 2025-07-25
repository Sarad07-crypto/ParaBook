// Statistics Page JavaScript - Improved with Better Error Handling

let bookingsData = [];
let filteredBookings = [];

// DOM Content Loaded
document.addEventListener("DOMContentLoaded", function () {
  setupEventListeners();
  loadStatisticsData();
});

// Setup event listeners
function setupEventListeners() {
  // Filter listeners
  const dateFilter = document.getElementById("dateFilter");
  const statusFilter = document.getElementById("statusFilter");
  const searchInput = document.getElementById("searchInput");

  if (dateFilter) {
    dateFilter.addEventListener("change", applyFilters);
  }
  if (statusFilter) {
    statusFilter.addEventListener("change", applyFilters);
  }
  if (searchInput) {
    searchInput.addEventListener("input", debounce(applyFilters, 300));
  }
}

// Debounce function for search input
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Map frontend status to database status
function mapStatusToDatabase(frontendStatus) {
  const statusMap = {
    upcoming: "pending",
    completed: "completed",
    all: "all",
  };
  return statusMap[frontendStatus] || frontendStatus;
}

// Load statistics data from API
async function loadStatisticsData() {
  try {
    showLoading(true);

    // Get filter values with fallbacks
    const dateFilter = document.getElementById("dateFilter");
    const statusFilter = document.getElementById("statusFilter");
    const searchInput = document.getElementById("searchInput");

    // Map the status filter value to database equivalent
    const statusValue = statusFilter ? statusFilter.value : "all";
    const mappedStatus = mapStatusToDatabase(statusValue);

    const urlParams = new URLSearchParams({
      date_filter: dateFilter ? dateFilter.value : "all",
      status_filter: mappedStatus,
      search: searchInput ? searchInput.value : "",
    });

    console.log("Fetching data with params:", urlParams.toString());

    const response = await fetch(`Web/php/AJAX/statistics_P.php?${urlParams}`, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "Cache-Control": "no-cache",
      },
      credentials: "same-origin", // Include session cookies
    });

    console.log("Response status:", response.status);
    console.log("Response headers:", response.headers);

    if (!response.ok) {
      const errorText = await response.text();
      console.error("Server response:", errorText);
      throw new Error(
        `Server error ${response.status}: ${response.statusText}`
      );
    }

    const contentType = response.headers.get("content-type");
    if (!contentType || !contentType.includes("application/json")) {
      const responseText = await response.text();
      console.error("Non-JSON response:", responseText);
      throw new Error("Server returned non-JSON response");
    }

    const result = await response.json();
    console.log("Parsed response:", result);

    if (!result.success) {
      throw new Error(result.error || "Failed to load data");
    }

    // Validate response structure
    if (!result.data || !result.data.bookings || !result.data.statistics) {
      throw new Error("Invalid response structure");
    }

    bookingsData = result.data.bookings || [];
    filteredBookings = [...bookingsData];

    updateStatisticsDisplay(result.data.statistics);
    renderBookingTable();

    console.log("Data loaded successfully:", {
      bookings: bookingsData.length,
      statistics: result.data.statistics,
    });
  } catch (error) {
    console.error("Error loading statistics:", error);

    // Show specific error messages
    showError(error.message);

    // Reset statistics display
    resetStatisticsDisplay();
  } finally {
    showLoading(false);
  }
}

// Apply filters and reload data
async function applyFilters() {
  console.log("Applying filters...");
  await loadStatisticsData();
}

// Update statistics display
function updateStatisticsDisplay(stats) {
  try {
    const totalFlightsEl = document.getElementById("totalFlights");
    const totalSpentEl = document.getElementById("totalSpent");
    const avgRatingEl = document.getElementById("avgRating");

    if (totalFlightsEl) {
      totalFlightsEl.textContent = stats.total_flights || 0;
    }
    if (totalSpentEl) {
      totalSpentEl.textContent = `$${(stats.total_spent || 0).toFixed(2)}`;
    }
    if (avgRatingEl) {
      avgRatingEl.textContent = (stats.avg_rating || 0).toFixed(1);
    }

    // Animate numbers
    animateNumbers();
  } catch (error) {
    console.error("Error updating statistics display:", error);
  }
}

// Reset statistics display
function resetStatisticsDisplay() {
  const totalFlightsEl = document.getElementById("totalFlights");
  const totalSpentEl = document.getElementById("totalSpent");
  const avgRatingEl = document.getElementById("avgRating");

  if (totalFlightsEl) totalFlightsEl.textContent = "0";
  if (totalSpentEl) totalSpentEl.textContent = "$0.00";
  if (avgRatingEl) avgRatingEl.textContent = "0.0";
}

// Animate numbers
function animateNumbers() {
  const statCards = document.querySelectorAll(".stat-card");
  statCards.forEach((card) => {
    card.style.transform = "scale(1.05)";
    card.style.transition = "transform 0.2s ease";
    setTimeout(() => {
      card.style.transform = "scale(1)";
    }, 200);
  });
}

// Map database status to display status
function mapStatusToDisplay(databaseStatus) {
  const statusMap = {
    pending: "upcoming",
    completed: "completed",
  };
  return statusMap[databaseStatus] || databaseStatus;
}

// Render booking table
function renderBookingTable() {
  const tbody = document.getElementById("bookingTableBody");

  if (!tbody) {
    console.error("Booking table body not found");
    return;
  }

  if (!filteredBookings || filteredBookings.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="empty-state" style="text-align: center; padding: 2rem;">
          <i class="fas fa-plane-slash"></i>
          <h3>No bookings found</h3>
          <p>Try adjusting your filters or search terms</p>
        </td>
      </tr>
    `;
    return;
  }

  try {
    tbody.innerHTML = filteredBookings
      .map((booking) => {
        // Safely access booking properties
        const id = booking.id || 0;
        const flightDate = booking.flightDate || "";
        const flightType = booking.flightType || "N/A";
        const amount = parseFloat(booking.amount) || 0;
        const status = booking.status || "unknown";
        const rating = parseInt(booking.rating) || 0;

        // Map database status to display status
        const displayStatus = mapStatusToDisplay(status);

        return `
          <tr>
            <td>${formatDate(flightDate)}</td>
            <td>${escapeHtml(flightType)}</td>
            <td>$${amount.toFixed(2)}</td>
            <td><span class="status-badge status-${displayStatus}">${escapeHtml(
          displayStatus
        )}</span></td>
            <td>${renderRatingStars(rating)}</td>
            <td>
              <button class="action-btn view-btn" onclick="viewBookingDetails(${id})">View</button>
            </td>
          </tr>
        `;
      })
      .join("");
  } catch (error) {
    console.error("Error rendering table:", error);
    showError("Error displaying booking data");
  }
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
  const map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  };
  return String(text).replace(/[&<>"']/g, (m) => map[m]);
}

// Format date safely
function formatDate(dateString) {
  if (!dateString) return "N/A";

  try {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return "Invalid Date";

    return date.toLocaleDateString("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
    });
  } catch (error) {
    console.error("Error formatting date:", error);
    return "Invalid Date";
  }
}

// Render rating stars
function renderRatingStars(rating) {
  const numRating = parseInt(rating) || 0;
  let stars = "";
  for (let i = 1; i <= 5; i++) {
    if (i <= numRating) {
      stars += '<i class="fas fa-star"></i>';
    } else {
      stars += '<i class="far fa-star"></i>';
    }
  }
  return `<div class="rating-stars">${stars}</div>`;
}

// View booking details
function viewBookingDetails(bookingId) {
  try {
    const booking = filteredBookings.find((b) => b.id == bookingId);
    if (!booking) {
      alert("Booking not found");
      return;
    }

    const displayStatus = mapStatusToDisplay(booking.status);

    const details = `
Flight Details:

Booking No: ${booking.bookingNo || "N/A"}
Date: ${formatDate(booking.flightDate)}
Type: ${booking.flightType || "N/A"}
Amount: $${(parseFloat(booking.amount) || 0).toFixed(2)}
Status: ${displayStatus || "N/A"}
Pickup Location: ${booking.pickup || "N/A"}
${booking.weight ? `Weight: ${booking.weight}kg` : ""}
Age: ${booking.age || "N/A"}
${booking.review ? `\n\nReview: ${booking.review}` : ""}
    `.trim();

    alert(details);
  } catch (error) {
    console.error("Error viewing booking details:", error);
    alert("Error loading booking details");
  }
}

// Show loading state
function showLoading(isLoading) {
  const tbody = document.getElementById("bookingTableBody");
  if (!tbody) return;

  if (isLoading) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="loading-state" style="text-align: center; padding: 2rem;">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Loading...</p>
        </td>
      </tr>
    `;
  }
}

// Show error state
function showError(message) {
  const tbody = document.getElementById("bookingTableBody");
  if (!tbody) return;

  tbody.innerHTML = `
    <tr>
      <td colspan="6" class="error-state" style="text-align: center; padding: 2rem; color: #dc3545;">
        <i class="fas fa-exclamation-triangle"></i>
        <h3>Error Loading Data</h3>
        <p>${escapeHtml(message)}</p>
        <button onclick="loadStatisticsData()" class="btn btn-primary" style="margin-top: 1rem;">
          Try Again
        </button>
      </td>
    </tr>
  `;
}
