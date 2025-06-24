<?php 
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
<link rel="stylesheet" href="Web/css/booking_p.css" />

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

    <script>
    // Flight type selection handler
    document.getElementById('mainFlightType').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const priceDisplay = document.getElementById('priceDisplay');
        const flightNameDisplay = document.getElementById('selectedFlightName');
        const flightPriceDisplay = document.getElementById('selectedFlightPrice');

        if (selectedOption.value && selectedOption.dataset.price) {
            const flightName = selectedOption.textContent.split(' (Rs.')[0]; // Get name without price
            const price = selectedOption.dataset.price;

            flightNameDisplay.textContent = flightName;
            flightPriceDisplay.textContent = 'Rs. ' + price;
            priceDisplay.style.display = 'block';
        } else {
            priceDisplay.style.display = 'none';
        }
    });

    // Function to submit booking data
    const submitBookingData = async (formElement) => {
        const formData = new FormData(formElement);

        try {
            const response = await fetch('Web/php/AJAX/submitBooking.php', {
                method: 'POST',
                body: formData
            });

            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Get response text first to debug
            const responseText = await response.text();
            console.log('Raw response:', responseText);

            // Try to parse as JSON
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Response was:', responseText);
                throw new Error('Invalid JSON response from server');
            }

            if (result.success) {
                
                document.getElementById('successMessage').textContent =
                    `Booking confirmed! Your booking number is: ${result.booking_no}`;
                    
                document.getElementById('mainBookingSuccess').style.display = 'flex';
                document.getElementById('mainBookingError').style.display = 'none';
                window.location.href = "Web/php/esewa.php";

                // setTimeout(() => {
                //     document.getElementById('mainBookingSuccess').style.display = 'none';
                //     formElement.reset();
                //     formElement.querySelector('.main-submit-btn').style.display = '';
                //     formElement.querySelector('.main-submit-btn').disabled = false;
                //     formElement.querySelector('.main-submit-btn').textContent = 'Book Now';
                //     document.getElementById('priceDisplay').style.display = 'none';
                // }, 3000);
            } else {
                document.getElementById('errorMessage').textContent = result.message;
                document.getElementById('mainBookingError').style.display = 'block';
                document.getElementById('mainBookingSuccess').style.display = 'none';
                formElement.querySelector('.main-submit-btn').style.display = '';
                formElement.querySelector('.main-submit-btn').disabled = false;
                formElement.querySelector('.main-submit-btn').textContent = 'Book Now';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('errorMessage').textContent = 'Network error: ' + error.message;
            document.getElementById('mainBookingError').style.display = 'block';
            document.getElementById('mainBookingSuccess').style.display = 'none';
            formElement.querySelector('.main-submit-btn').style.display = '';
            formElement.querySelector('.main-submit-btn').disabled = false;
            formElement.querySelector('.main-submit-btn').textContent = 'Book Now';
        }
    };

    // Booking form validation
    const validateBookingForm = (formSelector) => {
        const formElement = document.querySelector(formSelector);
        const validateOptions = [{
                attribute: "required",
                isValid: (input) => input.value.trim() === "",
            },
            {
                attribute: "type",
                isValid: (input) => {
                    if (input.type === "email") {
                        return !/^([^\s@]+)@([^\s@]+)\.[^\s@]{2,}$/.test(input.value);
                    }
                    if (input.type === "tel") {
                        return !/^(\+977-)?98\d{8}$/.test(input.value); // Updated Nepal phone format
                    }
                    if (input.type === "number" && input.name === "mainWeight") {
                        return input.value !== '' && (input.value < 30 || input.value > 120);
                    }
                    if (input.type === "number" && input.name === "mainAge") {
                        return input.value < 10 || input.value > 80;
                    }
                    return false;
                },
            },
            {
                attribute: "date",
                isValid: (input) => {
                    if (input.type === "date") {
                        const enteredDate = new Date(input.value);
                        const today = new Date();
                        today.setHours(0, 0, 0, 0); // Reset time to compare dates only
                        return isNaN(enteredDate.getTime()) || enteredDate < today;
                    }
                    return false;
                },
            },
        ];

        const validateSingleInput = (input) => {
            let error = false;
            for (const option of validateOptions) {
                if (
                    input.hasAttribute(option.attribute) ||
                    option.attribute === "type" ||
                    option.attribute === "date"
                ) {
                    if (option.isValid(input)) {
                        error = true;
                        break;
                    }
                }
            }
            if (error) {
                input.style.borderColor = "red";
            } else {
                input.style.borderColor = "#0d6efd";
            }
            return !error;
        };

        const setupInputEvents = () => {
            const inputs = Array.from(
                formElement.querySelectorAll("input, textarea, select")
            );
            inputs.forEach((input) => {
                input.addEventListener("input", () => validateSingleInput(input));
                input.addEventListener("blur", () => validateSingleInput(input));
            });
        };

        formElement.setAttribute("novalidate", "");
        formElement.addEventListener("submit", (event) => {
            event.preventDefault();
            const inputs = Array.from(
                formElement.querySelectorAll("input, textarea, select")
            );
            const isValid = inputs.every((input) => validateSingleInput(input));

            if (isValid) {
                // Disable submit button to prevent double submission
                const submitBtn = formElement.querySelector(".main-submit-btn");
                submitBtn.disabled = true;
                submitBtn.textContent = "Processing...";

                // Hide any previous error messages
                document.getElementById('mainBookingError').style.display = 'none';

                // Submit the booking data
                submitBookingData(formElement);
            }
        });
        setupInputEvents();
    };

    validateBookingForm("#mainBookingForm");

    document.getElementById("openTerms").onclick = function(e) {
        e.preventDefault();
        document.getElementById("termsCardOverlay").style.display = "flex";
    };

    function closeTermsCard() {
        document.getElementById("termsCardOverlay").style.display = "none";
    }
    document.getElementById("termsCardOverlay").onclick = function(e) {
        if (e.target === this) closeTermsCard();
    };
    document.addEventListener("keydown", function(e) {
        if (e.key === "Escape") closeTermsCard();
    });

    // Set minimum date to today
    document.getElementById('mainDate').min = new Date().toISOString().split('T')[0];
    </script>

</body>