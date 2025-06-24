// Flight type selection handler
document
  .getElementById("mainFlightType")
  .addEventListener("change", function () {
    const selectedOption = this.options[this.selectedIndex];
    const priceDisplay = document.getElementById("priceDisplay");
    const flightNameDisplay = document.getElementById("selectedFlightName");
    const flightPriceDisplay = document.getElementById("selectedFlightPrice");

    if (selectedOption.value && selectedOption.dataset.price) {
      const flightName = selectedOption.textContent.split(" (Rs.")[0]; // Get name without price
      const price = selectedOption.dataset.price;

      flightNameDisplay.textContent = flightName;
      flightPriceDisplay.textContent = "Rs. " + price;
      priceDisplay.style.display = "block";
    } else {
      priceDisplay.style.display = "none";
    }
  });

// Function to submit booking data
const submitBookingData = async (formElement) => {
  const formData = new FormData(formElement);

  try {
    const response = await fetch("Web/php/AJAX/bookingSubmit.php", {
      method: "POST",
      body: formData,
    });

    // Check if response is ok
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    // Get response text first to debug
    const responseText = await response.text();
    console.log("Raw response:", responseText);

    // Try to parse as JSON
    let result;
    try {
      result = JSON.parse(responseText);
    } catch (parseError) {
      console.error("JSON parse error:", parseError);
      console.error("Response was:", responseText);
      throw new Error("Invalid JSON response from server");
    }

    if (result.success) {
      document.getElementById(
        "successMessage"
      ).textContent = `Booking confirmed! Your booking number is: ${result.booking_no}`;
      document.getElementById("mainBookingSuccess").style.display = "flex";
      document.getElementById("mainBookingError").style.display = "none";

      // Fetch the latest notification for the current user
      fetch("Web/php/AJAX/bookingNotificationAPI.php?action=recent&limit=1")
        .then((res) => res.json())
        .then((data) => {
          if (data.success && data.notifications.length > 0) {
            const notification = data.notifications[0];
            // Display notification as a toast or alert
            showToast(notification.title, notification.message);
          }
        })
        .catch((err) => {
          console.error("Failed to fetch notifications:", err);
        });

      setTimeout(() => {
        document.getElementById("mainBookingSuccess").style.display = "none";
        formElement.reset();
        formElement.querySelector(".main-submit-btn").style.display = "";
        formElement.querySelector(".main-submit-btn").disabled = false;
        formElement.querySelector(".main-submit-btn").textContent = "Book Now";
        document.getElementById("priceDisplay").style.display = "none";
      }, 3000);
    } else {
      document.getElementById("errorMessage").textContent = result.message;
      document.getElementById("mainBookingError").style.display = "block";
      document.getElementById("mainBookingSuccess").style.display = "none";
      formElement.querySelector(".main-submit-btn").style.display = "";
      formElement.querySelector(".main-submit-btn").disabled = false;
      formElement.querySelector(".main-submit-btn").textContent = "Book Now";
    }
  } catch (error) {
    console.error("Error:", error);
    document.getElementById("errorMessage").textContent =
      "Network error: " + error.message;
    document.getElementById("mainBookingError").style.display = "block";
    document.getElementById("mainBookingSuccess").style.display = "none";
    formElement.querySelector(".main-submit-btn").style.display = "";
    formElement.querySelector(".main-submit-btn").disabled = false;
    formElement.querySelector(".main-submit-btn").textContent = "Book Now";
  }
};

// Booking form validation
const validateBookingForm = (formSelector) => {
  const formElement = document.querySelector(formSelector);
  const validateOptions = [
    {
      attribute: "required",
      isValid: (input) => input.value.trim() === "",
    },
    {
      attribute: "type",
      isValid: (input) => {
        if (input.type === "email") {
          return !/^([^\s@]+)@([^\s@]+)\.[^\s@]{2,}$/.test(input.value);
        }
        if (input.type === "tel") {
          return !/^(\+977-)?9\d{9}$/.test(input.value); // Updated Nepal phone format
        }
        if (input.type === "number" && input.name === "mainWeight") {
          return input.value !== "" && (input.value < 30 || input.value > 120);
        }
        if (input.type === "number" && input.name === "mainAge") {
          return input.value < 10 || input.value > 80;
        }
        return false;
      },
    },
    {
      attribute: "date",
      isValid: (input) => {
        if (input.type === "date") {
          const enteredDate = new Date(input.value);
          const today = new Date();
          today.setHours(0, 0, 0, 0); // Reset time to compare dates only
          return isNaN(enteredDate.getTime()) || enteredDate < today;
        }
        return false;
      },
    },
  ];

  const validateSingleInput = (input) => {
    let error = false;
    for (const option of validateOptions) {
      if (
        input.hasAttribute(option.attribute) ||
        option.attribute === "type" ||
        option.attribute === "date"
      ) {
        if (option.isValid(input)) {
          error = true;
          break;
        }
      }
    }
    if (error) {
      input.style.borderColor = "red";
    } else {
      input.style.borderColor = "#0d6efd";
    }
    return !error;
  };

  const setupInputEvents = () => {
    const inputs = Array.from(
      formElement.querySelectorAll("input, textarea, select")
    );
    inputs.forEach((input) => {
      input.addEventListener("input", () => validateSingleInput(input));
      input.addEventListener("blur", () => validateSingleInput(input));
    });
  };

  formElement.setAttribute("novalidate", "");
  formElement.addEventListener("submit", (event) => {
    event.preventDefault();
    const inputs = Array.from(
      formElement.querySelectorAll("input, textarea, select")
    );
    const isValid = inputs.every((input) => validateSingleInput(input));

    if (isValid) {
      // Disable submit button to prevent double submission
      const submitBtn = formElement.querySelector(".main-submit-btn");
      submitBtn.disabled = true;
      submitBtn.textContent = "Processing...";

      // Hide any previous error messages
      document.getElementById("mainBookingError").style.display = "none";

      // Submit the booking data
      submitBookingData(formElement);
    }
  });
  setupInputEvents();
};

validateBookingForm("#mainBookingForm");

function showToast(title, message) {
  const toast = document.getElementById("toastNotification");
  toast.innerHTML = `<strong>${title}</strong><br>${message}`;
  toast.style.display = "block";

  // Hide toast on click immediately
  toast.onclick = () => {
    toast.style.display = "none";
  };

  // Auto-hide after 4 seconds
  setTimeout(() => {
    toast.style.display = "none";
  }, 4000);
}

document.getElementById("openTerms").onclick = function (e) {
  e.preventDefault();
  document.getElementById("termsCardOverlay").style.display = "flex";
};

function closeTermsCard() {
  document.getElementById("termsCardOverlay").style.display = "none";
}
document.getElementById("termsCardOverlay").onclick = function (e) {
  if (e.target === this) closeTermsCard();
};
document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") closeTermsCard();
});

// Set minimum date to today
document.getElementById("mainDate").min = new Date()
  .toISOString()
  .split("T")[0];
