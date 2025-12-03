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
    <link rel="stylesheet" href="../assets/css/modern.css?v=1.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
