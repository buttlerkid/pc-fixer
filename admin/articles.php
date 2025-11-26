<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$db = new Database();
$conn = $db->connect();

// Handle Delete
if (isset($_POST['delete_id'])) {
    if (validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->execute([$_POST['delete_id']]);
        $success = "Article deleted successfully";
    }
}

// Get Articles
$stmt = $conn->query("SELECT * FROM articles ORDER BY created_at DESC");
$articles = $stmt->fetchAll();
?>
require_once __DIR__ . '/includes/header.php';
?>
    <style>
        /* Page specific styles */
        .content-card { background: var(--white); padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow-md); }
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { font-weight: 600; color: var(--secondary-color); background: #f9fafb; }
        tr:hover { background: #f9fafb; }
        
        .badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-secondary { background: #e5e7eb; color: #374151; }
        
        .action-btn { padding: 0.5rem; border-radius: 4px; color: var(--text-color); transition: all 0.2s; }
        .action-btn:hover { background: var(--bg-color); color: var(--primary-color); }
        .action-btn.delete:hover { color: #ef4444; }
    </style>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="color: var(--secondary-color);">Knowledge Base Articles</h1>
                <a href="article-editor.php" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> New Article
                </a>
            </div>

            <div class="content-card">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($articles)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 2rem; color: var(--light-text);">
                                        No articles found. Create one to get started!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($articles as $article): ?>
                                    <tr>
                                        <td style="font-weight: 500;"><?= htmlspecialchars($article['title']) ?></td>
                                        <td>
                                            <?php if ($article['is_published']): ?>
                                                <span class="badge badge-success">Published</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Draft</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= formatDate($article['created_at']) ?></td>
                                        <td><?= formatDate($article['updated_at']) ?></td>
                                        <td>
                                            <a href="article-editor.php?id=<?= $article['id'] ?>" class="action-btn" title="Edit">
                                                <i class="fa-solid fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this article?');">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="delete_id" value="<?= $article['id'] ?>">
                                                <button type="submit" class="action-btn delete" style="background: none; border: none; cursor: pointer;" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
