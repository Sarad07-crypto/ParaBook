<?php
    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role'])) {
        header('Location: /adminlogin');
        exit();
    }
    $adminID = $_SESSION['admin_id'];
    $userRole = $_SESSION['admin_role'];
    $isMainAdmin = ($userRole === 'main_admin');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="Web/css/admin.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
    /* Add your CSS styles here */
    .hidden {
        display: none !important;
    }

    .nav-tab.disabled {
        opacity: 0.5;
        pointer-events: none;
        cursor: not-allowed;
    }

    .role-select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        background-color: white;
        cursor: pointer;
        transition: border-color 0.3s ease;
    }

    .role-select:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
    }

    .role-select:hover {
        border-color: #007bff;
    }

    .admin-detail-value select {
        margin-top: 5px;
    }

    /* Enhanced admin detail styling */
    .admin-detail-item {
        margin-bottom: 5px;
        padding: 10px !important;
        border-bottom: 1px solid #f0f0f0;
    }

    .admin-detail-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
        font-size: 14px;
    }

    .admin-detail-value {
        color: #666;
        font-size: 14px;
    }

    .admin-detail-item:last-child {
        border-bottom: none;
        background-color: #f8f9fa;
        border-radius: 4px;
    }

    .admin-detail-item:last-child .admin-detail-label {
        color: #007bff;
        font-weight: 700;
    }
    </style>
</head>

