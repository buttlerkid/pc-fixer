<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';

requireAdmin();

$db = new Database();
$conn = $db->connect();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $conn->prepare("INSERT INTO homepage_content (section, key_name, content_value) VALUES ('hero', ?, ?) ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)");
        
        foreach ($_POST as $key => $value) {
            $stmt->execute([$key, $value]);
        }
        
        $message = "Hero section updated successfully!";
    } catch (PDOException $e) {
        $error = "Error updating content: " . $e->getMessage();
    }
}

$heroContent = getSectionContent('hero');
$pageTitle = 'Edit Hero Section';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="cms-editor">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="color: var(--secondary-color); margin: 0;">Edit Hero Section</h1>
        <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to CMS</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <div class="content-card">
        <form method="POST" style="padding: 2rem;">
            <div class="form-group">
                <label>Title Prefix</label>
                <input type="text" name="title_prefix" class="form-control" value="<?= htmlspecialchars($heroContent['title_prefix'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Highlighted Title Text</label>
                <input type="text" name="title_highlight" class="form-control" value="<?= htmlspecialchars($heroContent['title_highlight'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($heroContent['description'] ?? '') ?></textarea>
            </div>
            
            <div class="row" style="display: flex; gap: 1rem;">
                <div class="col" style="flex: 1;">
                    <div class="form-group">
                        <label>Primary Button Text</label>
                        <input type="text" name="btn_primary_text" class="form-control" value="<?= htmlspecialchars($heroContent['btn_primary_text'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="col" style="flex: 1;">
                    <div class="form-group">
                        <label>Primary Button Link</label>
                        <input type="text" name="btn_primary_link" class="form-control" value="<?= htmlspecialchars($heroContent['btn_primary_link'] ?? '') ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="row" style="display: flex; gap: 1rem;">
                <div class="col" style="flex: 1;">
                    <div class="form-group">
                        <label>Secondary Button Text</label>
                        <input type="text" name="btn_secondary_text" class="form-control" value="<?= htmlspecialchars($heroContent['btn_secondary_text'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="col" style="flex: 1;">
                    <div class="form-group">
                        <label>Secondary Button Link</label>
                        <input type="text" name="btn_secondary_link" class="form-control" value="<?= htmlspecialchars($heroContent['btn_secondary_link'] ?? '') ?>" required>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
