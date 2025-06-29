<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin-Dashboard</title>
    <link rel="stylesheet" href="Web/css/admin.css" />
</head>

<body>

    <div class="nav-tabs">
        <button class="nav-tab active" onclick="switchTab('pending')">Pending Approvals</button>
        <button class="nav-tab" onclick="switchTab('approved')">Approved Companies</button>
        <button class="nav-tab" onclick="switchTab('rejected')">Rejected Companies</button>
        <button class="nav-tab" onclick="switchTab('all')">All Companies</button>
    </div>

    <div class="main-content">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">5</div>
                <div class="stat-label">Pending Approvals</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">10</div>
                <div class="stat-label">Approved Companies</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">1</div>
                <div class="stat-label">Rejected Companies</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">20</div>
                <div class="stat-label">Total Companies</div>
            </div>
        </div>

        <div class="companies-section">
            <div class="section-header">
                <div class="section-title">Company Management</div>
                <div class="filter-buttons">
                    <button class="filter-btn active">All</button>
                    <button class="filter-btn">Today</button>
                    <button class="filter-btn">This Week</button>
                </div>
            </div>

            <div class="company-list" id="companyList">
                <!-- Pending Companies -->
                <div class="company-item" data-status="pending">
                    <div class="company-avatar">SH</div>
                    <div class="company-info">
                        <div class="company-name">Sky High Paragliding</div>
                        <div class="company-details">Pokhara, Nepal • Paragliding Tours & Training</div>
                        <div class="company-meta">
                            <span>📅 Applied: 2 days ago</span>
                            <span>📧 contact@skyhigh.com</span>
                            <span>📞 +977-9841234567</span>
                        </div>
                    </div>
                    <div class="status-badge status-pending">Pending</div>
                    <div class="action-buttons">
                        <button class="btn btn-view" onclick="viewCompany('sky-high')">View</button>
                        <button class="btn btn-approve" onclick="approveCompany('sky-high')">Approve</button>
                        <button class="btn btn-reject" onclick="rejectCompany('sky-high')">Reject</button>
                    </div>
                </div>

                <div class="company-item" data-status="pending">
                    <div class="company-avatar">AP</div>
                    <div class="company-info">
                        <div class="company-name">Anish PAudel</div>
                        <div class="company-details">Kathmandu, Nepal • Individual Paragliding Instructor</div>
                        <div class="company-meta">
                            <span>📅 Applied: 5 days ago</span>
                            <span>📧 buqwfwgbyf3byudwqyufbyuq@email.com</span>
                            <span>📞 +977-9812345678</span>
                        </div>
                    </div>
                    <div class="status-badge status-pending">Pending</div>
                    <div class="action-buttons">
                        <button class="btn btn-view" onclick="viewCompany('anish')">View</button>
                        <button class="btn btn-approve" onclick="approveCompany('anish')">Approve</button>
                        <button class="btn btn-reject" onclick="rejectCompany('anish')">Reject</button>
                    </div>
                </div>

                <div class="company-item" data-status="pending">
                    <div class="company-avatar">HF</div>
                    <div class="company-info">
                        <div class="company-name">Himalayan Flight Adventures</div>
                        <div class="company-details">Pokhara, Nepal • Tandem Flights & Adventure Tours</div>
                        <div class="company-meta">
                            <span>📅 Applied: 1 week ago</span>
                            <span>📧 info@himalayanflight.com</span>
                            <span>📞 +977-9823456789</span>
                        </div>
                    </div>
                    <div class="status-badge status-pending">Pending</div>
                    <div class="action-buttons">
                        <button class="btn btn-view" onclick="viewCompany('himalayan')">View</button>
                        <button class="btn btn-approve" onclick="approveCompany('himalayan')">Approve</button>
                        <button class="btn btn-reject" onclick="rejectCompany('himalayan')">Reject</button>
                    </div>
                </div>

                <!-- Approved Companies -->
                <div class="company-item" data-status="approved" style="display: none;">
                    <div class="company-avatar">EG</div>
                    <div class="company-info">
                        <div class="company-name">Eagle Paragliding Nepal</div>
                        <div class="company-details">Pokhara, Nepal • Professional Paragliding Services</div>
                        <div class="company-meta">
                            <span>📅 Approved: 1 month ago</span>
                            <span>📧 booking@eaglenepal.com</span>
                            <span>⭐ 4.8 Rating</span>
                        </div>
                    </div>
                    <div class="status-badge status-approved">Approved</div>
                    <div class="action-buttons">
                        <button class="btn btn-view" onclick="viewCompany('eagle')">View</button>
                    </div>
                </div>

                <!-- Rejected Companies -->
                <div class="company-item" data-status="rejected" style="display: none;">
                    <div class="company-avatar">UF</div>
                    <div class="company-info">
                        <div class="company-name">Unsafe Flying Co.</div>
                        <div class="company-details">Kathmandu, Nepal • Incomplete Documentation</div>
                        <div class="company-meta">
                            <span>📅 Rejected: 2 weeks ago</span>
                            <span>📧 contact@unsafeflying.com</span>
                            <span>❌ Missing Safety Certificates</span>
                        </div>
                    </div>
                    <div class="status-badge status-rejected">Rejected</div>
                    <div class="action-buttons">
                        <button class="btn btn-view" onclick="viewCompany('unsafe')">View</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="Web/scripts/admin.js"></script>

    <script>
        let currentTab = 'pending';

        function switchTab(tab) {
            currentTab = tab;
            
            // Update tab buttons
            document.querySelectorAll('.nav-tab').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filter companies
            const companies = document.querySelectorAll('.company-item');
            companies.forEach(company => {
                const status = company.dataset.status;
                if (tab === 'all' || status === tab) {
                    company.style.display = 'flex';
                } else {
                    company.style.display = 'none';
                }
            });
            
            // Update section title
            const sectionTitle = document.querySelector('.section-title');
            const tabTitles = {
                'pending': 'Pending Approvals',
                'approved': 'Approved Companies',
                'rejected': 'Rejected Companies',
                'all': 'All Companies'
            };
            sectionTitle.textContent = tabTitles[tab];
        }

        function approveCompany(companyId) {
            if (confirm('Are you sure you want to approve this company?')) {
                // Find the company item
                const companyItem = document.querySelector(`[data-status="pending"]`);
                const statusBadge = companyItem.querySelector('.status-badge');
                const actionButtons = companyItem.querySelector('.action-buttons');
                
                // Update status
                statusBadge.textContent = 'Approved';
                statusBadge.className = 'status-badge status-approved';
                companyItem.dataset.status = 'approved';
                
                // Update action buttons
                actionButtons.innerHTML = '<button class="btn btn-view" onclick="viewCompany(\'' + companyId + '\')">View</button>';
                
                // Show success message
                showNotification('Company approved successfully!', 'success');
                
                // Update stats
                updateStats();
            }
        }

        function rejectCompany(companyId) {
            const reason = prompt('Please provide a reason for rejection:');
            if (reason && reason.trim()) {
                // Find the company item
                const companyItem = document.querySelector(`[data-status="pending"]`);
                const statusBadge = companyItem.querySelector('.status-badge');
                const actionButtons = companyItem.querySelector('.action-buttons');
                
                // Update status
                statusBadge.textContent = 'Rejected';
                statusBadge.className = 'status-badge status-rejected';
                companyItem.dataset.status = 'rejected';
                
                // Update action buttons
                actionButtons.innerHTML = '<button class="btn btn-view" onclick="viewCompany(\'' + companyId + '\')">View</button>';
                
                // Show success message
                showNotification('Company rejected successfully!', 'error');
                
                // Update stats
                updateStats();
            }
        }

        function viewCompany(companyId) {
            alert('Opening detailed view for company: ' + companyId);
            // In a real application, this would open a modal or navigate to a detailed view
        }

        function showNotification(message, type) {
            // Create notification element
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
            
            // Add animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.remove();
                style.remove();
            }, 3000);
        }

        function updateStats() {
            // In a real application, this would update the statistics
            console.log('Updating statistics...');
        }

        // Search functionality
        document.querySelector('.search-input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const companies = document.querySelectorAll('.company-item');
            
            companies.forEach(company => {
                const companyName = company.querySelector('.company-name').textContent.toLowerCase();
                const companyDetails = company.querySelector('.company-details').textContent.toLowerCase();
                
                if (companyName.includes(searchTerm) || companyDetails.includes(searchTerm)) {
                    company.style.display = 'flex';
                } else {
                    company.style.display = 'none';
                }
            });
        });

        // Filter button functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>

   


</body>

</html>