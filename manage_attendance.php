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

$today = date('Y-m-d');
$class = $_POST['class'] ?? '';

$students = [];
if ($class != '') {
    $res = $conn->query("SELECT * FROM students WHERE class='$class'");
    while ($row = $res->fetch_assoc()) {
        $students[] = $row;
    }

    // Quick Sort function
function quickSort(&$arr, $low, $high) {
    if ($low < $high) {
        $pi = partition($arr, $low, $high); // Partition the array and get pivot index
        quickSort($arr, $low, $pi - 1);     //  sort left sub-array
        quickSort($arr, $pi + 1, $high);    // sort right sub array
    }
}

function partition(&$arr, $low, $high) {
    $pivot = $arr[$high]['roll_no'];  // Choose last element as pivot
    $i = $low - 1;                    // Index for smaller element boundary

    for ($j = $low; $j < $high; $j++) {
        if ($arr[$j]['roll_no'] < $pivot) { // If current element is smaller than pivot
            $i++;                          // Move boundary forward
            $temp = $arr[$i];              // Swap element at $i with element at $j
            $arr[$i] = $arr[$j];
            $arr[$j] = $temp;
        }
    }

    // Place pivot at correct position (after all smaller elements)
    $temp = $arr[$i + 1];
    $arr[$i + 1] = $arr[$high];
    $arr[$high] = $temp;

    return $i + 1; // Return pivot index
}


    // Sort students array by roll_no
    quickSort($students, 0, count($students) - 1);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Attendance</title>
</head>

<style>
body {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, sans-serif;
    background: #f2f3f5;
}

h2 {
    text-align: center;
    margin: 20px 0;
    font-weight: 600;
}

.main-container {
    width: 95%;
    margin: auto;
    display: flex;
    gap: 30px;
    justify-content: center;
}

.card {
    background: #ffffff;
    width: 45%;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

.card h3 {
    color: #e53935;
    text-align: center;
}

.card p {
    text-align: center;
    color: #777;
    font-size: 14px;
}

input, select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #f5f5f5;
    padding: 10px;
}

td {
    padding: 10px;
    text-align: center;
    border-bottom: 1px solid #eee;
}

button {
    width: 100%;
    background: #e53935;
    color: #fff;
    padding: 12px;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    cursor: pointer;
}

button:hover {
    background: #c62828;
}

@media(max-width: 900px) {
    .main-container {
        flex-direction: column;
    }
    .card {
        width: 90%;
    }
}
</style>

<body>

<div class="content">
<h2>Manage Attendance </h2>

<form method="post" action="save_attendance.php">

<div class="main-container">

    <div class="card">
        <h3>Attendance Details</h3>

        <input type="date"
               name="attendance_date"
               value="<?= $today ?>"
               min="<?= $today ?>"
               max="<?= $today ?>"
               required>

        <select name="class" required>
            <option value="">-- Select Class --</option>
            <?php 
            for ($c = 10; $c >= 1; $c--) {
                $selected = ($class == $c) ? "selected" : "";
                echo "<option value='$c' $selected>Class $c</option>";
            }
            ?>
        </select>

        <button type="submit" formaction="manage_attendance.php">Load Students</button>
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

        <br>
        <button type="submit">Save Daily Attendance</button>

        <?php } else { ?>
            <p>Please select a class to view students</p>
        <?php } ?>
    </div>

</div>
</form>

<form method="post" action="save_monthly_attendance.php">

<div class="main-container" style="margin-top:30px;">

<div class="card">
    <h3>Monthly Attendance</h3>
    <p>Enter total attendance per student</p>

    <select name="student_id" required>
        <option value="">-- Select Student --</option>
        <?php
        $all = $conn->query("SELECT * FROM students ORDER BY class, roll_no");
        while ($st = $all->fetch_assoc()) {
            echo "<option value='{$st['id']}'>{$st['name']} (Class {$st['class']})</option>";
        }
        ?>
    </select>

    <input type="month" name="month" required>
    <input type="number" name="total_days" placeholder="Total School Days" required>
    <input type="number" name="present_days" placeholder="Total Present Days" required>

    <button type="submit">Save Monthly Attendance</button>
</div>

</div>
</form>

</body>
</html>
