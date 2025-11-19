<?php
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets - LocalTechFix</title>
    <link rel="stylesheet" href="../public/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard { min-height: 100vh; background-color: var(--bg-color); }
        .dashboard-header { background: var(--white); padding: 1.5rem 0; box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .dashboard-nav { display: flex; justify-content: space-between; align-items: center; }
        .dashboard-nav .logo { font-size: 1.5rem; font-weight: 700; color: var(--primary-color); }
        .dashboard-nav .nav-links { display: flex; gap: 2rem; align-items: center; }
        .dashboard-nav .nav-links a { color: var(--text-color); font-weight: 500; }
        .dashboard-nav .nav-links a:hover { color: var(--primary-color); }
        .filters { background: var(--white); padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow-md); margin-bottom: 2rem; display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; }
        .filter-btn { padding: 0.5rem 1rem; border-radius: var(--radius); border: 1px solid var(--border-color); background: var(--white); cursor: pointer; transition: all 0.3s ease; }
        .filter-btn.active { background: var(--primary-color); color: var(--white); border-color: var(--primary-color); }
        .ticket-list { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow-md); overflow: hidden; }
        .ticket-item { padding: 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .ticket-item:last-child { border-bottom: none; }
        .ticket-item:hover { background: var(--bg-color); }
        .ticket-info h4 { color: var(--secondary-color); margin-bottom: 0.5rem; }
        .ticket-info p { color: var(--light-text); font-size: 0.875rem; }
        .ticket-meta { display: flex; gap: 1rem; align-items: center; }
        .badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-secondary { background: #e5e7eb; color: #374151; }
        .empty-state { text-align: center; padding: 3rem; color: var(--light-text); }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }
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
                        <a href="../public/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
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
        </div>
    </div>
</body>
</html>
