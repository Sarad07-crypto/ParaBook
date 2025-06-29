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

</body>

</html>