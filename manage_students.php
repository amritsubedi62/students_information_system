<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

include "config/db.php";

if (isset($_POST['save_student'])) {

    $name  = $_POST['name'];
    $class = $_POST['class'];
    $roll  = $_POST['roll_no'];
    $id    = $_POST['id'];

    if ($class < 1 || $class > 10) {
        echo "<script>alert('Class must be between 1 and 10');</script>";
    } else {

        if ($id == "") {
            $check = mysqli_query($conn,
                "SELECT * FROM students WHERE class='$class' AND roll_no='$roll'"
            );
            if (mysqli_num_rows($check) > 0) {
                echo "<script>alert('Roll number already exists in this class');</script>";
            } else {
                mysqli_query($conn,
                    "INSERT INTO students (name, class, roll_no)
                     VALUES ('$name','$class','$roll')"
                );
                echo "<script>alert('Student added successfully');</script>";
            }
        } else {
      
          $check = mysqli_query($conn,
              "SELECT * FROM students 
               WHERE class='$class' 
               AND roll_no='$roll' 
               AND id!='$id'"
          );
      
          if (mysqli_num_rows($check) > 0) {
              echo "<script>alert('Roll number already exists in this class');</script>";
          } else {
              mysqli_query($conn,
                  "UPDATE students SET
                   name='$name',
                   class='$class',
                   roll_no='$roll'
                   WHERE id='$id'"
              );
              echo "<script>alert('Student updated successfully');</script>";
          }
      }
    }
  }


if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM students WHERE id=".$_GET['delete']);
}

$editData = null;
if (isset($_GET['edit'])) {
    $res = mysqli_query($conn, "SELECT * FROM students WHERE id=".$_GET['edit']);
    $editData = mysqli_fetch_assoc($res);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Students</title>
<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>
<?php include "includes/navbar.php"; ?>

<div class="content">
<h2>Manage Students </h2>

<div class="dashboard">


<div class="card">
<h3><?= $editData ? "Update Student" : "Add Student" ?></h3>

<form method="POST">
<input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">

<input type="text" name="name"
placeholder="Student Name"
value="<?= $editData['name'] ?? '' ?>" required>

<input type="number" name="class" min="1" max="10"
placeholder="Class"
value="<?= $editData['class'] ?? '' ?>" required>

<input type="number" name="roll_no"
placeholder="Roll No"
value="<?= $editData['roll_no'] ?? '' ?>" required>

<button name="save_student">
<?= $editData ? "Update Student" : "Add Student" ?>
</button>
</form>
</div>

<div class="card" style="width:450px;">
<h3>Student List</h3>

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
<a href='?edit={$s['id']}'>Edit</a> |
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
