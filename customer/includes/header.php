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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/modern.css?v=1.3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional custom styles if needed */
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
