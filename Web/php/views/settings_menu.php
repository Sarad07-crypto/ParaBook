<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #007bff;
      --gradient: linear-gradient(90deg, #1e90ff, #0056b3);
      --header-padding: 12px 30px;
      --logo-size: 24px;
      --footer-padding: 30px 0 10px 0;
      --footer-font-size: 15px;
      --footer-gap: 30px;
      --nav-link-margin: 0 20px;
      --nav-link-font-size: 16px;
    }

    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f8f9fa;
    }

    .header {
      position: sticky;
      top: 0;
      z-index: 100;
      background: #fff;
      border-bottom: 0px solid #007bff;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    .top-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: var(--header-padding);
      background-color: #fff;
      border-bottom: 0px solid #ccc;
    }

    .logo {
      font-weight: bold;
      font-size: var(--logo-size);
      color: var(--primary-color);
      display: block;
    }

    .nav-bar {
      display: flex;
      justify-content: center;
      align-items: center;
      background: var(--gradient);
      padding: 10px 0;
      position: relative;
      color: white;
    }

    .nav-bar a {
      margin: var(--nav-link-margin);
      text-decoration: none;
      color: white;
      font-weight: 500;
      font-size: var(--nav-link-font-size);
      margin: 6px 0;
      padding: 0 20px;
    }

    .nav-bar a:hover {
      text-decoration: underline;
    }

    /* Settings Page Styles */
    .settings-container {
      max-width: 1000px;
      margin: 40px auto;
      padding: 0 20px;
      display: grid;
      grid-template-columns: 250px 1fr;
      gap: 30px;
    }

    .settings-sidebar {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
      padding: 20px 0;
      height: fit-content;
      position: sticky;
      top: 120px;
    }

    .settings-nav {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .settings-nav li {
      margin: 0;
    }

    .settings-nav a {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 15px 20px;
      color: #666;
      text-decoration: none;
      font-size: 15px;
      transition: all 0.2s;
      border-left: 3px solid transparent;
    }

    .settings-nav a:hover,
    .settings-nav a.active {
      background: #f0f4ff;
      color: var(--primary-color);
      border-left-color: var(--primary-color);
    }

    .settings-nav i {
      width: 16px;
      font-size: 16px;
    }

    .settings-content {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
      padding: 30px;
    }

    .settings-section {
      display: none;
    }

    .settings-section.active {
      display: block;
    }

    .section-header {
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid #f0f0f0;
    }

    .section-header h2 {
      color: #333;
      font-size: 24px;
      margin: 0 0 8px 0;
    }

    .section-header p {
      color: #666;
      margin: 0;
      font-size: 14px;
    }

    .form-group {
      margin-bottom: 25px;
    }

    .form-group label {
      display: block;
      color: #333;
      font-weight: 500;
      margin-bottom: 8px;
      font-size: 15px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 14px;
      transition: border-color 0.2s;
      box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .form-group textarea {
      resize: vertical;
      min-height: 100px;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .toggle-switch {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 24px;
    }

    .toggle-switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: 0.3s;
      border-radius: 24px;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 18px;
      width: 18px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      transition: 0.3s;
      border-radius: 50%;
    }

    input:checked + .slider {
      background-color: var(--primary-color);
    }

    input:checked + .slider:before {
      transform: translateX(26px);
    }

    .preference-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 0;
      border-bottom: 1px solid #f0f0f0;
    }

    .preference-item:last-child {
      border-bottom: none;
    }

    .preference-info h4 {
      margin: 0 0 4px 0;
      color: #333;
      font-size: 15px;
    }

    .preference-info p {
      margin: 0;
      color: #666;
      font-size: 13px;
    }

    .btn {
      background: var(--primary-color);
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      transition: background 0.2s;
    }

    .btn:hover {
      background: #0056b3;
    }

    .btn-secondary {
      background: #6c757d;
      margin-right: 10px;
    }

    .btn-secondary:hover {
      background: #545862;
    }

    .btn-danger {
      background: #dc3545;
    }

    .btn-danger:hover {
      background: #c82333;
    }

    .avatar-upload {
      display: flex;
      align-items: center;
      gap: 20px;
      margin-bottom: 20px;
    }

    .current-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: #ccc;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 32px;
      color: white;
      background: var(--primary-color);
    }

    .upload-btn {
      background: #f8f9fa;
      border: 1px solid #ddd;
      color: #333;
      padding: 8px 16px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
    }

    .upload-btn:hover {
      background: #e9ecef;
    }

    .danger-zone {
      background: #fff5f5;
      border: 1px solid #fed7d7;
      border-radius: 8px;
      padding: 20px;
      margin-top: 30px;
    }

    .danger-zone h3 {
      color: #e53e3e;
      margin-top: 0;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .settings-container {
        grid-template-columns: 1fr;
        gap: 20px;
        margin: 20px auto;
      }

      .settings-sidebar {
        position: static;
      }

      .settings-nav {
        display: flex;
        overflow-x: auto;
        padding: 0 10px;
      }

      .settings-nav li {
        min-width: 140px;
      }

      .settings-nav a {
        padding: 12px 15px;
        white-space: nowrap;
        text-align: center;
        flex-direction: column;
        gap: 6px;
        border-left: none;
        border-bottom: 3px solid transparent;
      }

      .settings-nav a:hover,
      .settings-nav a.active {
        border-left: none;
        border-bottom-color: var(--primary-color);
      }

      .form-row {
        grid-template-columns: 1fr;
      }

      .settings-content {
        padding: 20px;
      }
    }

    /* Dark mode styles */
    body.dark-mode {
      background: #181a1b !important;
      color: #e0e0e0 !important;
    }

    body.dark-mode .header,
    body.dark-mode .nav-bar,
    body.dark-mode .top-bar,
    body.dark-mode .settings-sidebar,
    body.dark-mode .settings-content {
      background: #23272a !important;
      color: #e0e0e0 !important;
    }

    body.dark-mode .settings-nav a {
      color: #b0b0b0;
    }

    body.dark-mode .settings-nav a:hover,
    body.dark-mode .settings-nav a.active {
      background: #2c3e50 !important;
      color: #3498db !important;
    }

    body.dark-mode .form-group input,
    body.dark-mode .form-group select,
    body.dark-mode .form-group textarea {
      background: #2c3e50;
      border-color: #444;
      color: #e0e0e0;
    }

    body.dark-mode .danger-zone {
      background: #2d1b1b;
      border-color: #5a2626;
    }
  </style>
