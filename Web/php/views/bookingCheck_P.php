    <?php
        require 'avatar.php';
        require('partials/header.php');
        $accType = $_SESSION['acc_type'] ?? 'passenger';
        if ($accType === 'company') {
            require('partials/nav_C.php');
        } else {
            require('partials/nav_P.php');
        }
        
        $serviceId = $_SESSION['service_id'] ?? 0;
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Booking Lookup</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </head>

    <style>
:root {
    --primary-color: #007bff;
    --gradient: linear-gradient(90deg, #1e90ff, #0056b3);
    --header-padding: 12px 30px;
    --logo-size: 24px;
    --footer-padding: 30px 0 10px 0;
    --footer-font-size: 15px;
    --footer-gap: 30px;
    --nav-link-margin: 0 20px;
    --nav-link-font-size: 16px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.container {
    width: 50%;
    padding: 40px;
    margin: 0 auto;
}

.booking-lookup-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    animation: slideUp 0.6s ease;
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

.card-header {
    background: var(--gradient);
    color: white;
    padding: 40px 30px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.card-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

.card-header h1 {
    font-size: 2.2rem;
    margin-bottom: 10px;
    font-weight: 700;
    position: relative;
    z-index: 1;
}

.card-header p {
    font-size: 1.1rem;
    opacity: 0.9;
    position: relative;
    z-index: 1;
}

.card-body {
    padding: 40px 30px;
}

.info-section {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
    border-left: 4px solid var(--primary-color);
}

.info-section h3 {
    color: #333;
    margin-bottom: 10px;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-section p {
    color: #666;
    font-size: 0.95rem;
    line-height: 1.5;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 1rem;
}

.form-group input {
    width: 100%;
    padding: 15px 20px;
    border: 2px solid #e1e5e9;
    border-radius: 12px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary-color);
    background: white;
    box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.1);
    transform: translateY(-2px);
}

.form-group input::placeholder {
    color: #999;
}

.lookup-btn {
    width: 100%;
    background: var(--gradient);
    color: white;
    border: none;
    padding: 18px;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 20px;
}

.lookup-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 123, 255, 0.3);
}

.lookup-btn:active {
    transform: translateY(-1px);
}

