<?php
session_start();

if (!isset($_POST['student_id'])) {
    header("Location: manage_attendance.php");
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "student_information_system");
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

/* ================= INPUT ================= */
$student_id   = intval($_POST['student_id']);
$month        = $_POST['month'];
$total_days   = intval($_POST['total_days']);
$present_days = intval($_POST['present_days']);

/* ================= VALIDATIONS ================= */

/* 1. empty check */
if ($student_id <= 0 || empty($month)) {
    die("Invalid input data.");
}

/* 2. total days limit (max 31/32 safe rule) */
if ($total_days <= 0 || $total_days > 31) {
    die("Error: Total days must be between 1 and 31.");
}

/* 3. present days cannot exceed total */
if ($present_days < 0 || $present_days > $total_days) {
    die("Error: Present days cannot be greater than total days.");
}

/* 4. month validation (no future month) */
$maxMonth = date('Y-m', strtotime('-1 month'));
if ($month > $maxMonth) {
    die("Error: You can only add monthly attendance for past months.");
}

/* 5. duplicate check */
$check = $conn->prepare("
    SELECT id 
    FROM attendance_monthly 
    WHERE student_id = ? AND month = ?
");
$check->bind_param("is", $student_id, $month);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    die("Error: Monthly attendance already exists for this student.");
}

/* ================= INSERT ================= */
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
    die("Error: " . $conn->error);
}

/* ================= CLOSE ================= */
$check->close();
$insert->close();
$conn->close();
?>