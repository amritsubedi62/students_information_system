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
    $role = trim($_POST['role']);

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
                $sql = "INSERT INTO users (username, email, password, role, status) 
                        VALUES ('$username', '$email', '$hashedPassword', 'teacher', 'pending')";
            } else {
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

  background: 
    linear-gradient(135deg, rgba(0,0,0,0.6), rgba(0,0,0,0.4)),
    url('sis.jpg');

  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  background-attachment: fixed;

  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;

  animation: fadeIn 0.8s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Glass Container */
.container {
  width: 100%;
  max-width: 420px;

  position: relative;

  background: rgba(255,255,255,0.12);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);

  padding: 40px 30px;
  border-radius: 20px;

  border: 1px solid rgba(255,80,80,0.25); /* subtle red hint */

  box-shadow: 
    0 10px 40px rgba(0,0,0,0.4),
    inset 0 0 15px rgba(255,0,0,0.08);

  animation: slideUp 0.7s ease;
}

/* Glow border */
.container::before {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: 20px;
  padding: 1px;
  background: linear-gradient(135deg, rgba(255,255,255,0.3), rgba(255,80,80,0.2));
  -webkit-mask: 
    linear-gradient(#fff 0 0) content-box, 
    linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
}

@keyframes slideUp {
  from { transform: translateY(30px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.container h1 {
  text-align: center;
  color: #fff;
  margin-bottom: 25px;
  font-size: 28px;
  text-shadow: 0 2px 10px rgba(255,0,0,0.4); /* red glow */
}

/* Inputs & Select */
.container form input,
.container form select {
  width: 100%;
  padding: 12px;
  margin-bottom: 5px;

  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(10px);

  border: 1px solid rgba(255,255,255,0.3);
  border-radius: 8px;

  color: #fff;
  font-size: 14px;

  transition: all 0.3s ease;
}

.container form input::placeholder {
  color: rgba(255,255,255,0.7);
}

/* 🔴 Focus red accent */
.container form input:focus,
.container form select:focus {
  border-color: #ff3b3b;
  box-shadow: 0 0 10px rgba(255,0,0,0.4);
  outline: none;
}

/* Dropdown */
.container form select option {
  background: #1e1e1e;
  color: #ffffff;
}

/* 🔴 Button (MAIN RED ACCENT) */
.container form button {
  width: 100%;
  padding: 12px;
  margin-top: 10px;

  background: linear-gradient(135deg, #ff3b3b, #b30000);

  color: #fff;

  border: none;
  border-radius: 8px;

  font-size: 15px;
  font-weight: bold;

  cursor: pointer;

  transition: all 0.3s ease;

  box-shadow: 0 5px 15px rgba(255,0,0,0.4);
}

.container form button:hover {
  background: linear-gradient(135deg, #ff5c5c, #d10000);
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(255,0,0,0.5);
}

/* Text & Links */
.container p {
  text-align: center;
  margin-top: 18px;
  color: rgba(255,255,255,0.9);
}

.container a {
  color: #ff4d4d;
  font-weight: bold;
  text-decoration: none;
}

.container a:hover {
  text-decoration: underline;
}

/* Error + Success */
.error-msg {
  color: #ff6b6b;
  font-size: 12px;
  margin-bottom: 8px;
}

.message {
  text-align: center;
  margin-bottom: 15px;
  color: #7CFF6B;
  font-weight: bold;
}
</style>

</head>

<body>

<div class="container">
  <h1>Signup</h1>

  <?php if ($message) echo "<p class='message'>$message</p>"; ?>

  <form method="POST">

    <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($username) ?>" required>
    <div class="error-msg"><?= $errors['username'] ?? '' ?></div>

    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>" required>
    <div class="error-msg"><?= $errors['email'] ?? '' ?></div>

    <input type="password" name="password" placeholder="Password (min 6 chars)" required>
    <div class="error-msg"><?= $errors['password'] ?? '' ?></div>

    <select name="role" required>
        <option value="">Select Role</option>
        <option value="parent" <?= $role==='parent'?'selected':'' ?>>Parent</option>
        <option value="teacher" <?= $role==='teacher'?'selected':'' ?>>Teacher</option>
    </select>

    <div class="error-msg"><?= $errors['role'] ?? '' ?></div>

    <button type="submit">Signup</button>

    <p>Already have an account? <a href="login.php">Login here</a></p>
  </form>
</div>

</body>
</html>