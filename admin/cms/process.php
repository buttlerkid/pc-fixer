<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';

requireAdmin();

$db = new Database();
$conn = $db->connect();

$message = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM process_steps WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "Step deleted successfully!";
    } catch (PDOException $e) {
        $error = "Error deleting step: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update
            $stmt = $conn->prepare("UPDATE process_steps SET step_number = ?, title = ?, description = ? WHERE id = ?");
            $stmt->execute([$_POST['step_number'], $_POST['title'], $_POST['description'], $_POST['id']]);
            $message = "Step updated successfully!";
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO process_steps (step_number, title, description) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['step_number'], $_POST['title'], $_POST['description']]);
            $message = "Step added successfully!";
        }
        
        // Update Section Header
        if (isset($_POST['header_title'])) {
             $stmt = $conn->prepare("INSERT INTO homepage_content (section, key_name, content_value) VALUES ('process', 'header_title', ?) ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)");
             $stmt->execute([$_POST['header_title']]);
        }
        if (isset($_POST['header_subtitle'])) {
             $stmt = $conn->prepare("INSERT INTO homepage_content (section, key_name, content_value) VALUES ('process', 'header_subtitle', ?) ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)");
             $stmt->execute([$_POST['header_subtitle']]);
        }

    } catch (PDOException $e) {
        $error = "Error saving step: " . $e->getMessage();
    }
}

$steps = getProcessSteps();
$sectionContent = getSectionContent('process');
$editItem = null;

if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM process_steps WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editItem = $stmt->fetch();
}

$pageTitle = 'Manage Process';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="cms-editor">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="color: var(--secondary-color); margin: 0;">Manage Process Steps</h1>
        <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to CMS</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <!-- Section Header Editor -->
    <div class="content-card" style="margin-bottom: 2rem;">
        <h3 style="margin-bottom: 1rem;">Section Header</h3>
        <form method="POST" style="padding: 1rem;">
            <div class="form-group">
                <label>Section Title</label>
                <input type="text" name="header_title" class="form-control" value="<?= htmlspecialchars($sectionContent['header_title'] ?? 'How It Works') ?>" required>
            </div>
            <div class="form-group">
                <label>Section Subtitle</label>
                <input type="text" name="header_subtitle" class="form-control" value="<?= htmlspecialchars($sectionContent['header_subtitle'] ?? '') ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Header</button>
        </form>
    </div>

    <div class="row" style="display: flex; gap: 2rem; flex-wrap: wrap;">
        <!-- Form -->
        <div class="col" style="flex: 1; min-width: 300px;">
            <div class="content-card">
                <h3 style="margin-bottom: 1rem;"><?= $editItem ? 'Edit Step' : 'Add New Step' ?></h3>
                <form method="POST">
                    <?php if ($editItem): ?>
                        <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Step Number</label>
                        <input type="number" name="step_number" class="form-control" value="<?= htmlspecialchars($editItem['step_number'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editItem['title'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary"><?= $editItem ? 'Update Step' : 'Add Step' ?></button>
                        <?php if ($editItem): ?>
                            <a href="process.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- List -->
        <div class="col" style="flex: 1.5; min-width: 300px;">
            <div class="content-card">
                <h3 style="margin-bottom: 1rem;">Existing Steps</h3>
                <div class="table-responsive">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border-color);">
                                <th style="text-align: left; padding: 0.5rem;">Step</th>
                                <th style="text-align: left; padding: 0.5rem;">Title</th>
                                <th style="text-align: right; padding: 0.5rem;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($steps as $item): ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 0.5rem;"><?= $item['step_number'] ?></td>
                                    <td style="padding: 0.5rem;"><?= htmlspecialchars($item['title']) ?></td>
                                    <td style="padding: 0.5rem; text-align: right;">
                                        <a href="?edit=<?= $item['id'] ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;"><i class="fa-solid fa-pen"></i></a>
                                        <a href="?delete=<?= $item['id'] ?>" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background-color: var(--danger-color); color: white;" onclick="return confirm('Are you sure?')"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
