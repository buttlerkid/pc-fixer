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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Tickets - Admin - LocalTechFix</title>
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
        .badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; white-space: nowrap; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-secondary { background: #e5e7eb; color: #374151; }
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
                        <a href="../index.php"><i class="fa-solid fa-globe"></i> Site</a>
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
                                    <td><?= getStatusBadge($ticket['status']) ?></td>
                                    <td><?= getPriorityBadge($ticket['priority']) ?></td>
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
        </div>
    </div>
    <script src="../assets/js/script.js"></script>
</body>
</html>
