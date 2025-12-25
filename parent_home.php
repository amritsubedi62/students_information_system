<?php
session_start();
include("config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'parent') {
    header("Location: login.php");
    exit;
}


$parent_id = $_SESSION['user_id'];

$student_sql = "SELECT * FROM students WHERE parent_id = '$parent_id'";
$student_result = mysqli_query($conn, $student_sql);


if (mysqli_num_rows($student_result) == 0) {
    $student = null;
} else {
    $student = mysqli_fetch_assoc($student_result);
    $student_id = $student['id'];

    $results_sql = "SELECT * FROM results WHERE student_id='$student_id'";
    $results = mysqli_query($conn, $results_sql);

    $attendance_sql = "SELECT * FROM attendance WHERE student_id='$student_id' ORDER BY date DESC";
    $attendance = mysqli_query($conn, $attendance_sql);
 
    $performance_sql = "SELECT * FROM performance WHERE student_id='$student_id'";
    $performance = mysqli_query($conn, $performance_sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Parent Dashboard</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    table {
      width: 100%;
      border-collapse: collapse;
      margin: 10px 0;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: center;
    }
    th {
      background-color: #b22222;
      color: white;
    }
    h2 {
      color: #b22222;
      margin-top: 20px;
    }
    .logout-btn {
      background-color: #333;
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 15px;
    }
    .logout-btn:hover {
      background-color: #555;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Welcome, Parent!</h1>

    <?php if (!$student): ?>
      <p>No child registered under your account yet.</p>
    <?php else: ?>
      <h2>Child Details</h2>
      <table>
        <tr><th>Name</th><td><?php echo $student['name']; ?></td></tr>
        <tr><th>Class</th><td><?php echo $student['class']; ?></td></tr>
        <tr><th>Roll No</th><td><?php echo $student['roll_no']; ?></td></tr>
      </table>

      <h2>Results</h2>
      <?php if (mysqli_num_rows($results) == 0): ?>
        <p>No results available yet.</p>
      <?php else: ?>
        <table>
          <tr>
            <th>Subject</th>
            <th>Marks</th>
            <th>Grade</th>
          </tr>
          <?php while($row = mysqli_fetch_assoc($results)): ?>
            <tr>
              <td><?php echo $row['subject']; ?></td>
              <td><?php echo $row['marks']; ?></td>
              <td><?php echo $row['grade']; ?></td>
            </tr>
          <?php endwhile; ?>
        </table>
      <?php endif; ?>

      <h2>Attendance</h2>
      <?php if (mysqli_num_rows($attendance) == 0): ?>
        <p>No attendance records yet.</p>
      <?php else: ?>
        <table>
          <tr>
            <th>Date</th>
            <th>Status</th>
          </tr>
          <?php while($row = mysqli_fetch_assoc($attendance)): ?>
            <tr>
              <td><?php echo $row['date']; ?></td>
              <td><?php echo $row['status']; ?></td>
            </tr>
          <?php endwhile; ?>
        </table>
      <?php endif; ?>

      <h2>Performance Remarks</h2>
      <?php if (mysqli_num_rows($performance) == 0): ?>
        <p>No performance records yet.</p>
      <?php else: ?>
        <table>
          <tr>
            <th>Remarks</th>
            <th>Rating</th>
          </tr>
          <?php while($row = mysqli_fetch_assoc($performance)): ?>
            <tr>
              <td><?php echo $row['remarks']; ?></td>
              <td><?php echo $row['rating']; ?>/10</td>
            </tr>
          <?php endwhile; ?>
        </table>
      <?php endif; ?>
    <?php endif; ?>

    <form action="logout.php" method="POST">
      <button type="submit" class="logout-btn">Logout</button>
    </form>
  </div>
</body>
</html>
