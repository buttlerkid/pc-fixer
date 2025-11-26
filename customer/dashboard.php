<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$userId = getUserId();
$db = new Database();
$conn = $db->connect();

// Get user info
$user = getUserById($userId);

// Get ticket statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM tickets WHERE user_id = ?");
$stmt->execute([$userId]);
$totalTickets = $stmt->fetch()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as pending FROM tickets WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$userId]);
$pendingTickets = $stmt->fetch()['pending'];

$stmt = $conn->prepare("SELECT COUNT(*) as in_progress FROM tickets WHERE user_id = ? AND status = 'in_progress'");
$stmt->execute([$userId]);
$inProgressTickets = $stmt->fetch()['in_progress'];

// Get recent tickets
$stmt = $conn->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$userId]);
$recentTickets = $stmt->fetchAll();
?>

<h1 style="margin-bottom: 2rem; color: var(--secondary-color);">
    Welcome back, <?= htmlspecialchars($user['name']) ?>!
</h1>

<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-icon">
            <i class="fa-solid fa-ticket"></i>
        </div>
        <div class="stat-number"><?= $totalTickets ?></div>
        <div class="stat-label">Total Tickets</div>
    </div>

    <div class="stat-card warning">
        <div class="stat-icon">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div class="stat-number"><?= $pendingTickets ?></div>
        <div class="stat-label">Pending Tickets</div>
    </div>

    <div class="stat-card info">
        <div class="stat-icon">
            <i class="fa-solid fa-wrench"></i>
        </div>
        <div class="stat-number"><?= $inProgressTickets ?></div>
        <div class="stat-label">In Progress</div>
    </div>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 class="section-title" style="margin-bottom: 0;">Recent Tickets</h2>
    <a href="create-ticket.php" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Create New Ticket
    </a>
</div>

<div class="ticket-list">
    <?php if (empty($recentTickets)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-inbox"></i>
            <h3>No tickets yet</h3>
            <p>Create your first ticket to get started</p>
            <a href="create-ticket.php" class="btn btn-primary" style="margin-top: 1rem;">
                <i class="fa-solid fa-plus"></i> Create Ticket
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($recentTickets as $ticket): ?>
            <div class="ticket-item">
                <div class="ticket-info">
                    <h4><?= htmlspecialchars($ticket['title']) ?></h4>
                    <p><?= truncate(htmlspecialchars($ticket['description']), 100) ?></p>
                    <p style="margin-top: 0.5rem;">
                        <i class="fa-solid fa-calendar"></i> <?= formatDate($ticket['created_at']) ?>
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

<?php if (!empty($recentTickets)): ?>
    <div style="text-align: center; margin-top: 2rem;">
        <a href="tickets.php" class="btn btn-secondary">
            View All Tickets <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
