<?php
    
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    require 'avatar.php';
    require 'partials/header.php';
    require 'partials/nav_P.php';
    require 'Web/php/connection.php';
    
    // Check if user is logged in first
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit();
    }
    
    $userid = $_SESSION['user_id'];

    // Get booking No from URL parameter
    $booking_no = isset($_GET['booking_no']) ? $_GET['booking_no'] : '';

    if (!$booking_no) {
        echo "<script>alert('Invalid booking number.'); window.history.back();</script>";
        exit();
    }

    // Fetch booking data with ownership verification
    $bookingStmt = $connect->prepare("SELECT * FROM bookings WHERE booking_no = ? AND user_id = ?");
    if (!$bookingStmt) {
        die("Prepare failed: " . $connect->error);
    }
    $bookingStmt->bind_param("si", $booking_no, $userid);
    $bookingStmt->execute();
    $bookingResult = $bookingStmt->get_result()->fetch_assoc();

    if (!$bookingResult) {
        echo "<script>alert('Booking not found or you are not authorized to edit it'); window.history.back();</script>";
        exit();
    }

    // Get flight type price from the booking's service and flight type
    $flightTypePrice = 0;
    if (!empty($bookingResult['flight_type']) && !empty($bookingResult['service_id'])) {
        $flightPriceStmt = $connect->prepare("
            SELECT price FROM service_flight_types 
            WHERE service_id = ? AND flight_type_name = ?
        ");
        if ($flightPriceStmt) {
            $flightPriceStmt->bind_param("is", $bookingResult['service_id'], $bookingResult['flight_type']);
            $flightPriceStmt->execute();
            $priceResult = $flightPriceStmt->get_result()->fetch_assoc();
            if ($priceResult) {
                $flightTypePrice = $priceResult['price'];
            }
        }
    }

    // Fetch user info
    $fill = $connect->prepare("SELECT firstName, lastName, gender, contact, country FROM users_info WHERE user_id = ?");
    if (!$fill) {
        die("Prepare failed: " . $connect->error);
    }
    $fill->bind_param("i", $userid);
    $fill->execute();
    $result = $fill->get_result()->fetch_assoc();
    $Firstname = $result['firstName'] ?? '';
    $Lastname = $result['lastName'] ?? '';
    $gender = $result['gender'] ?? '';
    $contact = $result['contact'] ?? '';
    $country = $result['country'] ?? '';

    $fillemail2 = $connect->prepare("SELECT email FROM users WHERE id = ?");
    if (!$fillemail2) {
        die("Prepare failed: " . $connect->error);
    }
    $fillemail2->bind_param("i", $userid);
    $fillemail2->execute();
    $emailResult = $fillemail2->get_result()->fetch_assoc();
    $email = $emailResult['email'] ?? '';
?>

<link rel="stylesheet" href="Web/css/booking_P.css" />

<body style="background: #fff; min-height: 100vh">
    <div class="main-wrap">
        <div class="booking-form-section">
            <h1 class="booking-title">Update Your Booking
                #<?php echo htmlspecialchars($bookingResult['booking_no']); ?></h1>
            <form class="styled-booking-form" id="mainBookingForm" autocomplete="off">
                <!-- Hidden field for booking ID -->
                <input type="hidden" id="bookingId" name="bookingId" value="<?php echo $booking_no; ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="mainName">Full Name</label>
                        <input type="text" id="mainName" name="mainName"
                            value="<?php echo htmlspecialchars($Firstname . ' ' . $Lastname); ?>" required
                            placeholder="Your Name" />
                    </div>
                    <div class="form-group">
                        <label for="mainEmail">Email Address</label>
                        <input type="email" id="mainEmail" name="mainEmail"
                            value="<?php echo htmlspecialchars($email); ?>" required placeholder="you@email.com" />
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="mainPhone">Contact Number</label>
                        <input type="tel" id="mainPhone" name="mainPhone"
                            value="<?php echo htmlspecialchars($contact); ?>" required placeholder="e.g. 98XXXXXXXX" />
                    </div>
                    <div class="form-group">
                        <label for="mainNationality">Nationality</label>
                        <input type="text" id="mainNationality" name="mainNationality"
                            value="<?php echo htmlspecialchars($country); ?>" required placeholder="Your Country" />
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="mainDate">Preferred Date</label>
                        <input type="date" id="mainDate" name="mainDate"
                            value="<?php echo htmlspecialchars($bookingResult['date']); ?>" required />
                    </div>
                    <div class="form-group">
                        <label for="mainPickup">Pickup Location</label>
                        <input type="text" id="mainPickup" name="mainPickup"
                            value="<?php echo htmlspecialchars($bookingResult['pickup']); ?>" required
                            placeholder="e.g. Lakeside, Pokhara" />
                    </div>
                    <!-- <div class="form-group">
                        <label for="mainFlightType">Flight Type</label>
                        <select id="mainFlightType" name="mainFlightType" required>
                            <option value="">Select Flight Type</option>
                            <?php if (!empty($flightTypes)): ?>
                            <?php foreach ($flightTypes as $flightType): ?>
                            <option value="<?php echo htmlspecialchars($flightType['id']); ?>"
                                data-price="<?php echo htmlspecialchars($flightType['price']); ?>"
                                <?php echo ($flightType['id'] == $currentFlightTypeId) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($flightType['flight_type_name']); ?>
                                (Rs. <?php echo htmlspecialchars($flightType['price']); ?>)
                            </option>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <option value="" disabled>No flight types available</option>
                            <?php endif; ?>
                        </select>
                    </div> -->
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="mainWeight">Weight (kg)</label>
                        <input type="number" id="mainWeight" name="mainWeight" min="30" max="120"
                            value="<?php echo htmlspecialchars($bookingResult['weight']); ?>" placeholder="e.g. 70" />
                    </div>
                    <div class="form-group">
                        <label for="mainAge">Age</label>
                        <input type="number" id="mainAge" name="mainAge" min="10" max="80"
                            value="<?php echo htmlspecialchars($bookingResult['age']); ?>" required
                            placeholder="e.g. 25" />
                    </div>
                </div>
                <div class="form-group">
                    <label>Gender:</label>
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male" <?php if ($gender == 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if ($gender == 'Female') echo 'selected'; ?>>Female</option>
                        <option value="Other" <?php if ($gender == 'Other') echo 'selected'; ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="mainNotes">Special Requests / Medical Conditions</label>
                    <textarea id="mainNotes" name="mainNotes" rows="3"
                        placeholder="Anything we should know?"><?php echo htmlspecialchars($bookingResult['medical_condition']); ?></textarea>
                </div>

                <!-- Price Display Section -->
                <div class="form-group" id="priceDisplay"
                    style="<?php echo !empty($currentFlightTypeId) ? 'display: block;' : 'display: none;'; ?> background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <h4 style="margin: 0 0 10px 0; color: #0d6efd;">Booking Summary</h4>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span id="selectedFlightName"
                            style="font-weight: 500;"><?php echo htmlspecialchars($bookingResult['flight_type'] ?? ''); ?></span>
                        <span id="selectedFlightPrice" style="font-size: 1.2em; font-weight: bold; color: #28a745;">Rs.
                            <?php echo htmlspecialchars($currentFlightTypePrice ?? '0'); ?></span>
                    </div>
                </div>

                <div class="form-group" style="
            margin-top: 18px;
            flex-direction: row;
            align-items: center;
            gap: 10px;
          ">
                    <input type="checkbox" id="termsCheck" required style="width: 18px; height: 18px" checked />
                    <label for="termsCheck" style="margin: 0; cursor: pointer">I agree to the
                        <a href="#" id="openTerms" style="color: #0d6efd; text-decoration: underline">Terms and
                            Conditions</a></label>
                </div>
                <button type="submit" class="main-submit-btn">Update Booking</button>
                <button type="button" class="main-submit-btn" onclick="window.history.back()"
                    style="background: #6c757d; margin-left: 10px;">Cancel</button>

                <div id="mainBookingSuccess" class="main-success-message" style="display: none">
                    <span>&#10003;</span> <span id="successMessage">Your booking has been updated successfully!</span>
                </div>
                <div id="mainBookingError" class="main-error-message"
                    style="display: none; background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;">
                    <span id="errorMessage">An error occurred. Please try again.</span>
                </div>
                <input type="hidden" name="booking_no" value="<?php echo htmlspecialchars($booking_no); ?>">
            </form>
        </div>
    </div>

    <!-- Terms and Conditions Card Overlay -->
    <div id="termsCardOverlay" class="terms-card-overlay" style="display: none">
        <div class="terms-card">
            <button class="close-terms-card" onclick="closeTermsCard()">
                &times;
            </button>
            <h2 class="terms-title">Terms and Conditions</h2>
            <div class="terms-content">
                <ol>
                    <li>All bookings are subject to weather conditions and availability.</li>
                    <li>Participants must be between 10 and 80 years old and weigh between 30kg and 120kg.</li>
                    <li>Medical conditions must be disclosed in advance. The company reserves the right to refuse
                        service for safety reasons.</li>
                    <li>Bookings can be rescheduled up to 24 hours before the flight time, subject to availability.</li>
                    <li>Refunds are only provided if the flight is cancelled due to weather or operational reasons.</li>
                    <li>Participants must follow all safety instructions provided by the pilot and staff.</li>
                    <li>Personal belongings are the responsibility of the participant.</li>
                    <li>By booking, you agree to our privacy policy and consent to the processing of your data for
                        booking purposes.</li>
                </ol>
            </div>
        </div>
    </div>

    <div id="toastNotification" style="
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #0d6efd;
    color: white;
    padding: 15px 20px;
    border-radius: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    font-weight: 600;
    z-index: 9999;
    display: none;
    max-width: 300px;
    cursor: pointer;
    "></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Handle flight type change to update price display
        $('#mainFlightType').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const flightName = selectedOption.text();
            const price = selectedOption.data('price');

            if (selectedOption.val()) {
                $('#selectedFlightName').text(flightName);
                $('#selectedFlightPrice').text('Rs. ' + price);
                $('#priceDisplay').show();
            } else {
                $('#priceDisplay').hide();
            }
        });

        // Handle form submission
        $('#mainBookingForm').on('submit', function(e) {
            e.preventDefault();

            // Show loading state
            const submitBtn = $('.main-submit-btn');
            const originalText = submitBtn.text();
            submitBtn.text('Updating...').prop('disabled', true);

            // Hide previous messages
            $('#mainBookingSuccess, #mainBookingError').hide();

            $.ajax({
                url: 'Web/php/AJAX/bookingUpdate.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#successMessage').text(response.message ||
                            'Your booking has been updated successfully!');
                        $('#mainBookingSuccess').show();

                        // Show toast notification
                        showToast('Booking updated successfully!', 'success');

                        // Redirect to home page after successful update
                        setTimeout(function() {
                            window.location.href = '/home';
                        }, 1000);
                    } else {
                        $('#errorMessage').text(response.message ||
                            'An error occurred. Please try again.');
                        $('#mainBookingError').show();
                        showToast('Error updating booking', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    $('#errorMessage').text(
                        'Unable to connect to server. Please try again.');
                    $('#mainBookingError').show();
                    showToast('Connection error', 'error');
                },
                complete: function() {
                    // Reset button state
                    submitBtn.text(originalText).prop('disabled', false);
                }
            });
        });

        // Terms and conditions handling
        $('#openTerms').on('click', function(e) {
            e.preventDefault();
            $('#termsCardOverlay').show();
        });
    });

    function closeTermsCard() {
        $('#termsCardOverlay').hide();
    }

    function showToast(message, type = 'info') {
        const toast = $('#toastNotification');
        const bgColor = type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#0d6efd';

        toast.css('background-color', bgColor)
            .text(message)
            .fadeIn();

        setTimeout(function() {
            toast.fadeOut();
        }, 4000);
    }
    </script>
</body>

<?php
  require 'partials/footer.php';
?>