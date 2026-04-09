<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "student_information_system");
if ($conn->connect_error) {
    die("Database Connection Failed");
}

$teacher_id = $_SESSION['user_id'];
$today = date('Y-m-d');

/* ================= DAILY ================= */
$class = $_POST['class'] ?? $_GET['class'] ?? '';

if ($class !== '') {
    $class = intval($class);

    $check = $conn->query("
        SELECT * FROM teacher_class
        WHERE teacher_id='$teacher_id'
        AND class='$class'
    ");

    if ($check->num_rows == 0) {
        die("Unauthorized access to this class.");
    }
}
/* ================= QUICK SORT (OWN ALGORITHM) ================= */
function quickSortStudents($array) {

    if (count($array) <= 1) {
        return $array;
    }

    $pivot = $array[0]; // choose first element as pivot
    $left = [];
    $right = [];

    for ($i = 1; $i < count($array); $i++) {

        if ($array[$i]['roll_no'] < $pivot['roll_no']) {
            $left[] = $array[$i];
        } else {
            $right[] = $array[$i];
        }
    }

    return array_merge(
        quickSortStudents($left),
        [$pivot],
        quickSortStudents($right)
    );
}

/* ================= DAILY STUDENTS ================= */
$students = [];

if ($class != '') {
    $res = $conn->query("
    SELECT * FROM students 
    WHERE class='$class'
");

    while ($row = $res->fetch_assoc()) {
        $students[] = $row;
    }
    $students = quickSortStudents($students);
}

/* ===================================================
   MONTHLY ATTENDANCE - CLEAN LOGIC (FIXED)
   =================================================== */

$monthly_class = $_POST['monthly_class'] ?? '';
$student_id    = $_POST['student_id'] ?? '';
$month         = $_POST['month'] ?? '';
$total_days    = $_POST['total_days'] ?? '';
$present_days  = $_POST['present_days'] ?? '';

/* STEP 1: ONLY LOAD MODE (class selected) */
$isLoad = !empty($monthly_class) && empty($student_id);

/* STEP 2: ONLY SAVE MODE (all fields exist) */
$isSave = !empty($student_id) && !empty($month) && !empty($total_days) && !empty($present_days);

/* ================= LOAD STUDENTS ================= */
$monthly_students = [];

if ($isLoad) {
    $monthly_class = intval($monthly_class);

    $res = $conn->query("
        SELECT id, name, roll_no 
        FROM students 
        WHERE class='$monthly_class'
        ORDER BY roll_no
    ");

    while ($row = $res->fetch_assoc()) {
        $monthly_students[] = $row;
    }
}

/* ================= SAVE MONTHLY ATTENDANCE ================= */
if ($isSave) {

    $student_id   = intval($student_id);
    $total_days   = intval($total_days);
    $present_days = intval($present_days);

    /* VALIDATIONS */
    if ($student_id <= 0 || empty($month)) {
        die("Invalid data.");
    }

    if ($total_days < 1 || $total_days > 31) {
        die("Total days must be between 1 and 31.");
    }

    if ($present_days < 0 || $present_days > $total_days) {
        die("Present days cannot be greater than total days.");
    }

    $maxMonth = date('Y-m', strtotime('-1 month'));
    if ($month > $maxMonth) {
        die("You can only add past months.");
    }

    /* DUPLICATE CHECK */
    $check = $conn->prepare("
        SELECT id FROM attendance_monthly 
        WHERE student_id=? AND month=?
    ");
    $check->bind_param("is", $student_id, $month);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        die("Monthly attendance already exists.");
    }

    /* INSERT */
    $insert = $conn->prepare("
        INSERT INTO attendance_monthly 
        (student_id, month, total_days, present_days)
        VALUES (?, ?, ?, ?)
    ");

    $insert->bind_param("isii", $student_id, $month, $total_days, $present_days);

    if ($insert->execute()) {
        header("Location: manage_attendance.php?success=1");
        exit;
    } else {
        die("Database error: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Attendance</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<style>
body 
{ margin:0; 
padding:0; 
background:#f2f3f5;
 }
h2 { text-align:center; margin:20px 0; }
.main-container { width:95%; margin:auto; display:flex; gap:30px; }
.card { background:#fff; width:45%; padding:25px; border-radius:12px; }
input, select { width:100%; padding:10px; margin-bottom:15px; }
table { width:100%; border-collapse:collapse; }
th, td { padding:10px; text-align:center; }
button { width:100%; padding:12px; background:#e53935; color:#fff; border:none; }
</style>

<body>
<a href="homepage.php" style="position:absolute; top:15px; left:15px; font-size:24px; text-decoration:none; color:white;">←</a>
<?php include "includes/navbar.php"; ?>

<div class="content">
<h2>Manage Attendance</h2>

<!-- ================= DAILY ATTENDANCE ================= -->
<form method="post" action="save_attendance.php">

<div class="main-container">

<div class="card">
    <h3>Attendance Details</h3>

    <input type="date" name="attendance_date" value="<?= $today ?>" min="<?= $today ?>" max="<?= $today ?>" required>

    <select name="class" required>
        <option value="">-- Select Class --</option>
        <?php
        $cls = $conn->query("
            SELECT class FROM teacher_class
            WHERE teacher_id='$teacher_id'
        ");

        while ($c = $cls->fetch_assoc()) {
            $selected = ($class == $c['class']) ? "selected" : "";
            echo "<option value='{$c['class']}' $selected>
                    Class {$c['class']}
                  </option>";
        }
        ?>
    </select>

    <button type="submit" formaction="manage_attendance.php">
        Load Students
    </button>
</div>

<div class="card">
    <h3>Student Attendance</h3>

    <?php if ($class != '') { ?>
    <table>
        <tr>
            <th>Roll No</th>
            <th>Name</th>
            <th>Status</th>
        </tr>

        <?php foreach ($students as $s) { ?>
        <tr>
            <td><?= $s['roll_no'] ?></td>
            <td><?= $s['name'] ?></td>
            <td>
                <input type="radio" name="status[<?= $s['id'] ?>]" value="Present" checked> Present
                <input type="radio" name="status[<?= $s['id'] ?>]" value="Absent"> Absent
            </td>
        </tr>
        <?php } ?>
    </table>

    <button type="submit">Save Daily Attendance</button>

    <?php } else { ?>
        <p>Please select a class</p>
    <?php } ?>
</div>

</div>
</form>

<form method="post" action="manage_attendance.php">

<div class="main-container" style="margin-top:40px;">

<div class="card">

<h3>Monthly Attendance</h3>

<select name="monthly_class" required onchange="this.form.submit()">
    <option value="">-- Select Class --</option>
    <?php
    $cls2 = $conn->query("
        SELECT class FROM teacher_class
        WHERE teacher_id='$teacher_id'
    ");

    while ($c = $cls2->fetch_assoc()) {
        $selected = ($monthly_class == $c['class']) ? "selected" : "";
        echo "<option value='{$c['class']}' $selected>
                Class {$c['class']}
              </option>";
    }
    ?>
</select>

<!-- STUDENT LIST -->
<select name="student_id" required>
    <option value="">-- Select Student --</option>
    <?php foreach ($monthly_students as $st) { ?>
        <option value="<?= $st['id'] ?>">
            <?= $st['name'] ?> (Roll <?= $st['roll_no'] ?>)
        </option>
    <?php } ?>
</select>

<?php $maxMonth = date('Y-m', strtotime('-1 month')); ?>

<input type="month" name="month" required max="<?= $maxMonth ?>">

<input type="number" name="total_days" placeholder="Total Days" min="1" max="31" required>

<input type="number" name="present_days" placeholder="Present Days" min="0" required>

<button type="submit">Save Monthly Attendance</button>

</div>
</div>
</form>

</div>

</body>
</html>