<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$userId = getUserId();
$db = new Database();
$conn = $db->connect();

// Get filter parameters
$statusFilter = $_GET['status'] ?? 'all';
$search = $_POST['search'] ?? '';

// Build query
$query = "SELECT * FROM tickets WHERE user_id = ?";
$params = [$userId];

if ($statusFilter !== 'all') {
    $query .= " AND status = ?";
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$tickets = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1 style="color: var(--secondary-color);">My Tickets</h1>
    <a href="create-ticket.php" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> New Ticket
    </a>
</div>

<div class="filters">
    <span style="font-weight: 600; color: var(--secondary-color);">Filter:</span>
    <a href="?status=all" class="filter-btn <?= $statusFilter === 'all' ? 'active' : '' ?>">All</a>
    <a href="?status=pending" class="filter-btn <?= $statusFilter === 'pending' ? 'active' : '' ?>">Pending</a>
    <a href="?status=in_progress" class="filter-btn <?= $statusFilter === 'in_progress' ? 'active' : '' ?>">In Progress</a>
    <a href="?status=completed" class="filter-btn <?= $statusFilter === 'completed' ? 'active' : '' ?>">Completed</a>
</div>

<div class="ticket-list">
    <?php if (empty($tickets)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-inbox"></i>
            <h3>No tickets found</h3>
            <p><?= $statusFilter !== 'all' ? 'Try changing the filter' : 'Create your first ticket to get started' ?></p>
        </div>
    <?php else: ?>
        <?php foreach ($tickets as $ticket): ?>
            <div class="ticket-item">
                <div class="ticket-info">
                    <h4><?= htmlspecialchars($ticket['title']) ?></h4>
                    <p><?= truncate(htmlspecialchars($ticket['description']), 150) ?></p>
                    <p style="margin-top: 0.5rem;">
                        <i class="fa-solid fa-calendar"></i> Created: <?= formatDate($ticket['created_at']) ?>
                        | <i class="fa-solid fa-clock"></i> Updated: <?= formatDate($ticket['updated_at']) ?>
                    </p>
                </div>
                <div class="ticket-meta">
                    <?= getStatusBadge($ticket['status']) ?>
                    <?= getPriorityBadge($ticket['priority']) ?>
                    <a href="ticket-detail.php?id=<?= $ticket['id'] ?>" class="btn btn-secondary">
                        View <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
