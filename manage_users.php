<?php
session_start();
include("config/db.php");

// SECURITY: Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle POST updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);

    // Update teacher status
    if (isset($_POST['update'])) {
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE users SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    // Delete user (teacher or parent)
    if (isset($_POST['delete'])) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: manage_users.php");
    exit;
}

// Fetch users
$teachers = $conn->query("SELECT * FROM users WHERE role='teacher' ORDER BY status DESC, username ASC");
$parents  = $conn->query("SELECT * FROM users WHERE role='parent' ORDER BY username ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users - Admin | SIS</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.content { padding: 20px; }
h2 { margin-bottom: 20px; color: #d32f2f; }
.card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 6px 15px rgba(0,0,0,0.1); margin-bottom: 20px; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
th { background: #f2f2f2; }
.status-pending { color: #fbc02d; font-weight: 600; }
.status-approved { color: #388e3c; font-weight: 600; }
button { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; background-color: #d32f2f; color: #fff; margin-right:5px; }
button:hover { opacity: 0.85; }
.update-form { display: none; margin-top: 10px; }
.update-form select { padding: 5px; border-radius: 6px; border: 1px solid #ccc; }
</style>
<script>
function showUpdateForm(id){
    document.getElementById('update-form-'+id).style.display = 'block';
}
function hideUpdateForm(id){
    document.getElementById('update-form-'+id).style.display = 'none';
}
</script>
</head>
<body>

<?php include "includes/navbar.php"; ?>

<div class="content">
  <h2>Manage Users</h2>

  <!-- Teachers Table -->
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
            <span class="status-<?= $row['status']; ?>"><?= ucfirst($row['status']); ?></span>
        </td>
        <td>
            <button onclick="showUpdateForm(<?= $row['id']; ?>)">Update</button>
            <form method="POST" style="display:inline-block">
                <input type="hidden" name="user_id" value="<?= $row['id']; ?>">
                <button type="submit" name="delete" onclick="return confirm('Are you sure to delete this teacher?');">Delete</button>
            </form>
        </td>
      </tr>
      <!-- Hidden update form -->
      <tr id="update-form-<?= $row['id']; ?>" class="update-form">
        <td colspan="4">
            <form method="POST">
                <input type="hidden" name="user_id" value="<?= $row['id']; ?>">
                <strong>Username:</strong> <?= htmlspecialchars($row['username']); ?> |
                <strong>Email:</strong> <?= htmlspecialchars($row['email']); ?> |
                <strong>Status:</strong>
                <select name="status">
                    <option value="pending" <?= $row['status']=='pending'?'selected':''; ?>>Pending</option>
                    <option value="approved" <?= $row['status']=='approved'?'selected':''; ?>>Approved</option>
                </select>
                <button type="submit" name="update">Save</button>
                <button type="button" onclick="hideUpdateForm(<?= $row['id']; ?>)">Cancel</button>
            </form>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>

  <!-- Parents Table -->
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
            <form method="POST" style="display:inline-block">
                <input type="hidden" name="user_id" value="<?= $row['id']; ?>">
                <button type="submit" name="delete" onclick="return confirm('Are you sure to delete this parent?');">Delete</button>
            </form>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>

</div>
</body>
</html>
