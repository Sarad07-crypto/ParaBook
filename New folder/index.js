function openSidebar() {
  document.getElementById('sidebar').classList.add('active');
  document.body.classList.add('sidebar-open');
  document.getElementById('sidebar-backdrop').classList.add('active');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('active');
  document.body.classList.remove('sidebar-open');
  document.getElementById('sidebar-backdrop').classList.remove('active');
}
// Optional: close sidebar when clicking outside
document.addEventListener('click', function(e) {
  const sidebar = document.getElementById('sidebar');
  const hamburger = document.querySelector('.hamburger');
  if (
    sidebar.classList.contains('active') &&
    !sidebar.contains(e.target) &&
    !hamburger.contains(e.target)
  ) {
    closeSidebar();
  }
});
// Optional: close menu when clicking outside (for better UX)
document.addEventListener('click', function(e) {
  const topBar = document.querySelector('.top-bar');
  const hamburger = document.querySelector('.hamburger');
  if (!topBar.contains(e.target) && topBar.classList.contains('active')) {
    topBar.classList.remove('active');
  }
});

// Close sidebar automatically when resizing from mobile to desktop
window.addEventListener('resize', function() {
  if (window.innerWidth > 600) {
    closeSidebar();
  }
});

function logout() {
  // Add your logout logic here
  alert('Logout clicked');
}
function toggleMobileSearch() {
  var box = document.getElementById('mobile-search-box');
  if (box.classList.contains('active')) {
    box.classList.remove('active');
  } else {
    box.classList.add('active');
    box.querySelector('input').focus();
  }
}