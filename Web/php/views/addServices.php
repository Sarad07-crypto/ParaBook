<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="Web/css/addServices.css?v=1.0" />
    <link href="https://cdn.boxicons.com/fonts/basic/boxicons.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="btn-wrapper">
        <button class="fixed-add-btn" id="loadServiceForm">
            <i class="fas fa-plus"></i>
            <span>Add Service</span>
        </button>
    </div>

    <!-- Tab Navigation -->
    <div class="service-tabs">
        <button class="tab-btn active" data-tab="approved">
            <i class="fas fa-check-circle"></i>
            Approved Services
            <span class="count-badge" id="approvedCount">0</span>
        </button>
        <button class="tab-btn" data-tab="pending">
            <i class="fas fa-clock"></i>
            Pending Services
            <span class="count-badge" id="pendingCount">0</span>
        </button>
    </div>

    <!-- Approved Services Section -->
    <div id="approvedSection" class="service-section active">
        <div class="section-header">
            <h2>Approved Services</h2>
            <p>These services are live and visible to customers</p>
        </div>
        <div class="company-grid" id="approvedGrid">
            <div class="add-company-wrapper">
                <!-- Add service button placeholder -->
            </div>
        </div>
    </div>

    <!-- Pending Services Section -->
    <div id="pendingSection" class="service-section">
        <div class="section-header">
            <h2>Pending Services</h2>
            <p>These services are waiting for admin approval</p>
        </div>
        <div class="company-grid" id="pendingGrid">
            <div class="add-company-wrapper">
                <!-- Add service button placeholder -->
            </div>
        </div>
    </div>

    <!-- Empty States -->
    <div class="empty-state" id="emptyApproved" style="display: none;">
        <div class="empty-icon">
            <i class="fas fa-clipboard-check"></i>
        </div>
        <h3>No Approved Services Yet</h3>
        <p>Once your services are approved by admin, they'll appear here.</p>
    </div>

    <div class="empty-state" id="emptyPending" style="display: none;">
        <div class="empty-icon">
            <i class="fas fa-hourglass-half"></i>
        </div>
        <h3>No Pending Services</h3>
        <p>Submit a new service to see it here while waiting for approval.</p>
    </div>

    <div id="serviceModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeModal">&times;</span>
            <div id="serviceFormContainer">Loading...</div>
        </div>
    </div>

    <script src="Web/scripts/addServices.js?v=1.1"></script>
</body>

</html>