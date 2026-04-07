<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

include "config/db.php";

$teacher_id = $_SESSION['user_id'];

/* ===== GET ALL ASSIGNED CLASSES ===== */
$classesResult = mysqli_query($conn,
    "SELECT class FROM teacher_class WHERE teacher_id='$teacher_id'"
);

$assignedClasses = [];
while ($row = mysqli_fetch_assoc($classesResult)) {
    $assignedClasses[] = $row['class'];
}

if (empty($assignedClasses)) {
    die("No class assigned to this teacher.");
}

/* ===== SELECT CURRENT CLASS ===== */
$assignedClass = isset($_GET['class']) ? intval($_GET['class']) : $assignedClasses[0];

if (!in_array($assignedClass, $assignedClasses)) {
    die("Unauthorized access to this class.");
}

/* ================= SAVE STUDENT ================= */
if (isset($_POST['save_student'])) {

    $name  = trim($_POST['name']);
    $roll  = intval($_POST['roll_no']);
    $id    = $_POST['id'];

    $class = intval($_POST['class']);

    // SECURITY CHECK
    if (!in_array($class, $assignedClasses)) {
        die("Unauthorized class selection.");
    }

    if ($name == "" || strlen($name) < 3) {
        echo "<script>alert('Student name must be at least 3 characters');</script>";
    }
    elseif ($roll < 1 || $roll > 100) {
        echo "<script>alert('Roll number must be between 1 and 100');</script>";
    }
    elseif ($class < 1 || $class > 10) {
        echo "<script>alert('Class must be between 1 and 10');</script>";
    }
    else {

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

/* ================= DELETE ================= */
if (isset($_GET['delete'])) {
    mysqli_query($conn,
        "DELETE FROM students 
         WHERE id=".$_GET['delete']." 
         AND class='$assignedClass'"
    );
}

/* ================= EDIT ================= */
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

<?php include __DIR__ . "/includes/navbar.php"; ?>

<div class="content">

<!-- CLASS SWITCHER -->
<div style="margin-bottom:15px;">
<?php foreach($assignedClasses as $c): ?>
    <a href="?class=<?= $c ?>"
       style="margin-right:10px; padding:5px 10px; background:#eee; border-radius:5px; text-decoration:none;">
       Class <?= $c ?>
    </a>
<?php endforeach; ?>
</div>

<h2>Manage Students (Class <?= $assignedClass ?>)</h2>

<div class="dashboard">

<!-- ADD / UPDATE -->
<div class="card">
<h3><?= $editData ? "Update Student" : "Add Student" ?></h3>

<form method="POST">
<input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">

<input type="text" name="name"
placeholder="Student Name"
value="<?= $editData['name'] ?? '' ?>" required>

<!-- DROPDOWN CLASS -->
<label></label>
<select name="class" required
        style="padding:10px; width:100%; border-radius:6px; border:1px solid #ccc; margin-bottom:10px;">
    
    <?php foreach($assignedClasses as $c): ?>
        <option value="<?= $c ?>" <?= ($assignedClass == $c ? 'selected' : '') ?>>
            Class <?= $c ?>
        </option>
    <?php endforeach; ?>

</select>

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
<td>{$assignedClass}</td>
<td>{$s['roll_no']}</td>
<td>
<a href='?class=$assignedClass&edit={$s['id']}'>Edit</a> |
<a class='delete' href='?class=$assignedClass&delete={$s['id']}'
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