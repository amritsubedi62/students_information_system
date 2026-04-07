<?php
session_start();
include("config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);

    if (isset($_POST['update'])) {
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE users SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['delete'])) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: manage_users.php");
    exit;
}

$teachers = $conn->query("SELECT * FROM users WHERE role='teacher' ORDER BY status DESC, username ASC");
$parents  = $conn->query("SELECT * FROM users WHERE role='parent' ORDER BY username ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Users</title>

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
    justify-content: space-between;
}

.nav-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.navbar a {
    color: white;
    text-decoration: none;
    font-weight: bold;
}

.logout-btn {
    background: white;
    color: #c0392b;
    padding: 6px 12px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
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
    color: #c0392b;
}

/* CARD */
.card {
    background: white;
    padding: 20px;
    border-radius: 6px;
    box-shadow: 0 0 8px rgba(0,0,0,0.08);
    margin-top: 20px;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th, td {
    padding: 10px;
    border: 1px solid #ddd;
}

th {
    background: #f9f9f9;
}

/* STATUS */
.status-pending {
    color: orange;
    font-weight: bold;
}

.status-approved {
    color: green;
    font-weight: bold;
}

/* BUTTON */
button {
    padding: 6px 10px;
    border: none;
    background: #c0392b;
    color: white;
    cursor: pointer;
    border-radius: 4px;
}

button:hover {
    background: #a93226;
}

/* UPDATE FORM */
.update-form {
    display: none;
    background: #fafafa;
}

select {
    padding: 5px;
}
</style>

<script>
function showUpdateForm(id){
    document.getElementById('update-form-'+id).style.display = 'table-row';
}
function hideUpdateForm(id){
    document.getElementById('update-form-'+id).style.display = 'none';
}
</script>

</head>

<body>
<?php include "includes/admin_navbar.php"; ?>
<!-- BACK -->
<a href="admin_dashboard.php" class="back">←</a>


<div class="container">

<h2>Manage Users</h2>

<!-- TEACHERS -->
<div class="card">
<h3>Teachers</h3>

<table>
<tr>
<th>Username</th>
<th>Email</th>
<th>Status</th>
<th>Actions</th>
</tr>

<?php while($row = $teachers->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($row['username']); ?></td>
<td><?= htmlspecialchars($row['email']); ?></td>
<td>
<span class="status-<?= $row['status']; ?>">
<?= ucfirst($row['status']); ?>
</span>
</td>
<td>
<button onclick="showUpdateForm(<?= $row['id']; ?>)">Update</button>

<form method="POST" style="display:inline;">
<input type="hidden" name="user_id" value="<?= $row['id']; ?>">
<button type="submit" name="delete"
onclick="return confirm('Delete this teacher?')">Delete</button>
</form>
</td>
</tr>

<tr id="update-form-<?= $row['id']; ?>" class="update-form">
<td colspan="4">
<form method="POST">
<input type="hidden" name="user_id" value="<?= $row['id']; ?>">

Status:
<select name="status">
<option value="pending" <?= $row['status']=='pending'?'selected':''; ?>>Pending</option>
<option value="approved" <?= $row['status']=='approved'?'selected':''; ?>>Approved</option>
</select>

<button name="update">Save</button>
<button type="button" onclick="hideUpdateForm(<?= $row['id']; ?>)">Cancel</button>
</form>
</td>
</tr>

<?php endwhile; ?>
</table>
</div>

<!-- PARENTS -->
<div class="card">
<h3>Parents</h3>

<table>
<tr>
<th>Username</th>
<th>Email</th>
<th>Actions</th>
</tr>

<?php while($row = $parents->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($row['username']); ?></td>
<td><?= htmlspecialchars($row['email']); ?></td>
<td>
<form method="POST">
<input type="hidden" name="user_id" value="<?= $row['id']; ?>">
<button type="submit" name="delete"
onclick="return confirm('Delete this parent?')">Delete</button>
</form>
</td>
</tr>
<?php endwhile; ?>

</table>
</div>

</div>

</body>
</html>