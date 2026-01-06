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

$username = $email = $password = $child_name = $class = $roll_no = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim(mysqli_real_escape_string($conn, $_POST['username']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = trim($_POST['password']);
    $child_name = trim(mysqli_real_escape_string($conn, $_POST['child_name']));
    $class = trim(mysqli_real_escape_string($conn, $_POST['class']));
    $roll_no = trim(mysqli_real_escape_string($conn, $_POST['roll_no']));

    // Validation
    if (empty($username)) $errors['username'] = "Username is required.";
    elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) $errors['username'] = "3-20 chars, letters/numbers/_ only.";

    if (empty($email)) $errors['email'] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email format.";

    if (empty($password)) $errors['password'] = "Password is required.";
    elseif (strlen($password) < 6) $errors['password'] = "Min 6 characters.";

    if (empty($child_name)) $errors['child_name'] = "Child name is required.";
    elseif (!preg_match('/^[A-Za-z]{2,}(?:\s[A-Za-z]{2,})+$/', $child_name) || strlen($child_name) < 7) {
      $errors['child_name'] = "Enter full name (min 2 words, letters only, min 7 chars).";
  }
    if (empty($class)) $errors['class'] = "Class is required.";
    elseif (!is_numeric($class) || $class < 1 || $class > 10) $errors['class'] = "Class must be 1-10.";

    if (empty($roll_no)) $errors['roll_no'] = "Roll number is required.";
    elseif (!ctype_digit($roll_no) || $roll_no < 1) $errors['roll_no'] = "Must be positive integer.";

    if (empty($errors)) {
        $check_sql = "SELECT * FROM users WHERE username='$username' OR email='$email'";
        $check_result = mysqli_query($conn, $check_sql);
        if (mysqli_num_rows($check_result) > 0) {
            $errors['username'] = "Username or email already exists!";
        } else {
            $hashedPassword = customHash($password, $username);
            $sql = "INSERT INTO users (username, email, password, role, child_name, child_class, child_roll_no) 
                VALUES ('$username', '$email', '$hashedPassword', 'parent', '$child_name', '$class', '$roll_no')";

            if (mysqli_query($conn, $sql)) {
                $message = "Signup successful! You can now login.";
                // Reset form fields
                $username = $email = $password = $child_name = $class = $roll_no = "";
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
<title>Parent Signup</title>
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

.container form input {
  width: 100%;
  padding: 12px;
  margin-bottom: 5px;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 14px;
  transition: border 0.3s, box-shadow 0.3s;
}
.container form input:focus {
  border-color: #d32f2f;
  box-shadow: 0 0 6px rgba(211,47,47,0.3);
  outline: none;
}

.error-msg {
  color: red;
  font-size: 13px;
  margin-bottom: 10px;
}

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

.message {
  text-align: center;
  margin-bottom: 15px;
  color: green;
  font-weight: 500;
  animation: fadeIn 0.6s ease;
}
</style>
</head>
<body>
<div class="container">
  <h1>Parent Signup</h1>
  <?php if ($message) echo "<p class='message'>$message</p>"; ?>
  <form method="POST" id="signupForm">
    <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($username) ?>" required>
    <div class='error-msg' id="usernameError"><?= $errors['username'] ?? '' ?></div>

    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>" required>
    <div class='error-msg' id="emailError"><?= $errors['email'] ?? '' ?></div>

    <input type="password" name="password" placeholder="Password (min 6 chars)" required>
    <div class='error-msg' id="passwordError"><?= $errors['password'] ?? '' ?></div>

    <input type="text" name="child_name" placeholder="Child Name" value="<?= htmlspecialchars($child_name) ?>" required>
    <div class='error-msg' id="childNameError"><?= $errors['child_name'] ?? '' ?></div>

    <input type="text" name="class" placeholder="Class" value="<?= htmlspecialchars($class) ?>" required>
    <div class='error-msg' id="classError"><?= $errors['class'] ?? '' ?></div>

    <input type="text" name="roll_no" placeholder="Roll Number" value="<?= htmlspecialchars($roll_no) ?>" required>
    <div class='error-msg' id="rollNoError"><?= $errors['roll_no'] ?? '' ?></div>

    <button type="submit">Signup</button>
    <p>Already have an account? <a href="login.php">Login here</a></p>
  </form>
</div>

<script>
const username = document.querySelector('input[name="username"]');
const email = document.querySelector('input[name="email"]');
const password = document.querySelector('input[name="password"]');
const childName = document.querySelector('input[name="child_name"]');
const classInput = document.querySelector('input[name="class"]');
const rollNo = document.querySelector('input[name="roll_no"]');

username.addEventListener('input', () => {
    const val = username.value;
    const regex = /^[a-zA-Z0-9_]{3,20}$/;
    document.getElementById('usernameError').textContent = regex.test(val) ? '' : '3-20 chars, letters/numbers/_ only.';
});
email.addEventListener('input', () => {
    const val = email.value;
    document.getElementById('emailError').textContent = val.includes('@') && val.includes('.') ? '' : 'Invalid email format.';
});

password.addEventListener('input', () => {
    document.getElementById('passwordError').textContent = password.value.length >= 6 ? '' : 'Password must be at least 6 chars.';
});

childName.addEventListener('input', () => {
    const val = childName.value.trim();

    const fullNameRegex = /^[A-Za-z]{2,}(?:\s[A-Za-z]{2,})+$/;

    if (!fullNameRegex.test(val)) {
        document.getElementById('childNameError').textContent =
            'Enter full name (at least 2 words, letters only).';
    } 
    else if (val.length < 7) {
        document.getElementById('childNameError').textContent =
            'Full name must be at least 7 characters.';
    } 
    else {
        document.getElementById('childNameError').textContent = '';
    }
});


classInput.addEventListener('input', () => {
    const val = parseInt(classInput.value);
    document.getElementById('classError').textContent = (val >= 1 && val <= 10) ? '' : 'Class must be 1-10.';
});

rollNo.addEventListener('input', () => {
    const val = parseInt(rollNo.value);
    document.getElementById('rollNoError').textContent = (val > 0) ? '' : 'Must be positive integer.';
});
</script>
</body>
</html>