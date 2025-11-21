<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$userId = getUserId();
$db = new Database();
$conn = $db->connect();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $priority = $_POST['priority'] ?? 'medium';
        
        if (empty($title) || empty($description)) {
            $error = 'Title and description are required';
        } else {
            $stmt = $conn->prepare("INSERT INTO tickets (user_id, title, description, priority) VALUES (?, ?, ?, ?)");
            try {
                $stmt->execute([$userId, $title, $description, $priority]);
                $ticketId = $conn->lastInsertId();
                
                // Handle file upload if present
                if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    uploadFile($_FILES['file'], $ticketId);
                }
                
                $success = 'Ticket created successfully!';
                header('Location: ticket-detail.php?id=' . $ticketId);
                exit;
            } catch (PDOException $e) {
                error_log("Ticket creation error: " . $e->getMessage());
                $error = 'Failed to create ticket. Please try again.';
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
    <title>Create Ticket - LocalTechFix</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard { min-height: 100vh; background-color: var(--bg-color); }
        .dashboard-header { background: var(--white); padding: 1.5rem 0; box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .dashboard-nav { display: flex; justify-content: space-between; align-items: center; }
        .dashboard-nav .logo { font-size: 1.5rem; font-weight: 700; color: var(--primary-color); }
        .dashboard-nav .nav-links { display: flex; gap: 2rem; align-items: center; }
        .dashboard-nav .nav-links a { color: var(--text-color); font-weight: 500; }
        .dashboard-nav .nav-links a:hover { color: var(--primary-color); }
        .form-container { max-width: 800px; background: var(--white); padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow-md); }
        .alert { padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; }
        .alert-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="dashboard-header">
            <div class="container">
                <div class="dashboard-nav">
                    <div class="logo"><i class="fa-solid fa-microchip"></i> LocalTechFix</div>
                    <div class="nav-links">
                        <a href="dashboard.php"><i class="fa-solid fa-home"></i> Dashboard</a>
                        <a href="tickets.php"><i class="fa-solid fa-ticket"></i> My Tickets</a>
                        <a href="create-ticket.php"><i class="fa-solid fa-plus"></i> New Ticket</a>
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
            <h1 style="margin-bottom: 2rem; color: var(--secondary-color);">Create New Ticket</h1>

            <div class="form-container">
                <?php if ($error): ?>
                    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    
                    <div class="form-group">
                        <label for="title">Issue Title *</label>
                        <input type="text" id="title" name="title" required placeholder="Brief description of the issue" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="description">Detailed Description *</label>
                        <textarea id="description" name="description" rows="6" required placeholder="Please describe the issue in detail..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius);">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="file">Attach File (Optional)</label>
                        <input type="file" id="file" name="file" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.log">
                        <p style="font-size: 0.875rem; color: var(--light-text); margin-top: 0.5rem;">
                            <i class="fa-solid fa-info-circle"></i> Max 5MB. Allowed: images, PDF, documents, logs
                        </p>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-paper-plane"></i> Submit Ticket
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fa-solid fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../assets/js/script.js"></script>
</body>
</html>
