$(document).ready(function () {
  // Load existing user data
  loadUserData();

  // Handle profile image preview
  $("#profileImage").on("change", function (e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        $("#profileImagePreview").attr("src", e.target.result);
      };
      reader.readAsDataURL(file);
    }
  });

  // Handle form submission - remove any existing handlers first
  $("#profileForm")
    .off("submit")
    .on("submit", function (e) {
      e.preventDefault();
      e.stopImmediatePropagation();
      submitProfileData();
    });

  // Also handle button click specifically to prevent double submission
  $('#profileForm button[type="submit"]')
    .off("click")
    .on("click", function (e) {
      e.preventDefault();
      e.stopImmediatePropagation();
      submitProfileData();
    });
});

function loadUserData() {
  $.ajax({
    url: "Web/php/AJAX/userDataAPI.php",
    type: "GET",
    dataType: "json",
    success: function (response) {
      console.log("User data response:", response); // Debug log

      if (response.success && response.data) {
        const userData = response.data;

        // Populate form fields
        if (userData.firstName) $("#firstName").val(userData.firstName);
        if (userData.lastName) $("#lastName").val(userData.lastName);
        if (userData.email) $("#email").val(userData.email);
        if (userData.contact) $("#contact").val(userData.contact);
        if (userData.country) $("#country").val(userData.country);

        // Handle DOB - ensure proper format for date input
        if (
          userData.DOB &&
          userData.DOB !== "" &&
          userData.DOB !== "0000-00-00"
        ) {
          let dateValue = userData.DOB;
          let finalDateValue = "";

          // Check if it's already in YYYY-MM-DD format
          if (dateValue.match(/^\d{4}-\d{2}-\d{2}$/)) {
            finalDateValue = dateValue;
          } else if (dateValue.includes("/")) {
            // Convert MM/DD/YYYY or DD/MM/YYYY to YYYY-MM-DD
            const dateParts = dateValue.split("/");
            if (dateParts.length === 3) {
              // Assume MM/DD/YYYY format
              const year = dateParts[2];
              const month = dateParts[0].padStart(2, "0");
              const day = dateParts[1].padStart(2, "0");
              finalDateValue = `${year}-${month}-${day}`;
            }
          } else {
            // Try to parse other date formats
            const date = new Date(dateValue);
            if (!isNaN(date.getTime())) {
              // Format to YYYY-MM-DD for HTML date input
              const year = date.getFullYear();
              const month = String(date.getMonth() + 1).padStart(2, "0");
              const day = String(date.getDate()).padStart(2, "0");
              finalDateValue = `${year}-${month}-${day}`;
            }
          }

          // Only set the value if we successfully parsed a date
          if (finalDateValue) {
            $("#dob").val(finalDateValue);
            console.log(
              "Original DOB:",
              userData.DOB,
              "Setting to:",
              finalDateValue
            );
          } else {
            console.warn("Could not parse DOB:", userData.DOB);
          }
        }

        // Set radio buttons
        if (userData.acc_type) {
          $(`input[name="userType"][value="${userData.acc_type}"]`).prop(
            "checked",
            true
          );
        }
        if (userData.gender) {
          $(`input[name="gender"][value="${userData.gender}"]`).prop(
            "checked",
            true
          );
        }

        // Set profile image
        if (userData.avatar && userData.avatar !== "default-avatar.png") {
          // Construct the full path for form login avatars
          let avatarSrc = userData.avatar;
          if (
            !avatarSrc.startsWith("http") &&
            !avatarSrc.startsWith("/") &&
            !avatarSrc.startsWith("Assets/")
          ) {
            avatarSrc = "Assets/uploads/avatars/" + userData.avatar;
          }
          $("#profileImagePreview").attr("src", avatarSrc);
        } else {
          $("#profileImagePreview").attr(
            "src",
            "Assets/uploads/avatars/default-avatar.png"
          );
        }
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading user data:", error);
      console.error("Response:", xhr.responseText); // Debug log
    },
  });
}

function resetForm() {
  // Reset all form fields
  $("#profileForm")[0].reset();

  // Clear radio button selections
  $('input[name="gender"]').prop("checked", false);
  $('input[name="userType"]').prop("checked", false);

  // Reset profile image preview to default
  $("#profileImagePreview").attr(
    "src",
    "Assets/uploads/avatars/default-avatar.png"
  );

  // Clear any custom values
  $("#firstName").val("");
  $("#lastName").val("");
  $("#email").val("");
  $("#contact").val("");
  $("#country").val("");
  $("#dob").val("");

  console.log("Form has been reset");
}

function submitProfileData() {
  console.log("submitProfileData called - checking for duplicate calls");

  // Check if already submitting to prevent duplicate submissions
  if (window.isSubmitting) {
    console.log("Already submitting, preventing duplicate call");
    return false;
  }

  // Set flag immediately to prevent other calls
  window.isSubmitting = true;

  // Also disable the submit button immediately
  $('#profileForm button[type="submit"]')
    .prop("disabled", true)
    .text("Processing...");
  // Basic form validation with element existence checks
  const firstNameEl = $("#firstName");
  const lastNameEl = $("#lastName");
  const emailEl = $("#email");
  const contactEl = $("#contact");

  // Try multiple possible DOB element IDs
  let dobEl = $("#dob");
  if (dobEl.length === 0) dobEl = $("#DOB");
  if (dobEl.length === 0) dobEl = $("#dateOfBirth");
  if (dobEl.length === 0) dobEl = $("#date_of_birth");
  if (dobEl.length === 0) dobEl = $('input[name="DOB"]');
  if (dobEl.length === 0) dobEl = $('input[name="dob"]');
  if (dobEl.length === 0) dobEl = $('input[type="date"]');

  // Check if required elements exist
  if (firstNameEl.length === 0) {
    console.error("firstName element not found");
    alert("Form error: firstName field not found");
    return;
  }
  if (lastNameEl.length === 0) {
    console.error("lastName element not found");
    alert("Form error: lastName field not found");
    return;
  }
  if (emailEl.length === 0) {
    console.error("email element not found");
    alert("Form error: email field not found");
    return;
  }
  if (contactEl.length === 0) {
    console.error("contact element not found");
    alert("Form error: contact field not found");
    return;
  }
  if (dobEl.length === 0) {
    console.error(
      "DOB element not found. Checked IDs: dob, DOB, dateOfBirth, date_of_birth"
    );
    console.error("Available date inputs:", $('input[type="date"]'));
    alert("Form error: Date of birth field not found. Please check your HTML.");
    return;
  }

  console.log(
    "Found DOB element with ID/selector:",
    dobEl.attr("id") || dobEl.attr("name") || 'input[type="date"]'
  );

  const firstName = firstNameEl.val() ? firstNameEl.val().trim() : "";
  const lastName = lastNameEl.val() ? lastNameEl.val().trim() : "";
  const email = emailEl.val() ? emailEl.val().trim() : "";
  const contact = contactEl.val() ? contactEl.val().trim() : "";
  const dob = dobEl.val() ? dobEl.val().trim() : "";

  // Debug: Log field values
  console.log("Field values:");
  console.log("firstName:", firstName);
  console.log("lastName:", lastName);
  console.log("email:", email);
  console.log("contact:", contact);
  console.log("dob:", dob);

  // Check required fields
  if (!firstName) {
    alert("First name is required");
    $("#firstName").focus();
    window.isSubmitting = false; // Reset flag on validation failure
    $('#profileForm button[type="submit"]')
      .prop("disabled", false)
      .text("Update Profile");
    return false;
  }

  if (!lastName) {
    alert("Last name is required");
    $("#lastName").focus();
    window.isSubmitting = false; // Reset flag on validation failure
    $('#profileForm button[type="submit"]')
      .prop("disabled", false)
      .text("Update Profile");
    return false;
  }

  if (!email) {
    alert("Email is required");
    $("#email").focus();
    window.isSubmitting = false; // Reset flag on validation failure
    $('#profileForm button[type="submit"]')
      .prop("disabled", false)
      .text("Update Profile");
    return false;
  }

  // Validate email format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    alert("Please enter a valid email address");
    $("#email").focus();
    window.isSubmitting = false; // Reset flag on validation failure
    $('#profileForm button[type="submit"]')
      .prop("disabled", false)
      .text("Update Profile");
    return false;
  }

  // Validate contact number
  if (!contact) {
    alert("Contact number is required");
    $("#contact").focus();
    window.isSubmitting = false; // Reset flag on validation failure
    $('#profileForm button[type="submit"]')
      .prop("disabled", false)
      .text("Update Profile");
    return false;
  }

  // Contact number validation (starts with 9 and exactly 10 digits)
  const contactRegex = /^9\d{9}$/;
  if (!contactRegex.test(contact)) {
    alert(
      "Please enter a valid contact number (starts with 9 and exactly 10 digits)"
    );
    $("#contact").focus();
    window.isSubmitting = false; // Reset flag on validation failure
    $('#profileForm button[type="submit"]')
      .prop("disabled", false)
      .text("Update Profile");
    return false;
  }

  // Validate date of birth
  if (!dob) {
    alert("Date of birth is required");
    dobEl.focus();
    window.isSubmitting = false; // Reset flag on validation failure
    $('#profileForm button[type="submit"]')
      .prop("disabled", false)
      .text("Update Profile");
    return false;
  }

  // Check if date is valid and not in the future (temporarily disabled for testing)
  const dobDate = new Date(dob);
  const currentDate = new Date();

  if (isNaN(dobDate.getTime())) {
    alert("Please enter a valid date of birth");
    dobEl.focus();
    window.isSubmitting = false; // Reset flag on validation failure
    $('#profileForm button[type="submit"]')
      .prop("disabled", false)
      .text("Update Profile");
    return false;
  }

  // Temporarily comment out future date check for testing
  /*
        if (dobDate >= currentDate) {
            alert('Date of birth cannot be today or in the future');
            dobEl.focus();
            return;
        }
        */

  const minAge = 5;
  const minDate = new Date();
  minDate.setFullYear(currentDate.getFullYear() - minAge);

  if (dobDate > minDate) {
    alert(`You must be at least ${minAge} years old`);
    dobEl.focus();
    window.isSubmitting = false; // Reset flag on validation failure
    $('#profileForm button[type="submit"]')
      .prop("disabled", false)
      .text("Update Profile");
    return;
  }

  // Check maximum age (e.g., 120 years old)
  const maxAge = 120;
  const maxDate = new Date();
  maxDate.setFullYear(currentDate.getFullYear() - maxAge);

  if (dobDate < maxDate) {
    alert(`Please enter a valid date of birth (maximum age: ${maxAge} years)`);
    dobEl.focus();
    window.isSubmitting = false; // Reset flag on validation failure
    $('#profileForm button[type="submit"]')
      .prop("disabled", false)
      .text("Update Profile");
    return;
  }

  // Check if gender is selected
  if (!$('input[name="gender"]:checked').length) {
    alert("Please select your gender");
    window.isSubmitting = false; // Reset flag on validation failure
    $('#profileForm button[type="submit"]')
      .prop("disabled", false)
      .text("Update Profile");
    return;
  }

  const formData = new FormData($("#profileForm")[0]);

  // Debug: log form data
  console.log("Submitting form data:");
  for (let [key, value] of formData.entries()) {
    console.log(key, value);
  }

  $.ajax({
    url: "Web/php/AJAX/userDataUpdateAPI.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    beforeSend: function () {
      // Optional: show loading indicator
      console.log("Submitting profile update...");
      console.log("AJAX URL:", "Web/php/AJAX/userDataUpdateAPI.php");
      // Disable submit button to prevent double submission
      $('#profileForm button[type="submit"]')
        .prop("disabled", true)
        .text("Updating...");
    },
    success: function (response) {
      console.log("Update response:", response); // Debug log
      console.log(
        "Alert count check - this should only appear once per submission"
      );

      if (response.success) {
        alert("Profile updated successfully!");

        // Reset the form
        resetForm();

        // Add a small delay before redirect to ensure user sees the success message
        setTimeout(function () {
          // Redirect to home page
          window.location.href = "/home";
        }, 1000); // 1 second delay
      } else {
        alert("Error: " + response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error("Ajax error details:");
      console.error("Status:", status);
      console.error("Error:", error);
      console.error("Response status:", xhr.status);
      console.error("Response text:", xhr.responseText);
      console.error("Ready state:", xhr.readyState);

      if (xhr.status === 0) {
        alert(
          "Network error: Unable to connect to server. Please check your internet connection."
        );
      } else if (xhr.status === 404) {
        alert("Error 404: The requested page was not found.");
      } else if (xhr.status === 500) {
        alert("Error 500: Internal server error. Please check server logs.");
      } else {
        alert(
          "Error updating profile: " + error + " (Status: " + xhr.status + ")"
        );
      }
    },
    complete: function () {
      // Re-enable submit button
      $('#profileForm button[type="submit"]')
        .prop("disabled", false)
        .text("Update Profile");
      // Reset submission flag
      window.isSubmitting = false;
    },
  });
}
