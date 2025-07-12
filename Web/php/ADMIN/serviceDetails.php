<?php
    session_start();

    // Check if user is logged in as admin
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role'])) {
        header('Location: /adminlogin');
        exit();
    }
    $adminID = $_SESSION['admin_id'];
    $userRole = $_SESSION['admin_role'];
    $isMainAdmin = ($userRole === 'main_admin');
    require_once '../connection.php';
        
    $service_id = $_GET['id'] ?? null;
    if (!$service_id) {
        header('Location: /adminlogin');
        exit();
    }

    // Fetch service details including location data
    $query = "
        SELECT cs.*, 
            GROUP_CONCAT(DISTINCT CONCAT(sft.flight_type_name, '|', sft.price) SEPARATOR ';;') as flight_types,
            GROUP_CONCAT(DISTINCT sop.photo_path SEPARATOR ';;') as office_photos,
            sl.latitude, sl.longitude, sl.address, sl.formatted_address, sl.place_id
        FROM company_services cs
        LEFT JOIN service_flight_types sft ON cs.id = sft.service_id
        LEFT JOIN service_office_photos sop ON cs.id = sop.service_id
        LEFT JOIN service_locations sl ON cs.id = sl.service_id
        WHERE cs.id = ?
        GROUP BY cs.id
    ";

    $stmt = $connect->prepare($query);
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $service = $result->fetch_assoc();

    if (!$service) {
        header('Location: /adminhome');
        exit();
    }

    // Parse flight types
    $flightTypes = [];
    if (!empty($service['flight_types'])) {
        foreach (explode(';;', $service['flight_types']) as $type) {
            if (strpos($type, '|') !== false) {
                list($name, $price) = explode('|', $type, 2);
                $flightTypes[] = [
                    'name' => trim($name),
                    'price' => trim($price)
                ];
            }
        }
    }

    // Parse photos
    $officePhotos = [];
    if (!empty($service['office_photos'])) {
        foreach (explode(';;', $service['office_photos']) as $photo) {
            if (trim($photo) !== '') {
                $officePhotos[] = trim($photo);
            }
        }
    }

    // Get minimum price
    $minPrice = 0;
    if (!empty($flightTypes)) {
        $prices = array_column($flightTypes, 'price');
        $minPrice = min($prices);
    }

    // Location data
    $hasLocation = !empty($service['latitude']) && !empty($service['longitude']);
    $locationData = [
        'latitude' => $service['latitude'] ? floatval($service['latitude']) : null,
        'longitude' => $service['longitude'] ? floatval($service['longitude']) : null,
        'address' => $service['address'] ?? null,
        'formatted_address' => $service['formatted_address'] ?? null,
        'place_id' => $service['place_id'] ?? null
    ];
    ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Details - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
    :root {
        --color-BG: #056cc5;
        --color-blue: #0659e7;
    }

    .admin-service-details {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .back-button {
        display: inline-block;
        margin-bottom: 20px;
        padding: 10px 20px;
        background: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
    }

    .service-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .service-actions {
        display: flex;
        gap: 10px;
    }

    .main-wrap {
        display: flex;
        gap: 30px;
    }

    .left-section {
        flex: 2;
    }

    .right-section {
        flex: 1;
    }

    .company-title h1 {
        font-size: 2.5rem;
        margin-bottom: 20px;
    }

    .profile-row {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .profile-pic {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #f0f0f0;
    }

    .profile-info {
        display: flex;
        flex-direction: column;
    }

    .profile-name {
        font-weight: bold;
        font-size: 1.2rem;
    }

    .profile-address {
        color: #666;
        font-size: 0.9rem;
    }

    .slider-area {
        margin: 20px 0;
    }

    .main-slider {
        position: relative;
        width: 100%;
        height: 400px;
        overflow: hidden;
        border-radius: 10px;
    }

    .main-slider img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .slider-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0, 0, 0, 0.5);
        color: white;
        border: none;
        padding: 10px;
        border-radius: 50%;
        cursor: pointer;
        z-index: 1;
    }

    .slider-arrow.left {
        left: 10px;
    }

    .slider-arrow.right {
        right: 10px;
    }

    .thumbs-bar {
        display: flex;
        gap: 10px;
        margin-top: 10px;
        overflow-x: auto;
    }

    .thumbs-bar img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 5px;
        cursor: pointer;
        border: 2px solid transparent;
    }

    .thumbs-bar img.selected {
        border-color: #007bff;
    }

    .desc-section {
        margin-top: 30px;
    }

    .desc-section h2,
    .desc-section h3 {
        margin-bottom: 15px;
    }

    .desc-section ul {
        list-style: none;
        padding: 0;
    }

    .desc-section li {
        background: #f8f9fa;
        padding: 10px;
        margin-bottom: 5px;
        border-radius: 5px;
    }

    .admin-actions {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        line-height: 1.8;
    }

    .admin-actions h3 {
        margin-bottom: 5px;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        margin-right: 10px;
        margin-bottom: 10px;
    }

    .btn-approve {
        background: #28a745;
        color: white;
    }

    .btn-reject {
        background: #dc3545;
        color: white;
    }

    .status-badge {
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: bold;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-approved {
        background: #d4edda;
        color: #155724;
    }

    .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }

    /* Map Section Styles */
    .location-section {
        margin-top: 30px;
        border: 1px solid #ddd;
        border-radius: 10px;
        overflow: hidden;
    }

    .location-header {
        background: #f8f9fa;
        padding: 15px;
        border-bottom: 1px solid #ddd;
    }

    .location-header h3 {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .location-header .fas {
        color: #007bff;
    }

    #serviceLocationMap {
        width: 100%;
        height: 400px;
        border: none;
    }

    .location-info {
        padding: 15px;
        background: #f8f9fa;
        border-top: 1px solid #ddd;
    }

    .location-info p {
        margin: 5px 0;
        font-size: 14px;
    }

    .location-info strong {
        color: #333;
    }

    .no-location {
        padding: 20px;
        text-align: center;
        color: #666;
        font-style: italic;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        background-color: #f5f5f5;
    }

    .header {
        background: linear-gradient(135deg, #4285f4 0%, #1976d2 100%);
        color: white;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .logo {
        font-size: 28px;
        font-weight: bold;
        color: white;
    }

    .admin-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        backdrop-filter: blur(10px);
    }

    .desc-section {
        list-style: none;
        padding: 0;
        margin: 0;
        border-radius: 20px;
    }

    .desc-section li {
        border-left: 3px solid var(--color-blue);
        padding: 10px 15px;
        margin-bottom: 8px;
        background-color: rgba(6, 89, 231, 0.05);
        border-radius: 0 5px 5px 0;
        border-radius: 5px;
    }

    .desc-section li:hover {
        background-color: rgba(6, 89, 231, 0.1);
    }
    </style>

</head>

<body>
    <div class="header">
        <div class="admin-badge"><?php echo ucfirst(str_replace('_', ' ', $userRole ?? 'Admin')); ?></div>
        <a href="/adminlogout" class="logout-btn" title="Logout">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
    <div class="admin-service-details">
        <a href="/adminhome" class="back-button">← Back to Admin Dashboard</a>

        <div class="service-header">
            <div>
                <h1><?php echo htmlspecialchars($service['service_title']); ?></h1>
                <span class="status-badge status-<?php echo $service['status']; ?>">
                    <?php echo ucfirst($service['status']); ?>
                </span>
            </div>
        </div>

        <div class="main-wrap">
            <!-- Left Section -->
            <div class="left-section">
                <div class="profile-row">
                    <img src="<?php echo !empty($service['company_logo']) ? '/' . ltrim($service['company_logo'], '/') : '/Assets/images/default-company.png'; ?>"
                        alt="Company Logo" class="profile-pic">
                    <div class="profile-info">
                        <span class="profile-name"><?php echo htmlspecialchars($service['company_name']); ?></span>
                        <span
                            class="profile-address"><?php echo htmlspecialchars($service['address'] ?? 'No address provided'); ?></span>
                        <span
                            class="profile-address"><?php echo htmlspecialchars($service['contact'] ?? 'No contact provided'); ?></span>
                    </div>
                </div>

                <?php if (!empty($officePhotos)): ?>
                <!-- Enhanced Dynamic Image Slider -->
                <div class="slider-area">
                    <div class="main-slider">
                        <?php if (count($officePhotos) > 1): ?>
                        <button class="slider-arrow left" onclick="prevScreenshot()" aria-label="Previous">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <?php endif; ?>
                        <img id="mainImage" src="<?php echo '/' . ltrim($officePhotos[0], '/'); ?>" alt="Service Image">
                        <?php if (count($officePhotos) > 1): ?>
                        <button class="slider-arrow right" onclick="nextScreenshot()" aria-label="Next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <?php endif; ?>
                    </div>

                    <?php if (count($officePhotos) > 1): ?>
                    <div class="thumbs-bar screenshots-list" id="screenshotsList">
                        <?php foreach ($officePhotos as $index => $photo): ?>
                        <img src="<?php echo '/' . ltrim($photo, '/'); ?>" alt="Screenshot <?php echo $index + 1; ?>"
                            onclick="selectScreenshot(<?php echo $index; ?>)"
                            class="<?php echo $index === 0 ? 'selected' : ''; ?>">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="desc-section">
                    <h2>About This Service</h2>
                    <div style="white-space: pre-line;">
                        <?php echo htmlspecialchars($service['service_description'] ?? 'No description available.'); ?>
                    </div>

                    <?php if (!empty($flightTypes)): ?>
                    <h3>Available Options</h3>
                    <ul>
                        <?php foreach ($flightTypes as $flightType): ?>
                        <li><?php echo htmlspecialchars($flightType['name']); ?> -
                            Rs.<?php echo htmlspecialchars($flightType['price']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>

                <!-- Location Section -->
                <div class="location-section">
                    <div class="location-header">
                        <h3>
                            <i class="fas fa-map-marker-alt"></i>
                            Service Location
                        </h3>
                    </div>
                    <?php if ($hasLocation): ?>
                    <div id="serviceLocationMap"></div>
                    <div class="location-info">
                        <p><strong>Coordinates:</strong> <?php echo $locationData['latitude']; ?>,
                            <?php echo $locationData['longitude']; ?></p>
                        <?php if ($locationData['formatted_address']): ?>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($locationData['formatted_address']); ?>
                        </p>
                        <?php elseif ($locationData['address']): ?>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($locationData['address']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="no-location">
                        <p>No location information provided for this service.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Section -->
            <div class="right-section">
                <div class="admin-actions">
                    <h3>Admin Actions</h3>
                    <p><strong>Service ID:</strong> <?php echo $service['id']; ?></p>
                    <p><strong>Created:</strong> <?php echo date('Y-m-d H:i:s', strtotime($service['created_at'])); ?>
                    </p>
                    <p><strong>Status:</strong>
                        <span class="status-badge status-<?php echo $service['status']; ?>">
                            <?php echo ucfirst($service['status']); ?>
                        </span>
                    </p>

                    <?php if ($service['status'] === 'pending'): ?>
                    <div style="margin-top: 20px;">
                        <button class="btn btn-approve" onclick="approveService(<?php echo $service['id']; ?>)">
                            Approve Service
                        </button>
                        <button class="btn btn-reject" onclick="rejectService(<?php echo $service['id']; ?>)">
                            Reject Service
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    // Image slider functionality
    let currentImageIndex = 0;
    const images =
        <?php echo json_encode(array_map(function($photo) { return '/' . ltrim($photo, '/'); }, $officePhotos)); ?>;

    function nextScreenshot() {
        if (images.length > 1) {
            currentImageIndex = (currentImageIndex + 1) % images.length;
            updateMainImage();
        }
    }

    function prevScreenshot() {
        if (images.length > 1) {
            currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
            updateMainImage();
        }
    }

    function selectScreenshot(index) {
        currentImageIndex = index;
        updateMainImage();
    }

    function updateMainImage() {
        document.getElementById('mainImage').src = images[currentImageIndex];

        // Update thumbnail selection
        const thumbs = document.querySelectorAll('.thumbs-bar img');
        thumbs.forEach((thumb, index) => {
            thumb.classList.toggle('selected', index === currentImageIndex);
        });
    }

    // Map initialization
    <?php if ($hasLocation): ?>
    let serviceMap;

    function initializeServiceMap() {
        const latitude = <?php echo $locationData['latitude']; ?>;
        const longitude = <?php echo $locationData['longitude']; ?>;

        // Initialize map
        serviceMap = L.map('serviceLocationMap').setView([latitude, longitude], 15);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(serviceMap);

        // Add marker for the service location
        const marker = L.marker([latitude, longitude]).addTo(serviceMap);

        // Add popup with service information
        const popupContent = `
            <div style="text-align: center;">
                <h4><?php echo htmlspecialchars($service['service_title']); ?></h4>
                <p><strong><?php echo htmlspecialchars($service['company_name']); ?></strong></p>
                <?php if ($locationData['formatted_address']): ?>
                    <p><?php echo htmlspecialchars($locationData['formatted_address']); ?></p>
                <?php endif; ?>
            </div>
        `;

        marker.bindPopup(popupContent).openPopup();
    }

    // Initialize map when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initializeServiceMap();
    });
    <?php endif; ?>

    // Admin actions
    function approveService(serviceId) {
        if (confirm('Are you sure you want to approve this service?')) {
            updateServiceStatus(serviceId, 'approved');
        }
    }

    function rejectService(serviceId) {
        const reason = prompt('Please provide a reason for rejection:');
        if (reason && reason.trim()) {
            updateServiceStatus(serviceId, 'rejected', reason);
        }
    }

    function updateServiceStatus(serviceId, status, reason = '') {
        const formData = new FormData();
        formData.append('action', 'updateServiceStatus');
        formData.append('serviceId', serviceId);
        formData.append('status', status);
        if (reason) formData.append('reason', reason);

        fetch('/Web/php/AJAX/updateServiceStatus.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload(); // Refresh to show updated status
                } else {
                    alert('Failed to update service status: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error updating service status: ' + error.message);
            });
    }
    </script>
</body>

</html>