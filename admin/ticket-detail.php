<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$adminId = getUserId();
$ticketId = $_GET['id'] ?? 0;
$db = new Database();
$conn = $db->connect();

// Get ticket details
$stmt = $conn->prepare("SELECT t.*, u.name as customer_name, u.email as customer_email FROM tickets t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->execute([$ticketId]);
$ticket = $stmt->fetch();

if (!$ticket) {
    redirect('tickets.php');
}

// Handle status/priority update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_ticket'])) {
    if (validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $status = $_POST['status'] ?? $ticket['status'];
        $priority = $_POST['priority'] ?? $ticket['priority'];
        
        $stmt = $conn->prepare("UPDATE tickets SET status = ?, priority = ? WHERE id = ?");
        $stmt->execute([$status, $priority, $ticketId]);
        redirect('ticket-detail.php?id=' . $ticketId);
    }
}

// Handle new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    if (validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $content = sanitize($_POST['message']);
        if (!empty($content)) {
            $stmt = $conn->prepare("INSERT INTO messages (ticket_id, author_id, content, is_admin) VALUES (?, ?, ?, 1)");
            $stmt->execute([$ticketId, $adminId, $content]);
            redirect('ticket-detail.php?id=' . $ticketId);
        }
    }
}

// Get messages
$stmt = $conn->prepare("SELECT m.*, u.name as author_name FROM messages m JOIN users u ON m.author_id = u.id WHERE m.ticket_id = ? ORDER BY m.created_at ASC");
$stmt->execute([$ticketId]);
$messages = $stmt->fetchAll();

// Get files
$stmt = $conn->prepare("SELECT * FROM files WHERE ticket_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$ticketId]);
$files = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?= $ticketId ?> - Admin - LocalTechFix</title>
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
        .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; }
        .ticket-detail, .ticket-sidebar, .messages, .files { background: var(--white); padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow-md); margin-bottom: 2rem; }
        .ticket-header { padding-bottom: 1.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); }
        .ticket-header h1 { color: var(--secondary-color); margin-bottom: 0.5rem; }
        .badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-secondary { background: #e5e7eb; color: #374151; }
        .message { padding: 1rem; margin-bottom: 1rem; border-radius: var(--radius); background: var(--bg-color); }
        .message.admin { background: #eff6ff; border-left: 3px solid var(--primary-color); }
        .message-header { display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.875rem; }
        .message-author { font-weight: 600; color: var(--secondary-color); }
        .message-time { color: var(--light-text); }
        .file-item { display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--bg-color); border-radius: var(--radius); margin-bottom: 0.5rem; }
        .file-icon { font-size: 1.5rem; color: var(--primary-color); }
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
            <a href="tickets.php" style="display: inline-block; margin-bottom: 1rem; color: var(--primary-color);">
                <i class="fa-solid fa-arrow-left"></i> Back to All Tickets
            </a>

            <div class="content-grid">
                <div>
                    <div class="ticket-detail">
                        <div class="ticket-header">
                            <h1><?= htmlspecialchars($ticket['title']) ?></h1>
                            <p style="color: var(--light-text); margin-bottom: 1rem;">
                                <i class="fa-solid fa-user"></i> <?= htmlspecialchars($ticket['customer_name']) ?> (<?= htmlspecialchars($ticket['customer_email']) ?>)
                            </p>
                            <p style="color: var(--light-text);">
                                <i class="fa-solid fa-calendar"></i> Created: <?= formatDate($ticket['created_at']) ?>
                                | <i class="fa-solid fa-clock"></i> Updated: <?= formatDate($ticket['updated_at']) ?>
                            </p>
                        </div>

                        <div>
                            <h3 style="color: var(--secondary-color); margin-bottom: 1rem;">Description</h3>
                            <p style="color: var(--text-color); line-height: 1.6;"><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
                        </div>
                    </div>

                    <?php if (!empty($files)): ?>
                        <div class="files">
                            <h3 style="color: var(--secondary-color); margin-bottom: 1rem;">Attached Files</h3>
                            <?php foreach ($files as $file): ?>
                                <div class="file-item">
                                    <i class="fa-solid <?= getFileIcon($file['filename']) ?> file-icon"></i>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600; color: var(--secondary-color);"><?= htmlspecialchars($file['filename']) ?></div>
                                        <div style="font-size: 0.875rem; color: var(--light-text);">
                                            <?= formatFileSize($file['filesize']) ?> • <?= formatDate($file['uploaded_at']) ?>
                                        </div>
                                    </div>
                                    <a href="../assets/uploads/<?= htmlspecialchars($file['filepath']) ?>" class="btn btn-secondary" download>
                                        <i class="fa-solid fa-download"></i> Download
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="messages">
                        <h3 style="color: var(--secondary-color); margin-bottom: 1rem;">Messages</h3>
                        
                        <?php if (empty($messages)): ?>
                            <p style="color: var(--light-text); text-align: center; padding: 2rem;">No messages yet</p>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="message <?= $message['is_admin'] ? 'admin' : '' ?>">
                                    <div class="message-header">
                                        <span class="message-author">
                                            <?= $message['is_admin'] ? '<i class="fa-solid fa-shield-halved"></i> ' : '' ?>
                                            <?= htmlspecialchars($message['author_name']) ?>
                                        </span>
                                        <span class="message-time"><?= formatDate($message['created_at']) ?></span>
                                    </div>
                                    <div><?= nl2br(htmlspecialchars($message['content'])) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <form method="POST" style="margin-top: 2rem;">
                            <?= csrfField() ?>
                            <div class="form-group">
                                <label for="message">Reply to Customer</label>
                                <textarea id="message" name="message" rows="4" required placeholder="Type your message here..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-paper-plane"></i> Send Reply
                            </button>
                        </form>
                    </div>
                </div>

                <div>
                    <div class="ticket-sidebar">
                        <h3 style="color: var(--secondary-color); margin-bottom: 1.5rem;">Ticket Management</h3>
                        
                        <form method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="update_ticket" value="1">
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius);">
                                    <option value="pending" <?= $ticket['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="waiting_parts" <?= $ticket['status'] === 'waiting_parts' ? 'selected' : '' ?>>Waiting for Parts</option>
                                    <option value="completed" <?= $ticket['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $ticket['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="priority">Priority</label>
                                <select id="priority" name="priority" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius);">
                                    <option value="low" <?= $ticket['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
                                    <option value="medium" <?= $ticket['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                                    <option value="high" <?= $ticket['priority'] === 'high' ? 'selected' : '' ?>>High</option>
                                    <option value="urgent" <?= $ticket['priority'] === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa-solid fa-save"></i> Update Ticket
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/script.js"></script>
</body>
</html>
