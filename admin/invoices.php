<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

require_once __DIR__ . '/includes/header.php';

$db = new Database();
$conn = $db->connect();

// Handle Delete
if (isset($_POST['delete_id'])) {
    if (validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $stmt = $conn->prepare("DELETE FROM invoices WHERE id = ?");
        $stmt->execute([$_POST['delete_id']]);
        $success = "Invoice deleted successfully";
    }
}

// Handle Mark as Paid
if (isset($_POST['mark_paid_id'])) {
    if (validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $stmt = $conn->prepare("UPDATE invoices SET status = 'paid' WHERE id = ?");
        $stmt->execute([$_POST['mark_paid_id']]);
        $success = "Invoice marked as paid";
    }
}

// Get Invoices
$query = "SELECT i.*, u.name as customer_name, u.email as customer_email 
          FROM invoices i 
          JOIN users u ON i.user_id = u.id 
          ORDER BY i.created_at DESC";
$stmt = $conn->query($query);
$invoices = $stmt->fetchAll();
?>



<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1 style="color: var(--secondary-color);">Invoices</h1>
    <a href="invoice-editor.php" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Create Invoice
    </a>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success" style="margin-bottom: 1rem;">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<div class="content-card">
    <div class="data-table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: var(--light-text);">
                            No invoices found. Create one to get started!
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td style="font-weight: 500;">
                                <a href="invoice-view.php?id=<?= $invoice['id'] ?>" style="color: var(--primary-color); text-decoration: none;">
                                     <?= htmlspecialchars($invoice['invoice_number']) ?>
                                </a>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($invoice['customer_name']) ?></div>
                                <div style="font-size: 0.8rem; color: var(--light-text);"><?= htmlspecialchars($invoice['customer_email']) ?></div>
                            </td>
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
                                <a href="invoice-view.php?id=<?= $invoice['id'] ?>" class="btn btn-secondary btn-sm" title="View">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="invoice-editor.php?id=<?= $invoice['id'] ?>" class="btn btn-primary btn-sm" title="Edit">
                                    <i class="fa-solid fa-edit"></i>
                                </a>
                                <?php if ($invoice['status'] === 'unpaid'): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Mark this invoice as paid?');">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="mark_paid_id" value="<?= $invoice['id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm" title="Mark as Paid">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="delete_id" value="<?= $invoice['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
