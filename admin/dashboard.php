<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$db = new Database();
$conn = $db->connect();

// Get statistics
$stmt = $conn->query("SELECT COUNT(*) as total FROM tickets");
$totalTickets = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as pending FROM tickets WHERE status = 'pending'");
$pendingTickets = $stmt->fetch()['pending'];

$stmt = $conn->query("SELECT COUNT(*) as in_progress FROM tickets WHERE status = 'in_progress'");
$inProgressTickets = $stmt->fetch()['in_progress'];

$stmt = $conn->query("SELECT COUNT(*) as completed FROM tickets WHERE status = 'completed'");
$completedTickets = $stmt->fetch()['completed'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
$totalCustomers = $stmt->fetch()['total'];

// Get recent tickets
$stmt = $conn->query("SELECT t.*, u.name as customer_name FROM tickets t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 10");
$recentTickets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LocalTechFix</title>
    <link rel="stylesheet" href="../public/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard { min-height: 100vh; background-color: var(--bg-color); }
        .dashboard-header { background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); padding: 1.5rem 0; box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .dashboard-nav { display: flex; justify-content: space-between; align-items: center; }
        .dashboard-nav .logo { font-size: 1.5rem; font-weight: 700; color: var(--white); }
        .dashboard-nav .nav-links { display: flex; gap: 2rem; align-items: center; }
        .dashboard-nav .nav-links a { color: var(--white); font-weight: 500; opacity: 0.9; }
        .dashboard-nav .nav-links a:hover { opacity: 1; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: var(--white); padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow-md); }
        .stat-card .stat-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem; }
        .stat-card.primary .stat-icon { background: #eff6ff; color: var(--primary-color); }
        .stat-card.warning .stat-icon { background: #fef3c7; color: #f59e0b; }
        .stat-card.info .stat-icon { background: #dbeafe; color: #3b82f6; }
        .stat-card.success .stat-icon { background: #d1fae5; color: #10b981; }
        .stat-card.secondary .stat-icon { background: #e5e7eb; color: #6b7280; }
        .stat-card .stat-number { font-size: 2rem; font-weight: 700; color: var(--secondary-color); }
        .stat-card .stat-label { color: var(--light-text); font-size: 0.875rem; }
        .ticket-table { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow-md); overflow: hidden; }
        .ticket-table table { width: 100%; border-collapse: collapse; }
        .ticket-table th { background: var(--bg-color); padding: 1rem; text-align: left; font-weight: 600; color: var(--secondary-color); border-bottom: 2px solid var(--border-color); }
        .ticket-table td { padding: 1rem; border-bottom: 1px solid var(--border-color); }
        .ticket-table tr:last-child td { border-bottom: none; }
        .ticket-table tr:hover { background: var(--bg-color); }
        .badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
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
                        <a href="../public/index.php"><i class="fa-solid fa-globe"></i> Site</a>
                        <a href="../public/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <h1 style="margin-bottom: 2rem; color: var(--secondary-color);">Admin Dashboard</h1>

            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon"><i class="fa-solid fa-ticket"></i></div>
                    <div class="stat-number"><?= $totalTickets ?></div>
                    <div class="stat-label">Total Tickets</div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
                    <div class="stat-number"><?= $pendingTickets ?></div>
                    <div class="stat-label">Pending</div>
                </div>

                <div class="stat-card info">
                    <div class="stat-icon"><i class="fa-solid fa-wrench"></i></div>
                    <div class="stat-number"><?= $inProgressTickets ?></div>
                    <div class="stat-label">In Progress</div>
                </div>

                <div class="stat-card success">
                    <div class="stat-icon"><i class="fa-solid fa-check-circle"></i></div>
                    <div class="stat-number"><?= $completedTickets ?></div>
                    <div class="stat-label">Completed</div>
                </div>

                <div class="stat-card secondary">
                    <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-number"><?= $totalCustomers ?></div>
                    <div class="stat-label">Customers</div>
                </div>
            </div>

            <h2 style="color: var(--secondary-color); margin-bottom: 1.5rem;">Recent Tickets</h2>

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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTickets as $ticket): ?>
                            <tr>
                                <td>#<?= $ticket['id'] ?></td>
                                <td><?= htmlspecialchars($ticket['customer_name']) ?></td>
                                <td><?= truncate(htmlspecialchars($ticket['title']), 50) ?></td>
                                <td><?= getStatusBadge($ticket['status']) ?></td>
                                <td><?= getPriorityBadge($ticket['priority']) ?></td>
                                <td><?= formatDate($ticket['created_at'], 'M d, Y') ?></td>
                                <td>
                                    <a href="ticket-detail.php?id=<?= $ticket['id'] ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <a href="tickets.php" class="btn btn-primary">
                    View All Tickets <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
