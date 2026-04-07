<?php
include("config/db.php");

$errors = [];
$message = "";

/* =========================
   HASH FUNCTION
========================= */
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

/* =========================
   FORM SUBMIT
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = trim(mysqli_real_escape_string($conn, $_POST['username']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    /* =========================
       USERNAME VALIDATION
       - starts with letter
       - only letters & numbers
       - min 3 chars
    ========================= */
    if (empty($username)) {
        $errors['username'] = "Username is required.";
    }
    elseif (!preg_match('/^[A-Za-z][A-Za-z0-9]{2,19}$/', $username)) {
        $errors['username'] = "Start with letter, only letters/numbers, min 3 chars.";
    }

    /* =========================
       EMAIL VALIDATION
    ========================= */
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    /* =========================
       PASSWORD VALIDATION
    ========================= */
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    }
    elseif (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters.";
    }

    /* =========================
       ROLE VALIDATION
    ========================= */
    if (empty($role) || !in_array($role, ['parent','teacher'])) {
        $errors['role'] = "Select a valid role.";
    }

    /* =========================
       DUPLICATE CHECK
    ========================= */
    if (empty($errors)) {

        $check_sql = "SELECT * FROM users 
                      WHERE username='$username' OR email='$email' 
                      LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            $errors['username'] = "Username already exists!";
        }
    }

    /* =========================
       INSERT USER
    ========================= */
    if (empty($errors)) {

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
            $message = "Error: " . mysqli_error($conn);
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
    linear-gradient(135deg, rgba(0,0,0,0.65), rgba(0,0,0,0.45)),
    url('image.jpg');

  background-size: cover;
  background-position: center;
  background-attachment: fixed;

  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}

/* MAIN CARD (MORE WHITE & BRIGHT) */
.container {
  width: 100%;
  max-width: 420px;

  background: rgba(255, 255, 255, 0.35); /* brighter */
  backdrop-filter: blur(18px);

  padding: 40px 30px;
  border-radius: 20px;

  border: 1px solid rgba(255, 255, 255, 0.5);

  box-shadow: 0 15px 45px rgba(0,0,0,0.35);
}

/* TITLE */
.container h1 {
  text-align: center;
  color: #ffffff;
  margin-bottom: 20px;
  text-shadow: 0 2px 10px rgba(0,0,0,0.4);
}

/* INPUT FIELDS */
input, select {
  width: 100%;
  padding: 12px;
  margin-top: 5px;
  margin-bottom: 5px;

  background: rgba(255, 255, 255, 0.85); /* VERY CLEAR */
  border: 1px solid rgba(255, 255, 255, 0.8);
  border-radius: 8px;

  color: #111;  /* dark text for readability */
  font-size: 14px;

  outline: none;
}

/* INPUT FOCUS */
input:focus, select:focus {
  border: 1px solid #00c853;
  box-shadow: 0 0 8px rgba(0,200,83,0.4);
}

/* BUTTON */
button {
  width: 100%;
  padding: 12px;
  margin-top: 10px;

  background: linear-gradient(135deg, #f1120a, #ff0a0a);
  color: #fff;

  border: none;
  border-radius: 8px;

  cursor: pointer;
  font-weight: bold;
}

/* ERROR TEXT (FIXED VISIBILITY) */
.error-msg {
  color: #ff3b3b;   /* strong red */
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 8px;

  text-shadow: 0 1px 2px rgba(0,0,0,0.4);
}

/* SUCCESS MESSAGE */
.message {
  text-align: center;
  color: #00e676;
  font-weight: bold;
  margin-bottom: 10px;
  text-shadow: 0 1px 3px rgba(0,0,0,0.4);
}
</style>
</head>

<body>

<div class="container">

<h1>Signup</h1>

<?php if($message) echo "<div class='message'>$message</div>"; ?>

<form method="POST">

<input type="text" name="username" placeholder="Username"
value="<?= htmlspecialchars($username) ?>">
<div class="error-msg"><?= $errors['username'] ?? '' ?></div>

<input type="email" name="email" placeholder="Email"
value="<?= htmlspecialchars($email) ?>">
<div class="error-msg"><?= $errors['email'] ?? '' ?></div>

<input type="password" name="password" placeholder="Password">
<div class="error-msg"><?= $errors['password'] ?? '' ?></div>

<select name="role">
  <option value="">Select Role</option>
  <option value="parent" <?= $role=='parent'?'selected':'' ?>>Parent</option>
  <option value="teacher" <?= $role=='teacher'?'selected':'' ?>>Teacher</option>
</select>

<div class="error-msg"><?= $errors['role'] ?? '' ?></div>

<button type="submit">Signup</button>
<div style="text-align:center; margin-top:15px;">
  <span style="color:#fff;">Already have an account?</span>
  <a href="login.php"
     style="color:#ff3b3b; font-weight:bold; text-decoration:none; margin-left:5px;">
    Login here
  </a>
</div>

</form>

</div>

</body>
</html>