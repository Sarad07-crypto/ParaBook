<?php
  require 'avatar.php';
  require 'partials/header.php';
  require 'partials/nav_C.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParaBook - User Profile</title>
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
        --secondary-color: #6c757d;
        --success-color: #007bff;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --light-color: #f8f9fa;
        --dark-color: #343a40;
        --border-radius: 8px;
        --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
      }

      * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
      }

      body {
          font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
          background: #f0f2f5;
          min-height: 100vh;
          line-height: 1.6;
      }

      .fiverr-style-profile {
          display: flex;
          max-width: 1200px;   
          margin: 0 auto;
          padding: 40px 20px;
          gap: 40px;
          min-height: 100vh;
      }

      /* Sidebar */
      .profile-sidebar {
          width: 380px;
          background: rgba(255, 255, 255, 0.95);
          backdrop-filter: blur(20px);
          border: 1px solid rgba(255, 255, 255, 0.2);
          padding: var(--header-padding);
          padding: 40px 30px;
          border-radius: var(--border-radius);
          border-radius: 24px;
          box-shadow: var(--box-shadow);
          box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
          height: fit-content;
          position: sticky;
          top: 40px;
          transition: var(--transition);
      }

      .profile-sidebar:hover {
          transform: translateY(-5px);
          box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
      }

      .profile-avatar-circle {
          width: 120px;
          height: 120px;
          border-radius: 50%;
          background: var(--gradient);
          color: white;
          display: flex;
          justify-content: center;
          align-items: center;
          font-size: 3rem;
          font-weight: 600;
          margin: 0 auto 24px;
          box-shadow: 0 10px 30px rgba(0, 123, 255, 0.3);
          transition: var(--transition);
          position: relative;
          overflow: hidden;
      }

      .profile-avatar-circle::before {
          content: '';
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, transparent 100%);
          border-radius: 50%;
          opacity: 0;
          transition: opacity 0.3s ease;
      }

      .profile-avatar-circle:hover {
          transform: scale(1.05);
          box-shadow: 0 15px 40px rgba(0, 123, 255, 0.4);
      }

      .profile-avatar-circle:hover::before {
          opacity: 1;
      }

      .profile-sidebar h2 {
          text-align: center;
          margin-bottom: 8px;
          font-size: 1.8rem;
          font-weight: 700;
          color: #1a1a1a;
      }

      .username {
          text-align: center;
          color: #6b7280;
          margin-bottom: 32px;
          font-size: 1rem;
          font-weight: 500;
      }

      .profile-info-section {
          margin-bottom: 32px;
      }

      .profile-info-item {
          display: flex;
          align-items: center;
          margin: 16px 0;
          font-size: var(--footer-font-size);
          color: var(--dark-color);
          font-weight: 500;
      }

      .profile-info-item.muted {
          color: var(--secondary-color);
          font-size: 14px;
          font-weight: 400;
      }

      .profile-info-item .icon {
          margin-right: 12px;
          font-size: 1.1rem;
      }

      .profile-btn {
          display: block;
          width: 100%;
          padding: 16px 24px;
          border: none;
          border-radius: var(--border-radius);
          border-radius: 16px;
          margin-bottom: 12px;
          font-weight: 600;
          cursor: pointer;
          font-size: var(--footer-font-size);
          transition: var(--transition);
          text-decoration: none;
          text-align: center;
          position: relative;
          overflow: hidden;
      }



      .preview-btn:hover {
          background: var(--primary-color);
          color: white;
          transform: translateY(-2px);
          box-shadow: 0 8px 20px rgba(0, 123, 255, 0.3);
      }

      .explore-btn {
          background: var(--success-color);
          color: white;
          border: none;
          box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
      }

      .explore-btn:hover {
          background: #218838;
          transform: translateY(-2px);
          box-shadow: 0 8px 20px rgba(40, 167, 69, 0.4);
      }

      /* Right Main Content */
      .profile-main-content {
          flex: 1;
          background: rgba(255, 255, 255, 0.95);
          backdrop-filter: blur(20px);
          border-radius: var(--border-radius);
          border-radius: 24px;
          padding: 40px;
          box-shadow: var(--box-shadow);
          box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
          border: 1px solid rgba(255, 255, 255, 0.2);
      }

      .profile-banner {
          background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
          border: 1px solid var(--primary-color);
          border-opacity: 0.3;
          padding: 20px 28px;
          border-radius: var(--border-radius);
          border-radius: 16px;
          margin-bottom: 32px;
          font-size: var(--footer-font-size);
          color: var(--primary-color);
          font-weight: 500;
      }

      .profile-banner a {
          color: var(--primary-color);
          text-decoration: none;
          font-weight: 600;
          border-bottom: 1px solid var(--primary-color);
          transition: var(--transition);
      }

      .profile-banner a:hover {
          color: #0056b3;
          border-bottom-color: #0056b3;
      }

      .profile-main-content h2 {
          font-size: 2.2rem;
          margin-bottom: 16px;
          font-weight: 700;
          color: var(--dark-color);
          background: var(--gradient);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          background-clip: text;
      }

      .profile-main-content > p {
          color: var(--secondary-color);
          margin-bottom: 32px;
          font-size: var(--nav-link-font-size);
          line-height: 1.7;
      }

      .profile-progress-section {
          margin-bottom: 40px;
          padding: 24px;
          background: var(--light-color);
          border-radius: var(--border-radius);
          border-radius: 16px;
          border: 1px solid #e2e8f0;
      }

      .profile-progress-section label {
          display: block;
          margin-bottom: 12px;
          font-weight: 600;
          color: var(--dark-color);
          font-size: var(--footer-font-size);
      }

      .progress-bar {
          background: #e2e8f0;
          border-radius: var(--border-radius);
          border-radius: 12px;
          height: 12px;
          margin: 12px 0;
          width: 100%;
          overflow: hidden;
          position: relative;
      }

      .progress-fill {
          height: 100%;
          background: var(--success-color);
          border-radius: var(--border-radius);
          border-radius: 12px;
          transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
          position: relative;
      }

      .progress-fill::after {
          content: '';
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.3) 50%, transparent 100%);
          animation: shimmer 2s infinite;
      }

      @keyframes shimmer {
          0% { transform: translateX(-100%); }
          100% { transform: translateX(100%); }
      }

      .progress-text {
          font-size: 0.9rem;
          color: #6b7280;
          font-weight: 500;
      }

      .recent-achievements {
          margin-bottom: 40px;
          padding: 32px;
          border: 1px solid #e5e7eb;
          border-radius: 20px;
          background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
          transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      }

      .recent-achievements:hover {
          transform: translateY(-2px);
          box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
      }

      .recent-achievements h3 {
          margin-bottom: 20px;
          font-size: 1.4rem;
          font-weight: 700;
          color: #1f2937;
      }

      .achievement-item {
          margin: 16px 0;
          font-size: 1rem;
          color: #374151;
          font-weight: 500;
          padding: 12px 16px;
          background: white;
          border-radius: 12px;
          border: 1px solid #e5e7eb;
      }

      .profile-todo-list {
          display: flex;
          flex-direction: column;
          gap: 16px;
      }

      .todo-item {
          background: white;
          border: 1px solid #e5e7eb;
          padding: 24px 28px;
          border-radius: var(--border-radius);
          border-radius: 16px;
          display: flex;
          justify-content: space-between;
          align-items: center;
          font-size: var(--nav-link-font-size);
          font-weight: 500;
          color: var(--dark-color);
          transition: var(--transition);
      }

      .todo-item:hover {
          background: var(--light-color);
          transform: translateY(-2px);
          box-shadow: var(--box-shadow);
      }

      .todo-item button {
          background: var(--gradient);
          color: white;
          border: none;
          padding: 12px 24px;
          border-radius: var(--border-radius);
          border-radius: 12px;
          cursor: pointer;
          font-size: 14px;
          font-weight: 600;
          transition: var(--transition);
          box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
      }

      .todo-item button:hover {
          transform: translateY(-2px);
          box-shadow: 0 6px 16px rgba(0, 123, 255, 0.4);
      }

      /* Responsive Design */
      @media (max-width: 900px) {
          .fiverr-style-profile {
              flex-direction: column;
              padding: 20px 15px;
              gap: 24px;
          }

          .profile-sidebar {
              width: 100%;
              position: static;
          }

          .profile-main-content {
              padding: 32px 24px;
          }

          .profile-main-content h2 {
              font-size: 1.8rem;
          }
      }

      @media (max-width: 600px) {
          .profile-sidebar {
              padding: 24px 20px;
          }

          .profile-main-content {
              padding: 24px 20px;
          }

          .todo-item {
              flex-direction: column;
              align-items: stretch;
              gap: 16px;
              text-align: center;
          }

          .todo-item button {
              width: 100%;
          }
      }
    </style>
