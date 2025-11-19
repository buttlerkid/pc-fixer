<?php
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - LocalTechFix</title>
    <link rel="stylesheet" href="../public/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard {
            min-height: 100vh;
            background-color: var(--bg-color);
        }
        .dashboard-header {
            background: var(--white);
            padding: 1.5rem 0;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }
        .dashboard-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dashboard-nav .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        .dashboard-nav .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        .dashboard-nav .nav-links a {
            color: var(--text-color);
            font-weight: 500;
        }
        .dashboard-nav .nav-links a:hover {
            color: var(--primary-color);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--white);
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
        }
        .stat-card .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .stat-card.primary .stat-icon {
            background: #eff6ff;
            color: var(--primary-color);
        }
        .stat-card.warning .stat-icon {
            background: #fef3c7;
            color: #f59e0b;
        }
        .stat-card.info .stat-icon {
            background: #dbeafe;
            color: #3b82f6;
        }
        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
        }
        .stat-card .stat-label {
            color: var(--light-text);
            font-size: 0.875rem;
        }
        .section-title {
            font-size: 1.5rem;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
        }
        .ticket-list {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }
        .ticket-item {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ticket-item:last-child {
            border-bottom: none;
        }
        .ticket-item:hover {
            background: var(--bg-color);
        }
        .ticket-info h4 {
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }
        .ticket-info p {
            color: var(--light-text);
            font-size: 0.875rem;
        }
        .ticket-meta {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        .badge-secondary {
            background: #e5e7eb;
            color: #374151;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--light-text);
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="dashboard-header">
            <div class="container">
                <div class="dashboard-nav">
                    <div class="logo">
                        <i class="fa-solid fa-microchip"></i> LocalTechFix
                    </div>
                    <div class="nav-links">
                        <a href="dashboard.php"><i class="fa-solid fa-home"></i> Dashboard</a>
                        <a href="tickets.php"><i class="fa-solid fa-ticket"></i> My Tickets</a>
                        <a href="create-ticket.php"><i class="fa-solid fa-plus"></i> New Ticket</a>
                        <a href="../public/index.php"><i class="fa-solid fa-globe"></i> Home</a>
                        <a href="../public/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
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
        </div>
    </div>
</body>
</html>
