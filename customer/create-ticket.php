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
                
                // Send Email Notification to Admin
                try {
                    $adminEmail = getSetting('smtp_from_email');
                    $subject = "New Ticket Created: #$ticketId - $title";
                    
                    // Construct base URL dynamically
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                    $baseUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2);
                    
                    $body = "<h2>New Ticket Created</h2>
                             <p><strong>Ticket ID:</strong> #$ticketId</p>
                             <p><strong>Customer:</strong> " . htmlspecialchars($_SESSION['user_name']) . "</p>
                             <p><strong>Title:</strong> " . htmlspecialchars($title) . "</p>
                             <p><strong>Priority:</strong> " . ucfirst($priority) . "</p>
                             <p><strong>Description:</strong><br>" . nl2br(htmlspecialchars($description)) . "</p>
                             <p><a href='" . $baseUrl . "/admin/ticket-detail.php?id=$ticketId'>View Ticket in Admin Panel</a></p>";
                             
                    sendEmail($adminEmail, $subject, $body);
                } catch (Exception $e) {
                    // Don't block ticket creation if email fails
                    error_log("Email notification failed: " . $e->getMessage());
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

require_once __DIR__ . '/includes/header.php';
?>

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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
