<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "student_information_system");
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$student_id   = intval($_POST['student_id']);
$month        = $_POST['month'];
$total_days   = intval($_POST['total_days']);
$present_days = intval($_POST['present_days']);

if ($present_days > $total_days) {
    die("Error: Present days cannot be more than total days.");
}

$maxMonth = date('Y-m', strtotime('-1 month')); 
if ($month > $maxMonth) {
    die("Error: You can only add monthly attendance for past months.");
}

$check = $conn->prepare("SELECT id FROM attendance_monthly WHERE student_id = ? AND month = ?");
$check->bind_param("is", $student_id, $month);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    die("Error: Monthly attendance for this student and month already exists.");
}

$insert = $conn->prepare("INSERT INTO attendance_monthly (student_id, month, total_days, present_days) VALUES (?, ?, ?, ?)");
$insert->bind_param("isii", $student_id, $month, $total_days, $present_days);

if ($insert->execute()) {
    header("Location: manage_attendance.php?success=1");
    exit;
} else {
    die("Error: " . $conn->error);
}

$check->close();
$insert->close();
$conn->close();
?>