<body>
    <div class="header">
        <div class="admin-badge"><?php echo ucfirst(str_replace('_', ' ', $userRole ?? 'Admin')); ?></div>
        <a href="/adminlogout" class="logout-btn" title="Logout">
            <i style="color:#f2f2f2;font-size: 25px;" class='bx bx-log-out'></i>
        </a>
    </div>

    <div class="search-container">
        <div class="search-bar">
            <input type="text" class="search-input" placeholder="Search services...">
            <span class="search-icon">🔍</span>
        </div>
    </div>

    <div class="nav-tabs">
        <?php if ($isMainAdmin): ?>
        <button class="nav-tab" onclick="switchTab('pending-admins')">Pending Admins</button>
        <?php endif; ?>
        <button class="nav-tab active" onclick="switchTab('pending-services')">Pending Services</button>
        <button class="nav-tab" onclick="switchTab('approved-services')">Approved Services</button>
        <button class="nav-tab" onclick="switchTab('rejected-services')">Rejected Services</button>
        <button class="nav-tab" onclick="switchTab('all-services')">All Services</button>
    </div>

    <div class="main-content">
        <div class="stats-grid">
            <?php if ($isMainAdmin): ?>
            <div class="stat-card">
                <div class="stat-number" id="pendingAdminsCount">0</div>
                <div class="stat-label">Pending Admins</div>
            </div>
            <?php endif; ?>
            <div class="stat-card">
                <div class="stat-number" id="pendingServicesCount">0</div>
                <div class="stat-label">Pending Services</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="approvedServicesCount">0</div>
                <div class="stat-label">Approved Services</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="totalServicesCount">0</div>
                <div class="stat-label">Total Services</div>
            </div>
        </div>

        <div class="companies-section">
            <div class="section-header">
                <div class="section-title">Pending Services</div>
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filterByDate('all')">All</button>
                    <button class="filter-btn" onclick="filterByDate('today')">Today</button>
                    <button class="filter-btn" onclick="filterByDate('week')">This Week</button>
                </div>
            </div>

            <!-- Pending Admins Section (Only for Main Admin) -->
            <?php if ($isMainAdmin): ?>
            <div class="company-list" id="pendingAdminsList" style="display: none;">
                <div class="loading" id="loadingSpinner">
                    <div>Loading pending admins...</div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Services List -->
            <div class="company-list" id="servicesList">
                <div class="loading" id="servicesLoadingSpinner">
                    <div>Loading services...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Details Modal (Only for Main Admin) -->
    <?php if ($isMainAdmin): ?>
    <div id="adminModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Admin Details</h2>
                <span class="close" onclick="closeAdminModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="admin-detail-grid" id="adminDetails">
                    <!-- Admin details will be populated here -->
                </div>
                <div class="modal-actions">
                    <button class="btn-modal btn-cancel-modal" onclick="closeAdminModal()">Cancel</button>
                    <button class="btn-modal btn-reject-modal" onclick="rejectAdmin()" id="rejectBtn">Reject</button>
                    <button class="btn-modal btn-approve-modal" onclick="approveAdmin()"
                        id="approveBtn">Approve</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Service Details Modal -->
    <div id="serviceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Service Details</h2>
                <span class="close" onclick="closeServiceModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="service-detail-grid" id="serviceDetails">
                    <!-- Service details will be populated here -->
                </div>
                <div class="modal-actions">
                    <button class="btn-modal btn-cancel-modal" onclick="closeServiceModal()">Cancel</button>
                    <button class="btn-modal btn-reject-modal" onclick="rejectService()"
                        id="rejectServiceBtn">Reject</button>
                    <button class="btn-modal btn-approve-modal" onclick="approveService()"
                        id="approveServiceBtn">Approve</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    let currentTab = 'pending-services';
    let currentAdminId = null;
    let currentServiceId = null;
    let currentDateFilter = 'all';
    let allServices = [];
    let allAdmins = []; // Add this to store all admins
    const isMainAdmin = <?php echo $isMainAdmin ? 'true' : 'false'; ?>;
    const userRole = '<?php echo $userRole; ?>';

    // Load data on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadServices();

        <?php if ($isMainAdmin): ?>
        // Load pending admins for main admin
        fetch('Web/php/ADMIN/createAdmins.php?check_role=1')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.is_main_admin) {
                    loadPendingAdmins();
                }
            })
            .catch(error => console.error('Error checking role:', error));
        <?php endif; ?>
    });

    function loadServices() {
        const loadingSpinner = document.getElementById('servicesLoadingSpinner');
        if (loadingSpinner) {
            loadingSpinner.style.display = 'block';
        }

        fetch('Web/php/AJAX/fetchPendingCompanyServices.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (loadingSpinner) {
                    loadingSpinner.style.display = 'none';
                }

                if (data.success) {
                    allServices = data.services;
                    displayServices();
                    updateServicesCounts();
                } else {
                    showError('Failed to load services: ' + data.message);
                }
            })
            .catch(error => {
                if (loadingSpinner) {
                    loadingSpinner.style.display = 'none';
                }
                showError('Error loading services: ' + error.message);
            });
    }

    function displayServices() {
        const servicesList = document.getElementById('servicesList');
        let servicesToShow = [];

        // Filter by tab
        switch (currentTab) {
            case 'pending-services':
                servicesToShow = allServices.filter(service => service.status === 'pending');
                break;
            case 'approved-services':
                servicesToShow = allServices.filter(service => service.status === 'approved');
                break;
            case 'rejected-services':
                servicesToShow = allServices.filter(service => service.status === 'rejected');
                break;
            case 'all-services':
                servicesToShow = allServices;
                break;
            default:
                servicesToShow = allServices.filter(service => service.status === 'pending');
        }

        // Filter by date
        if (currentDateFilter !== 'all') {
            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() - 7);

            servicesToShow = servicesToShow.filter(service => {
                const serviceDate = new Date(service.created_at);
                const serviceDateOnly = new Date(serviceDate.getFullYear(), serviceDate.getMonth(), serviceDate
                    .getDate());

                if (currentDateFilter === 'today') {
                    return serviceDateOnly.getTime() === today.getTime();
                } else if (currentDateFilter === 'week') {
                    return serviceDateOnly >= weekStart;
                }
                return true;
            });
        }

        if (servicesToShow.length === 0) {
            servicesList.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">🏢</div>
                <h3>No Services Found</h3>
                <p>No services match the current filter criteria.</p>
            </div>
        `;
            return;
        }

        let html = '';
        servicesToShow.forEach(service => {
            const initials = service.company_name.substring(0, 2).toUpperCase();
            const createdDate = new Date(service.created_at).toLocaleDateString();
            const statusClass = `status-${service.status}`;
            const statusText = service.status.charAt(0).toUpperCase() + service.status.slice(1);

            html += `
            <div class="service-item" data-service-id="${service.id}" data-status="${service.status}">
                <div class="service-info">
                    <div class="service-title">Service Title: ${service.service_title}</div>
                    <div class="service-company">Company Name: ${service.company_name}</div>
                    <div class="service-meta">
                        <span>📅 Created: ${createdDate}</span>
                    </div>
                </div>
                <div class="status-badge ${statusClass}">${statusText}</div>
                <div class="action-buttons">
                    <button class="btn btn-view" onclick="viewServiceDetails(${service.id})">View Details</button>
                    ${service.status === 'pending' ? 
                        `<button class="btn btn-approve" onclick="approveServiceDirect(${service.id})">Approve</button>
                         <button class="btn btn-reject" onclick="rejectServiceDirect(${service.id})">Reject</button>` : 
                        ''
                    }
                </div>
            </div>
        `;
        });

        servicesList.innerHTML = html;
    }

    function updateServicesCounts() {
        const pendingCount = allServices.filter(s => s.status === 'pending').length;
        const approvedCount = allServices.filter(s => s.status === 'approved').length;
        const totalCount = allServices.length;

        document.getElementById('pendingServicesCount').textContent = pendingCount;
        document.getElementById('approvedServicesCount').textContent = approvedCount;
        document.getElementById('totalServicesCount').textContent = totalCount;
    }

    function switchTab(tab) {
        // Prevent sub-admins from accessing pending admins tab
        if (tab === 'pending-admins' && !isMainAdmin) {
            console.warn('Access denied: Sub-admins cannot access pending admins');
            return;
        }

        currentTab = tab;

        // Update tab buttons
        document.querySelectorAll('.nav-tab').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');

        // Show/hide appropriate sections
        if (tab === 'pending-admins' && isMainAdmin) {
            document.getElementById('pendingAdminsList').style.display = 'block';
            document.getElementById('servicesList').style.display = 'none';
            document.querySelector('.section-title').textContent = 'Pending Admins';
            loadPendingAdmins();
        } else {
            if (isMainAdmin) {
                document.getElementById('pendingAdminsList').style.display = 'none';
            }
            document.getElementById('servicesList').style.display = 'block';

            // Update section title
            const tabTitles = {
                'pending-services': 'Pending Services',
                'approved-services': 'Approved Services',
                'rejected-services': 'Rejected Services',
                'all-services': 'All Services'
            };
            document.querySelector('.section-title').textContent = tabTitles[tab];

            displayServices();
        }
    }

    function filterByDate(filter) {
        currentDateFilter = filter;

        // Update filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');

        // Re-display appropriate content based on current tab
        if (currentTab === 'pending-admins' && isMainAdmin) {
            displayPendingAdmins(allAdmins); // Use the filtered display function
        } else {
            displayServices();
        }
    }

    // Open detailed view in a new tab/window
    function viewServiceDetails(serviceId) {
        // Fix the URL format - add proper query parameter
        window.open(`Web/php/ADMIN/servicedetails.php?id=${serviceId}`, '_blank');
    }

    function approveServiceDirect(serviceId) {
        if (!serviceId || serviceId <= 0) {
            showError('Invalid service ID');
            return;
        }

        if (confirm('Are you sure you want to approve this service?')) {
            updateServiceStatus(serviceId, 'approved');
        }
    }

    function rejectServiceDirect(serviceId) {
        if (!serviceId || serviceId <= 0) {
            showError('Invalid service ID');
            return;
        }

        const reason = prompt('Please provide a reason for rejection:');
        if (reason && reason.trim()) {
            updateServiceStatus(serviceId, 'rejected', reason.trim());
        } else {
            showError('Rejection reason is required');
        }
    }

    function updateServiceStatus(serviceId, status, reason = '') {
        if (!serviceId || serviceId <= 0) {
            showError('Invalid service ID: ' + serviceId);
            return;
        }

        if (!status || !['pending', 'approved', 'rejected'].includes(status)) {
            showError('Invalid status: ' + status);
            return;
        }

        const formData = new FormData();
        formData.append('action', 'updateServiceStatus');
        formData.append('serviceId', serviceId);
        formData.append('status', status);
        if (reason) formData.append('reason', reason);

        fetch('Web/php/AJAX/updateServiceStatus.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        showNotification(data.message, 'success');
                        loadServices(); // Refresh the services list
                    } else {
                        showError('Failed to update service status: ' + data.message);
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    console.error('Response text:', text);
                    showError('Invalid response from server. Check console for details.');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showError('Error updating service status: ' + error.message);
            });
    }

    <?php if ($isMainAdmin): ?>
    // Admin management functions for main admin only
    function loadPendingAdmins() {
        const loadingSpinner = document.getElementById('loadingSpinner');
        if (loadingSpinner) {
            loadingSpinner.style.display = 'block';
        }

        fetch('Web/php/ADMIN/createAdmins.php?action=getPendingAdmins')
            .then(response => response.json())
            .then(data => {
                if (loadingSpinner) {
                    loadingSpinner.style.display = 'none';
                }
                if (data.success) {
                    allAdmins = data.data; // Store all admins
                    displayPendingAdmins(allAdmins);
                    updatePendingAdminsCount(allAdmins.length);
                } else {
                    showError('Failed to load pending admins: ' + data.message);
                }
            })
            .catch(error => {
                if (loadingSpinner) {
                    loadingSpinner.style.display = 'none';
                }
                showError('Error loading pending admins: ' + error.message);
            });
    }

    function displayPendingAdmins(admins) {
        let adminsToShow = admins;

        // Apply date filter for admins
        if (currentDateFilter !== 'all') {
            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() - 7);

            adminsToShow = admins.filter(admin => {
                const adminDate = new Date(admin.created_at);
                const adminDateOnly = new Date(adminDate.getFullYear(), adminDate.getMonth(), adminDate
                    .getDate());

                if (currentDateFilter === 'today') {
                    return adminDateOnly.getTime() === today.getTime();
                } else if (currentDateFilter === 'week') {
                    return adminDateOnly >= weekStart;
                }
                return true;
            });
        }

        const pendingAdminsList = document.getElementById('pendingAdminsList');

        if (adminsToShow.length === 0) {
            pendingAdminsList.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">👤</div>
                <h3>No Pending Admins</h3>
                <p>No admins match the current filter criteria.</p>
            </div>
        `;
            return;
        }

        let html = '';
        adminsToShow.forEach(admin => {
            const initials = admin.first_name.charAt(0) + admin.last_name.charAt(0);
            const fullName = admin.first_name + ' ' + admin.last_name;
            const applicationDate = new Date(admin.created_at).toLocaleDateString();

            html += `
            <div class="pending-admin-item" data-admin-id="${admin.id}">
                <div class="admin-avatar">${initials}</div>
                <div class="admin-info">
                    <div class="admin-name">${fullName}</div>
                    <div class="admin-details">Sub Admin • ${admin.gender}</div>
                    <div class="admin-meta">
                        <span>📅 Applied: ${applicationDate}</span>
                        <span>📧 ${admin.email}</span>
                        <span>📞 ${admin.contact}</span>
                    </div>
                </div>
                <div class="status-badge status-pending">Pending</div>
                <div class="action-buttons">
                    <button class="btn btn-view" onclick="viewAdmin(${admin.id})">View</button>
                </div>
            </div>
        `;
        });

        pendingAdminsList.innerHTML = html;
    }

    function viewAdmin(adminId) {
        currentAdminId = adminId;

        fetch(`Web/php/ADMIN/createAdmins.php?action=getAdminDetails&id=${adminId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayAdminDetails(data.data);
                    document.getElementById('adminModal').style.display = 'block';
                } else {
                    showError('Failed to load admin details: ' + data.message);
                }
            })
            .catch(error => {
                showError('Error loading admin details: ' + error.message);
            });
    }

    function displayAdminDetails(admin) {
        const adminDetails = document.getElementById('adminDetails');
        const birthDate = new Date(admin.date_of_birth).toLocaleDateString();
        const applicationDate = new Date(admin.created_at).toLocaleDateString();

        adminDetails.innerHTML = `
        <div class="admin-detail-item">
            <div class="admin-detail-label">Full Name</div>
            <div class="admin-detail-value">${admin.first_name} ${admin.last_name}</div>
        </div>
        <div class="admin-detail-item">
            <div class="admin-detail-label">Email</div>
            <div class="admin-detail-value">${admin.email}</div>
        </div>
        <div class="admin-detail-item">
            <div class="admin-detail-label">Contact</div>
            <div class="admin-detail-value">${admin.contact}</div>
        </div>
        <div class="admin-detail-item">
            <div class="admin-detail-label">Gender</div>
            <div class="admin-detail-value">${admin.gender}</div>
        </div>
        <div class="admin-detail-item">
            <div class="admin-detail-label">Date of Birth</div>
            <div class="admin-detail-value">${birthDate}</div>
        </div>
        <div class="admin-detail-item">
            <div class="admin-detail-label">Current Role</div>
            <div class="admin-detail-value">${admin.role.replace('_', ' ').toUpperCase()}</div>
        </div>
        <div class="admin-detail-item">
            <div class="admin-detail-label">Applied Date</div>
            <div class="admin-detail-value">${applicationDate}</div>
        </div>
        <div class="admin-detail-item">
            <div class="admin-detail-label">Approve As</div>
            <div class="admin-detail-value">
                <select id="approveAsRole" class="role-select">
                    <option value="sub_admin" ${admin.role === 'sub_admin' ? 'selected' : ''}>Sub Admin</option>
                    <option value="main_admin" ${admin.role === 'main_admin' ? 'selected' : ''}>Main Admin</option>
                </select>
            </div>
        </div>
    `;
    }

    function approveAdmin() {
        if (!currentAdminId) {
            showError('No admin selected');
            return;
        }

        const roleSelect = document.getElementById('approveAsRole');
        if (!roleSelect) {
            showError('Role selection not found');
            return;
        }

        const selectedRole = roleSelect.value;

        if (!selectedRole) {
            showError('Please select a role for approval');
            return;
        }

        const roleDisplayName = selectedRole.replace('_', ' ').toUpperCase();

        if (confirm(`Are you sure you want to approve this admin as ${roleDisplayName}?`)) {
            updateAdminStatus(currentAdminId, 'approve', selectedRole);
        }
    }

    function rejectAdmin() {
        if (!currentAdminId) return;

        if (confirm('Are you sure you want to reject this admin?')) {
            updateAdminStatus(currentAdminId, 'reject');
        }
    }

    function updateAdminStatus(adminId, action, role = null) {
        // Add loading state to modal buttons
        const approveBtn = document.querySelector('#adminModal .btn-approve');
        const rejectBtn = document.querySelector('#adminModal .btn-reject');

        if (approveBtn) approveBtn.disabled = true;
        if (rejectBtn) rejectBtn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'updateAdminStatus');
        formData.append('adminId', adminId);
        formData.append('status', action);

        if (role && action === 'approve') {
            formData.append('role', role);
        }

        fetch('Web/php/ADMIN/createAdmins.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Re-enable buttons
                if (approveBtn) approveBtn.disabled = false;
                if (rejectBtn) rejectBtn.disabled = false;

                if (data.success) {
                    showNotification(data.message, 'success');
                    closeAdminModal();
                    loadPendingAdmins();
                } else {
                    showError('Failed to update admin status: ' + data.message);
                }
            })
            .catch(error => {
                // Re-enable buttons
                if (approveBtn) approveBtn.disabled = false;
                if (rejectBtn) rejectBtn.disabled = false;

                showError('Error updating admin status: ' + error.message);
            });
    }

    function closeAdminModal() {
        document.getElementById('adminModal').style.display = 'none';
        currentAdminId = null;
    }

    function updatePendingAdminsCount(count) {
        const countElement = document.getElementById('pendingAdminsCount');
        if (countElement) {
            countElement.textContent = count;
        }
    }
    <?php endif; ?>

    // Utility functions
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 1000;
        animation: slideIn 0.3s ease;
        background: ${type === 'success' ? '#28a745' : '#dc3545'};
    `;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    function showError(message) {
        showNotification(message, 'error');
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        const adminModal = document.getElementById('adminModal');
        if (adminModal && event.target === adminModal) {
            closeAdminModal();
        }
    }

    // Search functionality
    document.querySelector('.search-input').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();

        if (currentTab === 'pending-admins' && isMainAdmin) {
            const adminItems = document.querySelectorAll('.pending-admin-item');
            adminItems.forEach(item => {
                const adminName = item.querySelector('.admin-name').textContent.toLowerCase();
                const adminEmail = item.querySelector('.admin-meta span:nth-child(2)').textContent
                    .toLowerCase();

                if (adminName.includes(searchTerm) || adminEmail.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        } else {
            const serviceItems = document.querySelectorAll('.service-item');
            serviceItems.forEach(item => {
                const serviceName = item.querySelector('.service-title').textContent.toLowerCase();
                const companyName = item.querySelector('.service-company').textContent.toLowerCase();

                if (serviceName.includes(searchTerm) || companyName.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    });
    </script>
</body>

</html>