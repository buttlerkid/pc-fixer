<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$db = new Database();
$conn = $db->connect();

$success = '';
$error = '';

// Handle customer deletion
if (isset($_POST['delete_customer']) && isset($_POST['customer_id'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        $customerId = (int)$_POST['customer_id'];
        
        // Check if customer has tickets
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tickets WHERE user_id = ?");
        $stmt->execute([$customerId]);
        $ticketCount = $stmt->fetch()['count'];
        
        if ($ticketCount > 0) {
            $error = "Cannot delete customer with existing tickets. Please delete or reassign their tickets first.";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'");
            if ($stmt->execute([$customerId])) {
                $success = "Customer deleted successfully";
            } else {
                $error = "Failed to delete customer";
            }
        }
    }
}

// Handle customer update
if (isset($_POST['update_customer'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        $customerId = (int)$_POST['customer_id'];
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        
        if (empty($name) || empty($email)) {
            $error = "Name and email are required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } else {
            // Check if email already exists for another user
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $customerId]);
            if ($stmt->fetch()) {
                $error = "Email already in use by another customer";
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ? AND role = 'customer'");
                if ($stmt->execute([$name, $email, $customerId])) {
                    $success = "Customer updated successfully";
                } else {
                    $error = "Failed to update customer";
                }
            }
            }
        }
    }


