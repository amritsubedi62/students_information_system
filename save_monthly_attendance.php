<?php
$conn = new mysqli("localhost", "root", "", "student_information_system");
if ($conn->connect_error) {
    die("DB Error");
}

$student_id   = $_POST['student_id'];
$month        = $_POST['month'];        
$total_days   = $_POST['total_days'];
$present_days = $_POST['present_days'];

if ($present_days > $total_days) {
    die("Present days cannot be more than total days");
}


$sql = "
INSERT INTO attendance_monthly (student_id, month, total_days, present_days)
VALUES ('$student_id','$month','$total_days','$present_days')
ON DUPLICATE KEY UPDATE
total_days='$total_days',
present_days='$present_days'
";

$conn->query($sql);

header("Location: manage_attendance.php");
?>
