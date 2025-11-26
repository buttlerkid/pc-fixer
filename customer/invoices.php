<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$userId = getUserId();
$db = new Database();
$conn = $db->connect();

// Get Invoices
$stmt = $conn->prepare("SELECT * FROM invoices WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$invoices = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Invoices - LocalTechFix</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard { min-height: 100vh; background-color: var(--bg-color); }
        .dashboard-header { background: var(--white); padding: 1.5rem 0; box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .dashboard-nav { display: flex; justify-content: space-between; align-items: center; }
        .dashboard-nav .logo { font-size: 1.5rem; font-weight: 700; color: var(--primary-color); }
        .dashboard-nav .nav-links { display: flex; gap: 2rem; align-items: center; }
        .dashboard-nav .nav-links a { color: var(--text-color); font-weight: 500; }
        .dashboard-nav .nav-links a:hover { color: var(--primary-color); }
        
        .content-card { background: var(--white); padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow-md); }
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { font-weight: 600; color: var(--secondary-color); background: #f9fafb; }
        tr:hover { background: #f9fafb; }
        
        .badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        
        .btn-view { padding: 0.5rem 1rem; background: var(--primary-color); color: white; border-radius: 4px; text-decoration: none; font-size: 0.875rem; transition: background 0.2s; }
        .btn-view:hover { background: var(--primary-dark); }
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
                        <a href="invoices.php" style="color: var(--primary-color); font-weight: 700;"><i class="fa-solid fa-file-invoice-dollar"></i> My Invoices</a>
                        <a href="../knowledge-base.php"><i class="fa-solid fa-book"></i> Knowledge Base</a>
                        <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                        <button class="theme-toggle" aria-label="Toggle dark mode" style="background:none; border:none; color:inherit; cursor:pointer; font-size:1rem;">
                            <i class="fa-solid fa-moon"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <h1 style="margin-bottom: 2rem; color: var(--secondary-color);">My Invoices</h1>

            <div class="content-card">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($invoices)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 2rem; color: var(--light-text);">
                                        You don't have any invoices yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($invoices as $invoice): ?>
                                    <tr>
                                        <td style="font-weight: 500;"><?= htmlspecialchars($invoice['invoice_number']) ?></td>
                                        <td style="font-weight: 600;">$<?= number_format($invoice['amount'], 2) ?></td>
                                        <td>
                                            <?php if ($invoice['status'] === 'paid'): ?>
                                                <span class="badge badge-success">Paid</span>
                                            <?php elseif ($invoice['status'] === 'unpaid'): ?>
                                                <span class="badge badge-warning">Unpaid</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Cancelled</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $invoice['due_date'] ? formatDate($invoice['due_date']) : '-' ?></td>
                                        <td><?= formatDate($invoice['created_at']) ?></td>
                                        <td>
                                            <a href="invoice-view.php?id=<?= $invoice['id'] ?>" class="btn-view">
                                                <i class="fa-solid fa-eye"></i> View
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
    </div>
    <script src="../assets/js/script.js"></script>
</body>
</html>
