function setTimeFilter(period) {
  // Remove active class from all buttons
  document.querySelectorAll(".filter-btn").forEach((btn) => {
    btn.classList.remove("active");
  });

  // Add active class to clicked button
  event.target.classList.add("active");

  // Add loading animation
  const charts = document.querySelectorAll(".chart-placeholder");
  charts.forEach((chart) => {
    chart.innerHTML =
      '<div class="loading-animation"></div><br>Loading ' + period + " data...";

    // Simulate data loading
    setTimeout(() => {
      const originalContent =
        chart.getAttribute("data-original") || chart.innerHTML;
      if (period === "today") {
        chart.innerHTML =
          '<div><i class="fas fa-chart-line" style="font-size: 3rem; margin-bottom: 10px; display: block;"></i>Today\'s Data<br><small>Real-time statistics</small></div>';
      } else if (period === "week") {
        chart.innerHTML =
          '<div><i class="fas fa-chart-area" style="font-size: 3rem; margin-bottom: 10px; display: block;"></i>Weekly Trends<br><small>7-day overview</small></div>';
      } else if (period === "month") {
        chart.innerHTML =
          '<div><i class="fas fa-chart-bar" style="font-size: 3rem; margin-bottom: 10px; display: block;"></i>Monthly Analysis<br><small>30-day summary</small></div>';
      } else {
        chart.innerHTML =
          '<div><i class="fas fa-chart-pie" style="font-size: 3rem; margin-bottom: 10px; display: block;"></i>Yearly Overview<br><small>Annual performance</small></div>';
      }
    }, 1000);
  });

  // Update stats based on period (simplified simulation)
  updateStats(period);
}

function updateStats(period) {
  const multipliers = {
    today: 1,
    week: 7,
    month: 30,
    year: 365,
  };

  const baseStats = [147, 24850, 89, 23];
  const statElements = document.querySelectorAll(".stat-value");

  statElements.forEach((element, index) => {
    if (index < baseStats.length) {
      const newValue = Math.floor(
        baseStats[index] *
          (multipliers[period] || 1) *
          (0.8 + Math.random() * 0.4)
      );
      if (index === 1) {
        element.textContent = "$" + newValue.toLocaleString();
      } else {
        element.textContent = newValue.toLocaleString();
      }
    }
  });
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

// Simulate real-time updates
setInterval(() => {
  const flightCount = document.querySelector(".stat-value");
  if (flightCount && flightCount.textContent !== "$24,850") {
    const currentValue = parseInt(flightCount.textContent);
    const newValue = currentValue + Math.floor(Math.random() * 3);
    flightCount.textContent = newValue;
  }
}, 30000); // Update every 30 seconds

// Initialize page
document.addEventListener("DOMContentLoaded", () => {
  console.log("Paragliding Statistics Dashboard Loaded");

  // Add fade-in animation to cards
  const cards = document.querySelectorAll(
    ".stat-card, .chart-card, .weather-card, .instructor-card"
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
});
