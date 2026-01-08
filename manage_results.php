<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

include "config/db.php";

$teacher_id = $_SESSION['user_id'];

$allowedSubjects = ['Math','Science','Social','English','Nepali'];

/* ===============================
   CLASS CONTEXT (SAME PATTERN)
================================ */
$selectedClass   = $_GET['class'] ?? $_POST['class'] ?? '';
$selectedStudent = $_GET['student_id'] ?? $_POST['student_id'] ?? '';

if ($selectedClass !== '') {
    $selectedClass = intval($selectedClass);

    // Verify teacher is assigned to this class
    $checkClass = mysqli_query($conn,
        "SELECT * FROM teacher_class 
         WHERE teacher_id='$teacher_id' 
         AND class='$selectedClass'"
    );

    if (mysqli_num_rows($checkClass) == 0) {
        die("Unauthorized access to this class.");
    }
}

$message = '';
$editData = null;

/* ===============================
   EDIT RESULT (SECURE)
================================ */
if (isset($_GET['edit'])) {
    $res = mysqli_query($conn,
        "SELECT r.* FROM results r
         JOIN students s ON r.student_id = s.id
         WHERE r.id=".$_GET['edit']."
         AND s.class='$selectedClass'"
    );
    $editData = mysqli_fetch_assoc($res);
}

/* ===============================
   DELETE RESULT (SECURE)
================================ */
if (isset($_GET['delete'])) {
    mysqli_query($conn,
        "DELETE r FROM results r
         JOIN students s ON r.student_id = s.id
         WHERE r.id=".$_GET['delete']."
         AND s.class='$selectedClass'"
    );
    header("Location: manage_results.php?class=$selectedClass&student_id=$selectedStudent");
    exit;
}

/* ===============================
   SAVE RESULT
================================ */
if (isset($_POST['save_result'])) {

    $student_id = $_POST['student_id'];
    $subject    = trim($_POST['subject']);
    $marks      = $_POST['marks'];
    $id         = $_POST['id'];

    // Ensure student belongs to teacher's class
    $stuCheck = mysqli_query($conn,
        "SELECT * FROM students 
         WHERE id='$student_id' 
         AND class='$selectedClass'"
    );

    if (mysqli_num_rows($stuCheck) == 0) {
        die("Invalid student access.");
    }

    if (!in_array($subject, $allowedSubjects)) {
        $message = 'You can only enter marks for the 5 subjects: '.implode(", ", $allowedSubjects);
    } elseif ($marks < 0 || $marks > 100) {
        $message = 'Marks must be between 0 and 100';
    } else {

        $check = mysqli_query($conn,
            "SELECT * FROM results 
             WHERE student_id='$student_id' 
             AND subject='$subject'
             AND id!='$id'"
        );

        if (mysqli_num_rows($check) > 0) {
            $message = 'This subject result already exists for this student';
        } else {
            if ($id == "") {
                mysqli_query($conn,
                    "INSERT INTO results (student_id, subject, marks)
                     VALUES ('$student_id','$subject','$marks')"
                );
            } else {
                mysqli_query($conn,
                    "UPDATE results 
                     SET subject='$subject', marks='$marks' 
                     WHERE id='$id'"
                );
            }
            header("Location: manage_results.php?class=$selectedClass&student_id=$student_id");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Manage Results</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<a href="homepage.php"
   style="position:absolute; top:15px; left:15px;
          font-size:24px; text-decoration:none; color:white;">←</a>

<?php include "includes/navbar.php"; ?>

<div class="content">
<h2>Manage Results</h2>

<div class="dashboard">

<!-- ================= ADD / UPDATE RESULT ================= -->
<div class="card">
<h3><?= $editData ? "Update Result" : "Add Result" ?></h3>

<form method="POST">

<input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">

<select name="class" onchange="this.form.submit()" required>
<option value="">Select Class</option>
<?php
$cls = mysqli_query($conn,
    "SELECT class FROM teacher_class 
     WHERE teacher_id='$teacher_id'
     ORDER BY class"
);
while ($c = mysqli_fetch_assoc($cls)) {
    $sel = ($selectedClass == $c['class']) ? "selected" : "";
    echo "<option value='{$c['class']}' $sel>Class {$c['class']}</option>";
}
?>
</select>

<?php if ($selectedClass) { ?>
<select name="student_id" onchange="this.form.submit()" required>
<option value="">Select Student</option>
<?php
$students = mysqli_query($conn,
    "SELECT * FROM students 
     WHERE class='$selectedClass' 
     ORDER BY roll_no"
);
while ($s = mysqli_fetch_assoc($students)) {
    $sel = ($selectedStudent == $s['id']) ? "selected" : "";
    echo "<option value='{$s['id']}' $sel>
          Roll {$s['roll_no']} - {$s['name']}
          </option>";
}
?>
</select>
<?php } ?>

<?php if ($selectedStudent) { ?>

<select name="subject" required>
<option value="">Select Subject</option>
<?php
foreach ($allowedSubjects as $sub) {
    $sel = ($editData['subject'] ?? '') == $sub ? "selected" : "";
    echo "<option value='$sub' $sel>$sub</option>";
}
?>
</select>

<input type="number" name="marks" min="0" max="100"
placeholder="Marks"
value="<?= $editData['marks'] ?? '' ?>" required>

<button name="save_result">
<?= $editData ? "Update Result" : "Add Result" ?>
</button>

<?php } else { ?>
<p style="text-align:center; opacity:0.7;">
Select a student to add results
</p>
<?php } ?>

</form>
</div>

<!-- ================= RESULT LIST ================= -->
<div class="card" style="width:500px;">
<h3>Result List</h3>

<table>
<tr>
<th>Subject</th>
<th>Marks</th>
<th>Action</th>
</tr>

<?php
if ($selectedStudent) {

$q = mysqli_query($conn,
    "SELECT * FROM results 
     WHERE student_id='$selectedStudent'
     ORDER BY FIELD(subject,'Math','Science','Social','English','Nepali')"
);

if (mysqli_num_rows($q) == 0) {
    echo "<tr><td colspan='3' style='text-align:center;'>No results added</td></tr>";
}

while ($r = mysqli_fetch_assoc($q)) {
echo "<tr>
<td>{$r['subject']}</td>
<td>{$r['marks']}</td>
<td>
<a href='?edit={$r['id']}&class=$selectedClass&student_id=$selectedStudent'>Edit</a> |
<a class='delete'
href='?delete={$r['id']}&class=$selectedClass&student_id=$selectedStudent'
onclick='return confirm(\"Delete this result?\")'>Delete</a>
</td>
</tr>";
}

} else {
echo "<tr><td colspan='3' style='text-align:center;'>
Select a student to view results
</td></tr>";
}
?>
</table>
</div>

</div>
</div>

<?php if (!empty($message)): ?>
<script>alert("<?= $message ?>");</script>
<?php endif; ?>

</body>
</html>
