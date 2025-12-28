<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

include "config/db.php";

// Allowed subjects
$allowedSubjects = ['Math','Science','Social','English','Nepali'];

// Get selected class & student from GET or POST (persist selection)
$selectedClass   = $_GET['class'] ?? $_POST['class'] ?? '';
$selectedStudent = $_GET['student_id'] ?? $_POST['student_id'] ?? '';

$message = '';
$editData = null;

/* EDIT */
if (isset($_GET['edit'])) {
    $res = mysqli_query($conn, "SELECT * FROM results WHERE id=".$_GET['edit']);
    $editData = mysqli_fetch_assoc($res);
}

/* DELETE */
if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM results WHERE id=".$_GET['delete']);
    header("Location: manage_results.php?class=$selectedClass&student_id=$selectedStudent");
    exit;
}

/* SAVE / UPDATE RESULT */
if (isset($_POST['save_result'])) {

    $student_id = $_POST['student_id'];
    $subject    = trim($_POST['subject']);
    $marks      = $_POST['marks'];
    $id         = $_POST['id'];

    // Check subject is allowed
    if (!in_array($subject, $allowedSubjects)) {
        $message = 'You can only enter marks for the 5 subjects: '.implode(", ", $allowedSubjects);
    } elseif ($marks < 0 || $marks > 100) {
        $message = 'Marks must be between 0 and 100';
    } else {
        // Prevent duplicate subject for same student
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
                    "UPDATE results SET subject='$subject', marks='$marks' WHERE id='$id'"
                );
            }
            // Success → redirect to persist selection
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

<a href="homepage.php" style="position:absolute; top:15px; left:15px; font-size:24px; text-decoration:none; color:white;">←</a>

<?php include "includes/navbar.php"; ?>

<div class="content">
<h2>Manage Results</h2>

<div class="dashboard">

<!-- ADD / UPDATE RESULT -->
<div class="card">
<h3><?= $editData ? "Update Result" : "Add Result" ?></h3>

<form method="POST">

<input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">

<!-- SELECT CLASS -->
<select name="class" onchange="this.form.submit()" required>
<option value="">Select Class</option>
<?php
for ($i = 1; $i <= 10; $i++) {
    $sel = ($selectedClass == $i) ? "selected" : "";
    echo "<option value='$i' $sel>Class $i</option>";
}
?>
</select>

<!-- SELECT STUDENT -->
<?php if ($selectedClass) { ?>
<select name="student_id" onchange="this.form.submit()" required>
<option value="">Select Student</option>
<?php
$students = mysqli_query($conn,
    "SELECT * FROM students WHERE class='$selectedClass' ORDER BY roll_no"
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

<!-- SUBJECT -->
<select name="subject" required>
<option value="">Select Subject</option>
<?php
foreach($allowedSubjects as $sub) {
    $sel = ($editData['subject'] == $sub) ? "selected" : "";
    echo "<option value='$sub' $sel>$sub</option>";
}
?>
</select>

<!-- MARKS -->
<input type="number" name="marks" min="0" max="100"
placeholder="Marks"
value="<?= $editData['marks'] ?? '' ?>" required>

<button name="save_result">
<?= $editData ? "Update Result" : "Add Result" ?>
</button>

<?php } else { ?>
<p style="text-align:center; opacity:0.7;">Select a student to add results</p>
<?php } ?>

</form>
</div>

<!-- RESULT LIST -->
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
<a class='delete' href='?delete={$r['id']}&class=$selectedClass&student_id=$selectedStudent'
onclick='return confirm(\"Delete this result?\")'>Delete</a>
</td>
</tr>";
}

} else {
echo "<tr><td colspan='3' style='text-align:center;'>Select a student to view results</td></tr>";
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