// Handle password reset
if (isset($_POST['reset_password'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        $customerId = (int)$_POST['customer_id'];
        $newPassword = trim($_POST['new_password']);
        $confirmPassword = trim($_POST['confirm_password']);
        
        if (empty($newPassword) || empty($confirmPassword)) {
            $error = "Both password fields are required";
        } elseif (strlen($newPassword) < 6) {
            $error = "Password must be at least 6 characters";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "Passwords do not match";
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'customer'");
            if ($stmt->execute([$hashedPassword, $customerId])) {
                $success = "Password reset successfully";
            } else {
                $error = "Failed to reset password";
            }
        }
    }
}

// Get search parameter
$search = $_GET['search'] ?? '';

// Get all customers with search
if ($search) {
    $stmt = $conn->prepare("SELECT u.*, COUNT(t.id) as ticket_count FROM users u LEFT JOIN tickets t ON u.id = t.user_id WHERE u.role = 'customer' AND (u.name LIKE ? OR u.email LIKE ?) GROUP BY u.id ORDER BY u.created_at DESC");
    $searchTerm = "%{$search}%";
    $stmt->execute([$searchTerm, $searchTerm]);
} else {
    $stmt = $conn->query("SELECT u.*, COUNT(t.id) as ticket_count FROM users u LEFT JOIN tickets t ON u.id = t.user_id WHERE u.role = 'customer' GROUP BY u.id ORDER BY u.created_at DESC");
}
$customers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Admin - LocalTechFix</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard { min-height: 100vh; background-color: var(--bg-color); }
        .dashboard-header { background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); padding: 1.5rem 0; box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .dashboard-nav { display: flex; justify-content: space-between; align-items: center; }
        .dashboard-nav .logo { font-size: 1.5rem; font-weight: 700; color: var(--white); }
        .dashboard-nav .nav-links { display: flex; gap: 2rem; align-items: center; }
        .dashboard-nav .nav-links a { color: var(--white); font-weight: 500; opacity: 0.9; }
        .dashboard-nav .nav-links a:hover { opacity: 1; }
        
        .search-bar { background: var(--white); padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow-md); margin-bottom: 2rem; }
        .search-bar form { display: flex; gap: 1rem; }
        .search-bar input { flex: 1; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius); }
        
        .customer-table { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow-md); overflow-x: auto; }
        .customer-table table { width: 100%; border-collapse: collapse; }
        .customer-table th { background: var(--bg-color); padding: 1rem; text-align: left; font-weight: 600; color: var(--secondary-color); border-bottom: 2px solid var(--border-color); }
        .customer-table td { padding: 1rem; border-bottom: 1px solid var(--border-color); }
        .customer-table tr:last-child td { border-bottom: none; }
        .customer-table tr:hover { background: var(--bg-color); }
        
        .action-buttons { display: flex; gap: 0.5rem; }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.875rem; }
        
        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal.active { display: flex; align-items: center; justify-content: center; }
        .modal-content { background: var(--white); padding: 2rem; border-radius: var(--radius); max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .modal-header h2 { margin: 0; color: var(--secondary-color); }
        .close-modal { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--light-text); }
        .close-modal:hover { color: var(--secondary-color); }
        
        .alert { padding: 1rem; border-radius: var(--radius); margin-bottom: 1rem; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
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
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <h1 style="margin-bottom: 2rem; color: var(--secondary-color);">Customers (<?= count($customers) ?>)</h1>

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

            <div class="search-bar">
                <form method="GET" action="">
                    <input type="text" name="search" placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-search"></i> Search
                    </button>
                    <?php if ($search): ?>
                        <a href="customers.php" class="btn btn-secondary">
                            <i class="fa-solid fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="customer-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Total Tickets</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 3rem; color: var(--light-text);">
                                    <?= $search ? 'No customers found matching your search' : 'No customers found' ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><strong>#<?= $customer['id'] ?></strong></td>
                                    <td><?= htmlspecialchars($customer['name']) ?></td>
                                    <td><?= htmlspecialchars($customer['email']) ?></td>
                                    <td>
                                        <span style="background: #eff6ff; color: var(--primary-color); padding: 0.25rem 0.75rem; border-radius: 20px; font-weight: 600;">
                                            <?= $customer['ticket_count'] ?> tickets
                                        </span>
                                    </td>
                                    <td><?= formatDate($customer['created_at'], 'M d, Y') ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button onclick="viewCustomer(<?= $customer['id'] ?>)" class="btn btn-secondary btn-sm" title="View Details">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button onclick="editCustomer(<?= $customer['id'] ?>, '<?= htmlspecialchars($customer['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($customer['email'], ENT_QUOTES) ?>')" class="btn btn-primary btn-sm" title="Edit">
                                                <i class="fa-solid fa-edit"></i>
                                            </button>
                                            <button onclick="resetPassword(<?= $customer['id'] ?>, '<?= htmlspecialchars($customer['name'], ENT_QUOTES) ?>')" class="btn btn-secondary btn-sm" style="background: #fef3c7; color: #92400e;" title="Reset Password">
                                                <i class="fa-solid fa-key"></i>
                                            </button>
                                            <a href="tickets.php?customer=<?= $customer['id'] ?>" class="btn btn-secondary btn-sm" title="View Tickets">
                                                <i class="fa-solid fa-ticket"></i>
                                            </a>
                                            <button onclick="deleteCustomer(<?= $customer['id'] ?>, '<?= htmlspecialchars($customer['name'], ENT_QUOTES) ?>', <?= $customer['ticket_count'] ?>)" class="btn btn-secondary btn-sm" style="background: #fee2e2; color: #991b1b;" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- View Customer Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Customer Details</h2>
                <button class="close-modal" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div id="viewModalContent">
                Loading...
            </div>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Customer</h2>
                <button class="close-modal" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form method="POST" action="">
                <?= csrfField() ?>
                <input type="hidden" name="customer_id" id="edit_customer_id">
                <input type="hidden" name="update_customer" value="1">
                
                <div class="form-group">
                    <label for="edit_name">Name</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Save Changes
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Confirm Deletion</h2>
                <button class="close-modal" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            <div id="deleteModalContent">
                <p>Are you sure you want to delete this customer?</p>
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <input type="hidden" name="customer_id" id="delete_customer_id">
                    <input type="hidden" name="delete_customer" value="1">
                    
                    <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-secondary" style="background: #991b1b; color: white;">
                            <i class="fa-solid fa-trash"></i> Delete Customer
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div id="resetPasswordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Reset Customer Password</h2>
                <button class="close-modal" onclick="closeModal('resetPasswordModal')">&times;</button>
            </div>
            <form method="POST" action="">
                <?= csrfField() ?>
                <input type="hidden" name="customer_id" id="reset_customer_id">
                <input type="hidden" name="reset_password" value="1">
                
                <p style="margin-bottom: 1.5rem; color: var(--light-text);">
                    Resetting password for: <strong id="reset_customer_name"></strong>
                </p>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6" placeholder="Minimum 6 characters">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Re-enter password">
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-key"></i> Reset Password
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('resetPasswordModal')">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function viewCustomer(id) {
            document.getElementById('viewModal').classList.add('active');
            document.getElementById('viewModalContent').innerHTML = 'Loading...';
            
            // Fetch customer details via AJAX (you can implement this later)
            fetch(`customer-details.php?id=${id}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('viewModalContent').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('viewModalContent').innerHTML = 'Error loading customer details';
                });
        }
        
        function editCustomer(id, name, email) {
            document.getElementById('edit_customer_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('editModal').classList.add('active');
        }
        
        function deleteCustomer(id, name, ticketCount) {
            if (ticketCount > 0) {
                alert(`Cannot delete ${name}. This customer has ${ticketCount} ticket(s). Please delete or reassign their tickets first.`);
                return;
            }
            
            document.getElementById('delete_customer_id').value = id;
            document.getElementById('deleteModalContent').querySelector('p').textContent = 
                `Are you sure you want to delete "${name}"? This action cannot be undone.`;
            document.getElementById('deleteModal').classList.add('active');
        }
        
        function resetPassword(id, name) {
            document.getElementById('reset_customer_id').value = id;
            document.getElementById('reset_customer_name').textContent = name;
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
            document.getElementById('resetPasswordModal').classList.add('active');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>
</body>
</html>
