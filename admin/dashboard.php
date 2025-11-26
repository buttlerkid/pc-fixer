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
require_once __DIR__ . '/includes/header.php';
?>

            <style>
                /* Page specific styles */
                .ticket-table { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow-md); overflow: hidden; }
                .ticket-table table { width: 100%; border-collapse: collapse; }
                .ticket-table th { background: var(--bg-color); padding: 1rem; text-align: left; font-weight: 600; color: var(--secondary-color); border-bottom: 2px solid var(--border-color); }
                .ticket-table td { padding: 1rem; border-bottom: 1px solid var(--border-color); }
                .ticket-table tr:last-child td { border-bottom: none; }
                .ticket-table tr:hover { background: var(--bg-color); }
            </style>

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

                <a href="customers.php" style="text-decoration: none; color: inherit;">
                    <div class="stat-card secondary" style="cursor: pointer; transition: transform 0.2s;">
                        <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                        <div class="stat-number"><?= $totalCustomers ?></div>
                        <div class="stat-label">Customers</div>
                    </div>
                </a>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
