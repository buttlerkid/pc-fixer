<?php
require_once __DIR__ . '/config/database.php';

$db = new Database();
$conn = $db->connect();

$email = 'admin@test.com';
$password = 'password';
$name = 'Test Admin';
$role = 'admin';

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Check if user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo "User already exists.\n";
    // Update to be admin just in case
    $stmt = $conn->prepare("UPDATE users SET role = 'admin', password = ? WHERE email = ?");
    $stmt->execute([$hashedPassword, $email]);
    echo "User updated to admin.\n";
} else {
    $stmt = $conn->prepare("INSERT INTO users (email, name, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$email, $name, $hashedPassword, $role]);
    echo "Admin user created.\n";
}
?>
