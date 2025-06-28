<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Failed - Paragliding Adventure</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Arial', sans-serif;
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .error-container {
        background: white;
        border-radius: 20px;
        padding: 40px;
        max-width: 500px;
        width: 100%;
        text-align: center;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        animation: slideUp 0.6s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .error-icon {
        width: 80px;
        height: 80px;
        background: #dc3545;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        animation: shake 0.5s ease-in-out;
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-5px);
        }

        75% {
            transform: translateX(5px);
        }
    }

    .error-icon::before {
        content: "✕";
        color: white;
        font-size: 40px;
        font-weight: bold;
    }

    .error-title {
        color: #333;
        font-size: 28px;
        margin-bottom: 10px;
        font-weight: 600;
    }

    .error-message {
        color: #666;
        font-size: 16px;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .error-details {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin: 20px 0;
        text-align: left;
        border-left: 4px solid #dc3545;
    }

    .error-details h3 {
        color: #dc3545;
        margin-bottom: 15px;
        font-size: 18px;
    }

    .error-reasons {
        color: #666;
        line-height: 1.6;
    }

    .error-reasons ul {
        margin: 10px 0;
        padding-left: 20px;
    }

    .error-reasons li {
        margin-bottom: 5px;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-top: 30px;
    }

    .btn {
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: #007bff;
        color: white;
    }

    .btn-primary:hover {
        background: #0056b3;
        transform: translateY(-2px);
    }

    .btn-danger {
        background: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background: #c82333;
        transform: translateY(-2px);
    }

    .support-info {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 8px;
        padding: 15px;
        margin: 20px 0;
        font-size: 14px;
        color: #856404;
    }

    .support-info strong {
        color: #533f03;
    }

    @media (max-width: 600px) {
        .error-container {
            padding: 30px 20px;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }
    }
    </style>
</head>

<body>

    <div class="error-container">
        <div class="error-icon"></div>

        <h1 class="error-title">Payment Failed</h1>
        <p class="error-message">
            We're sorry, but your booking could not be completed.
            Your payment was not processed and no charges were made.
        </p>

        <div class="error-details">
            <h3>What happened?</h3>
            <div class="error-reasons">
                <p id="errorMessage">
                    <?php 
                $message = $_GET['message'] ?? 'Payment was cancelled or failed.';
                echo htmlspecialchars(urldecode($message)); 
                ?>
                </p>

                <p><strong>Common reasons for payment failure:</strong></p>
                <ul>
                    <li>Payment was cancelled by user</li>
                    <li>Insufficient funds in eSewa account</li>
                    <li>Network connectivity issues</li>
                    <li>eSewa service temporarily unavailable</li>
                    <li>Invalid payment credentials</li>
                </ul>
            </div>
        </div>

        <div class="support-info">
            <strong>Need help?</strong> Contact our support team if you continue experiencing issues.
            We're here to help you complete your booking!
        </div>

        <div class="action-buttons">
            <a href="/booking" class="btn btn-primary">Try Again</a>
            <a href="/contact" class="btn btn-danger">Contact Support</a>
        </div>
    </div>

    <script>
    // Get error message from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');

    if (message) {
        console.error('Booking error:', decodeURIComponent(message));

        // Update error message if it exists
        const errorElement = document.getElementById('errorMessage');
        if (errorElement && message !== 'Payment was cancelled or failed.') {
            errorElement.textContent = decodeURIComponent(message);
        }
    }

    // Auto redirect to booking page after 60 seconds
    setTimeout(() => {
        window.location.href = '/booking';
    }, 60000);
    </script>

</body>

</html>