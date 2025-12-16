<?php
session_start();
include("config/db.php");

$message = "";

// Hashing ALgorithm
function customHash($password) {
    $hash = 0;
    for ($i = 0; $i < strlen($password); $i++) {
        $hash = ($hash * 31 + ord($password[$i])) % 1000000007;
    }
    return $hash;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim(mysqli_real_escape_string($conn, $_POST['username']));
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $message = "Please fill in all fields.";
    } else {
        $sql = "SELECT * FROM users WHERE username='$username' OR email='$username'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);


            $hashedEnteredPassword = customHash($password);

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
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="container">
    <h1>Login</h1>
    <?php if ($message) echo "<p style='color:red;'>$message</p>"; ?>

    <form method="POST">
      <input type="text" name="username" placeholder="Username or Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
      <p>Don’t have an account? <a href="signup.php">Signup here</a></p>
    </form>
  </div>
</body>
</html>
