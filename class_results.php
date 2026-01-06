<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

include "config/db.php";

$selectedClass = $_POST['class'] ?? '';
$subjects = ['Math','Science','Social','English','Nepali'];
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Class Results & Ranking</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.center-search {
    display:flex;
    justify-content:center;
    margin:25px 0;
}
.full-table {
    width:100%;
    overflow-x:auto;
}
.fail {
    background:#ffdddd;
    font-weight:bold;
}
</style>
</head>

<body>

<a href="homepage.php" style="position:absolute; top:15px; left:15px; font-size:24px; text-decoration:none; color:white;">←</a>
<?php include "includes/navbar.php"; ?>

<div class="content">
<h2 style="text-align:center;">Class Results & Ranking</h2>

<div class="center-search">
<form method="POST">
<select name="class" onchange="this.form.submit()" required>
<option value="">Select Class</option>
<?php
for ($i=1;$i<=10;$i++) {
    $sel = ($selectedClass==$i) ? "selected":""; 
    echo "<option value='$i' $sel>Class $i</option>";
}
?>
</select>
</form>
</div>

<?php
if ($selectedClass):

$students = mysqli_query($conn,
    "SELECT * FROM students WHERE class='$selectedClass' ORDER BY roll_no"
);

$data = [];

while ($s = mysqli_fetch_assoc($students)) {

    $total = 0;
    $fail  = false;
    $count = 0;
    $marksArr = [];

    foreach ($subjects as $sub) {
        $q = mysqli_query($conn,
            "SELECT marks FROM results 
             WHERE student_id='{$s['id']}' AND subject='$sub'"
        );
        if ($r = mysqli_fetch_assoc($q)) {
            $marks = $r['marks'];
            $marksArr[$sub] = $marks;
            $total += $marks;
            $count++;
            if ($marks < 40) $fail = true;
        } else {
            $marksArr[$sub] = null;
        }
    }

    $percentage = ($count==5) ? round(($total/500)*100,2) : null;

    $data[] = [
        'student'=>$s,
        'marks'=>$marksArr,
        'total'=>$total,
        'percentage'=>$percentage,
        'fail'=>$fail,
        'complete'=>($count==5)
    ];
}


function smartAssignRanks($data) {
    $passList = [];
    $failList = [];

    // Split pass and fail students
    foreach ($data as $d) {
        if ($d['complete'] && !$d['fail']) {
            $passList[] = $d;
        } else {
            $failList[] = $d;
        }
    }

    // Sort passing students by total marks descending
    for ($i = 0; $i < count($passList) - 1; $i++) {
        for ($j = $i + 1; $j < count($passList); $j++) {
            if ($passList[$j]['total'] > $passList[$i]['total']) {
                $temp = $passList[$i];
                $passList[$i] = $passList[$j];
                $passList[$j] = $temp;
            }
        }
    }

    $rank = 1;
    $prevTotal = null;
    $sameRankCount = 0;

    foreach ($passList as $k => &$d) {
        if ($prevTotal === $d['total']) {
            $d['rank'] = $rank;  
            $sameRankCount++;
        } else {
            $rank += $sameRankCount;
            $d['rank'] = $rank;
            $sameRankCount = 1;
        }
        $prevTotal = $d['total'];
    }
    unset($d);

    foreach ($failList as &$d) {
        $d['rank'] = '-';
    }
    unset($d);

    $finalList = [];
    foreach ($passList as $d) $finalList[] = $d;
    foreach ($failList as $d) $finalList[] = $d;

    return $finalList;
}


$data = smartAssignRanks($data);
?>

<div class="full-table">
<table>
<tr>
<th>Rank</th>
<th>Roll</th>
<th>Name</th>
<?php foreach ($subjects as $s) echo "<th>$s</th>"; ?>
<th>Total</th>
<th>Percentage</th>
</tr>

<?php foreach ($data as $d): ?>
<tr>
<td><?= $d['rank'] ?></td>
<td><?= $d['student']['roll_no'] ?></td>
<td><?= $d['student']['name'] ?></td>

<?php foreach ($subjects as $sub):
    $m = $d['marks'][$sub];
    $cls = ($m!==null && $m<40) ? "fail":""; 
?>
<td class="<?= $cls ?>">
<?= $m!==null ? $m : '-' ?>
</td>
<?php endforeach; ?>

<td><?= $d['complete'] ? $d['total'] : '-' ?></td>
<td><?= ($d['complete'] && !$d['fail']) ? $d['percentage']."%" : '-' ?></td>
</tr>
<?php endforeach; ?>

</table>
</div>

<?php endif; ?>

</div>
</body>
</html>