</head>
<body>
<div class="fiverr-style-profile">
  <!-- Left Sidebar -->
  <div class="profile-sidebar">
    <div class="profile-avatar-circle">
      <img src="<?php echo $avatar ?>" alt="image not found"
                                onerror="showInitial(this, '<?php echo $firstInitial ?>')">
    </div>
    <h2>Sabin Pandey</h2>
    <p class="username">@sabinpandey657</p>
    
    <div class="profile-info-section">
      <div class="profile-info-item">
        <span class="icon">📍</span>
        <span>Located in Nepal</span>
      </div>
      <div class="profile-info-item">
        <span class="icon">📅</span>
        <span>Joined in June 2025</span>
      </div>
      <div class="profile-info-item muted">
        <span class="icon">🌐</span>
        <span>English, Nepali</span>
      </div>
      <div class="profile-info-item muted">
        <span class="icon">⏰</span>
        <span>9AM - 6PM</span>
      </div>
    </div>

    
    <button onclick="window.location.href='/home" class="profile-btn explore-btn">Explore ParaBook →</button>
  </div>

  <!-- Right Main Content -->
  <div class="profile-main-content">


    <h2>About me</h2>
    <p>Tell us a bit more about yourself to help us suggest the best paragliding experiences for you.</p>


  </div>
