<?php
include "config/db.php"; // your DB connection file

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

// ---- ADMIN DETAILS ----
$username = "admin";
$email = "admin@sis.com";
$password = "admin123";
$role = "admin";
$status = "approved";

// Hash password
$hashedPassword = customHash($password, $username);

// Insert admin
$sql = "INSERT INTO users (username, email, password, role, status)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $username, $email, $hashedPassword, $role, $status);

if ($stmt->execute()) {
    echo "Admin account created successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
