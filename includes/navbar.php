<?php
// navbar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="navbar">
<h1 class="logo">
  <a href="homepage.php" style="text-decoration:none; color:inherit;">
    Student Information System
  </a>
</h1>

    <?php if (isset($_SESSION['user_id'])): ?>
        <button class="logout-btn" onclick="logoutConfirm()" title="Logout">🔓</button>
    <?php endif; ?>
</div>

<script>
function logoutConfirm() {
    if (confirm("Are you sure you want to logout?")) {
        window.location.href = "logout.php";
    }
}
</script>
