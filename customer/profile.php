<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$userId = getUserId();
$db = new Database();
$conn = $db->connect();

$success = '';
$error = '';

// Get User Data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $address = trim($_POST['address']);
            
            if (empty($name) || empty($email)) {
                $error = 'Name and Email are required';
            } else {
                // Check if email is taken by another user
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $userId]);
                if ($stmt->fetch()) {
                    $error = 'Email is already in use';
                } else {
                    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
                    if ($stmt->execute([$name, $email, $phone, $address, $userId])) {
                        $success = 'Profile updated successfully';
                        // Refresh user data
                        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->execute([$userId]);
                        $user = $stmt->fetch();
                        // Update session name if changed
                        $_SESSION['user_name'] = $user['name'];
                    } else {
                        $error = 'Failed to update profile';
                    }
                }
            }
        } elseif ($action === 'change_password') {
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $error = 'All password fields are required';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'New passwords do not match';
            } elseif (strlen($newPassword) < 6) {
                $error = 'New password must be at least 6 characters';
            } else {
                if (password_verify($currentPassword, $user['password'])) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    if ($stmt->execute([$hashedPassword, $userId])) {
                        $success = 'Password changed successfully';
                    } else {
                        $error = 'Failed to update password';
                    }
                } else {
                    $error = 'Incorrect current password';
                }
            }
        }
    }
}
?>

<div class="profile-container">
    <h1 style="margin-bottom: 2rem; color: var(--secondary-color);">My Profile</h1>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="profile-card">
        <h2 class="section-title"><i class="fa-solid fa-user"></i> Personal Information</h2>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update_profile">
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Update Profile
            </button>
        </form>
    </div>

    <div class="profile-card">
        <h2 class="section-title"><i class="fa-solid fa-lock"></i> Change Password</h2>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="change_password">
            
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
            </div>
            
            <button type="submit" class="btn btn-secondary">
                <i class="fa-solid fa-key"></i> Change Password
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
