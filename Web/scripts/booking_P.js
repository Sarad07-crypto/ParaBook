$(document).ready(function () {
  // Flight type selection handler
  $("#mainFlightType").change(function () {
    const selectedOption = $(this).find("option:selected");
    const priceDisplay = $("#priceDisplay");
    const flightNameDisplay = $("#selectedFlightName");
    const flightPriceDisplay = $("#selectedFlightPrice");

    if (selectedOption.val() && selectedOption.data("price")) {
      const flightName = selectedOption.text().split(" (Rs.")[0];
      const price = selectedOption.data("price");

      flightNameDisplay.text(flightName);
      flightPriceDisplay.text("Rs. " + price);
      priceDisplay.show();
    } else {
      priceDisplay.hide();
    }
  });

  // Set minimum date to today
  const today = new Date().toISOString().split("T")[0];
  $("#mainDate").attr("min", today);

  // Form validation setup
  setupFormValidation();

  // Terms and conditions modal handlers
  $("#openTerms").click(function (e) {
    e.preventDefault();
    $("#termsCardOverlay").show();
  });

  // Close terms modal
  $(document).on("click", "#termsCardOverlay", function (e) {
    if (e.target === this) {
      closeTermsCard();
    }
  });

  $(document).keydown(function (e) {
    if (e.key === "Escape") {
      closeTermsCard();
    }
  });

  // Cleanup expired bookings every 30 minutes
  setInterval(cleanupExpiredBookings, 30 * 60 * 1000);
});

// Function to setup form validation
function setupFormValidation() {
  const form = $("#mainBookingForm");

  // Set form action and method for direct submission
  form.attr("action", "/bookingSubmit");
  form.attr("method", "POST");

  // Real-time validation for inputs
  form.find("input, textarea, select").on("input blur", function () {
    validateSingleInput($(this));
  });

  // Form submission handler - Validate before allowing submission
  form.on("submit", function (e) {
    console.log("=== FORM SUBMISSION STARTED ===");

    // Validate all inputs
    const inputs = form.find("input, textarea, select");
    let isValid = true;

    inputs.each(function () {
      if (!validateSingleInput($(this))) {
        isValid = false;
      }
    });

    console.log("Form validation result:", isValid);

    if (!isValid) {
      e.preventDefault(); // Prevent submission if validation fails
      console.log("Form validation failed - submission prevented");
      showErrorMessage("Please fill in all required fields correctly.");
      return false;
    }

    // If validation passes, show processing state and allow normal form submission
    const submitBtn = form.find(".main-submit-btn");
    submitBtn.prop("disabled", true).text("Processing...");

    // Hide previous error messages
    $("#mainBookingError").hide();

    console.log("Form validation passed - proceeding with submission");

    // Show loading message
    showLoadingMessage("Processing your booking request...");

    // Form will submit normally to bookingSubmit.php
    return true;
  });

  // Handle submit button click
  form.find(".main-submit-btn").on("click", function (e) {
    // Let the form handle the submission naturally
    // The form's submit event handler will validate first
  });
}

// Function to validate single input
function validateSingleInput($input) {
  const input = $input[0];
  let isValid = true;
  let errorMessage = "";

  // Required field validation
  if ($input.prop("required") && $input.val().trim() === "") {
    isValid = false;
    errorMessage = "This field is required";
  }

  // Email validation
  else if (input.type === "email" && $input.val()) {
    const emailRegex = /^([^\s@]+)@([^\s@]+)\.[^\s@]{2,}$/;
    if (!emailRegex.test($input.val())) {
      isValid = false;
      errorMessage = "Please enter a valid email address";
    }
  }

  // Phone validation
  else if (input.type === "tel" && $input.val()) {
    const phoneRegex = /^(\+977-)?9\d{9}$/;
    if (!phoneRegex.test($input.val())) {
      isValid = false;
      errorMessage =
        "Please enter a valid phone number (e.g., +977-9XXXXXXXXX)";
    }
  }

  // Weight validation
  else if (
    input.type === "number" &&
    input.name === "mainWeight" &&
    $input.val()
  ) {
    const weight = parseFloat($input.val());
    if (weight < 30 || weight > 120) {
      isValid = false;
      errorMessage = "Weight must be between 30 and 120 kg";
    }
  }

  // Age validation
  else if (
    input.type === "number" &&
    input.name === "mainAge" &&
    $input.val()
  ) {
    const age = parseInt($input.val());
    if (age < 10 || age > 80) {
      isValid = false;
      errorMessage = "Age must be between 10 and 80 years";
    }
  }

  // Date validation
  else if (input.type === "date" && $input.val()) {
    const enteredDate = new Date($input.val());
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    if (isNaN(enteredDate.getTime()) || enteredDate < today) {
      isValid = false;
      errorMessage = "Please select a valid future date";
    }
  }

  // Update input styling
  if (isValid) {
    $input.css("border-color", "#0d6efd");
    $input.removeClass("is-invalid");
  } else {
    $input.css("border-color", "red");
    $input.addClass("is-invalid");
  }

  return isValid;
}

