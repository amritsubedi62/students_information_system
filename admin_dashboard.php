<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Student Information System</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script>
    function logoutConfirm() {
      if(confirm("Are you sure you want to logout?")) {
        window.location.href = "logout.php";
      }
    }
  </script>
</head>
<body>

<?php include "includes/navbar.php"; ?>

<div class="content">
  <h2>Welcome, Admin 👑</h2>

  <div class="dashboard">

    <div class="card">
      <h3>User Management</h3>
      <p>View all teachers and parents. Approve or deactivate accounts.</p>
      <a href="manage_users.php">Go →</a>
    </div>


    <div class="card">
      <h3>Assign Teachers</h3>
      <p>Assign one teacher per class and manage teacher-class access.</p>
      <a href="assign_teacher.php">Go →</a>
    </div>


    <div class="card">
      <h3>Reports & Analytics</h3>
      <p>View class-wise performance and system-wide analytics.</p>
      <a href="admin_reports.php">Go →</a>
    </div>
    <div class="card">
      <h3>Result & Ranking</h3>
      <p>View student results and class-wise ranking system.</p>
      <a href="admin_results.php">Go →</a>
  </div>

  </div>
</div>

</body>
</html>
