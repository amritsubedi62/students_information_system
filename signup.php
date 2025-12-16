<?php
include("config/db.php");

$message = "";

// Hashing Algorithm
function customHash($password) {
    $hash = 0;
    for ($i = 0; $i < strlen($password); $i++) {
        $hash = ($hash * 31 + ord($password[$i])) % 1000000007;
    }
    return $hash;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim(mysqli_real_escape_string($conn, $_POST['username']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = trim($_POST['password']);
    $child_name = trim(mysqli_real_escape_string($conn, $_POST['child_name']));
    $class = trim(mysqli_real_escape_string($conn, $_POST['class']));
    $roll_no = trim(mysqli_real_escape_string($conn, $_POST['roll_no']));

    // ✅ Basic input validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format!";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long!";
    } else {
        // ✅ Check for duplicate email or username
        $check_sql = "SELECT * FROM users WHERE username='$username' OR email='$email'";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            $message = "Username or email already exists!";
        } else {
            // ✅ Hash password using your custom algorithm
            $hashedPassword = customHash($password);

            // ✅ Insert into users table
            $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashedPassword', 'parent')";
            if (mysqli_query($conn, $sql)) {
                $user_id = mysqli_insert_id($conn);

                // ✅ Insert into students table
                $sql2 = "INSERT INTO students (parent_id, name, class, roll_no) 
                         VALUES ('$user_id', '$child_name', '$class', '$roll_no')";
                if (mysqli_query($conn, $sql2)) {
                    $message = "Signup successful! You can now login.";
                } else {
                    $message = "Error adding student: " . mysqli_error($conn);
                }
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
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="container">
    <h1>Parent Signup</h1>
    <?php if ($message) echo "<p style='color:green;'>$message</p>"; ?>

    <form method="POST">
      <input type="text" name="username" placeholder="Username" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password (min 6 chars)" required>
      <input type="text" name="child_name" placeholder="Child Name" required>
      <input type="text" name="class" placeholder="Class" required>
      <input type="text" name="roll_no" placeholder="Roll Number" required>
      <button type="submit">Signup</button>
      <p>Already have an account? <a href="login.php">Login here</a></p>
    </form>
  </div>
</body>
</html>
