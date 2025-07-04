<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Portal - Login</title>
    <!-- Boxicons CSS -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <style>
    :root {
        --primary-color: #dc3545;
        --gradient: linear-gradient(135deg, #dc3545, #8b0000);
        --header-padding: 15px 35px;
        --logo-size: 26px;
        --footer-padding: 35px 0 15px 0;
        --footer-font-size: 14px;
        --footer-gap: 35px;
        --nav-link-margin: 0 25px;
        --nav-link-font-size: 17px;
        --admin-shadow: 0 10px 30px rgba(220, 53, 69, 0.3);
        --admin-border: 2px solid rgba(220, 53, 69, 0.1);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1;
    }

    .container {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 450px;
        padding: 20px;
    }

    .sub-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: var(--admin-shadow);
        border: var(--admin-border);
        overflow: hidden;
        position: relative;
    }

    .sub-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: var(--gradient);
        z-index: 1;
    }

    .login-wrapper-cols {
        padding: 30px 40px 40px 40px;
        position: relative;
    }

    .login-form h3 {
        color: #2c3e50;
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 10px;
        text-align: center;
        position: relative;
    }

    .login-form h3::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 50%;
        transform: translateX(-50%);
        width: 50px;
        height: 3px;
        background: var(--gradient);
        border-radius: 2px;
    }

    .login-form p {
        color: #666;
        font-size: 15px;
        text-align: center;
        margin-bottom: 35px;
        font-weight: 500;
    }

    .form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .input-box-wrapper {
        position: relative;
    }

    .input-box {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-box input {
        width: 100%;
        padding: 15px 20px;
        border: 2px solid #e8ecf0;
        border-radius: 12px;
        font-size: 16px;
        background: #fafbfc;
        transition: all 0.3s ease;
        outline: none;
        font-weight: 500;
    }

    .input-box input:focus {
        border-color: var(--primary-color);
        background: white;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    }

    .input-box input.pwd {
        padding-right: 55px;
    }

    .eye-icon {
        position: absolute;
        right: 18px;
        font-size: 20px;
        color: #999;
        cursor: pointer;
        transition: color 0.3s ease;
    }

    .eye-icon:hover {
        color: var(--primary-color);
    }

    .error-icon,
    .check-icon {
        position: absolute;
        right: 18px;
        font-size: 18px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .error-icon {
        color: #e74c3c;
    }

    .check-icon {
        color: #27ae60;
    }

    .error-icon-show {
        opacity: 1;
    }

    .check-icon-show {
        opacity: 1;
    }

    .check-box {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 10px 0;
    }

    .check-box label {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #555;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
    }

    .remember-checkbox {
        width: 18px;
        height: 18px;
        accent-color: var(--primary-color);
        cursor: pointer;
    }

    .check-box a {
        color: var(--primary-color);
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .check-box a:hover {
        color: #8b0000;
    }

    .submit-box {
        margin-top: 0px;
    }

    .submit-box input {
        width: 100%;
        padding: 15px;
        background: var(--gradient);
        border: none;
        border-radius: 12px;
        color: white;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .submit-box input:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
    }

    .admin-info {
        background: linear-gradient(135deg, #fff5f5, #ffeaea);
        border: 1px solid rgba(220, 53, 69, 0.2);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        margin-top: 20px;
    }

    .admin-info h4 {
        color: var(--primary-color);
        font-size: 16px;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .admin-info p {
        color: #666;
        font-size: 13px;
        margin: 0;
    }

    .signup-section {
        text-align: center;
        margin-top: 20px;
        padding: 15px;
        background: rgba(220, 53, 69, 0.05);
        border-radius: 10px;
        border: 1px solid rgba(220, 53, 69, 0.1);
    }

    .signup-section p {
        color: #666;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .signup-section a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: color 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .signup-section a:hover {
        color: #8b0000;
    }

    .signup-section a i {
        font-size: 16px;
    }

    /* Responsive Design */
    @media (max-width: 480px) {
        .container {
            padding: 15px;
        }

        .login-wrapper-cols {
            padding: 40px 25px 30px 25px;
        }

        .login-form h3 {
            font-size: 28px;
        }

    }

    /* Alert Styles Only */
    .alert-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .alert-overlay.show {
        opacity: 1;
        visibility: visible;
    }

    .alert-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 30px;
        max-width: 450px;
        width: 90%;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        text-align: center;
        transform: scale(0.7);
        transition: transform 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .alert-overlay.show .alert-box {
        transform: scale(1);
    }

    .alert-box::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transform: rotate(45deg);
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% {
            transform: translateX(-100%) translateY(-100%) rotate(45deg);
        }

        100% {
            transform: translateX(100%) translateY(100%) rotate(45deg);
        }
    }

    .alert-icon {
        font-size: 4rem;
        color: #ffd700;
        margin-bottom: 20px;
        display: block;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }

        100% {
            transform: scale(1);
        }
    }

    .alert-title {
        color: #ffffff;
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 15px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .alert-message {
        color: #f0f0f0;
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 20px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .alert-details {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 25px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .alert-details p {
        margin: 5px 0;
        color: #ffffff;
        font-size: 0.95rem;
    }

    .alert-details strong {
        color: #ffd700;
    }

    .alert-button {
        background: linear-gradient(45deg, #ff6b6b, #ee5a24);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 25px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(238, 90, 36, 0.3);
        position: relative;
        overflow: hidden;
    }

    .alert-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(238, 90, 36, 0.4);
    }

    .alert-button:active {
        transform: translateY(0);
    }

    .alert-button::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: width 0.3s, height 0.3s;
    }

    .alert-button:hover::after {
        width: 100%;
        height: 100%;
    }

    .loading-spinner {
        display: none;
        width: 20px;
        height: 20px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 10px auto;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .error-alert .alert-box {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    }

    .error-alert .alert-icon {
        color: #ffffff;
    }

    .success-alert .alert-box {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    }

    .success-alert .alert-icon {
        color: #ffffff;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="sub-container">
            <div class="login-wrapper-cols">
                <div class="login-form">
                    <h3>Admin Login</h3>
                    <p>Secure access to administrative dashboard</p>
                    <form class="form" method="POST" action="/logincheck">
                        <div class="input-box-wrapper">
                            <div class="input-box">
                                <input type="text" name="email" placeholder="Administrator Email"
                                    pattern="^[^\s@]{3,}@[^\s@]{3,}\.[^\s@]{2,}$" required />
                                <span class="error-icon">
                                    <i class='bx bx-error-circle'></i>
                                </span>
                                <span class="check-icon">
                                    <i class='bx bx-check-circle'></i>
                                </span>
                            </div>
                        </div>
                        <div class="input-box">
                            <input type="password" minlength="8" name="password" placeholder="Admin Password"
                                class="pwd" required />
                            <i class="bx bx-hide eye-icon"></i>
                        </div>
                        <div class="check-box">
                            <label>
                                <input type="checkbox" class="remember-checkbox" /> Keep me signed in
                            </label>
                            <a href="/admin/forgot-password">Reset Password?</a>
                        </div>
                        <div class="submit-box">
                            <input type="submit" value="Access Dashboard" name="adminlogin" />
                        </div>
                    </form>

                    <div class="admin-info">
                        <h4>Administrative Access Only</h4>
                        <p>This portal is restricted to authorized administrators only. All login attempts are monitored
                            and logged.</p>
                    </div>

                    <div class="signup-section">
                        <p>Don't have an account ?</p>
                        <a href="/adminsignup">
                            <i class='bx bx-user-plus'></i>
                            Create Admin Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Overlay -->
    <div class="alert-overlay" id="alertOverlay">
        <div class="alert-box">
            <i class="bx bx-time-five alert-icon" id="alertIcon"></i>
            <h3 class="alert-title" id="alertTitle">Account Pending Approval</h3>
            <p class="alert-message" id="alertMessage">
                Your account is pending approval by the main administrator. Please wait for approval before accessing
                the dashboard.
            </p>
            <div class="alert-details" id="alertDetails">
                <p><strong>Name:</strong> <span id="adminName">-</span></p>
                <p><strong>Registration Date:</strong> <span id="registrationDate">-</span></p>
                <p><strong>Status:</strong> <span id="accountStatus">Pending</span></p>
            </div>
            <button class="alert-button" id="alertButton" onclick="closeAlert()">
                <span>Understood</span>
            </button>
        </div>
    </div>

    <div class="loading-spinner" id="loadingSpinner"></div>

    <script>
    // Password show and hide
    const showPWDIcons = document.querySelectorAll(".eye-icon");

    showPWDIcons.forEach((icon) => {
        icon.addEventListener("click", () => {
            const currPWD = icon.parentElement.querySelector(".pwd");

            if (currPWD.type === "password") {
                currPWD.type = "text";
                icon.classList.replace("bx-hide", "bx-show");
            } else {
                currPWD.type = "password";
                icon.classList.replace("bx-show", "bx-hide");
            }
        });
    });

    // Alert functions
    function showAlert(type, title, message, details = null) {
        const overlay = document.getElementById('alertOverlay');
        const alertBox = overlay.querySelector('.alert-box');
        const icon = document.getElementById('alertIcon');
        const titleEl = document.getElementById('alertTitle');
        const messageEl = document.getElementById('alertMessage');
        const detailsEl = document.getElementById('alertDetails');

        // Reset classes
        overlay.className = 'alert-overlay';

        // Set content
        titleEl.textContent = title;
        messageEl.textContent = message;

        // Set icon and styling based on type
        if (type === 'pending') {
            overlay.classList.add('show');
            icon.className = 'bx bx-time-five alert-icon';
            detailsEl.style.display = 'block';

            if (details) {
                document.getElementById('adminName').textContent = details.admin_name || '-';
                document.getElementById('registrationDate').textContent =
                    details.created_at ? new Date(details.created_at).toLocaleDateString() : '-';
                document.getElementById('accountStatus').textContent = 'Pending Approval';
            }
        } else if (type === 'error') {
            overlay.classList.add('show', 'error-alert');
            icon.className = 'bx bx-error-circle alert-icon';
            detailsEl.style.display = 'none';
        } else if (type === 'success') {
            overlay.classList.add('show', 'success-alert');
            icon.className = 'bx bx-check-circle alert-icon';
            detailsEl.style.display = 'none';
        }

        overlay.classList.add('show');
    }

    function closeAlert() {
        const overlay = document.getElementById('alertOverlay');
        overlay.classList.remove('show');
    }

    // Email validation and form handling
    const validateForm = (formSelector) => {
        const formElement = document.querySelector(formSelector);

        const validateSingleFormInput = (formInput) => {
            const input = formInput.querySelector("input");
            const error = formInput.querySelector(".error-icon");
            const success = formInput.querySelector(".check-icon");

            let formInputError = false;

            // Check if required and empty
            if (input.hasAttribute("required") && input.value.trim() === "") {
                formInputError = true;
            }

            // Check email pattern
            if (!formInputError && input.hasAttribute("pattern")) {
                const regex = new RegExp(input.pattern);
                if (!regex.test(input.value)) {
                    formInputError = true;
                }
            }

            // Check password length
            if (!formInputError && input.name === "password") {
                if (input.value.length < 8) {
                    formInputError = true;
                }
            }

            // Apply UI styling
            if (formInputError) {
                if (error) error.classList.add("error-icon-show");
                if (success) success.classList.remove("check-icon-show");
                input.style.borderColor = "#e74c3c";
            } else {
                if (error) error.classList.remove("error-icon-show");
                if (success) success.classList.add("check-icon-show");
                input.style.borderColor = "#dc3545";
            }

            return !formInputError;
        };

        const validateCheckbox = () => {
            const checkbox = formElement.querySelector(".remember-checkbox");
            const label = checkbox.closest("label");
            const isChecked = checkbox.checked;

            // Remove any invalid styling since checkbox is optional
            checkbox.classList.remove("invalid");
            label.classList.remove("invalid");

            return true; // Always return true since checkbox is optional
        };

        const setupInputEvents = () => {
            const formInputs = Array.from(formElement.querySelectorAll(".input-box"));

            formInputs.forEach((formInput) => {
                const input = formInput.querySelector("input");

                input.addEventListener("input", () => {
                    validateSingleFormInput(formInput);
                });

                input.addEventListener("blur", () => {
                    validateSingleFormInput(formInput);
                });
            });

            // Validate checkbox on change (optional)
            const checkbox = formElement.querySelector(".remember-checkbox");
            if (checkbox) {
                checkbox.addEventListener("change", validateCheckbox);
            }
        };

        // Form submission with AJAX
        formElement.setAttribute("novalidate", "");
        formElement.addEventListener("submit", (event) => {
            event.preventDefault();

            const formInputs = Array.from(formElement.querySelectorAll(".input-box"));
            const isValidInputs = formInputs.every((formInput) =>
                validateSingleFormInput(formInput)
            );
            const isCheckboxValid = validateCheckbox();

            if (isValidInputs && isCheckboxValid) {
                // Show loading
                const submitButton = formElement.querySelector('input[type="submit"]');
                const originalValue = submitButton.value;
                submitButton.disabled = true;
                submitButton.value = 'Logging in...';

                // Submit form via AJAX
                const formData = new FormData(formElement);

                fetch('Web/php/ADMIN/logincheck.php', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showAlert('success', 'Login Successful!', data.message);
                            setTimeout(() => {
                                window.location.href = data.redirect || '/adminhome';
                            }, 2000);
                        } else if (data.status === 'pending') {
                            showAlert('pending', 'Account Pending Approval', data.message, data);
                        } else {
                            showAlert('error', 'Login Failed', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('error', 'Connection Error', 'An error occurred. Please try again.');
                    })
                    .finally(() => {
                        // Re-enable submit button
                        submitButton.disabled = false;
                        submitButton.value = originalValue;
                    });
            } else {
                console.log("Form has errors.");
            }
        });

        setupInputEvents();
    };

    validateForm(".form");
    </script>
</body>

</html>