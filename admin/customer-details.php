<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

if (!isset($_GET['id'])) {
    echo "Invalid request";
    exit;
}

$db = new Database();
$conn = $db->connect();

$customerId = (int)$_GET['id'];

// Get customer details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'customer'");
$stmt->execute([$customerId]);
$customer = $stmt->fetch();

if (!$customer) {
    echo "Customer not found";
    exit;
}

// Get customer's tickets
$stmt = $conn->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$customerId]);
$tickets = $stmt->fetchAll();

// Get ticket statistics
$stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM tickets WHERE user_id = ? GROUP BY status");
$stmt->execute([$customerId]);
$ticketStats = [];
while ($row = $stmt->fetch()) {
    $ticketStats[$row['status']] = $row['count'];
}
?>

<style>
    .customer-info { margin-bottom: 1.5rem; }
    .customer-info .info-row { display: flex; padding: 0.75rem 0; border-bottom: 1px solid var(--border-color); }
    .customer-info .info-row:last-child { border-bottom: none; }
    .customer-info .info-label { font-weight: 600; width: 120px; color: var(--secondary-color); }
    .customer-info .info-value { flex: 1; color: var(--light-text); }
    
    .stats-mini { display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 1rem; margin: 1.5rem 0; }
    .stat-mini { background: var(--bg-color); padding: 1rem; border-radius: var(--radius); text-align: center; }
    .stat-mini .number { font-size: 1.5rem; font-weight: 700; color: var(--primary-color); }
    .stat-mini .label { font-size: 0.75rem; color: var(--light-text); margin-top: 0.25rem; }
    
    .ticket-list { max-height: 300px; overflow-y: auto; }
    .ticket-item { padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius); margin-bottom: 0.5rem; }
    .ticket-item:hover { background: var(--bg-color); }
    .ticket-title { font-weight: 600; color: var(--secondary-color); margin-bottom: 0.25rem; }
    .ticket-meta { font-size: 0.875rem; color: var(--light-text); }
</style>

<div class="customer-info">
    <div class="info-row">
        <div class="info-label">Customer ID:</div>
        <div class="info-value">#<?= $customer['id'] ?></div>
    </div>
    <div class="info-row">
        <div class="info-label">Name:</div>
        <div class="info-value"><?= htmlspecialchars($customer['name']) ?></div>
    </div>
    <div class="info-row">
        <div class="info-label">Email:</div>
        <div class="info-value"><?= htmlspecialchars($customer['email']) ?></div>
    </div>
    <div class="info-row">
        <div class="info-label">Joined:</div>
        <div class="info-value"><?= formatDate($customer['created_at'], 'F d, Y') ?></div>
    </div>
    <div class="info-row">
        <div class="info-label">Total Tickets:</div>
        <div class="info-value"><?= count($tickets) ?></div>
    </div>
</div>

<?php if (!empty($ticketStats)): ?>
    <h3 style="margin: 1.5rem 0 1rem; color: var(--secondary-color);">Ticket Statistics</h3>
    <div class="stats-mini">
        <div class="stat-mini">
            <div class="number"><?= $ticketStats['pending'] ?? 0 ?></div>
            <div class="label">Pending</div>
        </div>
        <div class="stat-mini">
            <div class="number"><?= $ticketStats['in_progress'] ?? 0 ?></div>
            <div class="label">In Progress</div>
        </div>
        <div class="stat-mini">
            <div class="number"><?= $ticketStats['completed'] ?? 0 ?></div>
            <div class="label">Completed</div>
        </div>
    </div>
<?php endif; ?>

<h3 style="margin: 1.5rem 0 1rem; color: var(--secondary-color);">Recent Tickets</h3>
<div class="ticket-list">
    <?php if (empty($tickets)): ?>
        <p style="text-align: center; color: var(--light-text); padding: 2rem;">No tickets found</p>
    <?php else: ?>
        <?php foreach (array_slice($tickets, 0, 5) as $ticket): ?>
            <div class="ticket-item">
                <div class="ticket-title">
                    #<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['title']) ?>
                </div>
                <div class="ticket-meta">
                    <?= getStatusBadge($ticket['status']) ?>
                    <?= getPriorityBadge($ticket['priority']) ?>
                    • <?= formatDate($ticket['created_at'], 'M d, Y') ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div style="margin-top: 1.5rem;">
    <a href="tickets.php?customer=<?= $customer['id'] ?>" class="btn btn-primary">
        <i class="fa-solid fa-ticket"></i> View All Tickets
    </a>
</div>
