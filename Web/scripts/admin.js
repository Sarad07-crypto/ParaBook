let currentTab = "pending";

function switchTab(tab) {
  currentTab = tab;

  // Update tab buttons
  document
    .querySelectorAll(".nav-tab")
    .forEach((btn) => btn.classList.remove("active"));
  event.target.classList.add("active");

  // Filter companies
  const companies = document.querySelectorAll(".company-item");
  companies.forEach((company) => {
    const status = company.dataset.status;
    if (tab === "all" || status === tab) {
      company.style.display = "flex";
    } else {
      company.style.display = "none";
    }
  });

  // Update section title
  const sectionTitle = document.querySelector(".section-title");
  const tabTitles = {
    pending: "Pending Approvals",
    approved: "Approved Companies",
    rejected: "Rejected Companies",
    all: "All Companies",
  };
  sectionTitle.textContent = tabTitles[tab];
}

function approveCompany(companyId) {
  if (confirm("Are you sure you want to approve this company?")) {
    // Find the company item
    const companyItem = document.querySelector(`[data-status="pending"]`);
    const statusBadge = companyItem.querySelector(".status-badge");
    const actionButtons = companyItem.querySelector(".action-buttons");

    // Update status
    statusBadge.textContent = "Approved";
    statusBadge.className = "status-badge status-approved";
    companyItem.dataset.status = "approved";

    // Update action buttons
    actionButtons.innerHTML =
      '<button class="btn btn-view" onclick="viewCompany(\'' +
      companyId +
      "')\">View</button>";

    // Show success message
    showNotification("Company approved successfully!", "success");

    // Update stats
    updateStats();
  }
}

function rejectCompany(companyId) {
  const reason = prompt("Please provide a reason for rejection:");
  if (reason && reason.trim()) {
    // Find the company item
    const companyItem = document.querySelector(`[data-status="pending"]`);
    const statusBadge = companyItem.querySelector(".status-badge");
    const actionButtons = companyItem.querySelector(".action-buttons");

    // Update status
    statusBadge.textContent = "Rejected";
    statusBadge.className = "status-badge status-rejected";
    companyItem.dataset.status = "rejected";

    // Update action buttons
    actionButtons.innerHTML =
      '<button class="btn btn-view" onclick="viewCompany(\'' +
      companyId +
      "')\">View</button>";

    // Show success message
    showNotification("Company rejected successfully!", "error");

    // Update stats
    updateStats();
  }
}

function viewCompany(companyId) {
  alert("Opening detailed view for company: " + companyId);
  // In a real application, this would open a modal or navigate to a detailed view
}

function showNotification(message, type) {
  // Create notification element
  const notification = document.createElement("div");
  notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                font-weight: 600;
                z-index: 1000;
                animation: slideIn 0.3s ease;
                background: ${type === "success" ? "#28a745" : "#dc3545"};
            `;
  notification.textContent = message;

  // Add animation
  const style = document.createElement("style");
  style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `;
  document.head.appendChild(style);

  document.body.appendChild(notification);

  // Remove after 3 seconds
  setTimeout(() => {
    notification.remove();
    style.remove();
  }, 3000);
}

function updateStats() {
  // In a real application, this would update the statistics
  console.log("Updating statistics...");
}

// Search functionality
document.querySelector(".search-input").addEventListener("input", function (e) {
  const searchTerm = e.target.value.toLowerCase();
  const companies = document.querySelectorAll(".company-item");

  companies.forEach((company) => {
    const companyName = company
      .querySelector(".company-name")
      .textContent.toLowerCase();
    const companyDetails = company
      .querySelector(".company-details")
      .textContent.toLowerCase();

    if (
      companyName.includes(searchTerm) ||
      companyDetails.includes(searchTerm)
    ) {
      company.style.display = "flex";
    } else {
      company.style.display = "none";
    }
  });
});

// Filter button functionality
document.querySelectorAll(".filter-btn").forEach((btn) => {
  btn.addEventListener("click", function () {
    document
      .querySelectorAll(".filter-btn")
      .forEach((b) => b.classList.remove("active"));
    this.classList.add("active");
  });
});
