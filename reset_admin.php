<?php
require_once 'config/config.php';
require_once 'includes/Database.php';

$database = new Database();
$db = $database->connect();

$email = 'admin@test.com';
$password = 'password123';
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Check if admin exists
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    // Update password
    $stmt = $db->prepare("UPDATE users SET password = ?, role = 'admin' WHERE email = ?");
    if ($stmt->execute([$hashed, $email])) {
        echo "Admin password reset successfully for $email";
    } else {
        echo "Failed to reset password";
    }
} else {
    // Create admin
    $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
    if ($stmt->execute(['Admin', $email, $hashed])) {
        echo "Admin user created successfully: $email / $password";
    } else {
        echo "Failed to create admin user";
    }
}
?>
