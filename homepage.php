<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Dashboard - Student Information System</title>
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
  <h2>Welcome, Teacher 👩‍🏫</h2>

  <div class="dashboard">
    <div class="card">
      <h3>Manage Students</h3>
      <p>Add, edit, or remove student details.</p>
      <a href="manage_students.php">Go →</a>
    </div>

    <div class="card">
      <h3>Manage Attendance</h3>
      <p>Record and view attendance of students.</p>
      <a href="manage_attendance.php">Go →</a>
    </div>

    <div class="card">
      <h3>Manage Results</h3>
      <p>Enter subject-wise marks.</p>
      <a href="manage_results.php">Go →</a>
    </div>

    <div class="card">
      <h3>Class Results & Ranking</h3>
      <p>View class-wise results, percentage & rank.</p>
      <a href="class_results.php">Go →</a>
    </div>

    <div class="card">
      <h3>Reports & Analytics</h3>
      <p>Overall performance and insights.</p>
      <a href="reports.php">Go →</a>
    </div>
  </div>
</div>

</body>
</html>
