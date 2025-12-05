<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 Server Error - LocalTechFix</title>
    <link rel="stylesheet" href="<?= getThemeCss() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .error-page {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
        }
        .error-content {
            max-width: 600px;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 800;
            color: var(--warning-color, #f59e0b);
            line-height: 1;
            margin-bottom: 1rem;
            font-family: 'Outfit', sans-serif;
        }
        .error-title {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--text-color);
        }
        .error-message {
            font-size: 1.125rem;
            color: var(--light-text);
            margin-bottom: 2rem;
        }
        .error-icon {
            font-size: 4rem;
            color: var(--text-color);
            margin-bottom: 1.5rem;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container header-container">
            <a href="/" class="logo">
                <i class="fa-solid fa-microchip"></i>
                <span>LocalTechFix</span>
            </a>
            <nav class="nav">
                <ul class="nav-list">
                    <li><a href="/" class="nav-link">Home</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="error-page">
        <div class="error-content">
            <div class="error-icon"><i class="fa-solid fa-coffee"></i></div>
            <div class="error-code">500</div>
            <h1 class="error-title">Something Broke</h1>
            <p class="error-message">We're fixing it faster than a spilled coffee on a keyboard. Please try again in a few moments.</p>
            <a href="/" class="btn btn-primary">Go Home</a>
        </div>
    </div>


    <!-- Footer -->
    <?php include 'includes/public_footer.php'; ?>
    <script src="/assets/js/script.js"></script>
</body>
</html>
