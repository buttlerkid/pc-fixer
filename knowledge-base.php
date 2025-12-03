<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$db = new Database();
$conn = $db->connect();

// Get Published Articles
$search = $_GET['q'] ?? '';
$query = "SELECT * FROM articles WHERE is_published = 1";
$params = [];

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$articles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge Base - LocalTechFix</title>
    <link rel="stylesheet" href="<?= getThemeCss() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-container">
            <a href="index.php" class="logo">
                <i class="fa-solid fa-microchip"></i> LocalTechFix
            </a>
            
            <button class="mobile-menu-btn">
                <i class="fa-solid fa-bars"></i>
            </button>

            <nav class="nav">
                <ul class="nav-list">
                    <li><a href="index.php" class="nav-link">Home</a></li>
                    <li><a href="knowledge-base.php" class="nav-link active" style="color: var(--primary-color);">Knowledge Base</a></li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin/dashboard.php" class="btn btn-primary">Admin Panel</a></li>
                        <?php else: ?>
                            <li><a href="customer/dashboard.php" class="btn btn-primary">My Dashboard</a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="login.php" class="nav-link">Login</a></li>
                        <li><a href="register.php" class="btn btn-primary">Sign Up</a></li>
                    <?php endif; ?>
                    <li>
                        <button class="theme-toggle" id="themeToggle" aria-label="Toggle Dark Mode">
                            <i class="fa-solid fa-moon"></i>
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- KB Hero -->
    <div class="kb-header">
        <div class="container">
            <h1>Knowledge Base</h1>
            <p>Find answers to common questions and troubleshooting guides.</p>
            <form class="search-box" method="GET">
                <input type="text" name="q" placeholder="Search for articles..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit"><i class="fa-solid fa-search"></i></button>
            </form>
        </div>
    </div>

    <!-- Articles Grid -->
    <div class="container" style="margin-bottom: 4rem;">
        <?php if (empty($articles)): ?>
            <div style="text-align: center; padding: 4rem; color: var(--light-text);">
                <i class="fa-solid fa-book-open" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p>No articles found matching your search.</p>
            </div>
        <?php else: ?>
            <div class="kb-grid">
                <?php foreach ($articles as $article): ?>
                    <div class="kb-card">
                        <div class="kb-content">
                            <h2 class="kb-title"><?= htmlspecialchars($article['title']) ?></h2>
                            <div class="kb-excerpt">
                                <?= truncate(strip_tags($article['content']), 150) ?>
                            </div>
                            <button onclick="openArticle(<?= $article['id'] ?>)" class="kb-link">
                                Read More <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Hidden Content for Modal -->
                    <div id="article-<?= $article['id'] ?>" style="display:none;">
                        <h1 style="margin-bottom:1.5rem; color:var(--secondary-color); font-size: 2rem;"><?= htmlspecialchars($article['title']) ?></h1>
                        <div style="line-height:1.8; color:var(--text-color);">
                            <?= $article['content'] ?>
                        </div>
                        <div style="margin-top:2rem; padding-top:1rem; border-top:1px solid var(--border-color); color:var(--light-text); font-size:0.875rem;">
                            Last updated: <?= formatDate($article['updated_at']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Article Modal -->
    <div id="articleModal" class="article-modal">
        <div class="article-content">
            <button class="close-modal" onclick="closeModal()"><i class="fa-solid fa-times"></i></button>
            <div id="modalBody"></div>
        </div>
    </div>

    <!-- Footer -->
    <footer style="background-color: var(--white); padding: 4rem 0; border-top: 1px solid var(--border-color);">
        <div class="container" style="text-align: center;">
            <div class="footer-brand" style="color: var(--secondary-color);">
                <i class="fa-solid fa-microchip" style="color: var(--primary-color);"></i> LocalTechFix
            </div>
            <p style="color: var(--light-text);">Professional Computer Repair Services</p>
            <div class="social-links">
                <a href="#"><i class="fa-brands fa-facebook"></i></a>
                <a href="#"><i class="fa-brands fa-twitter"></i></a>
                <a href="#"><i class="fa-brands fa-instagram"></i></a>
                <a href="#"><i class="fa-brands fa-linkedin"></i></a>
            </div>
            <div class="copyright">
                &copy; <?= date('Y') ?> LocalTechFix. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
    <script>
        function openArticle(id) {
            const content = document.getElementById('article-' + id).innerHTML;
            document.getElementById('modalBody').innerHTML = content;
            document.getElementById('articleModal').classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Fix modal title color in dark mode dynamically if needed, 
            // but CSS classes should handle it if structure is correct.
            // The hidden content uses inline styles for now, let's fix that in the modal body injection if needed.
            // Actually, let's replace the inline styles in the hidden div with classes in the next step if this isn't perfect.
        }

        function closeModal() {
            document.getElementById('articleModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Close modal on outside click
        document.getElementById('articleModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
