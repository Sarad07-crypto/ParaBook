<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="Web/css/style.css" />
</head>
<style>
body {
    display: block !important;
    position: static !important;
    margin: 0;
    padding: 0;
    overflow: auto !important;
}
</style>

<body>


    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>


    <div class="sidebar" id="sidebar">
        <button class="close-btn" onclick="closeSidebar()">&times;</button>

        <div class="avatar" style="margin-bottom: 24px; margin-top: 10px; align-self: center;"><img
                src="<?php echo $avatar ?>">
        </div>
        <div class="sidebar-section sidebar-links">
            <a href="/home">Home</a>
            <a href="/bookingcompany">Bookings</a>
            <a href="/statistics">Analytics</a>
            <a href="#">Revenue</a>
        </div>
        <div class="sidebar-section">
            <button class="logout-btn" onclick="window.location.href='/logout'">Log out</button>
        </div>
    </div>
    <!-- Navigation Bar -->
    <div class="nav-bar">
        <a href="/home">Home</a>
        <a href="/bookingcompany">Bookings</a>
        <a href="/statistics">Analytics</a>
        <a href="#">Revenue</a>
    </div>
    <script src="Web/scripts/views.js"></script>
</body>

</html>