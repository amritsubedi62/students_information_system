<?php
session_start();
include("config/db.php");

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usernameOrEmail = trim(mysqli_real_escape_string($conn, $_POST['username']));
    $password = trim($_POST['password']);

    if (empty($usernameOrEmail) || empty($password)) {
        $message = "Please fill in all fields.";
    } else {
        $sql = "SELECT * FROM users WHERE username='$usernameOrEmail' OR email='$usernameOrEmail'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $hashedEnteredPassword = customHash($password, $row['username']);

            if ($hashedEnteredPassword == $row['password']) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['username'] = $row['username'];

                if ($row['role'] == 'teacher') {
                    header("Location: homepage.php");
                } else {
                    header("Location: parent_home.php");
                }
                exit;
            } else {
                $message = "Invalid password!";
            }
        } else {
            $message = "User not found!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
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

.container h1 {
  text-align: center;
  color: #d32f2f;
  margin-bottom: 25px;
  font-size: 28px;
}

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
}
.container form button:hover {
  background-color: #9a0007;
  transform: translateY(-2px);
}

.container p {
  text-align: center;
  margin-top: 18px;
  color: #444;
}

.container a {
  color: #d32f2f;
  text-decoration: none;
  font-weight: 500;
}
.container a:hover { text-decoration: underline; }

.message {
  text-align: center;
  margin-bottom: 15px;
  color: red;
  font-weight: 500;
  animation: fadeIn 0.6s ease;
}

.error-msg {
  color: red;
  font-size: 13px;
  margin-bottom: 10px;
}
</style>
</head>
<body>
<div class="container">
  <h1>Login</h1>
  <?php if ($message) echo "<p class='message'>$message</p>"; ?>
  <form method="POST" id="loginForm">
    <input type="text" name="username" placeholder="Username or Email" required>
    <div class="error-msg" id="usernameError"></div>

    <input type="password" name="password" placeholder="Password" required>
    <div class="error-msg" id="passwordError"></div>

    <button type="submit">Login</button>
    <p>Don’t have an account? <a href="signup.php">Signup here</a></p>
  </form>
</div>

<script>
// Real-time validation
const usernameInput = document.querySelector('input[name="username"]');
const passwordInput = document.querySelector('input[name="password"]');

usernameInput.addEventListener('input', () => {
    const val = usernameInput.value.trim();
    if(val === '') {
        document.getElementById('usernameError').textContent = "Username or email is required.";
    } else if (val.includes('@') && !val.includes('.')) {
        document.getElementById('usernameError').textContent = "Invalid email format.";
    } else {
        document.getElementById('usernameError').textContent = "";
    }
});

passwordInput.addEventListener('input', () => {
    const val = passwordInput.value;
    if(val === '') {
        document.getElementById('passwordError').textContent = "Password is required.";
    } else if(val.length < 6) {
        document.getElementById('passwordError').textContent = "Password must be at least 6 chars.";
    } else {
        document.getElementById('passwordError').textContent = "";
    }
});
</script>
</body>
</html>
