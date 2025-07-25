<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Signup</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
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
        padding: 20px 0;
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
        max-width: 600px;
        padding: 20px;
    }

    .signup-wrapper {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: var(--admin-shadow);
        border: var(--admin-border);
        overflow: hidden;
        position: relative;
    }

    .signup-wrapper::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: var(--gradient);
        z-index: 1;
    }

    .admin-badge {
        position: absolute;
        top: 15px;
        right: 20px;
        background: var(--gradient);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .signup-form {
        padding: 50px 40px 40px 40px;
        position: relative;
    }

    .signup-form p {
        color: #2c3e50;
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 10px;
        text-align: center;
        position: relative;
    }

    .form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .input-box-wrapper {
        display: flex;
        gap: 15px;
    }

    .input-box {
        position: relative;
        display: flex;
        align-items: center;
        flex: 1;
    }

    .input-box input,
    .input-box select {
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

    .input-box input:focus,
    .input-box select:focus {
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

    .error-message-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-top: -10px;
    }

    .password-requirements {
        flex: 1;
        margin-right: 20px;
    }

    .password-requirements ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .pwd-rqm-li {
        font-size: 12px;
        color: #dc3545;
        margin: 3px 0;
        display: none;
        position: relative;
        padding-left: 15px;
    }

    .pwd-rqm-li::before {
        content: '✗';
        position: absolute;
        left: 0;
        color: #dc3545;
    }

    .pwd-rqm-li-show {
        display: block;
    }

    .pwd-rqm-li-show-valid {
        display: block;
        color: #27ae60;
    }

    .pwd-rqm-li-show-valid::before {
        content: '✓';
        color: #27ae60;
    }

    .error-message {
        font-size: 12px;
        color: #dc3545;
        display: none;
        margin-top: 5px;
    }

    .error-message-show {
        display: block;
    }

    .check-box {
        display: flex;
        align-items: center;
        margin: 15px 0;
    }

    .check-box-col {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .usertype-wrapper {
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 14px;
        font-weight: 500;
        color: #555;
    }

    .usertype {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .usertype label {
        display: flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
    }

    .usertype input[type="radio"] {
        width: 16px;
        height: 16px;
        accent-color: var(--primary-color);
    }

    .submit-box {
        margin-top: 20px;
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

    .login-section {
        text-align: center;
        margin-top: 20px;
        padding: 15px;
        background: rgba(220, 53, 69, 0.05);
        border-radius: 10px;
        border: 1px solid rgba(220, 53, 69, 0.1);
    }

    .login-section p {
        color: #666;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .login-section a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: color 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .login-section a:hover {
        color: #8b0000;
    }

    .login-section a i {
        font-size: 16px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .container {
            padding: 15px;
        }

        .signup-form {
            padding: 40px 25px 30px 25px;
        }

        .signup-form p {
            font-size: 28px;
        }

        .input-box-wrapper {
            flex-direction: column;
            gap: 0;
        }

        .admin-badge {
            top: 10px;
            right: 15px;
            font-size: 11px;
            padding: 5px 10px;
        }

        .error-message-wrapper {
            flex-direction: column;
            gap: 10px;
        }

        .check-box-col {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
    }

    @media (max-width: 480px) {
        .signup-form p {
            font-size: 24px;
        }

        .usertype-wrapper {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
    }

    .message-box {
        padding: 15px 20px;
        border-radius: 8px;
        font-weight: bold;
        position: relative;
        margin-bottom: 20px;
    }

    .message-box.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .message-box.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    #closeMessage {
        color: inherit;
        opacity: 0.7;
    }

    #closeMessage:hover {
        opacity: 1;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="signup-wrapper">
            <div class="admin-badge">
                <i class='bx bx-user-plus'></i>
                Create Account
            </div>
            <div class="signup-form">
                <p>Create an account</p>
                <form class="form" method="post" action="/createadmins" id="signupForm">
                    <div class="input-box-wrapper">
                        <div class="input-box">
                            <input type="text" minlength="2" name="firstName" id="firstName" placeholder="First Name"
                                text-only required />
                            <span class="error-icon">
                                <i class='bx bx-error-circle'></i>
                            </span>
                            <span class="check-icon">
                                <i class='bx bx-check-circle'></i>
                            </span>
                        </div>
                        <div class="input-box">
                            <input type="text" minlength="2" name="lastName" id="lastName" placeholder="Last Name"
                                text-only required />
                            <span class="error-icon">
                                <i class='bx bx-error-circle'></i>
                            </span>
                            <span class="check-icon">
                                <i class='bx bx-check-circle'></i>
                            </span>
                        </div>
                    </div>
                    <div class="input-box-wrapper">
                        <div class="input-box">
                            <input type="text" name="email" placeholder="Email"
                                pattern="^[^\s@]{3,}@[^\s@]{3,}\.[^\s@]{2,}$" required />
                            <span class="error-icon">
                                <i class='bx bx-error-circle'></i>
                            </span>
                            <span class="check-icon">
                                <i class='bx bx-check-circle'></i>
                            </span>
                        </div>
                        <div class="input-box">
                            <input type="text" name="contact" placeholder="Contact No." pattern="^9\d{9}$" required />
                            <span class="error-icon">
                                <i class='bx bx-error-circle'></i>
                            </span>
                            <span class="check-icon">
                                <i class='bx bx-check-circle'></i>
                            </span>
                        </div>
                    </div>
                    <div class="input-box-wrapper">
                        <div class="input-box">
                            <input type="password" name="password" placeholder="Password" id="password" class="pwd"
                                pattern="^(?!.*\\s)(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$&!.])[A-Za-z\d@$&!.]{8,}$"
                                required />
                            <span class="error-icon">
                                <i class='bx bx-error-circle'></i>
                            </span>
                            <span class="check-icon">
                                <i class='bx bx-check-circle'></i>
                            </span>
                        </div>
                        <div class="input-box">
                            <input type="password" name="confirmPassword" placeholder="Confirm Password" class="pwd"
                                match="password" required />
                            <i class="bx bx-hide eye-icon"></i>
                        </div>
                    </div>
                    <div class="error-message-wrapper">
                        <span class="password-requirements">
                            <ul>
                                <li class="pwd-rqm-li">At least 8 characters</li>
                                <li class="pwd-rqm-li">At least 1 special character (@$&!.)</li>
                                <li class="pwd-rqm-li">At least 1 number</li>
                                <li class="pwd-rqm-li">At least 1 uppercase and 1 lowercase letter</li>
                            </ul>
                        </span>
                        <span class="error-message">Password didn't match</span>
                    </div>

                    <div class="input-box-wrapper">
                        <div class="input-box">
                            <input type="date" name="DOB" date-validation required />
                        </div>
                        <div class="check-box" style="flex: 1; margin: 0; display: flex; align-items: center;">
                            <div class="check-box-col" style="width: 100%;">
                                <div class="usertype-wrapper">
                                    <label>Gender:</label>
                                </div>
                                <div class="usertype-wrapper" style="margin-left:10px;">
                                    <div class="usertype">
                                        <label class="user-second-margin">
                                            <input type="radio" name="gender" value="Male" required radio-group />
                                            Male
                                        </label>
                                    </div>
                                    <div class="usertype">
                                        <label class="user-second-margin">
                                            <input type="radio" name="gender" value="Female" required radio-group />
                                            Female
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="submit-box">
                        <input type="submit" value="Create Account" />
                    </div>
                </form>

                <div id="messageContainer" style="display: none; margin: 20px 0;">
                    <div class="message-box">
                        <span id="messageText"></span>
                        <button type="button" id="closeMessage"
                            style="float: right; background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
                    </div>
                </div>

                <div class="login-section">
                    <p>Already have an account ?</p>
                    <a href="/adminlogin">
                        <i class='bx bx-log-in'></i>
                        Sign In Here
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="Web/scripts/signup.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('signupForm');
        const submitButton = form.querySelector('input[type="submit"]');
        const messageContainer = document.getElementById('messageContainer');
        const messageText = document.getElementById('messageText');
        const messageBox = document.querySelector('.message-box');
        const closeButton = document.getElementById('closeMessage');

        let isSubmitting = false; // Prevent double submission

        // Function to show message
        function showMessage(message, type) {
            messageText.textContent = message;
            messageBox.className = 'message-box ' + type;
            messageContainer.style.display = 'block';
            messageContainer.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }

        // Function to hide message
        function hideMessage() {
            messageContainer.style.display = 'none';
        }

        // Close message button
        closeButton.addEventListener('click', hideMessage);

        // Handle form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Prevent double submission
            if (isSubmitting) {
                console.log('Form already being submitted, ignoring...');
                return;
            }

            isSubmitting = true;
            submitButton.disabled = true;
            submitButton.value = 'Creating Account...';
            hideMessage();

            const formData = new FormData(form);

            fetch('/createadmins', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);

                    const isSuccess = data.success === true || data.status === 'success';

                    if (isSuccess) {
                        showMessage(data.message, 'success');
                        form.reset();

                        // Redirect after showing success message
                        setTimeout(() => {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        }, 2000);
                    } else {
                        const errorMessage = data.message || 'An error occurred. Please try again.';
                        showMessage(errorMessage, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('An error occurred. Please try again.', 'error');
                })
                .finally(() => {
                    // Re-enable form submission
                    isSubmitting = false;
                    submitButton.disabled = false;
                    submitButton.value = 'Create Account';
                });
        });
    });
    </script>
</body>

</html>