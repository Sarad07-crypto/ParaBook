<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link rel="stylesheet" href="Web/css/style.css?v=1.0" />

<style>
body {
    display: block !important;
    position: static !important;
    margin: 0;
    padding: 0;
    overflow: auto !important;
}
</style>


<div class="sidebar-backdrop" id="sidebar-backdrop"></div>


<div class="sidebar" id="sidebar">
    <button class="close-btn" onclick="closeSidebar()">&times;</button>

    <div class="avatar" style="margin-bottom: 24px; margin-top: 10px; align-self: center;"><img
            src="<?php echo $avatar ?>">
    </div>
    <div class="sidebar-section sidebar-links">
        <a href="#">Dashboard</a>
        <a href="#">Flight Management</a>
        <a href="#">Bookings</a>
        <a href="#">Analytics</a>
        <a href="#">Revenue</a>
    </div>
    <div class="sidebar-section">
        <a href="#" style="text-decoration: none;">Notifications</a><br>
        <a href="#" style="text-decoration: none;">Messages</a><br>
        <a href="#" style="text-decoration: none;">Reports</a><br>
        <a href="#" style="text-decoration: none;">Support</a>
        <span class="switch-text">Company Dashboard</span>
        <button class="logout-btn" onclick="window.location.href='/logout'">Log out</button>
    </div>
</div>
<!-- Navigation Bar -->
<div class="nav-bar">
    <a href="#">Dashboard</a>
    <a href="/bookingcheck">Bookings</a>
    <a href="#">Statistics</a>
</div>
<script src="Web/scripts/views.js"></script>