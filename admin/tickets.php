<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$db = new Database();
$conn = $db->connect();

// Get filter parameters
$statusFilter = $_GET['status'] ?? 'all';
$priorityFilter = $_GET['priority'] ?? 'all';

// Build query
$query = "SELECT t.*, u.name as customer_name, u.email as customer_email FROM tickets t JOIN users u ON t.user_id = u.id WHERE 1=1";
$params = [];

if ($statusFilter !== 'all') {
    $query .= " AND t.status = ?";
    $params[] = $statusFilter;
}

if ($priorityFilter !== 'all') {
    $query .= " AND t.priority = ?";
    $params[] = $priorityFilter;
}

$query .= " ORDER BY t.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$tickets = $stmt->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
            <style>
                /* Page specific styles */
                .filters { background: var(--white); padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow-md); margin-bottom: 2rem; display: flex; gap: 2rem; flex-wrap: wrap; align-items: center; }
                .filter-group { display: flex; gap: 0.5rem; align-items: center; }
                .filter-btn { padding: 0.5rem 1rem; border-radius: var(--radius); border: 1px solid var(--border-color); background: var(--white); cursor: pointer; transition: all 0.3s ease; text-decoration: none; color: var(--text-color); }
                .filter-btn.active { background: var(--primary-color); color: var(--white); border-color: var(--primary-color); }
                .ticket-table { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow-md); overflow-x: auto; }
                .ticket-table table { width: 100%; border-collapse: collapse; min-width: 900px; }
                .ticket-table th { background: var(--bg-color); padding: 1rem; text-align: left; font-weight: 600; color: var(--secondary-color); border-bottom: 2px solid var(--border-color); }
                .ticket-table td { padding: 1rem; border-bottom: 1px solid var(--border-color); }
                .ticket-table tr:last-child td { border-bottom: none; }
                .ticket-table tr:hover { background: var(--bg-color); }
            </style>

            <h1 style="margin-bottom: 2rem; color: var(--secondary-color);">All Tickets (<?= count($tickets) ?>)</h1>

            <div class="filters">
                <div class="filter-group">
                    <strong>Status:</strong>
                    <a href="?status=all&priority=<?= $priorityFilter ?>" class="filter-btn <?= $statusFilter === 'all' ? 'active' : '' ?>">All</a>
                    <a href="?status=pending&priority=<?= $priorityFilter ?>" class="filter-btn <?= $statusFilter === 'pending' ? 'active' : '' ?>">Pending</a>
                    <a href="?status=in_progress&priority=<?= $priorityFilter ?>" class="filter-btn <?= $statusFilter === 'in_progress' ? 'active' : '' ?>">In Progress</a>
                    <a href="?status=completed&priority=<?= $priorityFilter ?>" class="filter-btn <?= $statusFilter === 'completed' ? 'active' : '' ?>">Completed</a>
                </div>
                <div class="filter-group">
                    <strong>Priority:</strong>
                    <a href="?status=<?= $statusFilter ?>&priority=all" class="filter-btn <?= $priorityFilter === 'all' ? 'active' : '' ?>">All</a>
                    <a href="?status=<?= $statusFilter ?>&priority=urgent" class="filter-btn <?= $priorityFilter === 'urgent' ? 'active' : '' ?>">Urgent</a>
                    <a href="?status=<?= $statusFilter ?>&priority=high" class="filter-btn <?= $priorityFilter === 'high' ? 'active' : '' ?>">High</a>
                    <a href="?status=<?= $statusFilter ?>&priority=medium" class="filter-btn <?= $priorityFilter === 'medium' ? 'active' : '' ?>">Medium</a>
                </div>
            </div>

            <div class="ticket-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tickets)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 3rem; color: var(--light-text);">
                                    No tickets found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td><strong>#<?= $ticket['id'] ?></strong></td>
                                    <td>
                                        <?= htmlspecialchars($ticket['customer_name']) ?><br>
                                        <small style="color: var(--light-text);"><?= htmlspecialchars($ticket['customer_email']) ?></small>
                                    </td>
                                    <td><?= truncate(htmlspecialchars($ticket['title']), 40) ?></td>
                                    <td>
                                        <select onchange="updateTicket(<?= $ticket['id'] ?>, 'status', this.value)" class="form-select status-select">
                                            <option value="pending" <?= $ticket['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                            <option value="waiting_parts" <?= $ticket['status'] === 'waiting_parts' ? 'selected' : '' ?>>Waiting for Parts</option>
                                            <option value="completed" <?= $ticket['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                            <option value="cancelled" <?= $ticket['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select onchange="updateTicket(<?= $ticket['id'] ?>, 'priority', this.value)" class="form-select priority-select">
                                            <option value="low" <?= $ticket['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
                                            <option value="medium" <?= $ticket['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                                            <option value="high" <?= $ticket['priority'] === 'high' ? 'selected' : '' ?>>High</option>
                                            <option value="urgent" <?= $ticket['priority'] === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                                        </select>
                                    </td>
                                    <td><?= formatDate($ticket['created_at'], 'M d, Y') ?></td>
                                    <td><?= formatDate($ticket['updated_at'], 'M d, Y') ?></td>
                                    <td>
                                        <a href="ticket-detail.php?id=<?= $ticket['id'] ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                            Manage
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?>';

async function updateTicket(ticketId, field, value) {
    const data = {
        action: 'update_ticket',
        ticket_id: ticketId,
        csrf_token: CSRF_TOKEN
    };
    data[field] = value;

    try {
        const response = await fetch('ajax_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Optional: Show a toast or small indicator of success
            const select = document.querySelector(`select[onchange*="${ticketId}"][onchange*="'${field}'"]`);
            if (select) {
                const originalBorder = select.style.borderColor;
                select.style.borderColor = '#10b981'; // Green
                setTimeout(() => select.style.borderColor = originalBorder, 1000);
            }
        } else {
            alert(result.message || 'Failed to update ticket');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while updating the ticket');
    }
}
</script>
