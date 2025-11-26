<?php
// Ensure we have the current page for active state
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - LocalTechFix</title>
    <link rel="stylesheet" href="../assets/css/styles.css?v=1.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard { min-height: 100vh; background-color: var(--bg-color); }
        .dashboard-header { background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); padding: 1.5rem 0; box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .dashboard-nav { display: flex; justify-content: space-between; align-items: center; }
        .dashboard-nav .logo { font-size: 1.5rem; font-weight: 700; color: var(--white); }
        .dashboard-nav .nav-links { display: flex; gap: 1.5rem; align-items: center; }
        .dashboard-nav .nav-links a { color: var(--white); font-weight: 500; opacity: 0.8; transition: opacity 0.2s; text-decoration: none; font-size: 0.95rem; }
        .dashboard-nav .nav-links a:hover, .dashboard-nav .nav-links a.active { opacity: 1; font-weight: 700; }
        
        /* Common Admin Styles */
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
        
        .badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-secondary { background: #e5e7eb; color: #374151; }
        
        .alert { padding: 1rem; border-radius: var(--radius); margin-bottom: 1rem; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border-radius: var(--radius); font-weight: 600; text-decoration: none; cursor: pointer; transition: all 0.3s; border: none; font-size: 1rem; }
        .btn-primary { background-color: var(--primary-color); color: var(--white); }
        .btn-primary:hover { background-color: var(--primary-dark); transform: translateY(-2px); }
        .btn-secondary { background-color: var(--white); color: var(--primary-color); border: 2px solid var(--primary-color); }
        .btn-secondary:hover { background-color: var(--bg-color); transform: translateY(-2px); }
        .btn-block { display: flex; width: 100%; justify-content: center; }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="dashboard-header">
            <div class="container">
                <div class="dashboard-nav">
                    <div class="logo"><i class="fa-solid fa-shield-halved"></i> Admin Panel</div>
                    <div class="nav-links">
                        <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                            <i class="fa-solid fa-home"></i> Dashboard
                        </a>
                        <a href="tickets.php" class="<?= $currentPage === 'tickets.php' || $currentPage === 'ticket-detail.php' ? 'active' : '' ?>">
                            <i class="fa-solid fa-ticket"></i> Tickets
                        </a>
                        <a href="customers.php" class="<?= $currentPage === 'customers.php' ? 'active' : '' ?>">
                            <i class="fa-solid fa-users"></i> Customers
                        </a>
                        <a href="invoices.php" class="<?= $currentPage === 'invoices.php' || $currentPage === 'invoice-editor.php' || $currentPage === 'invoice-view.php' ? 'active' : '' ?>">
                            <i class="fa-solid fa-file-invoice-dollar"></i> Invoices
                        </a>
                        <a href="articles.php" class="<?= $currentPage === 'articles.php' || $currentPage === 'article-editor.php' ? 'active' : '' ?>">
                            <i class="fa-solid fa-book"></i> Knowledge Base
                        </a>
                        <a href="settings.php" class="<?= $currentPage === 'settings.php' ? 'active' : '' ?>">
                            <i class="fa-solid fa-cog"></i> Settings
                        </a>
                        
                        <div class="dropdown">
                            <div class="dropdown-trigger" style="color: var(--white); font-weight: 500; opacity: 0.8;">
                                <i class="fa-solid fa-user-circle"></i> Account <i class="fa-solid fa-caret-down"></i>
                            </div>
                            <div class="dropdown-content">
                                <a href="profile.php">
                                    <i class="fa-solid fa-user"></i> Profile
                                </a>
                                <a href="../index.php" target="_blank">
                                    <i class="fa-solid fa-globe"></i> View Site
                                </a>
                                <a href="../logout.php" style="color: #ef4444 !important;">
                                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
