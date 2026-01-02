<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

include "config/db.php";

$subjects = ['Math','Science','Social','English','Nepali'];
$selectedClass = $_GET['class'] ?? '';
$selectedStudent = $_GET['student_id'] ?? '';
$studentData = null;
$resultsData = [];
$feedback = [];
$monthlyAttendance = [];
$dailyAttendance = [];
$totalSchoolDays = 0;
$totalPresentDays = 0;

$currentMonth = date('Y-m'); // e.g., 2025-12
$resultsComplete = false;
$attendanceComplete = false;
$message = '';

if ($selectedClass && $selectedStudent) {
    // Fetch student info
    $res = mysqli_query($conn, "SELECT * FROM students WHERE id='$selectedStudent' AND class='$selectedClass'");
    $studentData = mysqli_fetch_assoc($res);

    // Fetch results
    $totalMarks = 0;
    $pass = true;
    $resultsComplete = true;
    foreach ($subjects as $sub) {
        $r = mysqli_query($conn, "SELECT marks FROM results WHERE student_id='$selectedStudent' AND subject='$sub'");
        $row = mysqli_fetch_assoc($r);
        if ($row && $row['marks'] !== null) {
            $mark = $row['marks'];
        } else {
            $mark = 0;
            $resultsComplete = false; // incomplete if any subject missing
        }
        $resultsData[$sub] = $mark;
        $totalMarks += $mark;
        if ($mark < 40) {
            $pass = false;
        }
    }

    if (!$resultsComplete) {
        $message = "Please update marks for all 5 subjects to view results, charts, and feedback.";
    } else {
        $percentage = round(($totalMarks/500)*100,2);

        foreach ($subjects as $sub) {
            if ($resultsData[$sub] < 40) $feedback[] = "Needs improvement in $sub";
        }
        if ($percentage >= 90 && $pass) $feedback[] = "Excellent overall performance!";
        elseif ($percentage >= 75 && $pass) $feedback[] = "Very good, keep it up!";
        elseif ($percentage >= 60 && $pass) $feedback[] = "Good, continue improving!";
        elseif ($percentage >= 50 && $pass) $feedback[] = "Average, need more effort!";
        elseif ($percentage < 50) $feedback[] = "Significant improvement needed!";
    }

    // Monthly attendance
    $maRes = mysqli_query($conn, "SELECT * FROM attendance_monthly WHERE student_id='$selectedStudent' ORDER BY month DESC");
    while($m = mysqli_fetch_assoc($maRes)){
        $monthlyAttendance[$m['month']] = ['total_days'=>$m['total_days'],'present_days'=>$m['present_days']];
        $totalSchoolDays += $m['total_days'];
        $totalPresentDays += $m['present_days'];
    }

    // Daily attendance
    $daRes = mysqli_query($conn, "SELECT * FROM attendance WHERE student_id='$selectedStudent' AND DATE_FORMAT(date,'%Y-%m')='$currentMonth' ORDER BY date DESC");
    while($d = mysqli_fetch_assoc($daRes)){
        $dailyAttendance[] = $d;
    }

    $attendanceComplete = !empty($monthlyAttendance) && !empty($dailyAttendance);
    if (!$attendanceComplete) {
        $message .= ($message ? " " : "") . "Attendance data is incomplete.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Student Analytics - Full</title>
<link rel="stylesheet" href="assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{margin:0; font-family:Arial,sans-serif;}
.content{width:95%; margin:auto; padding-bottom:50px;}
.card{width:100%; margin-bottom:20px; padding:15px; box-sizing:border-box; background:#f9f9f9; border-radius:5px; box-shadow:0 0 5px rgba(0,0,0,0.1);}

table{width:100%; border-collapse:collapse; margin-top:10px;}
th, td{border:1px solid #ccc; padding:8px; text-align:center;}
td.fail{color:red; font-weight:bold;}
.scrollable{max-height:300px; overflow-y:auto;}
.alert{color:red; font-weight:bold; text-align:center; margin-bottom:15px;}
h3{margin-top:0;}
canvas{width:100% !important; max-width:100%; height:400px !important;}
</style>
<style>
.cards-container{
    display:flex;
    flex-wrap:wrap;
    gap:20px;
}
.chart-card{
    flex: 1 1 calc(50% - 20px); /* two per row */
    box-sizing:border-box;
    background:#f9f9f9;
    padding:15px;
    border-radius:5px;
    box-shadow:0 0 5px rgba(0,0,0,0.1);
}
canvas{
    width:100% !important;
    height:300px !important; /* smaller height */
}
</style>
</head>
<body>
<<<<<<< HEAD
    <h1> This page is under construction. Soon we will complete this. </h1>
=======
<?php include "includes/navbar.php"; ?>

<div class="content">
<h2>Student Analytics</h2>

<!-- Search Section -->
<div class="card">
<form method="GET" style="text-align:center;">
    <select name="class" required>
        <option value="">Select Class</option>
        <?php for($i=1;$i<=10;$i++): ?>
            <option value="<?= $i ?>" <?= ($selectedClass==$i)?'selected':'' ?>>Class <?= $i ?></option>
        <?php endfor; ?>
    </select>

    <?php if ($selectedClass): ?>
    <select name="student_id" required>
        <option value="">Select Student</option>
        <?php
        $sres = mysqli_query($conn,"SELECT * FROM students WHERE class='$selectedClass' ORDER BY roll_no");
        while($s = mysqli_fetch_assoc($sres)):
        ?>
        <option value="<?= $s['id'] ?>" <?= ($selectedStudent==$s['id'])?'selected':'' ?>>Roll <?= $s['roll_no'] ?> - <?= $s['name'] ?></option>
        <?php endwhile; ?>
    </select>
    <?php endif; ?>
    <button type="submit">Show</button>
</form>
</div>

<?php if($message): ?>
<div class="alert"><?= $message ?></div>
<?php endif; ?>

<?php if($studentData): ?>
<div class="card">
<h3><?= $studentData['name'] ?> (Roll <?= $studentData['roll_no'] ?>)</h3>
<p>Class: <?= $studentData['class'] ?></p>
<p>Total School Days till now: <?= $totalSchoolDays ?>, Present Days: <?= $totalPresentDays ?></p>
</div>

<?php if($resultsComplete): ?>
<div class="card">
<h3>Results</h3>
<table>
<tr><?php foreach($subjects as $sub): ?><th><?= $sub ?></th><?php endforeach; ?><th>Total</th><th>Percentage</th></tr>
<tr><?php foreach($subjects as $sub): ?><td <?= ($resultsData[$sub]<40)?'class="fail"':'' ?>><?= $resultsData[$sub] ?></td><?php endforeach; ?>
<td><?= $totalMarks ?></td>
<td><?= $percentage ?>%</td>
</tr>
</table>
</div>

<div class="cards-container">
    <?php if($resultsComplete): ?>
    <div class="chart-card">
        <h3>Marks Chart</h3>
        <canvas id="marksChart"></canvas>
    </div>
    <?php endif; ?>

    <?php if($attendanceComplete): ?>
    <div class="chart-card">
        <h3>Attendance Overview</h3>
        <canvas id="attendanceChart"></canvas>
    </div>
    <?php endif; ?>
</div>

<div class="card scrollable">
<h3>Monthly Attendance</h3>
<table>
<tr><th>Month</th><th>Total Days</th><th>Present Days</th><th>Absent Days</th></tr>
<?php foreach($monthlyAttendance as $month=>$m): ?>
<tr>
<td><?= $month ?></td>
<td><?= $m['total_days'] ?></td>
<td><?= $m['present_days'] ?></td>
<td><?= $m['total_days'] - $m['present_days'] ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

<div class="card scrollable">
<h3>Daily Attendance (<?= $currentMonth ?>)</h3>
<table>
<tr><th>Date</th><th>Status</th></tr>
<?php foreach($dailyAttendance as $da): ?>
<tr>
<td><?= $da['date'] ?></td>
<td <?= ($da['status']=='Absent')?'class="fail"':'' ?>><?= $da['status'] ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<script>
<?php if($resultsComplete): ?>
const marksCtx = document.getElementById('marksChart').getContext('2d');
new Chart(marksCtx,{
    type:'bar',
    data:{
        labels: <?= json_encode($subjects) ?>,
        datasets:[{label:'Marks',data: <?= json_encode(array_values($resultsData)) ?>,backgroundColor:'#2196F3'}]
    },
    options:{scales:{y:{beginAtZero:true,max:100}}}
});
<?php endif; ?>

<?php if($attendanceComplete):
$lastMonthData = array_values(array_slice($monthlyAttendance, -1))[0] ?? ['total_days'=>0,'present_days'=>0];
$absentDays = $lastMonthData['total_days'] - $lastMonthData['present_days'];
?>
const attCtx = document.getElementById('attendanceChart').getContext('2d');
new Chart(attCtx, {
    type:'pie',
    data:{
        labels:['Present','Absent'],
        datasets:[{data:[<?= $lastMonthData['present_days'] ?>, <?= $absentDays ?>],backgroundColor:['#4CAF50','#F44336']}]
    },
    options:{responsive:true, plugins:{legend:{position:'bottom'}}}
});
<?php endif; ?>
</script>

<?php endif; ?>
</div>
>>>>>>> 1cf45459ec33a03ea3a9d8c7baeb929393b8af52
</body>
</html>
