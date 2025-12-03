<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

require_once __DIR__ . '/includes/header.php';

$userId = getUserId();
$db = new Database();
$conn = $db->connect();

// Get Invoices
$stmt = $conn->prepare("SELECT * FROM invoices WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$invoices = $stmt->fetchAll();
?>

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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
