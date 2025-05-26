<?php
  session_start();

  $loginType = '';
  
  if (isset($_SESSION['user_email'])) {
      $loginType = 'google';
  } elseif (isset($_SESSION['access_token']) && isset($_SESSION['userData'])) {
      $loginType = 'facebook';
  } elseif( (isset($_SESSION['Email'])) ){
      $loginType = 'form';
  }else {
    // echo"Error entering passenger page";
      header("Location: ../login_signup/login.php");
      exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Paragliding Booking Dashboard</title>
    <style>
      body {
        margin: 0;
        font-family: "apple-system-ui", "BlinkMacSystemFont", "Segoe UI",
          "Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans",
          "Helvetica Neue", sans-serif;
        display: flex;
        background-color: #f0f4f8;
      }
      .sidebar {
        width: 250px;
        background-color: #0062e2;
        color: white;
        height: 100vh;
        padding: 20px;
        box-sizing: border-box;
      }
      .sidebar h2 {
        margin-bottom: 30px;
        font-size: 20px;
      }
      .nav-link {
        display: flex;
        align-items: center;
        padding: 10px 0;
        cursor: pointer;
      }
      .nav-link:hover {
        background-color: #0050b881;
        border-radius: 5px;
        padding-left: 10px;
      }
      .content {
        flex: 1;
        padding: 30px;
      }
      .dashboard-title {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 20px;
      }
      .cards {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
      }
      .card {
        flex: 1;
        background-color: rgb(240, 198, 198);
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }
      .card2 {
        flex: 1;
        background-color: rgb(198, 240, 198);
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }
      .card3 {
        flex: 1;
        background-color: rgb(198, 198, 240);
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }
      .card4 {
        flex: 1;
        background-color: rgb(240, 240, 198);
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }
      .logout > a{
        text-decoration: none;
        color: white;
        padding:15px;
      }
      .card h3 {
        margin: 0 0 10px 0;
        font-size: 20px;
        color: #333;
      }

      .card p {
        font-size: 26px;
        font-weight: 600;
        color: #1e3a5f;
      }
      .card2 h3,
      .card3 h3,
      .card4 h3 {
        margin: 0 0 10px 0;
        font-size: 20px;
        color: #333;
      }
      .card2 p,
      .card3 p,
      .card4 p {
        font-size: 26px;
        font-weight: 600;
        color: #1e3a5f;
      }
      table {
        width: 100%;
        border-collapse: collapse;
        background-color: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }
      th,
      td {
        padding: 15px;
        text-align: left;
      }
      th {
        background-color: #f5f5f5;
        color: #333;
      }
      tr:not(:last-child) {
        border-bottom: 1px solid #eee;
      }
      .status {
        padding: 5px 10px;
        border-radius: 5px;
        color: white;
        font-size: 14px;
      }
      .confirmed {
        background-color: #2ca87f;
      }
      .pending {
        background-color: #4780ba;
      }
    </style>
  </head>
  <body>
    <div class="sidebar">
    
    <?php if ($loginType === 'google'): ?>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['givenName']) . " " . htmlspecialchars($_SESSION['familyName']); ?>!</h1>
        <img src="<?php echo htmlspecialchars($_SESSION['avatar']); ?>" alt="Profile Picture" width="100" style="border-radius:100px;" />
    <?php elseif ($loginType === 'facebook'): ?>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['userData']['first_name']) . " " . htmlspecialchars($_SESSION['userData']['last_name']); ?>!</h1>
        <img src="<?php echo htmlspecialchars($_SESSION['userData']['picture']['url']); ?>" alt="Profile Picture" width="100" style="border-radius:100px;" />
    <?php elseif ($loginType === 'form'): ?>
      <!-- <pre>print_r($_SESSION);</pre> -->
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['firstName']) . " " . htmlspecialchars($_SESSION['lastName']); ?>!</h1>
        <img src="<?php echo htmlspecialchars($_SESSION['avatar']); ?>" alt="Picture not found!" width="100" style="border-radius:100px;" />
    <?php endif; ?>
    
      <h2>Parabook</h2>
      <div class="nav-link">🏠 Home</div>
      <div class="nav-link">📅 Bookings</div>
      <div class="nav-link">👥 Customers</div>
      <div class="logout nav-link">  <a href="../login_signup/logout.php">Logout</a></div>
    </div>
    <div class="content">
      <div class="dashboard-title"><b>Dashboard</b></div>
      <div class="cards">
        <div class="card">
          <h3>Upcoming Bookings</h3>
          <p>5</p>
        </div>
        <div class="card2">
          <h3>Total Bookings</h3>
          <p>120</p>
        </div>
        <div class="card3">
          <h3>New Customers</h3>
          <p>8</p>
        </div>
        <div class="card4">
          <h3>Revenue</h3>
          <p>$3,920</p>
        </div>
      </div>
      <h3>Upcoming Bookings</h3>
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Customer</th>
            <th>Time</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>April 25, 2024</td>
            <td>Sarad Adhikari</td>
            <td>10:00 AM</td>
            <td><span class="status confirmed">Confirmed</span></td>
          </tr>
          <tr>
            <td>April 26, 2024</td>
            <td>Karun Sunuwar</td>
            <td>2:30 PM</td>
            <td><span class="status pending">Pending</span></td>
          </tr>
          <tr>
            <td>April 27, 2024</td>
            <td>Sabin Pandey</td>
            <td>11:15 AM</td>
            <td><span class="status confirmed">Confirmed</span></td>
          </tr>
          <tr>
            <td>April 28, 2024</td>
            <td>Anish Poudel</td>
            <td>3:45 PM</td>
            <td><span class="status confirmed">Confirmed</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </body>
</html>
