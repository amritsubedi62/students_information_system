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

                if ($row['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } elseif ($row['role'] === 'teacher') {
                    header("Location: homepage.php");
                } elseif ($row['role'] === 'parent') {
                    header("Location: parent_home.php");
                } else {
                    $message = "Role not recognized!";
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

/* Glass Login Box */
.container {
  width: 100%;
  max-width: 400px;

  position: relative;

  background: rgba(255,255,255,0.12);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);

  padding: 40px 30px;
  border-radius: 20px;

  border: 1px solid rgba(255,80,80,0.25); /* red hint */

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

/* Inputs */
.container form input {
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

/* 🔴 focus red accent */
.container form input:focus {
  border-color: #ff3b3b;
  box-shadow: 0 0 10px rgba(255,0,0,0.4);
  outline: none;
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

/* Links */
.container p {
  text-align: center;
  margin-top: 18px;
  color: rgba(255,255,255,0.9);
}

.container a {
  color: #ff4d4d;
  text-decoration: none;
  font-weight: bold;
}

.container a:hover {
  text-decoration: underline;
}

/* Messages */
.message {
  text-align: center;
  margin-bottom: 15px;
  color: #ff4d4d;
  font-weight: bold;
}

/* Error Text */
.error-msg {
  color: #ff6b6b;
  font-size: 12px;
  margin-bottom: 8px;
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
// Real-time validation (unchanged logic)
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