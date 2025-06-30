<?php
session_start();

// If user is already logged in, redirect to admin dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin.php');
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $admin_key = trim($_POST['admin_key']);
    
    // Basic validation
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password) || empty($admin_key)) {
        $error_message = 'Please fill in all fields.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error_message = 'Password must be at least 8 characters long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif ($admin_key !== 'ADMIN_SECRET_KEY_2024') { // Change this to your secret key
        $error_message = 'Invalid admin registration key.';
    } else {
        // Database connection (you'll need to update these credentials)
        $host = 'localhost';
        $dbname = 'admin_system';
        $username = 'root';
        $db_password = '';
        
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $db_password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error_message = 'An account with this email already exists.';
            } else {
                // Hash password and insert new admin
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO admins (full_name, email, password, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$full_name, $email, $hashed_password]);
                
                $success_message = 'Account created successfully! You can now login.';
            }
        } catch (PDOException $e) {
            $error_message = 'Database connection failed. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Signup - Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .signup-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            position: relative;
        }

        .signup-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .signup-header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .signup-header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .signup-form {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }

        .form-group.valid input {
            border-color: #28a745;
        }

        .form-group.invalid input {
            border-color: #dc3545;
        }

        .signup-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .signup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .signup-btn:active {
            transform: translateY(0);
        }

        .signup-btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            animation: shake 0.5s ease-in-out;
        }

        .error-message::before {
            content: "⚠️";
            margin-right: 8px;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            animation: slideIn 0.5s ease-in-out;
        }

        .success-message::before {
            content: "✅";
            margin-right: 8px;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e1e5e9;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #764ba2;
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            user-select: none;
        }

        .password-toggle:hover {
            color: #495057;
        }

        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }

        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }

        .admin-key-info {
            background: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #004085;
        }

        .admin-key-info::before {
            content: "ℹ️";
            margin-right: 8px;
        }

        @media (max-width: 480px) {
            .signup-container {
                margin: 10px;
            }
            
            .signup-header {
                padding: 25px 20px;
            }
            
            .signup-form {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-header">
            <h1>Admin Signup</h1>
            <p>Create your admin account</p>
        </div>
        
        <form class="signup-form" method="POST" action="" id="signupForm">
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required 
                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <span class="password-toggle" onclick="togglePassword('password')">👁️</span>
                <div class="password-strength" id="passwordStrength"></div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <span class="password-toggle" onclick="togglePassword('confirm_password')">👁️</span>
            </div>
            
            <div class="admin-key-info">
                Contact your system administrator to get the admin registration key.
            </div>
            
            <div class="form-group">
                <label for="admin_key">Admin Registration Key</label>
                <input type="password" id="admin_key" name="admin_key" required 
                       placeholder="Enter admin registration key">
            </div>
            
            <button type="submit" class="signup-btn" id="signupBtn">
                <span class="loading-spinner" id="loadingSpinner"></span>
                <span id="btnText">Create Account</span>
            </button>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </form>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleBtn = passwordInput.nextElementSibling;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }

        function checkPasswordStrength(password) {
            const strengthIndicator = document.getElementById('passwordStrength');
            let strength = 0;
            let feedback = '';
            
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            switch (strength) {
                case 0:
                case 1:
                case 2:
                    feedback = 'Weak password';
                    strengthIndicator.className = 'password-strength strength-weak';
                    break;
                case 3:
                case 4:
                    feedback = 'Medium password';
                    strengthIndicator.className = 'password-strength strength-medium';
                    break;
                case 5:
                    feedback = 'Strong password';
                    strengthIndicator.className = 'password-strength strength-strong';
                    break;
            }
            
            strengthIndicator.textContent = feedback;
        }

        document.getElementById('password').addEventListener('input', function() {
            checkPasswordStrength(this.value);
            validatePasswordMatch();
        });

        document.getElementById('confirm_password').addEventListener('input', validatePasswordMatch);

        function validatePasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const confirmGroup = document.getElementById('confirm_password').parentElement;
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    confirmGroup.classList.remove('invalid');
                    confirmGroup.classList.add('valid');
                } else {
                    confirmGroup.classList.remove('valid');
                    confirmGroup.classList.add('invalid');
                }
            } else {
                confirmGroup.classList.remove('valid', 'invalid');
            }
        }

        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const signupBtn = document.getElementById('signupBtn');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const btnText = document.getElementById('btnText');
            
            // Basic client-side validation
            const fullName = document.getElementById('full_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const adminKey = document.getElementById('admin_key').value.trim();
            
            if (!fullName || !email || !password || !confirmPassword || !adminKey) {
                e.preventDefault();
                showError('Please fill in all fields.');
                return;
            }
            
            if (!isValidEmail(email)) {
                e.preventDefault();
                showError('Please enter a valid email address.');
                return;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                showError('Password must be at least 8 characters long.');
                return;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                showError('Passwords do not match.');
                return;
            }
            
            // Show loading state
            signupBtn.classList.add('loading');
            loadingSpinner.style.display = 'inline-block';
            btnText.textContent = 'Creating Account...';
        });

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function showError(message) {
            // Remove existing error messages
            const existingError = document.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            // Create new error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            
            // Insert before the first form group
            const firstFormGroup = document.querySelector('.form-group');
            firstFormGroup.parentNode.insertBefore(errorDiv, firstFormGroup);
            
            // Reset button state
            const signupBtn = document.getElementById('signupBtn');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const btnText = document.getElementById('btnText');
            
            signupBtn.classList.remove('loading');
            loadingSpinner.style.display = 'none';
            btnText.textContent = 'Create Account';
        }

        // Add input validation styling
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    const formGroup = this.parentElement;
                    
                    if (this.value.trim() === '') {
                        formGroup.classList.remove('valid');
                        formGroup.classList.add('invalid');
                    } else if (this.type === 'email' && !isValidEmail(this.value)) {
                        formGroup.classList.remove('valid');
                        formGroup.classList.add('invalid');
                    } else {
                        formGroup.classList.remove('invalid');
                        formGroup.classList.add('valid');
                    }
                });
                
                input.addEventListener('focus', function() {
                    const formGroup = this.parentElement;
                    formGroup.classList.remove('valid', 'invalid');
                });
            });
        });
    </script>
</body>
</html>