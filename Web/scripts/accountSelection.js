// Only run once to prevent duplicate event listeners
if (!window.accountSelectionInitialized) {
  window.accountSelectionInitialized = true;

  console.log("Account selection page loaded successfully");
  console.log("Session ID: <?php echo session_id(); ?>");

  // Helper function to safely parse JSON
  function safeJsonParse(response) {
    return response.text().then((text) => {
      console.log("Raw response text:", text);
      if (!text || text.trim() === "") {
        throw new Error("Empty response from server");
      }
      try {
        return JSON.parse(text);
      } catch (e) {
        console.error("JSON parse error:", e);
        console.error("Response text that failed to parse:", text);
        throw new Error(
          "Invalid JSON response: " + text.substring(0, 100) + "..."
        );
      }
    });
  }

  document
    .getElementById("accountTypeForm")
    .addEventListener("submit", function (e) {
      e.preventDefault();
      console.log("Form submitted");

      const selectedOption = document.querySelector(
        'input[name="acc_type"]:checked'
      );
      const errorMessage = document.getElementById("errorMessage");

      if (!selectedOption) {
        errorMessage.style.display = "block";
        console.log("No option selected");
        return;
      }

      errorMessage.style.display = "none";
      console.log("Selected option:", selectedOption.value);

      // Disable the continue button to prevent double submission
      const continueBtn = document.getElementById("continueBtn");
      continueBtn.disabled = true;
      continueBtn.textContent = "Processing...";

      // Store the selected account type and proceed
      const accountType = selectedOption.value;
      console.log("Account type to be sent:", accountType);

      // Store in session via AJAX
      const formData = new FormData();
      formData.append("acc_type", accountType);
      formData.append("action", "set_account_type");

      // Log form data contents
      console.log("FormData contents:");
      for (let [key, value] of formData.entries()) {
        console.log(key, value);
      }

      console.log("Sending account type to server...");

      fetch("Web/php/accountHandler/account.handler.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          console.log("Set account type - Response status:", response.status);
          console.log("Set account type - Response ok:", response.ok);

          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }

          return response.text();
        })
        .then((text) => {
          console.log("Set account type - Raw response:", text);

          try {
            const data = JSON.parse(text);
            console.log("Set account type - Parsed response:", data);

            if (data.success) {
              console.log(
                "Account type set successfully, completing registration..."
              );

              // Add a small delay to ensure session is written
              setTimeout(() => {
                // Now complete the registration
                const completeFormData = new FormData();
                completeFormData.append("action", "complete_registration");

                console.log("Sending complete registration request...");

                fetch("Web/php/accountHandler/account.handler.php", {
                  method: "POST",
                  body: completeFormData,
                })
                  .then((response) => {
                    console.log(
                      "Complete registration response status:",
                      response.status
                    );

                    if (!response.ok) {
                      throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    return response.text();
                  })
                  .then((text) => {
                    console.log("Complete registration - Raw response:", text);

                    try {
                      const data = JSON.parse(text);
                      console.log(
                        "Complete registration - Parsed response:",
                        data
                      );

                      if (data.success) {
                        alert(data.message);
                        console.log("Redirecting to:", data.redirect_url);
                        window.location.href = data.redirect_url || "/home";
                      } else {
                        throw new Error(data.message || "Registration failed");
                      }
                    } catch (parseError) {
                      console.error(
                        "Failed to parse complete registration response:",
                        parseError
                      );
                      console.error("Raw text was:", text);
                      throw new Error("Invalid response format from server");
                    }
                  })
                  .catch((error) => {
                    console.error("Complete registration error:", error);
                    alert("Registration completion failed: " + error.message);

                    // Re-enable the button
                    continueBtn.disabled = false;
                    continueBtn.textContent = "Continue";
                  });
              }, 100); // 100ms delay
            } else {
              throw new Error(data.message || "Failed to set account type");
            }
          } catch (parseError) {
            console.error(
              "Failed to parse set account type response:",
              parseError
            );
            console.error("Raw text was:", text);
            throw new Error("Invalid response format from server");
          }
        })
        .catch((error) => {
          console.error("Set account type error:", error);
          alert(
            "An error occurred while setting account type: " + error.message
          );

          // Re-enable the button
          continueBtn.disabled = false;
          continueBtn.textContent = "Continue";
        });
    });
}

function cancelAccountSelection() {
  const formData = new FormData();
  formData.append("action", "cancel_selection");

  fetch("Web/php/accountHandler/account.handler.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.redirect_url) {
        window.location.href = data.redirect_url;
      } else {
        alert(
          "Failed to cancel selection: " + (data.message || "Unknown error")
        );
      }
    })
    .catch((error) => {
      console.error("Error cancelling selection:", error);
      alert("Error: " + error.message);
    });
}
