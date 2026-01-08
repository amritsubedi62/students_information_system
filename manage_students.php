<?php
session_start();

/* Teacher-only access */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

include "config/db.php";

$teacher_id = $_SESSION['user_id'];

/* ===============================
   FETCH ASSIGNED CLASS (NEW)
================================ */


/* Class must come from URL */
if (!isset($_GET['class'])) {
    die("Class not specified.");
}

$assignedClass = intval($_GET['class']);

/* Verify teacher is assigned to this class */
$checkClass = mysqli_query($conn,
    "SELECT * FROM teacher_class 
     WHERE teacher_id='$teacher_id' 
     AND class='$assignedClass'"
);

if (mysqli_num_rows($checkClass) == 0) {
    die("Unauthorized access to this class.");
}


/* ===============================
   SAVE / UPDATE STUDENT
================================ */
if (isset($_POST['save_student'])) {

    $name  = $_POST['name'];
    $roll  = $_POST['roll_no'];
    $id    = $_POST['id'];

    // FORCE class (security)
    $class = $assignedClass;

    if ($class < 1 || $class > 10) {
        echo "<script>alert('Class must be between 1 and 10');</script>";
    } else {

        if ($id == "") {
            $check = mysqli_query($conn,
                "SELECT * FROM students 
                 WHERE class='$class' AND roll_no='$roll'"
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
                     roll_no='$roll'
                     WHERE id='$id' AND class='$class'"
                );
                echo "<script>alert('Student updated successfully');</script>";
            }
        }
    }
}

/* ===============================
   DELETE STUDENT (SECURE)
================================ */
if (isset($_GET['delete'])) {
    mysqli_query($conn,
        "DELETE FROM students 
         WHERE id=".$_GET['delete']." 
         AND class='$assignedClass'"
    );
}

/* ===============================
   EDIT STUDENT (SECURE)
================================ */
$editData = null;
if (isset($_GET['edit'])) {
    $res = mysqli_query($conn,
        "SELECT * FROM students 
         WHERE id=".$_GET['edit']." 
         AND class='$assignedClass'"
    );
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

<a href="homepage.php" style="position:absolute; top:15px; left:15px; font-size:24px; text-decoration:none; color:white;">←</a>

<?php include "includes/navbar.php"; ?>

<div class="content">
<h2>Manage Students (Class <?= $assignedClass ?>)</h2>

<div class="dashboard">

<!-- ADD / UPDATE STUDENT -->
<div class="card">
<h3><?= $editData ? "Update Student" : "Add Student" ?></h3>

<form method="POST">
<input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">

<input type="text" name="name"
placeholder="Student Name"
value="<?= $editData['name'] ?? '' ?>" required>

<!-- CLASS LOCKED -->
<input type="number" name="class"
value="<?= $assignedClass ?>"
readonly>

<input type="number" name="roll_no"
placeholder="Roll No"
value="<?= $editData['roll_no'] ?? '' ?>" required>

<button name="save_student">
<?= $editData ? "Update Student" : "Add Student" ?>
</button>
</form>
</div>

<!-- STUDENT LIST -->
<div class="card" style="width:450px;">
<h3>Student List (Class <?= $assignedClass ?>)</h3>

<table>
<tr>
<th>Name</th>
<th>Class</th>
<th>Roll</th>
<th>Action</th>
</tr>

<?php
$q = mysqli_query($conn,
    "SELECT * FROM students WHERE class='$assignedClass'"
);

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