</head>
<body>


  <!-- Settings Container -->
  <div class="settings-container">
    <!-- Sidebar Navigation -->
    <div class="settings-sidebar">
      <ul class="settings-nav">
        <li><a href="#" class="active" onclick="showSection('account')" data-section="account">
          <i class="fas fa-user"></i>Account Settings
        </a></li>

        <li><a href="#" onclick="showSection('security')" data-section="security">
          <i class="fas fa-lock"></i>Security
        </a></li>

        <li><a href="#" onclick="showSection('help')" data-section="help">
          <i class="fas fa-question-circle"></i>Help & Support
        </a></li>
      </ul>
    </div>

    <!-- Settings Content -->
    <div class="settings-content">
      <!-- Account Settings -->
      <div class="settings-section active" id="account">
        <div class="section-header">
          <h2>Account Settings</h2>
          <p>Manage your account information and profile details</p>
        </div>

        <div class="avatar-upload">
          <div class="current-avatar">U</div>
          <div>
            <button class="upload-btn">Change Avatar</button>
            <p style="font-size: 12px; color: #666; margin: 5px 0 0 0;">Max file size: 5MB</p>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>First Name</label>
            <input type="text" value="John" placeholder="Enter your first name">
          </div>
          <div class="form-group">
            <label>Last Name</label>
            <input type="text" value="Doe" placeholder="Enter your last name">
          </div>
        </div>

        <div class="form-group">
          <label>Email Address</label>
          <input type="email" value="john.doe@example.com" placeholder="Enter your email">
        </div>

        <div class="form-group">
          <label>Phone Number</label>
          <input type="tel" value="+1 (555) 123-4567" placeholder="Enter your phone number">
        </div>

        <div class="form-group">
          <label>Bio</label>
          <textarea placeholder="Tell us about yourself...">Software developer passionate about creating innovative solutions.</textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Country</label>
            <select>
              <option>United States</option>
              <option>Canada</option>
              <option>United Kingdom</option>
              <option>Australia</option>
            </select>
          </div>
          <div class="form-group">
            <label>Time Zone</label>
            <select>
              <option>UTC-8 (PST)</option>
              <option>UTC-5 (EST)</option>
              <option>UTC+0 (GMT)</option>
              <option>UTC+1 (CET)</option>
            </select>
          </div>
        </div>

        <button class="btn">Save Changes</button>
      </div>







      <!-- Security Settings -->
      <div class="settings-section" id="security">
        <div class="section-header">
          <h2>Security Settings</h2>
          <p>Manage your password and security preferences</p>
        </div>

        <div class="form-group">
          <label>Current Password</label>
          <input type="password" placeholder="Enter current password">
        </div>

        <div class="form-group">
          <label>New Password</label>
          <input type="password" placeholder="Enter new password">
        </div>

        <div class="form-group">
          <label>Confirm New Password</label>
          <input type="password" placeholder="Confirm new password">
        </div>

        <div class="preference-item">
          <div class="preference-info">
            <h4>Two-Factor Authentication</h4>
            <p>Add an extra layer of security to your account</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox">
            <span class="slider"></span>
          </label>
        </div>

        <div class="preference-item">
          <div class="preference-info">
            <h4>Login Alerts</h4>
            <p>Get notified when someone logs into your account</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" checked>
            <span class="slider"></span>
          </label>
        </div>

        <button class="btn">Update Password</button>
        <button class="btn btn-secondary">View Login History</button>
      </div>

      <!-- Billing Settings -->
      <div class="settings-section" id="billing">
        <div class="section-header">
          <h2>Billing & Subscription</h2>
          <p>Manage your subscription and payment methods</p>
        </div>

        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
          <h4 style="color: var(--primary-color); margin-top: 0;">Current Plan: Pro</h4>
          <p style="margin: 0; color: #666;">$29.99/month • Next billing date: January 15, 2025</p>
        </div>

        <div class="form-group">
          <label>Payment Method</label>
          <div style="border: 1px solid #ddd; border-radius: 6px; padding: 15px; display: flex; align-items: center; gap: 10px;">
            <i class="fab fa-cc-visa" style="font-size: 24px; color: #1a1f71;"></i>
            <span>**** **** **** 1234</span>
            <span style="margin-left: auto; color: var(--primary-color); cursor: pointer;">Change</span>
          </div>
        </div>

        <button class="btn">Upgrade Plan</button>
        <button class="btn btn-secondary">Download Invoice</button>
      </div>

      <!-- Help & Support -->
      <div class="settings-section" id="help">
        <div class="section-header">
          <h2>Help & Support</h2>
          <p>Get help and manage your account</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
          <div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; text-align: center;">
            <i class="fas fa-book" style="font-size: 32px; color: var(--primary-color); margin-bottom: 10px;"></i>
            <h4>Documentation</h4>
            <p style="color: #666; font-size: 14px;">Browse our comprehensive guides</p>
          </div>
          <div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; text-align: center;">
            <i class="fas fa-comments" style="font-size: 32px; color: var(--primary-color); margin-bottom: 10px;"></i>
            <h4>Live Chat</h4>
            <p style="color: #666; font-size: 14px;">Chat with our support team</p>
          </div>
          <div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; text-align: center;">
            <i class="fas fa-envelope" style="font-size: 32px; color: var(--primary-color); margin-bottom: 10px;"></i>
            <h4>Email Support</h4>
            <p style="color: #666; font-size: 14px;">Send us your questions</p>
          </div>
        </div>

        <div class="danger-zone">
          <h3>Danger Zone</h3>
          <p>These actions are irreversible. Please proceed with caution.</p>
          <button class="btn btn-danger">Delete Account</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    function showSection(sectionId) {
      // Hide all sections
      const sections = document.querySelectorAll('.settings-section');
      sections.forEach(section => section.classList.remove('active'));
      
      // Show selected section
      document.getElementById(sectionId).classList.add('active');
      
      // Update navigation
      const navLinks = document.querySelectorAll('.settings-nav a');
      navLinks.forEach(link => link.classList.remove('active'));
      document.querySelector(`[data-section="${sectionId}"]`).classList.add('active');
    }

    function toggleDarkMode() {
      document.body.classList.toggle('dark-mode');
      const icon = document.getElementById('darkModeIcon');
      const toggle = document.getElementById('darkModeToggle');
      
      if (document.body.classList.contains('dark-mode')) {
        icon.className = 'fas fa-sun';
        if (toggle) toggle.checked = true;
      } else {
        icon.className = 'fas fa-moon';
        if (toggle) toggle.checked = false;
      }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
      // Check for saved dark mode preference
      const darkMode = localStorage.getItem('darkMode');
      if (darkMode === 'enabled') {
        document.body.classList.add('dark-mode');
        document.getElementById('darkModeIcon').className = 'fas fa-sun';
        const toggle = document.getElementById('darkModeToggle');
        if (toggle) toggle.checked = true;
      }
    });

    // Save dark mode preference
    document.body.addEventListener('DOMNodeInserted', function() {
      if (document.body.classList.contains('dark-mode')) {
        localStorage.setItem('darkMode', 'enabled');
      } else {
        localStorage.setItem('darkMode', 'disabled');
      }
    });
  </script>
</body>
</html>