.lookup-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.loading-spinner {
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.help-section {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #e1e5e9;
}

.help-section p {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.help-links {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

.help-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    padding: 8px 16px;
    border-radius: 8px;
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.help-link:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    border: 1px solid #f5c6cb;
    display: none;
    animation: fadeIn 0.3s ease;
}

.success-message {
    background: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    border: 1px solid #c3e6cb;
    display: none;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.booking-result {
    display: none;
    margin-top: 30px;
    padding: 25px;
    background: #f8f9fa;
    border-radius: 15px;
    border: 2px solid #e1e5e9;
}

.booking-result.show {
    display: block;
    animation: slideDown 0.5s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.booking-header {
    text-align: center;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e1e5e9;
}

.booking-header h2 {
    color: var(--primary-color);
    font-size: 1.5rem;
    margin-bottom: 5px;
}

.booking-id {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
}

.booking-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 25px;
}

.detail-item {
    background: white;
    padding: 15px;
    border-radius: 10px;
    border: 1px solid #e1e5e9;
}

.detail-item label {
    font-size: 0.85rem;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
    display: block;
}

.detail-item span {
    font-size: 1rem;
    font-weight: 600;
    color: #333;
    display: block;
}

.status-badge {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 10px 0;
    min-width: 80px;
    text-align: center;
}

.status-confirmed {
    background: #d4edda;
    color: #155724;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.action-buttons {
    display: flex;
    gap: 20px;
    margin-top: 25px;
}

.action-btn {
    flex: 2;
    padding: 12px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background: var(--gradient);
    color: white;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.new-search-btn {
    width: 100%;
    background: transparent;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
    padding: 12px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 15px;
}

.new-search-btn:hover {
    background: var(--primary-color);
    color: white;
}

.loading {
    opacity: 0.7;
    cursor: not-allowed;
}

.error {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

/* Debug styles */
.debug-info {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    padding: 15px;
    margin: 20px 0;
    border-radius: 10px;
    display: none;
}

.debug-info h4 {
    color: #856404;
    margin-bottom: 10px;
}

.debug-info pre {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    font-size: 12px;
    overflow-x: auto;
}

/* update button disbled */
.new-search-btn.disabled,
.new-search-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    color: black;
}

.new-search-btn.disabled:hover,
.new-search-btn:disabled:hover {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
}

@media (max-width: 768px) {
    .container {
        width: 95%;
        padding: 20px 15px;
    }

    .card-header h1 {
        font-size: 1.8rem;
    }

    .card-body {
        padding: 30px 20px;
    }

    .booking-details {
        grid-template-columns: 1fr;
    }

    .action-buttons {
        flex-direction: column;
    }

    .help-links {
        flex-direction: column;
        gap: 10px;
    }
}
    </style>

    <body>
        <div class="container">
            <div class="booking-lookup-card">
                <div class="card-header">
                    <h1>✈️ Find Your Booking</h1>
                    <p>Enter your details to view and manage your reservation</p>
                </div>

                <div class="card-body">
                    <div class="info-section">
                        <h3>🔍 How to Find Your Booking</h3>
                        <p>Enter your booking number exactly as provided during your reservation. Your
                            booking details will be displayed instantly.</p>
                    </div>

                    <div id="errorMessage" class="error-message"></div>
                    <div id="successMessage" class="success-message"></div>
                    <div id="debugInfo" class="debug-info">
                        <h4>Debug Information:</h4>
                        <pre id="debugContent"></pre>
                    </div>

                    <form id="bookingLookupForm">
                        <div class="form-group">
                            <label for="bookingNumber">Booking Number *</label>
                            <input type="text" id="bookingNumber" placeholder="e.g., BK-123456789" maxlength="20"
                                required />
                        </div>

                        <div class="form-group">
                            <label for="dateOfBirth">Date of Birth *</label>
                            <input type="date" id="dateOfBirth" required />
                        </div>

                        <button type="submit" class="lookup-btn" id="lookupBtn">
                            <span id="btnText">🔍 Find My Booking</span>
                            <div id="loadingSpinner" class="loading-spinner" style="display: none;"></div>
                        </button>
                    </form>

                    <div id="bookingResult" class="booking-result">
                        <div class="booking-header">
                            <h2>Booking Found!</h2>
                            <div class="booking-id" id="foundBookingId"></div>
                        </div>

                        <div class="booking-details">
                            <!-- Personal Information Section -->
                            <div class="detail-item">
                                <label>Full Name</label>
                                <span id="passengerName"></span>
                            </div>
                            <div class="detail-item">
                                <label>Gender</label>
                                <span id="gender"></span>
                            </div>
                            <div class="detail-item">
                                <label>Contact Number</label>
                                <span id="contact"></span>
                            </div>
                            <div class="detail-item">
                                <label>Country</label>
                                <span id="country"></span>
                            </div>
                            <div class="detail-item">
                                <label>Email</label>
                                <span id="email"></span>
                            </div>

                            <!-- Flight Information Section -->
                            <div class="detail-item">
                                <label>Flight Date</label>
                                <span id="flightDate"></span>
                            </div>
                            <div class="detail-item">
                                <label>Pickup Location</label>
                                <span id="pickupLocation"></span>
                            </div>
                            <div class="detail-item">
                                <label>Flight Type</label>
                                <span id="flightType"></span>
                            </div>
                            <div class="detail-item">
                                <label>Weight (kg)</label>
                                <span id="weight"></span>
                            </div>
                            <div class="detail-item">
                                <label>Age</label>
                                <span id="age"></span>
                            </div>
                            <div class="detail-item">
                                <label>Medical Condition</label>
                                <span id="medicalCondition"></span>
                            </div>
                        </div>

                        <div class="status-badge" id="bookingStatus"></div>

                        <div class="action-buttons">
                            <button class="new-search-btn update-btn" onclick="editBooking()" id="editBtn">✏️ Update
                                Booking</button>
                            <button class="new-search-btn" onclick="newSearch()">🔍 Search Another Booking</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <script>
        $(document).ready(function() {
            // Cache jQuery selectors for better performance
            const $form = $('#bookingLookupForm');
            const $bookingResult = $('#bookingResult');
            const $errorMessage = $('#errorMessage');
            const $successMessage = $('#successMessage');
            const $debugInfo = $('#debugInfo');
            const $debugContent = $('#debugContent');
            const $loadingSpinner = $('#loadingSpinner');
            const $btnText = $('#btnText');
            const $lookupBtn = $('#lookupBtn');

            // Debug mode toggle
            let debugMode = false;

            // Check if user is logged in on page load
            checkUserAuthentication();

            // Form submission handler
            $form.on('submit', function(e) {
                e.preventDefault();
                searchBooking();
            });

            function checkUserAuthentication() {
                // Check if user is logged in
                $.ajax({
                    url: 'Web/php/AJAX/checkAuth.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (!response.authenticated) {
                            showError('Please log in to view your bookings.');
                            $form.hide();
                            // Optionally redirect to login page
                            // window.location.href = '/login';
                        }
                    },
                    error: function() {
                        showError('Unable to verify authentication. Please refresh the page.');
                    }
                });
            }

            function searchBooking() {
                const bookingNumber = $('#bookingNumber').val().trim();
                const dateOfBirth = $('#dateOfBirth').val();

                // Hide previous messages
                hideMessages();

                // Validation
                if (!bookingNumber || !dateOfBirth) {
                    showError('Please fill in all required fields.');
                    return;
                }

                // Show loading state
                showLoading(true);

                if (debugMode) {
                    logDebug('Starting booking search', {
                        bookingNumber: bookingNumber,
                        dateOfBirth: dateOfBirth,
                        ajaxUrl: 'Web/php/AJAX/bookingLookup.php'
                    });
                }

                // jQuery AJAX request with user authentication
                $.ajax({
                    url: 'Web/php/AJAX/bookingLookup.php',
                    type: 'POST',
                    data: {
                        booking_number: bookingNumber,
                        date_of_birth: dateOfBirth,
                        // Include a flag to verify user ownership
                        verify_user_ownership: true
                    },
                    dataType: 'json',
                    timeout: 10000, // 10 second timeout
                    success: function(response, textStatus, xhr) {
                        showLoading(false);

                        if (debugMode) {
                            logDebug('AJAX Success Response', {
                                response: response,
                                textStatus: textStatus,
                                status: xhr.status
                            });
                        }

                        if (response && response.success) {
                            if (response.data) {
                                displayBookingDetails(response.data);
                                showSuccess('Booking found successfully!');
                            } else {
                                showError('Booking data is missing from server response.');
                                if (debugMode) {
                                    logDebug('Missing booking data', response);
                                }
                            }
                        } else {
                            // Handle different error scenarios
                            if (response.error_code === 'UNAUTHORIZED') {
                                showError('You are not authorized to view this booking.');
                            } else if (response.error_code === 'NOT_FOUND') {
                                showError('Booking not found or does not belong to your account.');
                            } else if (response.error_code === 'NOT_AUTHENTICATED') {
                                showError('Please log in to view your bookings.');
                                // Optionally redirect to login
                                // setTimeout(() => window.location.href = '/login', 2000);
                            } else {
                                showError(response.message ||
                                    'Booking not found. Please check your details.');
                            }

                            if (debugMode) {
                                logDebug('Booking lookup failed', response);
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        showLoading(false);

                        const errorInfo = {
                            status: status,
                            error: error,
                            responseText: xhr.responseText,
                            statusCode: xhr.status,
                            readyState: xhr.readyState
                        };

                        if (debugMode) {
                            logDebug('AJAX Error', errorInfo);
                        }

                        console.error('AJAX Error:', errorInfo);

                        // Handle different error types
                        if (status === 'timeout') {
                            showError('Request timed out. Please try again.');
                        } else if (status === 'parsererror') {
                            showError('Server returned invalid data. Please try again.');
                            console.error('Response that failed to parse:', xhr.responseText);
                        } else if (xhr.status === 401) {
                            showError('Authentication required. Please log in.');
                        } else if (xhr.status === 403) {
                            showError('You are not authorized to view this booking.');
                        } else if (xhr.status === 404) {
                            showError(
                                'Service not found. Please check the file path: Web/php/AJAX/bookingLookup.php'
                            );
                        } else if (xhr.status === 500) {
                            showError('Server error. Please check server logs.');
                        } else if (status === 'error' && xhr.status === 0) {
                            showError('Network error. Please check your connection and file path.');
                        } else {
                            showError(
                                `An unexpected error occurred. Status: ${status}, Error: ${error}`
                            );
                        }
                    }
                });
            }

            function displayBookingDetails(booking) {
                if (debugMode) {
                    logDebug('Displaying booking details', booking);
                }

                // Security check: Verify booking ownership on client side as well
                if (!booking.user_id || !booking.verified_ownership) {
                    showError('Security error: Booking ownership could not be verified.');
                    return;
                }

                // Check if all required elements exist
                const requiredElements = [
                    'foundBookingId', 'passengerName', 'gender', 'contact', 'country', 'email',
                    'dateOfBirth', 'flightDate', 'pickupLocation', 'flightType', 'weight',
                    'age', 'medicalCondition', 'bookingStatus', 'editBtn'
                ];

                const missingElements = requiredElements.filter(id => $(`#${id}`).length === 0);

                if (missingElements.length > 0) {
                    console.error('Missing HTML elements:', missingElements);
                    showError(`Page setup error: Missing elements - ${missingElements.join(', ')}`);
                    return;
                }

                try {
                    // Populate booking details using jQuery with safe defaults
                    $('#foundBookingId').text(booking.booking_no || 'N/A');

                    // Personal Information
                    $('#passengerName').text(`${booking.firstName || ''} ${booking.lastName || ''}`.trim() ||
                        'N/A');
                    $('#gender').text(booking.gender || 'N/A');
                    $('#contact').text(booking.contact || 'N/A');
                    $('#country').text(booking.country || 'N/A');
                    $('#email').text(booking.email || 'N/A');

                    // Flight Information
                    $('#flightDate').text(booking.date ? formatDate(booking.date) : 'N/A');
                    $('#pickupLocation').text(booking.pickup || 'N/A');
                    $('#flightType').text(booking.flight_type || 'N/A');
                    $('#weight').text(booking.weight ? `${booking.weight} kg` : 'Not specified');
                    $('#age').text(booking.age || 'N/A');
                    $('#medicalCondition').text(booking.medical_condition || 'None');

                    // Set status badge
                    const status = booking.status || 'Unknown';
                    const $statusBadge = $('#bookingStatus');
                    $statusBadge.text(status)
                        .removeClass() // Remove all classes
                        .addClass(`status-badge status-${status.toLowerCase()}`);

                    // Show/hide/disable action buttons based on status
                    const $updateBtn = $('.update-btn');
                    const $cancelBtn = $('#cancelBtn');

                    if (status.toLowerCase() === 'cancelled') {
                        $updateBtn.hide();
                        if ($cancelBtn.length) $cancelBtn.hide();
                    } else if (status.toLowerCase() === 'confirmed') {
                        // Disable the edit button for confirmed bookings
                        $updateBtn.prop('disabled', true)
                            .addClass('disabled')
                            .attr('title', 'Cannot edit confirmed booking')
                            .show();

                        // Optionally disable cancel button as well
                        if ($cancelBtn.length) {
                            $cancelBtn.prop('disabled', true)
                                .addClass('disabled')
                                .attr('title', 'Cannot cancel confirmed booking')
                                .show();
                        }
                    } else {
                        // Enable buttons for other statuses (pending, etc.)
                        $updateBtn.prop('disabled', false)
                            .removeClass('disabled')
                            .removeAttr('title')
                            .show();

                        if ($cancelBtn.length) {
                            $cancelBtn.prop('disabled', false)
                                .removeClass('disabled')
                                .removeAttr('title')
                                .show();
                        }
                    }

                    // Show the result with jQuery animation
                    $bookingResult.addClass('show').fadeIn(300);

                    // Scroll to result smoothly
                    $('html, body').animate({
                        scrollTop: $bookingResult.offset().top - 50
                    }, 500);

                    if (debugMode) {
                        logDebug('Booking details displayed successfully');
                    }

                } catch (error) {
                    console.error('Error displaying booking details:', error);
                    showError('Error displaying booking details. Check console for details.');
                    if (debugMode) {
                        logDebug('Display error', error);
                    }
                }
            }

            function showLoading(show) {
                if (show) {
                    $loadingSpinner.show();
                    $btnText.text('Searching...');
                    $lookupBtn.prop('disabled', true).addClass('loading');
                } else {
                    $loadingSpinner.hide();
                    $btnText.text('🔍 Find My Booking');
                    $lookupBtn.prop('disabled', false).removeClass('loading');
                }
            }

            function showError(message) {
                $errorMessage.text(message).fadeIn(300);
                $successMessage.hide();
                $bookingResult.hide().removeClass('show');

                // Auto-hide error after 15 seconds for better debugging
                setTimeout(function() {
                    $errorMessage.fadeOut(300);
                }, 15000);
            }

            function showSuccess(message) {
                $successMessage.text(message).fadeIn(300);
                $errorMessage.hide();

                // Auto-hide success message after 5 seconds
                setTimeout(function() {
                    $successMessage.fadeOut(300);
                }, 5000);
            }

            function hideMessages() {
                $errorMessage.hide();
                $successMessage.hide();
                if (!debugMode) {
                    $debugInfo.hide();
                }
            }

            function logDebug(title, data) {
                if (!debugMode) return;

                const timestamp = new Date().toISOString();
                const debugText = `[${timestamp}] ${title}\n${JSON.stringify(data, null, 2)}\n\n`;

                $debugContent.text($debugContent.text() + debugText);
                $debugInfo.show();
                console.log(title, data);
            }

            function formatDate(dateString) {
                try {
                    const date = new Date(dateString);
                    if (isNaN(date.getTime())) {
                        return dateString; // Return original if invalid
                    }
                    return date.toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                } catch (error) {
                    console.error('Date formatting error:', error);
                    return dateString;
                }
            }

            // Global functions for action buttons
            window.editBooking = function() {
                const bookingNo = $('#foundBookingId')
                    .text(); // This should actually be booking_no, not booking_id
                console.log('Editing booking:', bookingNo);

                // Additional security check before editing
                if (bookingNo && bookingNo !== 'N/A') {
                    // Verify ownership again before editing
                    $.ajax({
                        url: 'Web/php/AJAX/verifyBookingOwnership.php',
                        type: 'POST',
                        data: {
                            booking_no: bookingNo
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.authorized) {
                                // Redirect to booking passenger form with booking no
                                window.location.href = '/bookingpassengerUpdate?booking_no=' +
                                    encodeURIComponent(bookingNo);
                            } else {
                                showError('You are not authorized to edit this booking.');
                            }
                        },
                        error: function() {
                            showError('Unable to verify booking ownership.');
                        }
                    });
                } else {
                    showError('Invalid booking number. Cannot edit booking.');
                }
            };

            window.cancelBooking = function() {
                const bookingId = $('#foundBookingId').text();
                if (confirm('Are you sure you want to cancel this booking?')) {
                    // Verify ownership before cancellation
                    $.ajax({
                        url: 'Web/php/AJAX/cancelBooking.php',
                        type: 'POST',
                        data: {
                            booking_id: bookingId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showSuccess('Booking cancelled successfully.');
                                // Refresh the booking display
                                setTimeout(() => {
                                    searchBooking();
                                }, 2000);
                            } else {
                                showError(response.message || 'Unable to cancel booking.');
                            }
                        },
                        error: function() {
                            showError('Error occurred while cancelling booking.');
                        }
                    });
                }
            };

            window.newSearch = function() {
                $form[0].reset();
                $bookingResult.hide().removeClass('show');
                hideMessages();
                $('#bookingNumber').focus();
            };

            // Additional enhancements
            $('#bookingNumber').focus();

            $('#dateOfBirth').on('keypress', function(e) {
                if (e.which === 13) {
                    $form.submit();
                }
            });

            $('#bookingNumber').on('input', function() {
                const value = $(this).val().trim();
                if (value.length > 0) {
                    $(this).removeClass('error');
                }
            });

            $('#dateOfBirth').on('change', function() {
                const value = $(this).val();
                if (value) {
                    $(this).removeClass('error');
                }
            });

            // Initialize debug info
            if (debugMode) {
                logDebug('Page initialized', {
                    jQueryVersion: $.fn.jquery,
                    debugMode: debugMode,
                    timestamp: new Date().toISOString()
                });
            }
        });
        </script>
    </body>

    </html>
    <?php require 'partials/footer.php'; ?>