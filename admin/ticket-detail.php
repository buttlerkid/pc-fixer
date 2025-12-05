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

// POST handling moved to ajax_actions.php

// Get messages
$stmt = $conn->prepare("SELECT m.*, u.name as author_name FROM messages m JOIN users u ON m.author_id = u.id WHERE m.ticket_id = ? ORDER BY m.created_at ASC");
$stmt->execute([$ticketId]);
$messages = $stmt->fetchAll();

// Get files
$stmt = $conn->prepare("SELECT * FROM files WHERE ticket_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$ticketId]);
$files = $stmt->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
    <style>
        /* Page specific styles */
        .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; }
        @media (max-width: 1024px) {
            .content-grid { grid-template-columns: 1fr; }
        }
    </style>

            <a href="tickets.php" style="display: inline-block; margin-bottom: 1rem; color: var(--primary-color);">
                <i class="fa-solid fa-arrow-left"></i> Back to All Tickets
            </a>

            <div class="content-grid">
                <div>
                    <div class="content-card">
                        <div class="ticket-detail-header">
                            <div class="ticket-title-row">
                                <div class="ticket-title-text">
                                    #<?= $ticket['id'] ?> <?= htmlspecialchars($ticket['title']) ?>
                                </div>
                                <?= getStatusBadge($ticket['status']) ?>
                            </div>
                            
                            <div class="ticket-info-grid">
                                <div class="ticket-info-item">
                                    <div class="ticket-info-icon"><i class="fa-solid fa-user"></i></div>
                                    <div class="ticket-info-content">
                                        <div class="ticket-info-label">Customer</div>
                                        <div class="ticket-info-value"><?= htmlspecialchars($ticket['customer_name']) ?></div>
                                    </div>
                                </div>
                                
                                <div class="ticket-info-item">
                                    <div class="ticket-info-icon"><i class="fa-solid fa-envelope"></i></div>
                                    <div class="ticket-info-content">
                                        <div class="ticket-info-label">Email</div>
                                        <div class="ticket-info-value"><?= htmlspecialchars($ticket['customer_email']) ?></div>
                                    </div>
                                </div>

                                <div class="ticket-info-item">
                                    <div class="ticket-info-icon"><i class="fa-solid fa-calendar"></i></div>
                                    <div class="ticket-info-content">
                                        <div class="ticket-info-label">Created</div>
                                        <div class="ticket-info-value"><?= formatDate($ticket['created_at']) ?></div>
                                    </div>
                                </div>

                                <div class="ticket-info-item">
                                    <div class="ticket-info-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                                    <div class="ticket-info-content">
                                        <div class="ticket-info-label">Last Updated</div>
                                        <div class="ticket-info-value"><?= formatDate($ticket['updated_at']) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="section-title">Description</h3>
                            <p style="color: var(--text-color); line-height: 1.6; margin-top: 1rem;"><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
                        </div>
                    </div>

                    <?php if (!empty($files)): ?>
                        <div class="content-card">
                            <h3 class="section-title">Attached Files</h3>
                            <div class="file-list">
                                <?php foreach ($files as $file): ?>
                                    <div class="file-card">
                                        <i class="fa-solid <?= getFileIcon($file['filename']) ?> file-icon" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                                        <div class="file-info">
                                            <div class="file-name"><?= htmlspecialchars($file['filename']) ?></div>
                                            <div class="file-meta">
                                                <?= formatFileSize($file['filesize']) ?> • <?= formatDate($file['uploaded_at']) ?>
                                            </div>
                                        </div>
                                        <a href="../assets/uploads/<?= htmlspecialchars($file['filepath']) ?>" class="btn btn-secondary btn-sm" download>
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="content-card">
                        <h3 class="section-title">Messages</h3>
                        
                        <?php if (empty($messages)): ?>
                            <p style="color: var(--light-text); text-align: center; padding: 2rem;">No messages yet</p>
                        <?php else: ?>
                            <div class="messages">
                            <?php foreach ($messages as $message): ?>
                                <div class="message-card <?= $message['is_admin'] ? 'admin' : '' ?>" id="message-<?= $message['id'] ?>">
                                    <div class="message-header">
                                        <div class="message-author">
                                            <?php if ($message['is_admin']): ?>
                                                <span class="message-badge">Staff</span>
                                            <?php else: ?>
                                                <i class="fa-solid fa-user-circle" style="font-size: 1.25rem;"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($message['author_name']) ?>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <span class="message-time"><?= formatDate($message['created_at']) ?></span>
                                            <button type="button" class="btn btn-danger delete-message-btn" data-message-id="<?= $message['id'] ?>" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; line-height: 1; min-width: auto;">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div style="line-height: 1.6; color: var(--text-color);"><?= nl2br(htmlspecialchars($message['content'])) ?></div>
                                </div>
                            <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form id="reply-form" style="margin-top: 2rem;">
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
                    <div class="content-card">
                        <h3 class="section-title">Ticket Management</h3>
                        
                        <form id="update-form">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-select status-select w-100">
                                    <option value="pending" <?= $ticket['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="waiting_parts" <?= $ticket['status'] === 'waiting_parts' ? 'selected' : '' ?>>Waiting for Parts</option>
                                    <option value="completed" <?= $ticket['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $ticket['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="priority">Priority</label>
                                <select id="priority" name="priority" class="form-select priority-select w-100">
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



<script>
    console.log('Script loaded! TICKET_ID:', <?= $ticketId ?>);
    const TICKET_ID = <?= $ticketId ?>;
    const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?>';

    // Helper for AJAX requests
    async function apiRequest(action, data = {}, options = {}) {
        try {
            const response = await fetch('ajax_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action,
                    csrf_token: CSRF_TOKEN,
                    ticket_id: TICKET_ID,
                    ...data
                }),
                ...options
            });
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            return { success: false, message: 'Network error' };
        }
    }

    // Delete Message Logic
    async function handleDelete(messageId) {
        console.log('handleDelete called for ID:', messageId);
        
        // Find the button to update its state
        const btn = document.querySelector(`.delete-message-btn[data-message-id="${messageId}"]`);
        let originalContent = '';
        if (btn) {
            originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            btn.disabled = true;
        }

        // Confirmation removed as per user request
        // if (!window.confirm('Are you sure you want to delete this message?')) { ... }

        try {
            // Add timeout to fetch
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000); // 10s timeout

            const result = await apiRequest('delete_message', { message_id: messageId }, { signal: controller.signal });
            clearTimeout(timeoutId);
            
            console.log('API Result:', result);
            
            if (result.success) {
                const messageEl = document.getElementById(`message-${messageId}`);
                if (messageEl) {
                    messageEl.style.opacity = '0';
                    setTimeout(() => messageEl.remove(), 300);
                }
            } else {
                alert(result.message || 'Failed to delete message');
                if (btn) {
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                }
            }
        } catch (error) {
            console.error('Delete error:', error);
            alert('An error occurred while deleting.');
            if (btn) {
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Event Delegation for Delete Buttons
        document.body.addEventListener('click', function(e) {
            console.log('Click target:', e.target.tagName, e.target.className);
            const btn = e.target.closest('.delete-message-btn');
            console.log('Resolved button:', btn);
            if (btn) {
                e.preventDefault();
                const messageId = btn.dataset.messageId;
                console.log('Message ID:', messageId);
                
                if (messageId) {
                    console.log('Calling handleDelete...');
                    handleDelete(messageId);
                }
            }
        });

        // Handle Reply Form
        const replyForm = document.getElementById('reply-form');
        const messageInput = document.getElementById('message');
        const messagesContainer = document.querySelector('.messages');

        if (replyForm) {
            replyForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const content = messageInput.value.trim();
                if (!content) return;

                const submitBtn = replyForm.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';

                const result = await apiRequest('send_message', { message: content });

                if (result.success) {
                    messageInput.value = '';
                    // Append new message before the form
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = result.html;
                    replyForm.parentNode.insertBefore(tempDiv.firstElementChild, replyForm);
                    
                    // Remove "No messages yet" if it exists
                    const noMsg = messagesContainer.querySelector('p[style*="text-align: center"]');
                    if (noMsg) noMsg.remove();
                } else {
                    alert(result.message || 'Failed to send message');
                }

                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });

            // Enter to Send
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    replyForm.dispatchEvent(new Event('submit'));
                }
            });
        }

        // Handle Ticket Update Form
        const updateForm = document.getElementById('update-form');
        if (updateForm) {
            updateForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const status = document.getElementById('status').value;
                const priority = document.getElementById('priority').value;
                const submitBtn = updateForm.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';

                const result = await apiRequest('update_ticket', { status, priority });

                if (result.success) {
                    alert('Ticket updated successfully');
                } else {
                    alert(result.message || 'Failed to update ticket');
                }

                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        }
    });
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
