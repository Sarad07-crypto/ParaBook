<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Type Selection</title>
</head>

<body>

    <?php
session_start();
if (!isset($_SESSION['show_account_selection']) || !isset($_SESSION['temp_user_data'])) {
    // Prevent direct access
    header("Location: /login");
    exit();
}


// Debug: Log current session state
error_log("AccountSelection page loaded. Session data: " . print_r($_SESSION, true));

// Check if we should show the account selection
if (!isset($_SESSION['temp_user_data']) || !isset($_SESSION['show_account_selection'])) {
    error_log("Missing session data - redirecting to login");
    error_log("temp_user_data exists: " . (isset($_SESSION['temp_user_data']) ? 'YES' : 'NO'));
    error_log("show_account_selection exists: " . (isset($_SESSION['show_account_selection']) ? 'YES' : 'NO'));
    
    echo "<script>
        console.log('Session data missing - redirecting to login');
        console.log('Session exists: " . (session_id() ? 'YES' : 'NO') . "');
    </script>";
    
    header('Location: /login');
    exit();
}

// Debug: Log successful session check
error_log("Session validation passed - showing account selection");

// Prevent any further output/inclusion that might duplicate content
$content_rendered = true;
?>

    <!-- Debug info for browser console -->
    <script>
    console.log('Account selection page loaded successfully');
    console.log('Session ID: <?php echo session_id(); ?>');
    </script>

    <style>
    .account-type-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(5px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .account-type-popup {
        background: white;
        border-radius: 20px;
        padding: 40px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        animation: slideIn 0.4s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-30px) scale(0.9);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .popup-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .popup-title {
        font-size: 28px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .popup-subtitle {
        font-size: 16px;
        color: #7f8c8d;
        line-height: 1.5;
    }

    .selection-container {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
    }

    .account-option {
        flex: 1;
        position: relative;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .account-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }

    .option-card {
        border: 3px solid #e0e6ed;
        border-radius: 15px;
        padding: 25px 20px;
        text-align: center;
        transition: all 0.3s ease;
        background: linear-gradient(145deg, #f8f9fa, #ffffff);
    }

    .account-option:hover .option-card {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }

    .account-option input[type="radio"]:checked+.option-card {
        border-color: #3498db;
        background: linear-gradient(145deg, #e3f2fd, #f0f8ff);
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(52, 152, 219, 0.3);
    }

    .option-icon {
        font-size: 48px;
        margin-bottom: 15px;
        display: block;
    }

    .passenger-icon {
        color: #3498db;
    }

    .company-icon {
        color: #e74c3c;
    }

    .option-title {
        font-size: 20px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
    }

    .option-description {
        font-size: 14px;
        color: #7f8c8d;
        line-height: 1.4;
    }

    .error-message {
        background: #fee;
        border: 1px solid #fcc;
        color: #c66;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        display: none;
    }

    .button-container {
        display: flex;
        gap: 15px;
        justify-content: center;
    }

    .btn {
        padding: 12px 30px;
        border: none;
        border-radius: 25px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-primary {
        background: linear-gradient(145deg, #3498db, #2980b9);
        color: white;
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.4);
    }

    .btn-primary:hover {
        background: linear-gradient(145deg, #2980b9, #3498db);
        box-shadow: 0 6px 20px rgba(52, 152, 219, 0.6);
        transform: translateY(-2px);
    }

    .btn-secondary {
        background: linear-gradient(145deg, #95a5a6, #7f8c8d);
        color: white;
        box-shadow: 0 4px 15px rgba(149, 165, 166, 0.4);
    }

    .btn-secondary:hover {
        background: linear-gradient(145deg, #7f8c8d, #95a5a6);
        box-shadow: 0 6px 20px rgba(149, 165, 166, 0.6);
        transform: translateY(-2px);
    }

    .debug-info {
        position: fixed;
        top: 10px;
        right: 10px;
        background: #333;
        color: white;
        padding: 10px;
        border-radius: 5px;
        font-size: 12px;
        font-family: monospace;
        z-index: 2000;
    }

    @media (max-width: 500px) {
        .selection-container {
            flex-direction: column;
        }

        .account-type-popup {
            padding: 30px 20px;
        }

        .button-container {
            flex-direction: column;
        }
    }
    </style>

    <!-- Debug info display -->
    <div class="debug-info">
        Debug Info:<br>
        Session ID: <?php echo session_id(); ?><br>
        Temp Data: <?php echo isset($_SESSION['temp_user_data']) ? 'YES' : 'NO'; ?><br>
        Show Selection: <?php echo isset($_SESSION['show_account_selection']) ? 'YES' : 'NO'; ?><br>
        <?php if (isset($_SESSION['temp_user_data'])): ?>
        Email: <?php echo htmlspecialchars($_SESSION['temp_user_data']['email'] ?? 'N/A'); ?>
        <?php endif; ?>
    </div>

    <div class="account-type-overlay" id="accountTypeOverlay">
        <div class="account-type-popup">
            <div class="popup-header">
                <h2 class="popup-title">Choose Your Account Type</h2>
                <p class="popup-subtitle">Select the option that best describes how you'll be using our platform</p>
            </div>

            <form id="accountTypeForm" method="POST">
                <div class="error-message" id="errorMessage">
                    Please select an account type to continue.
                </div>

                <div class="selection-container">
                    <label class="account-option">
                        <input type="radio" name="acc_type" value="passenger" id="passengerOption">
                        <div class="option-card">
                            <span class="option-icon passenger-icon">👤</span>
                            <h3 class="option-title">Passenger</h3>
                            <p class="option-description">Book rides and travel with ease. Perfect for individual
                                travelers.</p>
                        </div>
                    </label>

                    <label class="account-option">
                        <input type="radio" name="acc_type" value="company" id="companyOption">
                        <div class="option-card">
                            <span class="option-icon company-icon">🏢</span>
                            <h3 class="option-title">Company</h3>
                            <p class="option-description">Manage business travel and provide transportation services.
                            </p>
                        </div>
                    </label>
                </div>

                <div class="button-container">
                    <button type="submit" class="btn btn-primary" id="continueBtn">Continue</button>
                    <button type="button" class="btn btn-secondary" onclick="cancelAccountSelection()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Only run once to prevent duplicate event listeners
    if (!window.accountSelectionInitialized) {
        window.accountSelectionInitialized = true;

        console.log('Account selection page loaded successfully');
        console.log('Session ID: <?php echo session_id(); ?>');

        // Helper function to safely parse JSON
        function safeJsonParse(response) {
            return response.text().then(text => {
                console.log('Raw response text:', text);
                if (!text || text.trim() === '') {
                    throw new Error('Empty response from server');
                }
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    console.error('Response text that failed to parse:', text);
                    throw new Error('Invalid JSON response: ' + text.substring(0, 100) + '...');
                }
            });
        }

        document.getElementById('accountTypeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted');

            const selectedOption = document.querySelector('input[name="acc_type"]:checked');
            const errorMessage = document.getElementById('errorMessage');

            if (!selectedOption) {
                errorMessage.style.display = 'block';
                console.log('No option selected');
                return;
            }

            errorMessage.style.display = 'none';
            console.log('Selected option:', selectedOption.value);

            // Disable the continue button to prevent double submission
            const continueBtn = document.getElementById('continueBtn');
            continueBtn.disabled = true;
            continueBtn.textContent = 'Processing...';

            // Store the selected account type and proceed
            const accountType = selectedOption.value;
            console.log('Account type to be sent:', accountType);

            // Store in session via AJAX
            const formData = new FormData();
            formData.append('acc_type', accountType);
            formData.append('action', 'set_account_type');

            // Log form data contents
            console.log('FormData contents:');
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }

            console.log('Sending account type to server...');

            fetch('Web/php/accountHandler/account.handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Set account type - Response status:', response.status);
                    console.log('Set account type - Response ok:', response.ok);

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    return response.text();
                })
                .then(text => {
                    console.log('Set account type - Raw response:', text);

                    try {
                        const data = JSON.parse(text);
                        console.log('Set account type - Parsed response:', data);

                        if (data.success) {
                            console.log('Account type set successfully, completing registration...');

                            // Add a small delay to ensure session is written
                            setTimeout(() => {
                                // Now complete the registration
                                const completeFormData = new FormData();
                                completeFormData.append('action', 'complete_registration');

                                console.log('Sending complete registration request...');

                                fetch('Web/php/accountHandler/account.handler.php', {
                                        method: 'POST',
                                        body: completeFormData
                                    })
                                    .then(response => {
                                        console.log('Complete registration response status:',
                                            response.status);

                                        if (!response.ok) {
                                            throw new Error(
                                                `HTTP error! status: ${response.status}`);
                                        }

                                        return response.text();
                                    })
                                    .then(text => {
                                        console.log('Complete registration - Raw response:',
                                            text);

                                        try {
                                            const data = JSON.parse(text);
                                            console.log(
                                                'Complete registration - Parsed response:',
                                                data);

                                            if (data.success) {
                                                alert(data.message);
                                                console.log('Redirecting to:', data
                                                    .redirect_url);
                                                window.location.href = data.redirect_url ||
                                                    '/home';
                                            } else {
                                                throw new Error(data.message ||
                                                    'Registration failed');
                                            }
                                        } catch (parseError) {
                                            console.error(
                                                'Failed to parse complete registration response:',
                                                parseError);
                                            console.error('Raw text was:', text);
                                            throw new Error(
                                                'Invalid response format from server');
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Complete registration error:', error);
                                        alert('Registration completion failed: ' + error
                                            .message);

                                        // Re-enable the button
                                        continueBtn.disabled = false;
                                        continueBtn.textContent = 'Continue';
                                    });
                            }, 100); // 100ms delay

                        } else {
                            throw new Error(data.message || 'Failed to set account type');
                        }
                    } catch (parseError) {
                        console.error('Failed to parse set account type response:', parseError);
                        console.error('Raw text was:', text);
                        throw new Error('Invalid response format from server');
                    }
                })
                .catch(error => {
                    console.error('Set account type error:', error);
                    alert('An error occurred while setting account type: ' + error.message);

                    // Re-enable the button
                    continueBtn.disabled = false;
                    continueBtn.textContent = 'Continue';
                });
        });
    }

    function cancelAccountSelection() {
        const formData = new FormData();
        formData.append('action', 'cancel_selection');

        fetch('Web/php/accountHandler/account.handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    alert('Failed to cancel selection: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error cancelling selection:', error);
                alert('Error: ' + error.message);
            });
    }
    </script>

</body>

</html>