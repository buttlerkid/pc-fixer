<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$db = new Database();
$conn = $db->connect();

$articleId = $_GET['id'] ?? null;
$article = null;
$error = '';
$success = '';

if ($articleId) {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$articleId]);
    $article = $stmt->fetch();
    
    if (!$article) {
        redirect('articles.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        $title = sanitize($_POST['title']);
        $content = $_POST['content']; // Don't sanitize content too aggressively to allow HTML if needed, or use a purifier
        $isPublished = isset($_POST['is_published']) ? 1 : 0;
        
        if (empty($title) || empty($content)) {
            $error = 'Title and content are required';
        } else {
            if ($articleId) {
                $stmt = $conn->prepare("UPDATE articles SET title = ?, content = ?, is_published = ? WHERE id = ?");
                $stmt->execute([$title, $content, $isPublished, $articleId]);
                $success = 'Article updated successfully';
                // Refresh article data
                $stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
                $stmt->execute([$articleId]);
                $article = $stmt->fetch();
            } else {
                $stmt = $conn->prepare("INSERT INTO articles (title, content, is_published) VALUES (?, ?, ?)");
                $stmt->execute([$title, $content, $isPublished]);
                $newId = $conn->lastInsertId();
                redirect('articles.php');
            }
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>
    <style>
        /* Page specific styles */
        .editor-container { max-width: 800px; margin: 0 auto; background: var(--white); padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow-md); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--secondary-color); }
        .form-control { width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius); font-family: inherit; }
        textarea.form-control { min-height: 300px; resize: vertical; }
        
        .toggle-switch { position: relative; display: inline-block; width: 60px; height: 34px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 26px; width: 26px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--primary-color); }
        input:checked + .slider:before { transform: translateX(26px); }
    </style>

            <div style="margin-bottom: 2rem;">
                <a href="articles.php" style="color: var(--primary-color); text-decoration: none;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Articles
                </a>
            </div>

            <div class="editor-container">
                <h1 style="margin-bottom: 2rem; color: var(--secondary-color);"><?= $articleId ? 'Edit' : 'Create New' ?> Article</h1>

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

                <form method="POST">
                    <?= csrfField() ?>
                    
                    <div class="form-group">
                        <label for="title">Article Title</label>
                        <input type="text" id="title" name="title" class="form-control" required value="<?= htmlspecialchars($article['title'] ?? '') ?>" placeholder="e.g. How to reset your password">
                    </div>

                    <div class="form-group">
                        <label for="content">Content (HTML allowed)</label>
                        <textarea id="content" name="content" class="form-control" required placeholder="Write your article content here..."><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group" style="display: flex; align-items: center; justify-content: space-between;">
                        <label style="margin-bottom: 0;">Publish Article</label>
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_published" <?= ($article['is_published'] ?? 0) ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                            <i class="fa-solid fa-save"></i> <?= $articleId ? 'Update Article' : 'Create Article' ?>
                        </button>
                    </div>
                </form>
            </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
