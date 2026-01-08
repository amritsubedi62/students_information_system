<?php
include("config/db.php");

$errors = [];
$message = "";

// Hashing Algorithm 
function customHash($password, $username) {
    $add = "SIS_2025_" . $username;
    $input = $password . $add;

    $hash = 11;
    for ($round = 0; $round < 4; $round++) {
        for ($i = 0; $i < strlen($input); $i++) {
            $hash = ($hash * 37) + ord($input[$i]);
            $hash = $hash ^ ($hash >> 5);
            $hash = $hash & 0x7FFFFFFF;
        }
    }
    return $hash;
}

$username = $email = $password = $role = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim(mysqli_real_escape_string($conn, $_POST['username']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = trim($_POST['password']);
    $role = trim($_POST['role']); // new role field

    // Validation
    if (empty($username)) $errors['username'] = "Username is required.";
    elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) $errors['username'] = "3-20 chars, letters/numbers/_ only.";

    if (empty($email)) $errors['email'] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email format.";

    if (empty($password)) $errors['password'] = "Password is required.";
    elseif (strlen($password) < 6) $errors['password'] = "Min 6 characters.";

    if (empty($role) || !in_array($role, ['parent','teacher'])) $errors['role'] = "Select a valid role.";

    if (empty($errors)) {
        $check_sql = "SELECT * FROM users WHERE username='$username' OR email='$email'";
        $check_result = mysqli_query($conn, $check_sql);
        if (mysqli_num_rows($check_result) > 0) {
            $errors['username'] = "Username or email already exists!";
        } else {
            $hashedPassword = customHash($password, $username);

            if ($role === 'teacher') {
                // teacher signup: status = pending
                $sql = "INSERT INTO users (username, email, password, role, status) 
                        VALUES ('$username', '$email', '$hashedPassword', 'teacher', 'pending')";
            } else {
                // parent signup: status = approved
                $sql = "INSERT INTO users (username, email, password, role, status) 
                        VALUES ('$username', '$email', '$hashedPassword', 'parent', 'approved')";
            }

            if (mysqli_query($conn, $sql)) {
                $message = "Signup successful! You can now login.";
                $username = $email = $password = $role = "";
            } else {
                $message = "Error creating account: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Signup</title>
<style>
body {
  margin: 0;
  font-family: 'Segoe UI', sans-serif;
  background: linear-gradient(135deg, #f0f2f5, #e3e6eb);
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  animation: fadeIn 0.8s ease-in;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.container {
  width: 100%;
  max-width: 420px;
  background: #fff;
  padding: 40px;
  border-radius: 14px;
  box-shadow: 0 12px 30px rgba(0,0,0,0.15);
  animation: slideUp 0.6s ease;
}
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.container h1 { text-align: center; color: #d32f2f; margin-bottom: 25px; font-size: 28px; }

.container form input, .container form select {
  width: 100%;
  padding: 12px;
  margin-bottom: 5px;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 14px;
  transition: border 0.3s, box-shadow 0.3s;
}
.container form input:focus, .container form select:focus {
  border-color: #d32f2f;
  box-shadow: 0 0 6px rgba(211,47,47,0.3);
  outline: none;
}

.error-msg { color: red; font-size: 13px; margin-bottom: 10px; }

.container form button {
  width: 100%;
  padding: 12px;
  background-color: #d32f2f;
  color: #fff;
  border: none;
  border-radius: 6px;
  font-size: 15px;
  cursor: pointer;
  transition: background 0.3s, transform 0.2s;
  margin-top: 10px;
}
.container form button:hover {
  background-color: #9a0007;
  transform: translateY(-2px);
}

.container p { text-align: center; margin-top: 18px; color: #444; }
.container a { color: #d32f2f; text-decoration: none; font-weight: 500; }
.container a:hover { text-decoration: underline; }

.message { text-align: center; margin-bottom: 15px; color: green; font-weight: 500; animation: fadeIn 0.6s ease; }
</style>
</head>
<body>
<div class="container">
  <h1>Signup</h1>
  <?php if ($message) echo "<p class='message'>$message</p>"; ?>
  <form method="POST" id="signupForm">
    <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($username) ?>" required>
    <div class='error-msg'><?= $errors['username'] ?? '' ?></div>

    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>" required>
    <div class='error-msg'><?= $errors['email'] ?? '' ?></div>

    <input type="password" name="password" placeholder="Password (min 6 chars)" required>
    <div class='error-msg'><?= $errors['password'] ?? '' ?></div>

    <select name="role" required>
        <option value="">Select Role</option>
        <option value="parent" <?= $role==='parent'?'selected':'' ?>>Parent</option>
        <option value="teacher" <?= $role==='teacher'?'selected':'' ?>>Teacher</option>
    </select>
    <div class='error-msg'><?= $errors['role'] ?? '' ?></div>

    <button type="submit">Signup</button>
    <p>Already have an account? <a href="login.php">Login here</a></p>
  </form>
</div>
</body>
</html>
