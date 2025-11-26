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
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .page-header { background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); padding: 4rem 0; color: var(--white); text-align: center; margin-bottom: 3rem; }
        .page-header h1 { font-size: 2.5rem; margin-bottom: 1rem; }
        .page-header p { font-size: 1.1rem; opacity: 0.9; max-width: 600px; margin: 0 auto 2rem; }
        
        .search-box { max-width: 600px; margin: 0 auto; position: relative; }
        .search-box input { width: 100%; padding: 1rem 1.5rem; padding-right: 3rem; border-radius: 50px; border: none; font-size: 1.1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .search-box button { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--primary-color); font-size: 1.2rem; cursor: pointer; }
        
        .kb-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem; }
        .kb-card { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow-md); overflow: hidden; transition: transform 0.3s; height: 100%; display: flex; flex-direction: column; }
        .kb-card:hover { transform: translateY(-5px); }
        .kb-content { padding: 1.5rem; flex: 1; display: flex; flex-direction: column; }
        .kb-title { font-size: 1.25rem; font-weight: 700; color: var(--secondary-color); margin-bottom: 1rem; }
        .kb-excerpt { color: var(--text-color); margin-bottom: 1.5rem; flex: 1; line-height: 1.6; }
        .kb-link { color: var(--primary-color); font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; }
        
        /* Accordion style for single page view if we want, but grid is nice */
        .article-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .article-modal.active { display: flex; }
        .article-content { background: var(--white); width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto; border-radius: var(--radius); padding: 2rem; position: relative; }
        .close-modal { position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--light-text); }
    </style>
</head>
<body>
    <nav class="navbar" style="background: var(--white); box-shadow: var(--shadow-sm); padding: 1rem 0;">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <a href="index.php" class="logo" style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color); text-decoration: none;">
                <i class="fa-solid fa-microchip"></i> LocalTechFix
            </a>
            <div class="nav-links" style="display: flex; gap: 2rem;">
                <a href="index.php" style="color: var(--text-color); text-decoration: none; font-weight: 500;">Home</a>
                <a href="knowledge-base.php" style="color: var(--primary-color); text-decoration: none; font-weight: 700;">Knowledge Base</a>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="admin/dashboard.php" class="btn btn-primary">Admin Panel</a>
                    <?php else: ?>
                        <a href="customer/dashboard.php" class="btn btn-primary">My Dashboard</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" style="color: var(--text-color); text-decoration: none; font-weight: 500;">Login</a>
                    <a href="register.php" class="btn btn-primary">Get Started</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <header class="page-header">
        <div class="container">
            <h1>Knowledge Base</h1>
            <p>Find answers to common questions and troubleshooting guides.</p>
            <form class="search-box" method="GET">
                <input type="text" name="q" placeholder="Search for articles..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit"><i class="fa-solid fa-search"></i></button>
            </form>
        </div>
    </header>

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
                            <button onclick="openArticle(<?= $article['id'] ?>)" class="kb-link" style="background:none; border:none; cursor:pointer; padding:0; font-size:1rem;">
                                Read More <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Hidden Content for Modal -->
                    <div id="article-<?= $article['id'] ?>" style="display:none;">
                        <h1 style="margin-bottom:1.5rem; color:var(--secondary-color);"><?= htmlspecialchars($article['title']) ?></h1>
                        <div style="line-height:1.8; color:var(--text-color);">
                            <?= $article['content'] ?> <!-- Content is trusted as it comes from admin -->
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

    <script>
        function openArticle(id) {
            const content = document.getElementById('article-' + id).innerHTML;
            document.getElementById('modalBody').innerHTML = content;
            document.getElementById('articleModal').classList.add('active');
            document.body.style.overflow = 'hidden';
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
