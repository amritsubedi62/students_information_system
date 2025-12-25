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


$attendance_date = $_POST['attendance_date'] ?? '';
$today = date('Y-m-d');

if ($attendance_date !== $today) {
    die("Attendance can be recorded only for today.");
}
$checkDay = $conn->query("
    SELECT id FROM attendance 
    WHERE date='$attendance_date' 
    LIMIT 1
");

if ($checkDay->num_rows > 0) {
    die("Attendance for today has already been recorded.");
}


if (!isset($_POST['status']) || empty($_POST['status'])) {
    die("No attendance data received.");
}

foreach ($_POST['status'] as $student_id => $status) {
    $conn->query("
        INSERT INTO attendance (student_id, date, status)
        VALUES ('$student_id', '$attendance_date', '$status')
    ");
}

header("Location: manage_attendance.php?success=1");
exit;
?>
