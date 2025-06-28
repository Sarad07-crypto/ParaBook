<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - Paragliding Adventure</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Arial', sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .success-container {
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

    .success-icon {
        width: 80px;
        height: 80px;
        background: #28a745;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    .success-icon::before {
        content: "✓";
        color: white;
        font-size: 40px;
        font-weight: bold;
    }

    .success-title {
        color: #333;
        font-size: 28px;
        margin-bottom: 10px;
        font-weight: 600;
    }

    .success-message {
        color: #666;
        font-size: 16px;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .booking-details {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin: 20px 0;
        text-align: left;
    }

    .booking-details h3 {
        color: #333;
        margin-bottom: 15px;
        font-size: 18px;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }

    .detail-label {
        font-weight: 600;
        color: #555;
    }

    .detail-value {
        color: #333;
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

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #545b62;
        transform: translateY(-2px);
    }

    .email-note {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 15px;
        margin: 20px 0;
        border-radius: 0 8px 8px 0;
        font-size: 14px;
        color: #1976d2;
    }

    @media (max-width: 600px) {
        .success-container {
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

    <div class="success-container">
        <div class="success-icon"></div>

        <h1 class="success-title">Payment Successful!</h1>
        <p class="success-message">
            Your paragliding adventure has been booked and payment confirmed.
            Get ready for an amazing experience!
        </p>

        <div class="booking-details">
            <h3>Booking Details</h3>
            <div class="detail-row">
                <span class="detail-label">Booking Number:</span>
                <span class="detail-value" id="bookingNumber">
                    <?php echo htmlspecialchars($_GET['booking_no'] ?? 'N/A'); ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value" style="color: #28a745; font-weight: 600;">Confirmed & Paid</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span class="detail-value">eSewa</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Booking Date:</span>
                <span class="detail-value"><?php echo date('F j, Y g:i A'); ?></span>
            </div>
        </div>

        <div class="email-note">
            📧 A confirmation email with detailed booking information has been sent to your registered email address.
        </div>

        <div class="action-buttons">
            <a href="/dashboard" class="btn btn-primary">View My Bookings</a>
            <a href="/services" class="btn btn-secondary">Book Another Adventure</a>
        </div>
    </div>

    <script>
    // Show success message from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');

    if (message) {
        // You can add additional success handling here
        console.log('Success message:', decodeURIComponent(message));
    }

    // Auto redirect to dashboard after 30 seconds
    setTimeout(() => {
        window.location.href = '/dashboard';
    }, 30000);
    </script>

</body>

</html>