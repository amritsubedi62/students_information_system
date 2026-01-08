<?php
session_start();
include("config/db.php");

/* SECURITY: Admin only */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/* Handle Assignment */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = intval($_POST['teacher_id']);
    $class      = intval($_POST['class']);

    // Check if class already assigned
    $check = $conn->prepare("SELECT id FROM teacher_class WHERE class=?");
    $check->bind_param("i", $class);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Update existing assignment
        $update = $conn->prepare("UPDATE teacher_class SET teacher_id=? WHERE class=?");
        $update->bind_param("ii", $teacher_id, $class);
        $update->execute();
        $update->close();
    } else {
        // Insert new assignment
        $insert = $conn->prepare("INSERT INTO teacher_class (teacher_id, class) VALUES (?, ?)");
        $insert->bind_param("ii", $teacher_id, $class);
        $insert->execute();
        $insert->close();
    }
    $check->close();

    header("Location: assign_teacher.php");
    exit;
}

/* Fetch approved teachers */
$teachers = $conn->query("
    SELECT id, username, email 
    FROM users 
    WHERE role='teacher' AND status='approved'
    ORDER BY username ASC
");

/* Fetch existing assignments */
$assigned = $conn->query("
    SELECT tc.class, u.username 
    FROM teacher_class tc
    JOIN users u ON tc.teacher_id = u.id
    ORDER BY tc.class ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Assign Teachers | Admin - SIS</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.content { padding:20px; }
.card {
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 6px 15px rgba(0,0,0,0.1);
    margin-bottom:20px;
}
h2 { color:#d32f2f; margin-bottom:15px; }
table { width:100%; border-collapse:collapse; }
th, td { padding:12px; border-bottom:1px solid #eee; }
th { background:#f2f2f2; }
select {
    padding:6px;
    border-radius:6px;
    border:1px solid #ccc;
}
button {
    padding:8px 14px;
    border:none;
    border-radius:6px;
    background:#d32f2f;
    color:#fff;
    cursor:pointer;
}
button:hover { opacity:0.85; }
</style>
</head>
<body>

<?php include "includes/navbar.php"; ?>

<div class="content">
<h2>Assign Teacher to Class</h2>

<!-- Assignment Form -->
<div class="card">
<form method="POST">
    <label><strong>Select Teacher:</strong></label><br><br>
    <select name="teacher_id" required>
        <option value="">-- Select Approved Teacher --</option>
        <?php while($t = $teachers->fetch_assoc()): ?>
            <option value="<?= $t['id']; ?>">
                <?= htmlspecialchars($t['username']); ?> (<?= $t['email']; ?>)
            </option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <label><strong>Select Class:</strong></label><br><br>
    <select name="class" required>
        <option value="">-- Select Class --</option>
        <?php for($i=1; $i<=10; $i++): ?>
            <option value="<?= $i; ?>">Class <?= $i; ?></option>
        <?php endfor; ?>
    </select>
    <br><br>

    <button type="submit">Assign Teacher</button>
</form>
</div>

<!-- Assigned Classes -->
<div class="card">
<h3>Current Class Assignments</h3>
<table>
<tr>
    <th>Class</th>
    <th>Assigned Teacher</th>
</tr>
<?php while($row = $assigned->fetch_assoc()): ?>
<tr>
    <td>Class <?= $row['class']; ?></td>
    <td><?= htmlspecialchars($row['username']); ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

</div>
</body>
</html>
