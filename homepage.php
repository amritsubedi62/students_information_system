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
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f0f2f5;
      height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* Navbar */
    



.navbar {
    width: 100%;
    height: 70px;
    background-color: #d32f2f;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 40px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.logo {
    margin: 0;
    font-size: 22px;
    font-weight: 600;
    color: white;
}

.logout-btn {
    background: none;
    border: none;
    font-size: 26px;
    cursor: pointer;
    color: white;
    transition: 0.3s;
}

.logout-btn:hover {
    color: #ffdddd;
}

    /* Main content */
    .content {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      align-items: center;
      padding: 40px;
      width: 100%;
    }

    .content h2 {
      margin-bottom: 40px;
      color: #333;
    }

    .dashboard {
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
      justify-content: center;
      width: 100%;
      max-width: 1200px;
    }

    .card {
      background-color: #ffffff;
      width: 280px;
      padding: 25px;
      border-radius: 10px;
      text-align: center;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      transition: transform 0.3s, background 0.3s;
    }

    .card:hover {
      transform: translateY(-5px);
      background-color: #ffeaea;
    }

    .card h3 {
      color: #d32f2f;
      margin-bottom: 15px;
    }

    .card p {
      color: #555;
      margin-bottom: 20px;
    }

    .card a {
      display: inline-block;
      padding: 10px 15px;
      background-color: #d32f2f;
      color: white;
      border-radius: 5px;
      text-decoration: none;
      transition: background 0.3s;
    }

    .card a:hover {
      background-color: #9a0007;
    }
  </style>
  <script>
    function logoutConfirm() {
      if(confirm("Are you sure you want to logout?")) {
        window.location.href = "logout.php";
      }
    }
  </script>
</head>
<body>
  <!-- Navbar with Logout -->
  <div class="navbar">
    <h1 class="logo">Student Information System</h1>
    <button class="logout-btn" onclick="logoutConfirm()" title="Logout">🔓</button>
</div>


  <!-- Main content -->
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
        <p>Enter and analyze student marks and grades.</p>
        <a href="manage_results.php">Go →</a>
      </div>

      <div class="card">
        <h3>Reports & Analytics</h3>
        <p>View performance and generate reports.</p>
        <a href="reports.php">Go →</a>
      </div>
    </div>
  </div>
</body>
</html>
