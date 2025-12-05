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
        $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "Service deleted successfully!";
    } catch (PDOException $e) {
        $error = "Error deleting service: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update
            $stmt = $conn->prepare("UPDATE services SET icon = ?, title = ?, description = ?, display_order = ? WHERE id = ?");
            $stmt->execute([$_POST['icon'], $_POST['title'], $_POST['description'], $_POST['display_order'], $_POST['id']]);
            $message = "Service updated successfully!";
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO services (icon, title, description, display_order) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['icon'], $_POST['title'], $_POST['description'], $_POST['display_order']]);
            $message = "Service added successfully!";
        }
        
        // Update Section Header
        if (isset($_POST['header_title'])) {
             $stmt = $conn->prepare("INSERT INTO homepage_content (section, key_name, content_value) VALUES ('services', 'header_title', ?) ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)");
             $stmt->execute([$_POST['header_title']]);
        }
        if (isset($_POST['header_subtitle'])) {
             $stmt = $conn->prepare("INSERT INTO homepage_content (section, key_name, content_value) VALUES ('services', 'header_subtitle', ?) ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)");
             $stmt->execute([$_POST['header_subtitle']]);
        }

    } catch (PDOException $e) {
        $error = "Error saving service: " . $e->getMessage();
    }
}

$services = getServices();
$sectionContent = getSectionContent('services');
$editService = null;

if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editService = $stmt->fetch();
}

$pageTitle = 'Manage Services';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="cms-editor">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="color: var(--secondary-color); margin: 0;">Manage Services</h1>
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
                <input type="text" name="header_title" class="form-control" value="<?= htmlspecialchars($sectionContent['header_title'] ?? 'My Services') ?>" required>
            </div>
            <div class="form-group">
                <label>Section Subtitle</label>
                <input type="text" name="header_subtitle" class="form-control" value="<?= htmlspecialchars($sectionContent['header_subtitle'] ?? '') ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Header</button>
        </form>
    </div>

    <div class="row" style="display: flex; gap: 2rem; flex-wrap: wrap;">
        <!-- Service Form -->
        <div class="col" style="flex: 1; min-width: 300px;">
            <div class="content-card">
                <h3 style="margin-bottom: 1rem;"><?= $editService ? 'Edit Service' : 'Add New Service' ?></h3>
                <form method="POST">
                    <?php if ($editService): ?>
                        <input type="hidden" name="id" value="<?= $editService['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Icon Class (FontAwesome)</label>
                        <input type="text" name="icon" class="form-control" value="<?= htmlspecialchars($editService['icon'] ?? 'fa-solid fa-') ?>" required>
                        <small style="color: var(--light-text);">Example: fa-solid fa-laptop</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editService['title'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($editService['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Display Order</label>
                        <input type="number" name="display_order" class="form-control" value="<?= htmlspecialchars($editService['display_order'] ?? '0') ?>" required>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary"><?= $editService ? 'Update Service' : 'Add Service' ?></button>
                        <?php if ($editService): ?>
                            <a href="services.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Services List -->
        <div class="col" style="flex: 1.5; min-width: 300px;">
            <div class="content-card">
                <h3 style="margin-bottom: 1rem;">Existing Services</h3>
                <div class="table-responsive">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border-color);">
                                <th style="text-align: left; padding: 0.5rem;">Order</th>
                                <th style="text-align: left; padding: 0.5rem;">Icon</th>
                                <th style="text-align: left; padding: 0.5rem;">Title</th>
                                <th style="text-align: right; padding: 0.5rem;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 0.5rem;"><?= $service['display_order'] ?></td>
                                    <td style="padding: 0.5rem;"><i class="<?= $service['icon'] ?>"></i></td>
                                    <td style="padding: 0.5rem;"><?= htmlspecialchars($service['title']) ?></td>
                                    <td style="padding: 0.5rem; text-align: right;">
                                        <a href="?edit=<?= $service['id'] ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;"><i class="fa-solid fa-pen"></i></a>
                                        <a href="?delete=<?= $service['id'] ?>" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background-color: var(--danger-color); color: white;" onclick="return confirm('Are you sure?')"><i class="fa-solid fa-trash"></i></a>
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
