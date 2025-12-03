<?php
// Ensure we have the current page for active state
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - LocalTechFix</title>
    <link rel="stylesheet" href="../assets/css/modern.css?v=1.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard { min-height: 100vh; background-color: var(--bg-color); }
        .dashboard-header { background: var(--white); padding: 1.5rem 0; box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .dashboard-nav { display: flex; justify-content: space-between; align-items: center; }
        .dashboard-nav .logo { font-size: 1.5rem; font-weight: 700; color: var(--primary-color); }
        .dashboard-nav .nav-links { display: flex; gap: 2rem; align-items: center; }
        .dashboard-nav .nav-links a { color: var(--text-color); font-weight: 500; text-decoration: none; }
        .dashboard-nav .nav-links a:hover, .dashboard-nav .nav-links a.active { color: var(--primary-color); font-weight: 700; }
        
        /* Common Customer Styles */
        .content-card { background: var(--white); padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow-md); }
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { font-weight: 600; color: var(--secondary-color); background: #f9fafb; }
        tr:hover { background: #f9fafb; }
        
        .badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-secondary { background: #e5e7eb; color: #374151; }
        
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border-radius: var(--radius); font-weight: 600; text-decoration: none; cursor: pointer; transition: all 0.3s; border: none; font-size: 1rem; }
        .btn-primary { background-color: var(--primary-color); color: var(--white); }
        .btn-primary:hover { background-color: var(--primary-dark); transform: translateY(-2px); }
        .btn-secondary { background-color: var(--white); color: var(--primary-color); border: 2px solid var(--primary-color); }
        .btn-secondary:hover { background-color: var(--bg-color); transform: translateY(-2px); }
        
        .alert { padding: 1rem; border-radius: var(--radius); margin-bottom: 1rem; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--secondary-color); }
        .form-control { width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius); font-family: inherit; }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="dashboard-header">
            <div class="container">
                <div class="dashboard-nav">
                    <div class="logo"><i class="fa-solid fa-microchip"></i> LocalTechFix</div>
                    <div class="nav-links">
                        <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                            <i class="fa-solid fa-home"></i> Dashboard
                        </a>
                        <a href="tickets.php" class="<?= $currentPage === 'tickets.php' || $currentPage === 'ticket-detail.php' || $currentPage === 'create-ticket.php' ? 'active' : '' ?>">
                            <i class="fa-solid fa-ticket"></i> My Tickets
                        </a>
                        <a href="invoices.php" class="<?= $currentPage === 'invoices.php' || $currentPage === 'invoice-view.php' ? 'active' : '' ?>">
                            <i class="fa-solid fa-file-invoice-dollar"></i> My Invoices
                        </a>
                        <a href="../knowledge-base.php">
                            <i class="fa-solid fa-book"></i> Knowledge Base
                        </a>
                        
                        <div class="dropdown">
                            <div class="dropdown-trigger" style="color: var(--text-color); font-weight: 500;">
                                <i class="fa-solid fa-user-circle"></i> Account <i class="fa-solid fa-caret-down"></i>
                            </div>
                            <div class="dropdown-content">
                                <a href="profile.php">
                                    <i class="fa-solid fa-user"></i> Profile
                                </a>
                                <a href="../index.php">
                                    <i class="fa-solid fa-globe"></i> Home
                                </a>
                                <a href="../logout.php" style="color: #ef4444 !important;">
                                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                                </a>
                            </div>
                        </div>

                        <button class="theme-toggle" aria-label="Toggle dark mode" style="background:none; border:none; color:inherit; cursor:pointer; font-size:1rem;">
                            <i class="fa-solid fa-moon"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
