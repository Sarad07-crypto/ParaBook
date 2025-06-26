<?php
    require 'avatar.php';
    require 'partials/header.php';
    require 'partials/nav_P.php';
    session_start();

    $userid = $_SESSION['user_id'];
    $serviceId = $_SESSION['service_id'] ?? 0;
    include "Web/php/connection.php";

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

    // Fetch flight types for the service
    $flightTypes = [];
    if ($serviceId > 0) {
        $servicefetch = $connect->prepare("SELECT id, flight_type_name, price FROM service_flight_types WHERE service_id = ? ORDER BY price ASC");
        if (!$servicefetch) {
            die("Prepare failed: " . $connect->error);
        }
        $servicefetch->bind_param("i", $serviceId);
        $servicefetch->execute();
        $flightTypesResult = $servicefetch->get_result();
        while ($row = $flightTypesResult->fetch_assoc()) {
            $flightTypes[] = $row;
        }
    }
?>
<link rel="stylesheet" href="Web/css/booking_P.css?v=1.0" />

<body style="background: #fff; min-height: 100vh">
    <div class="main-wrap">
        <div class="booking-form-section">
            <h1 class="booking-title">Book Your Paragliding Adventure</h1>
            <form class="styled-booking-form" id="mainBookingForm" autocomplete="off">
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
                            value="<?php echo htmlspecialchars($contact); ?>" required
                            placeholder="e.g. +977-98XXXXXXXX" />
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
                        <input type="date" id="mainDate" name="mainDate" required />
                    </div>
                    <div class="form-group">
                        <label for="mainPickup">Pickup Location</label>
                        <input type="text" id="mainPickup" name="mainPickup" required
                            placeholder="e.g. Lakeside, Pokhara" />
                    </div>
                    <div class="form-group">
                        <label for="mainFlightType">Flight Type</label>
                        <select id="mainFlightType" name="mainFlightType" required>
                            <option value="">Select Flight Type</option>
                            <?php if (!empty($flightTypes)): ?>
                            <?php foreach ($flightTypes as $flightType): ?>
                            <option value="<?php echo htmlspecialchars($flightType['id']); ?>"
                                data-price="<?php echo htmlspecialchars($flightType['price']); ?>">
                                <?php echo htmlspecialchars($flightType['flight_type_name']); ?>
                                (Rs. <?php echo htmlspecialchars($flightType['price']); ?>)
                            </option>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <option value="" disabled>No flight types available</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="mainWeight">Weight (kg)</label>
                        <input type="number" id="mainWeight" name="mainWeight" min="30" max="120"
                            placeholder="e.g. 70" />
                    </div>
                    <div class="form-group">
                        <label for="mainAge">Age</label>
                        <input type="number" id="mainAge" name="mainAge" min="10" max="80" required
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
                        placeholder="Anything we should know?"></textarea>
                </div>

                <!-- Price Display Section -->
                <div class="form-group" id="priceDisplay"
                    style="display: none; background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <h4 style="margin: 0 0 10px 0; color: #0d6efd;">Booking Summary</h4>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span id="selectedFlightName" style="font-weight: 500;"></span>
                        <span id="selectedFlightPrice"
                            style="font-size: 1.2em; font-weight: bold; color: #28a745;"></span>
                    </div>
                </div>

                <div class="form-group" style="
            margin-top: 18px;
            flex-direction: row;
            align-items: center;
            gap: 10px;
          ">
                    <input type="checkbox" id="termsCheck" required style="width: 18px; height: 18px" />
                    <label for="termsCheck" style="margin: 0; cursor: pointer">I agree to the
                        <a href="#" id="openTerms" style="color: #0d6efd; text-decoration: underline">Terms and
                            Conditions</a></label>
                </div>
                <button type="submit" class="main-submit-btn">Book Now</button>
                <div id="mainBookingSuccess" class="main-success-message" style="display: none">
                    <span>&#10003;</span> <span id="successMessage">Thank you! Your booking has been received.</span>
                </div>
                <div id="mainBookingError" class="main-error-message"
                    style="display: none; background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;">
                    <span id="errorMessage">An error occurred. Please try again.</span>
                </div>
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
                    <li>
                        All bookings are subject to weather conditions and availability.
                    </li>
                    <li>
                        Participants must be between 10 and 80 years old and weigh between
                        30kg and 120kg.
                    </li>
                    <li>
                        Medical conditions must be disclosed in advance. The company
                        reserves the right to refuse service for safety reasons.
                    </li>
                    <li>
                        Bookings can be rescheduled up to 24 hours before the flight time,
                        subject to availability.
                    </li>
                    <li>
                        Refunds are only provided if the flight is cancelled due to weather
                        or operational reasons.
                    </li>
                    <li>
                        Participants must follow all safety instructions provided by the
                        pilot and staff.
                    </li>
                    <li>
                        Personal belongings are the responsibility of the participant.
                    </li>
                    <li>
                        By booking, you agree to our privacy policy and consent to the
                        processing of your data for booking purposes.
                    </li>
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

    <script src="Web/scripts/booking_P.js?v=1.0"></script>
</body>

<?php
  require 'partials/footer.php';
?>