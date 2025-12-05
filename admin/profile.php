<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

require_once __DIR__ . '/includes/header.php';

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
            
            if (empty($name) || empty($email)) {
                $error = 'Name and Email are required';
            } else {
                // Check if email is taken by another user
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $userId]);
                if ($stmt->fetch()) {
                    $error = 'Email is already in use';
                } else {
                    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                    if ($stmt->execute([$name, $email, $userId])) {
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

<style>
    .profile-container { max-width: 800px; margin: 0 auto; }
    .profile-card { background: var(--white); padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow-md); margin-bottom: 2rem; }
    .form-group { margin-bottom: 1.5rem; }
    .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--secondary-color); }
    .form-control { width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius); font-family: inherit; }
    .section-title { font-size: 1.25rem; color: var(--secondary-color); margin-bottom: 1.5rem; border-bottom: 2px solid var(--bg-color); padding-bottom: 0.5rem; }
</style>

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
            
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-key"></i> Change Password
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
