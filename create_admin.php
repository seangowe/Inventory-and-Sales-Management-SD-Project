<?php
include 'connection.php';

$conn = new mysqli("localhost", "root", "", "sales_inventory");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = "admin";
$password = "admin123";
$role = "admin";

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?) 
    ON DUPLICATE KEY UPDATE password = VALUES(password), role = VALUES(role)");
$stmt->bind_param("sss", $username, $hashedPassword, $role);

if ($stmt->execute()) {
    echo "Admin user is ready. Username: admin | Password: admin123";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>