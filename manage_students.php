<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

include "config/db.php";

if (isset($_POST['add_student'])) {
    $name = $_POST['name'];
    $class = $_POST['class'];
    $roll = $_POST['roll_no'];

    // Validate class
    if ($class < 1 || $class > 10) {
        echo "<script>alert('Class must be between 1 and 10');</script>";
    } else {
        // Check if roll number already exists in this class
        $check = mysqli_query($conn, "SELECT * FROM students WHERE class='$class' AND roll_no='$roll'");
        if (mysqli_num_rows($check) > 0) {
            echo "<script>alert('This roll number already exists for class $class');</script>";
        } else {
            mysqli_query($conn,
                "INSERT INTO students (name, class, roll_no)
                 VALUES ('$name','$class','$roll')"
            );
            echo "<script>alert('Student added successfully');</script>";
        }
    }
}



/* Delete student */
if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM students WHERE id=".$_GET['delete']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Students - Student Information System</title>
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
  font-size: 22px;
  font-weight: 600;
  color: white;
}

.content {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40px;
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
  max-width: 1200px;
}

.card {
  background-color: #ffffff;
  width: 300px;
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

.card input {
  width: 100%;
  padding: 10px;
  margin-bottom: 10px;
  border-radius: 6px;
  border: 1px solid #ccc;
}

.card button {
  width: 100%;
  padding: 10px;
  background-color: #d32f2f;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.card button:hover {
  background-color: #9a0007;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

table th, table td {
  padding: 8px;
  border-bottom: 1px solid #ddd;
  font-size: 14px;
}

table th {
  background-color: #f5f5f5;
}

.delete {
  color: #d32f2f;
  text-decoration: none;
}
</style>
</head>

<body>

<!-- Navbar -->
<div class="navbar">
  <h1 class="logo">Student Information System</h1>
</div>

<!-- Main content -->
<div class="content">
  <h2>Manage Students 👩‍🏫</h2>

  <div class="dashboard">

    <!-- ADD STUDENT CARD -->
    <div class="card">
      <h3>Add Student</h3>
      <p>Enter student details</p>

      <form method="POST">
        <input type="text" name="name" placeholder="Student Name" required>
        <input type="number" name="class" placeholder="Class" min="1" max="10" required>
        <input type="number" name="roll_no" placeholder="Roll No" required>
        <button name="add_student">Add Student</button>
      </form>
    </div>

    <!-- STUDENT LIST CARD -->
    <div class="card" style="width:450px;">
      <h3>Student List</h3>
      <p>Registered students</p>

      <table>
        <tr>
          <th>Name</th>
          <th>Class</th>
          <th>Roll</th>
          <th>Action</th>
        </tr>

        <?php
        $q = mysqli_query($conn,"SELECT * FROM students");
        while ($s = mysqli_fetch_assoc($q)) {
          echo "<tr>
            <td>{$s['name']}</td>
            <td>{$s['class']}</td>
            <td>{$s['roll_no']}</td>
            <td>
              <a class='delete' href='?delete={$s['id']}'
              onclick='return confirm(\"Delete this student?\")'>Delete</a>
            </td>
          </tr>";
        }
        ?>
      </table>
    </div>

  </div>
</div>

</body>
</html>
