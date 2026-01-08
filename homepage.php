<?php
session_start();
include("config/db.php");

// Only teachers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];

// Fetch teacher info
$sql = "SELECT * FROM users WHERE id='$teacher_id'";
$result = mysqli_query($conn, $sql);
$teacher = mysqli_fetch_assoc($result);

// Check approval
$approved = ($teacher['status'] === 'approved');

// Fetch classes assigned to this teacher
$classes = [];
if ($approved) {
    $class_sql = "SELECT class FROM teacher_class WHERE teacher_id='$teacher_id'";
    $class_result = mysqli_query($conn, $class_sql);
    while($row = mysqli_fetch_assoc($class_result)){
        $classes[] = $row['class'];
    }
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
  <h2>Welcome, <?= htmlspecialchars($teacher['username']) ?> 👩‍🏫</h2>

  <?php if(!$approved): ?>
    <div class="alert" style="color:#d32f2f; font-weight:bold;">
      Your account is pending admin approval. You cannot manage students, attendance, or results until approved.
    </div>
  <?php elseif(empty($classes)): ?>
    <div class="alert" style="color:#d32f2f; font-weight:bold;">
      You have not been assigned any class yet. Please wait for admin to assign your class.
    </div>
  <?php else: ?>

    <div class="dashboard">

      <!-- Manage Students: one card per class -->
      <?php foreach($classes as $class): ?>
        <div class="card">
          <h3>Manage Students (Class <?= $class ?>)</h3>
          <p>Add, edit, or remove student details for this class only.</p>
          <a href="manage_students.php?class=<?= $class ?>">Go →</a>
        </div>
      <?php endforeach; ?>

      <!-- Other cards: show only once -->
      <div class="card">
        <h3>Manage Attendance</h3>
        <p>Record and view attendance of students in your assigned classes.</p>
        <a href="manage_attendance.php">Go →</a>
      </div>

      <div class="card">
        <h3>Manage Results</h3>
        <p>Enter subject-wise marks for students in your assigned classes.</p>
        <a href="manage_results.php">Go →</a>
      </div>

      <div class="card">
        <h3>Class Results & Ranking</h3>
        <p>View class-wise results, percentage & rank for your assigned classes.</p>
        <a href="class_results.php">Go →</a>
      </div>

      <div class="card">
        <h3>Reports & Analytics</h3>
        <p>Overall performance insights for your assigned classes.</p>
        <a href="reports.php">Go →</a>
      </div>

    </div>

  <?php endif; ?>

</div>

</body>
</html>
