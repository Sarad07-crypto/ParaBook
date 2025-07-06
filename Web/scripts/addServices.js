// Tab switching functionality
$(document).on("click", ".tab-btn", function () {
  const tabType = $(this).data("tab");

  // Update active tab button
  $(".tab-btn").removeClass("active");
  $(this).addClass("active");

  // Update active section
  $(".service-section").removeClass("active");
  $(`#${tabType}Section`).addClass("active");

  console.log(`Switched to ${tabType} tab`);
});

// Load the modal and form content
$(document).on("click", "#loadServiceForm", function () {
  $("body").css("overflow", "hidden");
  $("#serviceModal").addClass("show");

  $("#serviceFormContainer").html(
    '<div class="loading-spinner">Loading form...</div>'
  );

  $.ajax({
    url: "Web/php/views/serviceForm.php",
    type: "GET",
    success: function (data) {
      $("#serviceFormContainer").html(data);
      // Load the script only once per click
      $.getScript("Web/scripts/serviceForm.js", function () {
        initServiceForm();
      });
    },
    error: function () {
      $("#serviceFormContainer").html("<p>Failed to load form.</p>");
    },
  });
});

// Close modal by clicking close button
$(document).on("click", "#closeModal", function () {
  $("#serviceModal").removeClass("show");
  $("body").css("overflow", "auto");
  $("#serviceFormContainer").empty();
});

// Close modal by clicking outside modal-content
$(window).on("click", function (e) {
  if ($(e.target).is("#serviceModal")) {
    $("#serviceModal").removeClass("show");
    $("body").css("overflow", "auto");
    $("#serviceFormContainer").empty();
  }
});

// When clicking on any company card, load the modal with existing service data
$(document).on("click", ".company-card", function () {
  const serviceId = $(this).data("service-id");

  $("body").css("overflow", "hidden");
  $("#serviceModal").addClass("show");
  $("#serviceFormContainer").html(
    '<div class="loading-spinner">Loading form...</div>'
  );

  $.ajax({
    url: "Web/php/views/serviceForm.php",
    type: "GET",
    success: function (data) {
      $("#serviceFormContainer").html(data);
      $.getScript("Web/scripts/serviceForm.js", function () {
        initServiceForm();
        loadServiceData(serviceId);
      });
    },
    error: function () {
      $("#serviceFormContainer").html("<p>Failed to load form.</p>");
    },
  });
});

// Function to load and display company services
function loadCompanyServices() {
  console.log("loadCompanyServices called");

  $.ajax({
    url: "Web/php/formDatabase/fetchServices.php",
    type: "GET",
    dataType: "json",
    success: function (response) {
      console.log("AJAX success. Response received:", response);

      if (response.success) {
        // Clear existing cards from both grids
        $("#approvedGrid").find(".company-card").remove();
        $("#pendingGrid").find(".company-card").remove();

        // Reset counts
        let approvedCount = 0;
        let pendingCount = 0;

        if (response.services.length > 0) {
          // Process each service
          response.services.forEach((service, index) => {
            console.log(`Processing service #${index}:`, service);

            // Get the lowest price from flight types
            let lowestPrice = null;
            if (service.flight_types && service.flight_types.length > 0) {
              const prices = service.flight_types.map((ft) => {
                const priceNum = parseFloat(ft.price.replace(/[^\d.]/g, ""));
                return priceNum;
              });
              lowestPrice = Math.min(...prices);
            }

            // Determine status badge
            const statusBadge = service.status
              ? `<div class="status-badge ${service.status}">${service.status}</div>`
              : '<div class="status-badge pending">pending</div>';

            const serviceCard = `
              <div class="company-card" data-service-id="${service.id}">
                ${statusBadge}
                <div class="company-thumbnail">
                  <img src="${service.thumbnail_path}" 
                      alt="${service.service_title}"
                      onerror="console.error('Image failed to load:', '${
                        service.thumbnail_path
                      }'); this.parentElement.innerHTML='<div style=\\'padding:50px;text-align:center;color:#999;background:#f5f5f5\\'>Image not available</div>';"
                      onload="console.log('Image loaded successfully for ${
                        service.company_name
                      }');">
                </div>
                <div class="company-info">
                  <div class="company-title"><b>${
                    service.company_name || "Unknown Company"
                  }</b></div>
                  <div class="company-desc">${
                    service.service_description || "No description available"
                  }</div>
                  <div class="company-meta">
                    <span class="company-rating">
                      <i class="fas fa-star"></i> 5.0 
                      <span class="company-reviews">(0)</span>
                    </span>
                    ${
                      lowestPrice
                        ? `<span class="company-price">Rs. ${lowestPrice.toLocaleString()}</span>`
                        : '<span class="company-price">Price not available</span>'
                    }
                  </div>
                </div>
              </div>
            `;

            // Add to appropriate grid based on status
            const status = service.status || "pending";
            if (status === "approved") {
              $("#approvedGrid")
                .find(".add-company-wrapper")
                .before(serviceCard);
              approvedCount++;
            } else if (status === "pending") {
              $("#pendingGrid")
                .find(".add-company-wrapper")
                .before(serviceCard);
              pendingCount++;
            }
          });
        }

        // Update counts in tab buttons
        $("#approvedCount").text(approvedCount);
        $("#pendingCount").text(pendingCount);

        // Show/hide empty states
        if (approvedCount === 0) {
          $("#emptyApproved").show();
          $("#approvedGrid").hide();
        } else {
          $("#emptyApproved").hide();
          $("#approvedGrid").show();
        }

        if (pendingCount === 0) {
          $("#emptyPending").show();
          $("#pendingGrid").hide();
        } else {
          $("#emptyPending").hide();
          $("#pendingGrid").show();
        }

        console.log(
          `Loaded ${approvedCount} approved and ${pendingCount} pending services`
        );
      } else {
        console.log("No services found or error in response");
        $("#approvedCount").text(0);
        $("#pendingCount").text(0);
        $("#emptyApproved").show();
        $("#emptyPending").show();
        $("#approvedGrid").hide();
        $("#pendingGrid").hide();
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX error loading services:", error);
      console.log("Status:", status);
      console.log("Response text:", xhr.responseText);
    },
  });
}

// Call this when the page loads
$(document).ready(function () {
  console.log("Document ready, calling loadCompanyServices");
  loadCompanyServices();

  // Also call it after successful form submission
  $(document).on("serviceAdded", function () {
    console.log("serviceAdded event triggered, reloading services");
    loadCompanyServices();
  });
});
