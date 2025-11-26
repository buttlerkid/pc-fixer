<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$invoiceId = $_GET['id'] ?? null;

if (!$invoiceId) {
    redirect('invoices.php');
}

$db = new Database();
$conn = $db->connect();

// Get Invoice Details
$stmt = $conn->prepare("SELECT i.*, u.name as customer_name, u.email as customer_email, u.address as customer_address, u.phone as customer_phone 
                        FROM invoices i 
                        JOIN users u ON i.user_id = u.id 
                        WHERE i.id = ?");
$stmt->execute([$invoiceId]);
$invoice = $stmt->fetch();

if (!$invoice) {
    redirect('invoices.php');
}

// Get Invoice Items
$stmt = $conn->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$stmt->execute([$invoiceId]);
$items = $stmt->fetchAll();

// Get Company Settings (Mocked for now, or use getSetting if available)
$companyName = getSetting('company_name', 'LocalTechFix');
$companyAddress = getSetting('company_address', '123 Tech Street, Silicon Valley, CA 94000');
$companyPhone = getSetting('company_phone', '(555) 123-4567');
$companyEmail = getSetting('company_email', 'support@localtechfix.com');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= htmlspecialchars($invoice['invoice_number']) ?> - LocalTechFix</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --text-color: #1f2937;
            --light-text: #6b7280;
            --border-color: #e5e7eb;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            background: #f3f4f6;
            margin: 0;
            padding: 2rem;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 3rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .company-info h1 {
            color: var(--primary-color);
            margin: 0 0 0.5rem 0;
        }
        
        .invoice-details {
            text-align: right;
        }
        
        .invoice-details h2 {
            margin: 0 0 0.5rem 0;
            color: var(--light-text);
        }
        
        .bill-to {
            margin-bottom: 2rem;
        }
        
        .bill-to h3 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        
        th {
            background: #f9fafb;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .totals {
            display: flex;
            justify-content: flex-end;
        }
        
        .totals-table {
            width: 300px;
        }
        
        .totals-table td {
            padding: 0.5rem 1rem;
            border: none;
        }
        
        .totals-table .total-row {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
            border-top: 2px solid var(--border-color);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        
        .status-paid { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .status-unpaid { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .status-cancelled { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        .no-print {
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-size: 1rem;
        }
        
        .btn-primary { background-color: var(--primary-color); color: white; }
        .btn-primary:hover { background-color: #1d4ed8; }
        
        .btn-secondary { background-color: white; color: var(--text-color); border: 1px solid var(--border-color); }
        .btn-secondary:hover { background-color: #f9fafb; }
        
        @media print {
            body { background: white; padding: 0; }
            .invoice-container { box-shadow: none; padding: 0; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="invoices.php" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back to Invoices
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fa-solid fa-print"></i> Print Invoice
        </button>
        <?php if ($invoice['status'] === 'unpaid'): ?>
            <a href="invoice-editor.php?id=<?= $invoice['id'] ?>" class="btn btn-secondary">
                <i class="fa-solid fa-edit"></i> Edit
            </a>
        <?php endif; ?>
    </div>

    <div class="invoice-container">
        <div class="invoice-header">
            <div class="company-info">
                <h1><?= htmlspecialchars($companyName) ?></h1>
                <div><?= htmlspecialchars($companyAddress) ?></div>
                <div><?= htmlspecialchars($companyPhone) ?></div>
                <div><?= htmlspecialchars($companyEmail) ?></div>
            </div>
            <div class="invoice-details">
                <h2>INVOICE</h2>
                <div style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">
                    #<?= htmlspecialchars($invoice['invoice_number']) ?>
                </div>
                <div>
                    <?php if ($invoice['status'] === 'paid'): ?>
                        <span class="status-badge status-paid">PAID</span>
                    <?php elseif ($invoice['status'] === 'unpaid'): ?>
                        <span class="status-badge status-unpaid">UNPAID</span>
                    <?php else: ?>
                        <span class="status-badge status-cancelled">CANCELLED</span>
                    <?php endif; ?>
                </div>
                <div><strong>Date:</strong> <?= formatDate($invoice['created_at']) ?></div>
                <div><strong>Due Date:</strong> <?= formatDate($invoice['due_date']) ?></div>
            </div>
        </div>
        
        <div class="bill-to">
            <h3>Bill To:</h3>
            <div style="font-weight: 600; font-size: 1.1rem;"><?= htmlspecialchars($invoice['customer_name']) ?></div>
            <div><?= htmlspecialchars($invoice['customer_email']) ?></div>
            <?php if (!empty($invoice['customer_phone'])): ?>
                <div><?= htmlspecialchars($invoice['customer_phone']) ?></div>
            <?php endif; ?>
            <?php if (!empty($invoice['customer_address'])): ?>
                <div style="white-space: pre-line;"><?= htmlspecialchars($invoice['customer_address']) ?></div>
            <?php endif; ?>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th style="width: 15%; text-align: center;">Quantity</th>
                    <th style="width: 15%; text-align: right;">Unit Price</th>
                    <th style="width: 20%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['description']) ?></td>
                        <td style="text-align: center;"><?= $item['quantity'] ?></td>
                        <td style="text-align: right;">$<?= number_format($item['unit_price'], 2) ?></td>
                        <td style="text-align: right;">$<?= number_format($item['total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="totals">
            <table class="totals-table">
                <tr>
                    <td style="text-align: right;"><strong>Subtotal:</strong></td>
                    <td style="text-align: right;">$<?= number_format($invoice['amount'], 2) ?></td>
                </tr>
                <tr>
                    <td style="text-align: right;"><strong>Tax (0%):</strong></td>
                    <td style="text-align: right;">$0.00</td>
                </tr>
                <tr class="total-row">
                    <td style="text-align: right;">Total:</td>
                    <td style="text-align: right;">$<?= number_format($invoice['amount'], 2) ?></td>
                </tr>
            </table>
        </div>
        
        <div style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid var(--border-color); text-align: center; color: var(--light-text); font-size: 0.875rem;">
            <p>Thank you for your business!</p>
            <p>Please make checks payable to <?= htmlspecialchars($companyName) ?></p>
        </div>
    </div>
</body>
</html>
