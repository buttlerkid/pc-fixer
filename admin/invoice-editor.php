<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

require_once __DIR__ . '/includes/header.php';

$db = new Database();
$conn = $db->connect();

$invoiceId = $_GET['id'] ?? null;
$invoice = null;
$invoiceItems = [];
$error = '';
$success = '';

// Get Customers for dropdown
$stmt = $conn->query("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name");
$customers = $stmt->fetchAll();

// Get Tickets for dropdown
$stmt = $conn->query("SELECT id, title, user_id FROM tickets ORDER BY created_at DESC");
$tickets = $stmt->fetchAll();

if ($invoiceId) {
    // Get Invoice Details
    $stmt = $conn->prepare("SELECT * FROM invoices WHERE id = ?");
    $stmt->execute([$invoiceId]);
    $invoice = $stmt->fetch();
    
    if (!$invoice) {
        redirect('invoices.php');
    }
    
    // Get Invoice Items
    $stmt = $conn->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
    $stmt->execute([$invoiceId]);
    $invoiceItems = $stmt->fetchAll();
} else {
    // Default values for new invoice
    $invoice = [
        'invoice_number' => 'INV-' . date('Ymd') . '-' . rand(100, 999),
        'user_id' => '',
        'ticket_id' => '',
        'due_date' => date('Y-m-d', strtotime('+7 days')),
        'status' => 'unpaid'
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        $userId = $_POST['user_id'];
        $ticketId = !empty($_POST['ticket_id']) ? $_POST['ticket_id'] : null;
        $invoiceNumber = $_POST['invoice_number'];
        $dueDate = $_POST['due_date'];
        $status = $_POST['status'];
        
        $items = [];
        $descriptions = $_POST['description'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $unitPrices = $_POST['unit_price'] ?? [];
        
        $totalAmount = 0;
        
        for ($i = 0; $i < count($descriptions); $i++) {
            if (!empty($descriptions[$i])) {
                $qty = (int)$quantities[$i];
                $price = (float)$unitPrices[$i];
                $lineTotal = $qty * $price;
                $totalAmount += $lineTotal;
                
                $items[] = [
                    'description' => $descriptions[$i],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total' => $lineTotal
                ];
            }
        }
        
        if (empty($userId)) {
            $error = 'Customer is required';
        } elseif (empty($items)) {
            $error = 'At least one item is required';
        } else {
            try {
                $conn->beginTransaction();
                
                if ($invoiceId) {
                    // Update Invoice
                    $stmt = $conn->prepare("UPDATE invoices SET user_id = ?, ticket_id = ?, invoice_number = ?, due_date = ?, status = ?, amount = ? WHERE id = ?");
                    $stmt->execute([$userId, $ticketId, $invoiceNumber, $dueDate, $status, $totalAmount, $invoiceId]);
                    
                    // Delete existing items
                    $stmt = $conn->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
                    $stmt->execute([$invoiceId]);
                    
                    $currentInvoiceId = $invoiceId;
                    $success = 'Invoice updated successfully';
                } else {
                    // Create Invoice
                    $stmt = $conn->prepare("INSERT INTO invoices (user_id, ticket_id, invoice_number, due_date, status, amount) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$userId, $ticketId, $invoiceNumber, $dueDate, $status, $totalAmount]);
                    $currentInvoiceId = $conn->lastInsertId();
                    $success = 'Invoice created successfully';
                }
                
                // Insert Items
                $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total) VALUES (?, ?, ?, ?, ?)");
                foreach ($items as $item) {
                    $stmt->execute([$currentInvoiceId, $item['description'], $item['quantity'], $item['unit_price'], $item['total']]);
                }
                
                $conn->commit();
                
                if (!$invoiceId) {
                    redirect('invoices.php');
                } else {
                    // Refresh data
                    $stmt = $conn->prepare("SELECT * FROM invoices WHERE id = ?");
                    $stmt->execute([$invoiceId]);
                    $invoice = $stmt->fetch();
                    
                    $stmt = $conn->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
                    $stmt->execute([$invoiceId]);
                    $invoiceItems = $stmt->fetchAll();
                }
                
            } catch (Exception $e) {
                $conn->rollBack();
                $error = 'Error saving invoice: ' . $e->getMessage();
            }
        }
    }
}
?>



<div style="margin-bottom: 2rem;">
    <a href="invoices.php" style="color: var(--primary-color); text-decoration: none;">
        <i class="fa-solid fa-arrow-left"></i> Back to Invoices
    </a>
</div>

<div class="content-card">
    <h1 style="margin-bottom: 2rem; color: var(--secondary-color);"><?= $invoiceId ? 'Edit' : 'Create New' ?> Invoice</h1>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="invoiceForm">
        <?= csrfField() ?>
        
        <div class="form-row">
            <div class="form-group">
                <label for="user_id">Customer</label>
                <select id="user_id" name="user_id" class="form-control form-select" required>
                    <option value="">Select Customer</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= $customer['id'] ?>" <?= $invoice['user_id'] == $customer['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($customer['name']) ?> (<?= htmlspecialchars($customer['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="ticket_id">Related Ticket (Optional)</label>
                <select id="ticket_id" name="ticket_id" class="form-control form-select">
                    <option value="">None</option>
                    <?php foreach ($tickets as $ticket): ?>
                        <option value="<?= $ticket['id'] ?>" <?= $invoice['ticket_id'] == $ticket['id'] ? 'selected' : '' ?>>
                            #<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="invoice_number">Invoice Number</label>
                <input type="text" id="invoice_number" name="invoice_number" class="form-control" value="<?= htmlspecialchars($invoice['invoice_number']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date" class="form-control" value="<?= $invoice['due_date'] ?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control form-select" required>
                <option value="unpaid" <?= $invoice['status'] === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                <option value="paid" <?= $invoice['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                <option value="cancelled" <?= $invoice['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
        
        <h3 class="section-title">Invoice Items</h3>
        
        <div class="table-container">
            <table class="invoice-items-table" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width: 50%;">Description</th>
                        <th style="width: 15%;">Quantity</th>
                        <th style="width: 20%;">Unit Price ($)</th>
                        <th style="width: 10%;">Total</th>
                        <th style="width: 5%;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($invoiceItems)): ?>
                        <?php foreach ($invoiceItems as $item): ?>
                            <tr>
                                <td><input type="text" name="description[]" value="<?= htmlspecialchars($item['description']) ?>" required placeholder="Item description"></td>
                                <td><input type="number" name="quantity[]" value="<?= $item['quantity'] ?>" min="1" required onchange="calculateTotals()"></td>
                                <td><input type="number" name="unit_price[]" value="<?= $item['unit_price'] ?>" step="0.01" min="0" required onchange="calculateTotals()"></td>
                                <td class="line-total">$<?= number_format($item['total'], 2) ?></td>
                                <td style="text-align: center;"><button type="button" class="btn-icon-danger" onclick="removeItem(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td><input type="text" name="description[]" required placeholder="Item description"></td>
                            <td><input type="number" name="quantity[]" value="1" min="1" required onchange="calculateTotals()"></td>
                            <td><input type="number" name="unit_price[]" value="0.00" step="0.01" min="0" required onchange="calculateTotals()"></td>
                            <td class="line-total">$0.00</td>
                            <td style="text-align: center;"><button type="button" class="btn-icon-danger" onclick="removeItem(this)"><i class="fa-solid fa-trash"></i></button></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <button type="button" class="btn-dashed" onclick="addItem()">
            <i class="fa-solid fa-plus"></i> Add Item
        </button>
        
        <div class="invoice-totals">
            <div class="totals-card">
                <div class="total-row grand-total">
                    <span>Total Amount:</span>
                    <span id="grandTotal">$<?= number_format($invoice['amount'] ?? 0, 2) ?></span>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">
                <i class="fa-solid fa-save"></i> <?= $invoiceId ? 'Update Invoice' : 'Create Invoice' ?>
            </button>
        </div>
    </form>
</div>

<script>
    function addItem() {
        const tbody = document.querySelector('#itemsTable tbody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="description[]" required placeholder="Item description"></td>
            <td><input type="number" name="quantity[]" value="1" min="1" required onchange="calculateTotals()"></td>
            <td><input type="number" name="unit_price[]" value="0.00" step="0.01" min="0" required onchange="calculateTotals()"></td>
            <td class="line-total">$0.00</td>
            <td style="text-align: center;"><button type="button" class="btn-icon-danger" onclick="removeItem(this)"><i class="fa-solid fa-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
    }
    
    function removeItem(btn) {
        const tbody = document.querySelector('#itemsTable tbody');
        if (tbody.children.length > 1) {
            btn.closest('tr').remove();
            calculateTotals();
        } else {
            alert('You must have at least one item.');
        }
    }
    
    function calculateTotals() {
        const rows = document.querySelectorAll('#itemsTable tbody tr');
        let grandTotal = 0;
        
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('input[name="quantity[]"]').value) || 0;
            const price = parseFloat(row.querySelector('input[name="unit_price[]"]').value) || 0;
            const total = qty * price;
            
            row.querySelector('.line-total').textContent = '$' + total.toFixed(2);
            grandTotal += total;
        });
        
        document.getElementById('grandTotal').textContent = '$' + grandTotal.toFixed(2);
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