</div>

<script>
  // Add smooth scroll and interactive elements
  document.querySelectorAll('.todo-item button').forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Add a ripple effect
      const ripple = document.createElement('span');
      const rect = this.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;
      
      ripple.style.width = ripple.style.height = size + 'px';
      ripple.style.left = x + 'px';
      ripple.style.top = y + 'px';
      ripple.classList.add('ripple');
      
      this.appendChild(ripple);
      
      setTimeout(() => {
        ripple.remove();
      }, 600);
      
      // Add functionality based on button text
      const buttonText = this.textContent.toLowerCase();
      if (buttonText.includes('start')) {
        alert('Bio completion feature coming soon!');
      } else if (buttonText.includes('upload')) {
        alert('Photo upload feature coming soon!');
      } else if (buttonText.includes('view')) {
        alert('Achievements page coming soon!');
      }
    });
  });

  // Add progress bar animation on page load
  window.addEventListener('load', () => {
    const progressFill = document.querySelector('.progress-fill');
    const targetWidth = progressFill.style.width;
    progressFill.style.width = '0%';
    
    setTimeout(() => {
      progressFill.style.width = targetWidth;
    }, 500);
  });

  // Add CSS for ripple effect
  const style = document.createElement('style');
  style.textContent = `
    .ripple {
      position: absolute;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.6);
      transform: scale(0);
      animation: ripple-animation 0.6s linear;
      pointer-events: none;
    }
    
    @keyframes ripple-animation {
      to {
        transform: scale(4);
        opacity: 0;
      }
    }
    
    .todo-item button {
      position: relative;
      overflow: hidden;
    }
  `;
  document.head.appendChild(style);
</script>
</body>
</html>

<?php
  require 'partials/footer.php';
?>