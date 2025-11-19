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
        if (updateSetting('site_theme', $theme)) {
            $success = 'Settings updated successfully';
        } else {
            $error = 'Failed to update settings';
        }
    }
}

$currentTheme = getSetting('site_theme', 'default');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin - LocalTechFix</title>
    <link rel="stylesheet" href="<?= getThemeCss('../') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard { min-height: 100vh; background-color: var(--bg-color); }
        .dashboard-header { background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); padding: 1.5rem 0; box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .dashboard-nav { display: flex; justify-content: space-between; align-items: center; }
        .dashboard-nav .logo { font-size: 1.5rem; font-weight: 700; color: var(--white); }
        .dashboard-nav .nav-links { display: flex; gap: 2rem; align-items: center; }
        .dashboard-nav .nav-links a { color: var(--white); font-weight: 500; opacity: 0.9; }
        .dashboard-nav .nav-links a:hover { opacity: 1; }
        
        .settings-card { background: var(--white); padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow-md); max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--secondary-color); }
        .theme-options { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .theme-option { border: 2px solid var(--border-color); border-radius: var(--radius); padding: 1rem; cursor: pointer; transition: all 0.3s; text-align: center; }
        .theme-option:hover { border-color: var(--primary-color); background: var(--bg-color); }
        .theme-option.active { border-color: var(--primary-color); background: #eff6ff; }
        .theme-option input { display: none; }
        .theme-preview { height: 100px; background: #f3f4f6; margin-bottom: 0.5rem; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #6b7280; font-size: 0.875rem; }
        .theme-preview.modern { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; }
        
        .alert { padding: 1rem; border-radius: var(--radius); margin-bottom: 1rem; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="dashboard-header">
            <div class="container">
                <div class="dashboard-nav">
                    <div class="logo"><i class="fa-solid fa-shield-halved"></i> Admin Panel</div>
                    <div class="nav-links">
                        <a href="dashboard.php"><i class="fa-solid fa-home"></i> Dashboard</a>
                        <a href="tickets.php"><i class="fa-solid fa-ticket"></i> All Tickets</a>
                        <a href="customers.php"><i class="fa-solid fa-users"></i> Customers</a>
                        <a href="settings.php" style="opacity: 1; font-weight: 700;"><i class="fa-solid fa-cog"></i> Settings</a>
                        <a href="../index.php"><i class="fa-solid fa-globe"></i> Site</a>
                        <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                        <button class="theme-toggle" aria-label="Toggle dark mode" style="background:none; border:none; color:inherit; cursor:pointer; font-size:1rem;">
                            <i class="fa-solid fa-moon"></i>
                        </button>
                        <button class="style-toggle" aria-label="Switch Theme" title="Switch Theme" style="background:none; border:none; color:inherit; cursor:pointer; font-size:1rem;">
                            <i class="fa-solid fa-palette"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <h1 style="margin-bottom: 2rem; color: var(--secondary-color); text-align: center;">Site Settings</h1>

            <?php if ($success): ?>
                <div class="alert alert-success" style="max-width: 600px; margin: 0 auto 1rem;">
                    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error" style="max-width: 600px; margin: 0 auto 1rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="settings-card">
                <form method="POST" action="">
                    <?= csrfField() ?>
                    
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
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fa-solid fa-save"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function selectTheme(element) {
            // Remove active class from all options
            document.querySelectorAll('.theme-option').forEach(opt => opt.classList.remove('active'));
            // Add active class to clicked option
            element.classList.add('active');
        }
    </script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