// Helper function to show loading message
function showLoadingMessage(message) {
  console.log("Showing loading message:", message);
  const $loadingElement = $("#mainBookingLoading");
  const $messageElement = $("#loadingMessage");

  if ($loadingElement.length && $messageElement.length) {
    $messageElement.text(message);
    $loadingElement.show();
    $("#mainBookingError").hide();
    $("#mainBookingSuccess").hide();
  } else {
    // Fallback to toast
    showToast("Processing", message);
  }
}

// Helper function to show error message
function showErrorMessage(message) {
  console.log("Showing error message:", message);
  const $errorElement = $("#mainBookingError");
  const $messageElement = $("#errorMessage");

  if ($errorElement.length && $messageElement.length) {
    $messageElement.text(message);
    $errorElement.show();
    $("#mainBookingSuccess").hide();
    $("#mainBookingLoading").hide();
  } else {
    // Fallback to toast
    showToast("Error", message);
  }
}

// Helper function to show success message
function showSuccessMessage(message, autoHide = true) {
  console.log("Showing success message:", message);
  const $successElement = $("#mainBookingSuccess");
  const $messageElement = $("#successMessage");

  if ($successElement.length && $messageElement.length) {
    $messageElement.text(message);
    $successElement.show();
    $("#mainBookingError").hide();
    $("#mainBookingLoading").hide();

    if (autoHide) {
      setTimeout(function () {
        $successElement.hide();
      }, 5000);
    }
  } else {
    // Fallback to toast
    showToast("Success", message);
  }
}

// Function to show toast notifications
function showToast(title, message) {
  const $toast = $(`
        <div class="toast-notification" style="
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${
              title === "Error"
                ? "#dc3545"
                : title === "Processing"
                ? "#ffc107"
                : "#28a745"
            };
            color: ${title === "Processing" ? "#000" : "white"};
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            max-width: 300px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        ">
            <div class="toast-content">
                <strong>${title}</strong>
                <p style="margin: 5px 0 0 0;">${message}</p>
            </div>
            <button class="toast-close" style="
                background: none;
                border: none;
                color: ${title === "Processing" ? "#000" : "white"};
                font-size: 18px;
                cursor: pointer;
                padding: 0;
                margin-left: 10px;
            ">×</button>
        </div>
    `);

  // Add click handler for close button
  $toast.find(".toast-close").click(function () {
    $toast.remove();
  });

  // Add to document
  $("body").append($toast);

  // Auto remove after 7 seconds
  setTimeout(function () {
    $toast.remove();
  }, 7000);
}

// Function to cleanup expired bookings
function cleanupExpiredBookings() {
  $.ajax({
    url: "Web/php/eSewa/cleanupTempBookings.php",
    type: "POST",
    success: function (result) {
      console.log("Cleanup completed:", result);
    },
    error: function (jqXHR, textStatus, errorThrown) {
      console.error("Cleanup error:", textStatus, errorThrown);
    },
  });
}

// Function to close terms modal
function closeTermsCard() {
  $("#termsCardOverlay").hide();
}

// Display session messages on page load
$(document).ready(function () {
  // Check for session success message
  if (typeof bookingSuccess !== "undefined" && bookingSuccess) {
    showSuccessMessage(
      "Booking saved successfully! Redirecting to payment...",
      false
    );
  }

  // Check for session error message
  if (typeof bookingError !== "undefined" && bookingError) {
    showErrorMessage(bookingError);
  }

  // Check for validation errors
  if (typeof validationErrors !== "undefined" && validationErrors) {
    Object.keys(validationErrors).forEach(function (field) {
      const $input = $(
        'input[name="' +
          field +
          '"], select[name="' +
          field +
          '"], textarea[name="' +
          field +
          '"]'
      );
      if ($input.length) {
        $input.css("border-color", "red").addClass("is-invalid");
      }
    });
  }

  // Restore form data if available
  if (typeof formData !== "undefined" && formData) {
    Object.keys(formData).forEach(function (field) {
      const $input = $(
        'input[name="' +
          field +
          '"], select[name="' +
          field +
          '"], textarea[name="' +
          field +
          '"]'
      );
      if ($input.length && formData[field]) {
        $input.val(formData[field]);
        if (field === "mainFlightType") {
          $input.trigger("change"); // Trigger price display update
        }
      }
    });
  }
});
