<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$success = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        $theme = $_POST['site_theme'] ?? 'default';
        updateSetting('site_theme', $theme);
        
        // Email Settings
        updateSetting('email_enabled', isset($_POST['email_enabled']) ? '1' : '0');
        updateSetting('smtp_host', $_POST['smtp_host'] ?? '');
        updateSetting('smtp_port', $_POST['smtp_port'] ?? '');
        updateSetting('smtp_user', $_POST['smtp_user'] ?? '');
        if (!empty($_POST['smtp_pass'])) {
            updateSetting('smtp_pass', $_POST['smtp_pass']);
        }
        updateSetting('smtp_from_email', $_POST['smtp_from_email'] ?? '');
        updateSetting('smtp_from_name', $_POST['smtp_from_name'] ?? '');
        
        // Test Email
        if (isset($_POST['test_email_btn']) && !empty($_POST['test_email_to'])) {
            $testResult = sendEmail($_POST['test_email_to'], 'Test Email from LocalTechFix', '<h1>It Works!</h1><p>This is a test email from your LocalTechFix admin panel.</p>');
            if ($testResult['success']) {
                $success = 'Settings saved and test email sent successfully!';
            } else {
                $error = 'Settings saved, but test email failed: ' . $testResult['message'];
            }
        } else {
            $success = 'Settings updated successfully';
        }
    }
}

$currentTheme = getSetting('site_theme', 'default');
$emailEnabled = getSetting('email_enabled', '0');
$smtpHost = getSetting('smtp_host', '');
$smtpPort = getSetting('smtp_port', '587');
$smtpUser = getSetting('smtp_user', '');
$smtpFromEmail = getSetting('smtp_from_email', '');
$smtpFromName = getSetting('smtp_from_name', 'LocalTechFix Support');
require_once __DIR__ . '/includes/header.php';
?>
    <style>
        /* Page specific styles */
        .settings-container { max-width: 800px; margin: 0 auto; display: grid; gap: 2rem; }
        .settings-card { background: var(--white); padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow-md); }
        .settings-card h2 { margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border-color); color: var(--primary-color); }
        
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--secondary-color); }
        .form-control { width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius); font-family: inherit; }
        
        .theme-options { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .theme-option { border: 2px solid var(--border-color); border-radius: var(--radius); padding: 1rem; cursor: pointer; transition: all 0.3s; text-align: center; }
        .theme-option:hover { border-color: var(--primary-color); background: var(--bg-color); }
        .theme-option.active { border-color: var(--primary-color); background: #eff6ff; }
        .theme-option input { display: none; }
        .theme-preview { height: 100px; background: #f3f4f6; margin-bottom: 0.5rem; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #6b7280; font-size: 0.875rem; }
        .theme-preview.modern { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; }
        
        .toggle-switch { position: relative; display: inline-block; width: 60px; height: 34px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 26px; width: 26px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--primary-color); }
        input:checked + .slider:before { transform: translateX(26px); }
    </style>

            <h1 style="margin-bottom: 2rem; color: var(--secondary-color); text-align: center;">Site Settings</h1>

            <?php if ($success): ?>
                <div class="alert alert-success" style="max-width: 800px; margin: 0 auto 1rem;">
                    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error" style="max-width: 800px; margin: 0 auto 1rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="settings-container">
                <?= csrfField() ?>
                
                <!-- Appearance Settings -->
                <div class="settings-card">
                    <h2><i class="fa-solid fa-paint-roller"></i> Appearance</h2>
                    <div class="form-group">
                        <label>Website Theme</label>
                        <div class="theme-options">
                            <label class="theme-option <?= $currentTheme === 'default' ? 'active' : '' ?>" onclick="selectTheme(this)">
                                <input type="radio" name="site_theme" value="default" <?= $currentTheme === 'default' ? 'checked' : '' ?>>
                                <div class="theme-preview">
                                    Default Theme
                                </div>
                                <strong>Classic Blue</strong>
                            </label>
                            
                            <label class="theme-option <?= $currentTheme === 'modern' ? 'active' : '' ?>" onclick="selectTheme(this)">
                                <input type="radio" name="site_theme" value="modern" <?= $currentTheme === 'modern' ? 'checked' : '' ?>>
                                <div class="theme-preview modern">
                                    Modern Theme
                                </div>
                                <strong>Modern Gradient</strong>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Email Settings -->
                <div class="settings-card">
                    <h2><i class="fa-solid fa-envelope"></i> Email Notifications</h2>
                    
                    <div class="form-group" style="display: flex; align-items: center; justify-content: space-between;">
                        <label style="margin-bottom: 0;">Enable Email Sending</label>
                        <label class="toggle-switch">
                            <input type="checkbox" name="email_enabled" <?= $emailEnabled === '1' ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars($smtpHost) ?>" placeholder="e.g. smtp.gmail.com">
                    </div>
                    
                    <div class="form-group">
                        <label>SMTP Port</label>
                        <input type="text" name="smtp_port" class="form-control" value="<?= htmlspecialchars($smtpPort) ?>" placeholder="e.g. 587">
                    </div>
                    
                    <div class="form-group">
                        <label>SMTP Username</label>
                        <input type="text" name="smtp_user" class="form-control" value="<?= htmlspecialchars($smtpUser) ?>" placeholder="email@example.com">
                    </div>
                    
                    <div class="form-group">
                        <label>SMTP Password</label>
                        <input type="password" name="smtp_pass" class="form-control" placeholder="Leave blank to keep unchanged">
                    </div>
                    
                    <div class="form-group">
                        <label>From Email</label>
                        <input type="email" name="smtp_from_email" class="form-control" value="<?= htmlspecialchars($smtpFromEmail) ?>" placeholder="noreply@yourdomain.com">
                    </div>
                    
                    <div class="form-group">
                        <label>From Name</label>
                        <input type="text" name="smtp_from_name" class="form-control" value="<?= htmlspecialchars($smtpFromName) ?>" placeholder="Support Team">
                    </div>
                    
                    <hr style="margin: 2rem 0; border: 0; border-top: 1px solid var(--border-color);">
                    
                    <h3>Test Configuration</h3>
                    <div class="form-group" style="display: flex; gap: 1rem;">
                        <input type="email" name="test_email_to" class="form-control" placeholder="Recipient Email for Test">
                        <button type="submit" name="test_email_btn" value="1" class="btn btn-secondary">
                            Send Test Email
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                    <i class="fa-solid fa-save"></i> Save All Settings
                </button>
            </form>

    <script>
        function selectTheme(element) {
            // Remove active class from all options
            document.querySelectorAll('.theme-option').forEach(opt => opt.classList.remove('active'));
            // Add active class to clicked option
            element.classList.add('active');
        }
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
