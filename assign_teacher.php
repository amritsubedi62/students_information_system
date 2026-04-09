<?php
session_start();
include("config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = intval($_POST['teacher_id']);
    $class      = intval($_POST['class']);

    $check = $conn->prepare("SELECT id FROM teacher_class WHERE class=?");
    $check->bind_param("i", $class);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $update = $conn->prepare("UPDATE teacher_class SET teacher_id=? WHERE class=?");
        $update->bind_param("ii", $teacher_id, $class);
        $update->execute();
        $update->close();
    } else {
        $insert = $conn->prepare("INSERT INTO teacher_class (teacher_id, class) VALUES (?, ?)");
        $insert->bind_param("ii", $teacher_id, $class);
        $insert->execute();
        $insert->close();
    }
    $check->close();

    header("Location: assign_teacher.php");
    exit;
}

$assigned = $conn->query("
    SELECT tc.class, u.username 
    FROM teacher_class tc
    JOIN users u ON tc.teacher_id = u.id
    ORDER BY CAST(tc.class AS UNSIGNED) ASC
");

$rows = [];
while($row = $assigned->fetch_assoc()) $rows[] = $row;

$total = count($rows);
$unassigned = 10 - $total;
?>

<!DOCTYPE html>
<html>
<head>
<title>Assign Teacher</title>

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f9;
}

/* NAVBAR */
.navbar {
    background: #c0392b;
    color: white;
    padding: 12px 20px;
    display: flex;
    align-items: center;
}

.navbar a {
    color: white;
    text-decoration: none;
    font-size: 18px;
    font-weight: bold;
}

/* BACK BUTTON */
.back {
    position: absolute;
    top: 15px;
    left: 15px;
    font-size: 20px;
    text-decoration: none;
    color: #333;
}

/* CONTENT */
.container {
    padding: 30px;
}

h2 {
    margin-bottom: 20px;
}

/* LAYOUT */
.dashboard {
    display: flex;
    gap: 30px;
}

/* CARD */
.card {
    background: white;
    padding: 20px;
    border-radius: 6px;
    box-shadow: 0 0 8px rgba(0,0,0,0.08);
    width: 320px;
}

.card h3 {
    margin-bottom: 15px;
}

/* FORM */
label {
    display: block;
    margin-top: 10px;
}

select {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
}

button {
    margin-top: 15px;
    padding: 10px;
    width: 100%;
    background: #c0392b;
    color: white;
    border: none;
    cursor: pointer;
}

button:hover {
    background: #a93226;
}

/* TABLE */
.table-card {
    width: 500px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 10px;
    border: 1px solid #ddd;
}

th {
    background: #f9f9f9;
}

.class-badge {
    background: #fdecea;
    color: #c0392b;
    padding: 4px 8px;
    border-radius: 4px;
}

/* FOOT TEXT */
.info {
    margin-top: 10px;
    font-size: 14px;
}
</style>

</head>

<body>

<?php include "includes/admin_navbar.php"; ?>
<!-- BACK -->
<a href="admin_dashboard.php" class="back">←</a>


<div class="container">

<h2>Manage Users</h2>

<div class="container">

<h2>Assign Teacher (Class 1–10)</h2>

<div class="dashboard">

<!-- FORM -->
<div class="card">
<h3>Assign Teacher</h3>

<form method="POST">

<label>Teacher</label>
<select name="teacher_id" required>
<option value="">Select teacher</option>
<?php
$teachers = $conn->query("SELECT id, username FROM users WHERE role='teacher' AND status='approved'");
while($t = $teachers->fetch_assoc()):
?>
<option value="<?= $t['id']; ?>">
<?= htmlspecialchars($t['username']); ?>
</option>
<?php endwhile; ?>
</select>

<label>Class</label>
<select name="class" required>
<option value="">Select class</option>
<?php for($i=1;$i<=10;$i++): ?>
<option value="<?= $i ?>">Class <?= $i ?></option>
<?php endfor; ?>
</select>

<button type="submit">Assign Teacher</button>

</form>
</div>

<!-- TABLE -->
<div class="card table-card">
<h3>Assigned Teachers</h3>

<table>
<tr>
<th>Class</th>
<th>Teacher</th>
</tr>

<?php if ($total == 0): ?>
<tr><td colspan="2">No assignments yet</td></tr>
<?php else: ?>
<?php foreach($rows as $row): ?>
<tr>
<td><span class="class-badge">Class <?= $row['class']; ?></span></td>
<td><?= htmlspecialchars($row['username']); ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>

</table>

<div class="info">
<?= $total ?> assigned, <?= $unassigned ?> remaining
</div>

</div>

</div>
</div>

</body>
</html>