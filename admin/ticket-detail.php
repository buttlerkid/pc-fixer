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
        .ticket-detail, .ticket-sidebar, .messages, .files { background: var(--white); padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow-md); margin-bottom: 2rem; }
        .ticket-header { padding-bottom: 1.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); }
        .ticket-title { font-size: 1.75rem; color: var(--secondary-color); margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between; }
        .ticket-meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; background: var(--bg-color); padding: 1.5rem; border-radius: var(--radius); }
        .meta-item { display: flex; align-items: flex-start; gap: 0.75rem; }
        .meta-icon { width: 40px; height: 40px; background: rgba(59, 130, 246, 0.1); color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
        .meta-content div:first-child { font-size: 0.875rem; color: var(--light-text); margin-bottom: 0.25rem; }
        .meta-content div:last-child { font-weight: 500; color: var(--secondary-color); }
        
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

            <a href="tickets.php" style="display: inline-block; margin-bottom: 1rem; color: var(--primary-color);">
                <i class="fa-solid fa-arrow-left"></i> Back to All Tickets
            </a>

            <div class="content-grid">
                <div>
                    <div class="ticket-detail">
                        <div class="ticket-header">
                            <div class="ticket-title">
                                <span>#<?= $ticket['id'] ?> <?= htmlspecialchars($ticket['title']) ?></span>
                                <?= getStatusBadge($ticket['status']) ?>
                            </div>
                            
                            <div class="ticket-meta-grid">
                                <div class="meta-item">
                                    <div class="meta-icon"><i class="fa-solid fa-user"></i></div>
                                    <div class="meta-content">
                                        <div>Customer</div>
                                        <div><?= htmlspecialchars($ticket['customer_name']) ?></div>
                                    </div>
                                </div>
                                
                                <div class="meta-item">
                                    <div class="meta-icon"><i class="fa-solid fa-envelope"></i></div>
                                    <div class="meta-content">
                                        <div>Email</div>
                                        <div><?= htmlspecialchars($ticket['customer_email']) ?></div>
                                    </div>
                                </div>

                                <div class="meta-item">
                                    <div class="meta-icon"><i class="fa-solid fa-calendar"></i></div>
                                    <div class="meta-content">
                                        <div>Created</div>
                                        <div><?= formatDate($ticket['created_at']) ?></div>
                                    </div>
                                </div>

                                <div class="meta-item">
                                    <div class="meta-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                                    <div class="meta-content">
                                        <div>Last Updated</div>
                                        <div><?= formatDate($ticket['updated_at']) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="section-title">Description</h3>
                            <p style="color: var(--text-color); line-height: 1.6;"><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
                        </div>
                    </div>

                    <?php if (!empty($files)): ?>
                        <div class="files">
                            <h3 class="section-title">Attached Files</h3>
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
                        <h3 class="section-title">Messages</h3>
                        
                        <?php if (empty($messages)): ?>
                            <p style="color: var(--light-text); text-align: center; padding: 2rem;">No messages yet</p>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="message <?= $message['is_admin'] ? 'admin' : '' ?>" id="message-<?= $message['id'] ?>">
                                    <div class="message-header">
                                        <span class="message-author">
                                            <?= $message['is_admin'] ? '<i class="fa-solid fa-shield-halved"></i> ' : '' ?>
                                            <?= htmlspecialchars($message['author_name']) ?>
                                        </span>
                                        <div>
                                            <span class="message-time" style="margin-right: 0.5rem;"><?= formatDate($message['created_at']) ?></span>
                                            <button type="button" class="btn btn-danger delete-message-btn" data-message-id="<?= $message['id'] ?>" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; line-height: 1; background-color: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div><?= nl2br(htmlspecialchars($message['content'])) ?></div>
                                </div>
                            <?php endforeach; ?>
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
                    <div class="ticket-sidebar">
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
