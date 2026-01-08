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

/* ===============================
   CLASS CONTEXT (IMPORTANT)
================================ */
$class = $_POST['class'] ?? $_GET['class'] ?? '';

if ($class !== '') {
    $class = intval($class);

    // Verify teacher is assigned to this class
    $check = $conn->query("
        SELECT * FROM teacher_class
        WHERE teacher_id='$teacher_id'
        AND class='$class'
    ");

    if ($check->num_rows == 0) {
        die("Unauthorized access to this class.");
    }
}

/* ===============================
   FETCH STUDENTS OF THIS CLASS
================================ */
$students = [];
if ($class != '') {
    $res = $conn->query("SELECT * FROM students WHERE class='$class'");
    while ($row = $res->fetch_assoc()) {
        $students[] = $row;
    }

    /* ===== Quick Sort by roll_no (UNCHANGED) ===== */
    function quickSort(&$arr, $low, $high) {
        if ($low < $high) {
            $pi = partition($arr, $low, $high);
            quickSort($arr, $low, $pi - 1);
            quickSort($arr, $pi + 1, $high);
        }
    }

    function partition(&$arr, $low, $high) {
        $pivot = $arr[$high]['roll_no'];
        $i = $low - 1;

        for ($j = $low; $j < $high; $j++) {
            if ($arr[$j]['roll_no'] < $pivot) {
                $i++;
                $temp = $arr[$i];
                $arr[$i] = $arr[$j];
                $arr[$j] = $temp;
            }
        }

        $temp = $arr[$i + 1];
        $arr[$i + 1] = $arr[$high];
        $arr[$high] = $temp;

        return $i + 1;
    }

    if (count($students) > 0) {
        quickSort($students, 0, count($students) - 1);
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
/* === UNCHANGED STYLES === */
body { margin:0; padding:0; background:#f2f3f5; }
h2 { text-align:center; margin:20px 0; font-weight:600; }
.main-container { width:95%; margin:auto; display:flex; gap:30px; justify-content:center; }
.card { background:#fff; width:45%; padding:25px; border-radius:12px;
        box-shadow:0 8px 25px rgba(0,0,0,0.08); }
.card h3 { color:#e53935; text-align:center; }
.card p { text-align:center; color:#777; font-size:14px; }
input, select { width:100%; padding:10px; margin-bottom:15px;
                border-radius:6px; border:1px solid #ccc; }
table { width:100%; border-collapse:collapse; }
th { background:#f5f5f5; padding:10px; }
td { padding:10px; text-align:center; border-bottom:1px solid #eee; }
button { width:100%; background:#e53935; color:#fff; padding:12px;
         border:none; border-radius:6px; font-size:15px; cursor:pointer; }
button:hover { background:#c62828; }
@media(max-width:900px) {
    .main-container { flex-direction:column; }
    .card { width:90%; }
}
</style>

<body>

<a href="homepage.php"
   style="position:absolute; top:15px; left:15px;
          font-size:24px; text-decoration:none; color:white;">←</a>

<?php include "includes/navbar.php"; ?>

<div class="content">
<h2>Manage Attendance</h2>

<form method="post" action="save_attendance.php">

<div class="main-container">

<!-- ================= ATTENDANCE DETAILS ================= -->
<div class="card">
    <h3>Attendance Details</h3>

    <input type="date"
           name="attendance_date"
           value="<?= $today ?>"
           min="<?= $today ?>"
           max="<?= $today ?>"
           required>

    <!-- CLASS SELECT (ONLY ASSIGNED CLASSES) -->
    <select name="class" required>
        <option value="">-- Select Class --</option>
        <?php
        $cls = $conn->query("
            SELECT class FROM teacher_class
            WHERE teacher_id='$teacher_id'
            ORDER BY class DESC
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

<!-- ================= STUDENT LIST ================= -->
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
                <input type="radio"
                       name="status[<?= $s['id'] ?>]"
                       value="Present" checked> Present
                <input type="radio"
                       name="status[<?= $s['id'] ?>]"
                       value="Absent"> Absent
            </td>
        </tr>
        <?php } ?>
    </table>

    <br>
    <button type="submit">Save Daily Attendance</button>

    <?php } else { ?>
        <p>Please select a class to view students</p>
    <?php } ?>
</div>

</div>
</form>

<!-- ================= MONTHLY ATTENDANCE ================= -->
<form method="post" action="save_monthly_attendance.php">

<div class="main-container" style="margin-top:30px;">
<div class="card">

<h3>Monthly Attendance</h3>
<p>Enter total attendance per student</p>

<select name="student_id" required>
    <option value="">-- Select Student --</option>
    <?php
    if ($class != '') {
        $all = $conn->query("
            SELECT * FROM students
            WHERE class='$class'
            ORDER BY roll_no
        ");
        while ($st = $all->fetch_assoc()) {
            echo "<option value='{$st['id']}'>
                    {$st['name']} (Roll {$st['roll_no']})
                  </option>";
        }
    }
    ?>
</select>

<?php $maxMonth = date('Y-m', strtotime('-1 month')); ?>
<input type="month" name="month" required max="<?= $maxMonth ?>">
<input type="number" name="total_days" placeholder="Total School Days" required>
<input type="number" name="present_days" placeholder="Total Present Days" required>

<button type="submit">Save Monthly Attendance</button>

</div>
</div>
</form>

</div>
</body>
</html>
