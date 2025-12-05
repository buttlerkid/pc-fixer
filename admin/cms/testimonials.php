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
        $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "Testimonial deleted successfully!";
    } catch (PDOException $e) {
        $error = "Error deleting testimonial: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update
            $stmt = $conn->prepare("UPDATE testimonials SET name = ?, role = ?, content = ?, rating = ?, display_order = ? WHERE id = ?");
            $stmt->execute([$_POST['name'], $_POST['role'], $_POST['content'], $_POST['rating'], $_POST['display_order'], $_POST['id']]);
            $message = "Testimonial updated successfully!";
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO testimonials (name, role, content, rating, display_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['name'], $_POST['role'], $_POST['content'], $_POST['rating'], $_POST['display_order']]);
            $message = "Testimonial added successfully!";
        }
        
        // Update Section Header
        if (isset($_POST['header_title'])) {
             $stmt = $conn->prepare("INSERT INTO homepage_content (section, key_name, content_value) VALUES ('testimonials', 'header_title', ?) ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)");
             $stmt->execute([$_POST['header_title']]);
        }
        if (isset($_POST['header_subtitle'])) {
             $stmt = $conn->prepare("INSERT INTO homepage_content (section, key_name, content_value) VALUES ('testimonials', 'header_subtitle', ?) ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)");
             $stmt->execute([$_POST['header_subtitle']]);
        }

    } catch (PDOException $e) {
        $error = "Error saving testimonial: " . $e->getMessage();
    }
}

$testimonials = getTestimonials();
$sectionContent = getSectionContent('testimonials');
$editItem = null;

if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editItem = $stmt->fetch();
}

$pageTitle = 'Manage Testimonials';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="cms-editor">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="color: var(--secondary-color); margin: 0;">Manage Testimonials</h1>
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
                <input type="text" name="header_title" class="form-control" value="<?= htmlspecialchars($sectionContent['header_title'] ?? 'What Clients Say') ?>" required>
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
                <h3 style="margin-bottom: 1rem;"><?= $editItem ? 'Edit Testimonial' : 'Add New Testimonial' ?></h3>
                <form method="POST">
                    <?php if ($editItem): ?>
                        <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Client Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($editItem['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Role / Location</label>
                        <input type="text" name="role" class="form-control" value="<?= htmlspecialchars($editItem['role'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Review Content</label>
                        <textarea name="content" class="form-control" rows="3" required><?= htmlspecialchars($editItem['content'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Rating (1-5)</label>
                        <input type="number" name="rating" class="form-control" min="1" max="5" value="<?= htmlspecialchars($editItem['rating'] ?? '5') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Display Order</label>
                        <input type="number" name="display_order" class="form-control" value="<?= htmlspecialchars($editItem['display_order'] ?? '0') ?>" required>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary"><?= $editItem ? 'Update Testimonial' : 'Add Testimonial' ?></button>
                        <?php if ($editItem): ?>
                            <a href="testimonials.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- List -->
        <div class="col" style="flex: 1.5; min-width: 300px;">
            <div class="content-card">
                <h3 style="margin-bottom: 1rem;">Existing Testimonials</h3>
                <div class="table-responsive">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border-color);">
                                <th style="text-align: left; padding: 0.5rem;">Order</th>
                                <th style="text-align: left; padding: 0.5rem;">Name</th>
                                <th style="text-align: left; padding: 0.5rem;">Rating</th>
                                <th style="text-align: right; padding: 0.5rem;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($testimonials as $item): ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 0.5rem;"><?= $item['display_order'] ?></td>
                                    <td style="padding: 0.5rem;"><?= htmlspecialchars($item['name']) ?></td>
                                    <td style="padding: 0.5rem;"><?= $item['rating'] ?>/5</td>
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
