<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$userId = getUserId();
$ticketId = $_GET['id'] ?? 0;
$db = new Database();
$conn = $db->connect();

// Get ticket details
$stmt = $conn->prepare("SELECT * FROM tickets WHERE id = ? AND user_id = ?");
$stmt->execute([$ticketId, $userId]);
$ticket = $stmt->fetch();

if (!$ticket) {
    redirect('tickets.php');
}

// Get messages
$stmt = $conn->prepare("SELECT m.*, u.name as author_name FROM messages m JOIN users u ON m.author_id = u.id WHERE m.ticket_id = ? ORDER BY m.created_at ASC");
$stmt->execute([$ticketId]);
$messages = $stmt->fetchAll();

// Get files
$stmt = $conn->prepare("SELECT * FROM files WHERE ticket_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$ticketId]);
$files = $stmt->fetchAll();

// Handle new message
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        $content = sanitize($_POST['message']);
        if (!empty($content)) {
            $stmt = $conn->prepare("INSERT INTO messages (ticket_id, author_id, content, is_admin) VALUES (?, ?, ?, 0)");
            $stmt->execute([$ticketId, $userId, $content]);
            
            // Send email to admin
            try {
                $adminEmail = getSetting('smtp_from_email');
                $subject = "New Reply on Ticket: #$ticketId - " . $ticket['title'];
                
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                $baseUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2);
                
                $body = "<h2>New Reply</h2>
                         <p>Customer <strong>" . htmlspecialchars($_SESSION['user_name']) . "</strong> has replied to a ticket.</p>
                         <p><strong>Message:</strong><br>" . nl2br($content) . "</p>
                         <p><a href='" . $baseUrl . "/admin/ticket-detail.php?id=$ticketId'>View Ticket in Admin Panel</a></p>";
                         
                sendEmail($adminEmail, $subject, $body);
            } catch (Exception $e) {
                error_log("Email notification failed: " . $e->getMessage());
            }
            
            redirect('ticket-detail.php?id=' . $ticketId);
        }
    }
}
?>

<a href="tickets.php" style="display: inline-block; margin-bottom: 1rem; color: var(--primary-color);">
    <i class="fa-solid fa-arrow-left"></i> Back to Tickets
</a>

<div class="ticket-detail">
    <div class="ticket-header">
        <div>
            <h1><?= htmlspecialchars($ticket['title']) ?></h1>
            <p style="color: var(--light-text);">
                <i class="fa-solid fa-calendar"></i> Created: <?= formatDate($ticket['created_at']) ?>
                | <i class="fa-solid fa-clock"></i> Updated: <?= formatDate($ticket['updated_at']) ?>
            </p>
        </div>
        <div class="ticket-meta">
            <?= getStatusBadge($ticket['status']) ?>
            <?= getPriorityBadge($ticket['priority']) ?>
        </div>
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
        <p style="color: var(--light-text); text-align: center; padding: 2rem;">No messages yet. Start the conversation!</p>
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
            <label for="message">Add a Message</label>
            <textarea id="message" name="message" rows="4" required placeholder="Type your message here..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-paper-plane"></i> Send Message
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
