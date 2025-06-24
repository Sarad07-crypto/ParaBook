<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Type Selection</title>
    <link rel="stylesheet" href="Web/css/accountSelection.css?v=1.0" />
</head>

<body>

    <?php
        session_start();
        if (!isset($_SESSION['show_account_selection']) || !isset($_SESSION['temp_user_data'])) {
            // Prevent direct access
            header("Location: /login");
            exit();
        }

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

        // Prevent any further output/inclusion that might duplicate content
        $content_rendered = true;
    ?>

    <script>
    console.log('Account selection page loaded successfully');
    console.log('Session ID: <?php echo session_id(); ?>');
    </script>

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

    <script src="Web/scripts/accountSelection.js?v=1.0"></script>
</body>

</html>