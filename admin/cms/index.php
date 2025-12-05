<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';

requireAdmin();

$pageTitle = 'Website Content';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="cms-dashboard">
    <h1 style="margin-bottom: 2rem; color: var(--secondary-color);">Website Content Management</h1>
    
    <div class="cms-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
        <!-- Hero Section -->
        <a href="hero.php" class="content-card" style="text-decoration: none; color: inherit; transition: transform 0.2s;">
            <div class="card-body" style="text-align: center; padding: 2rem;">
                <div class="icon" style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fa-solid fa-image"></i>
                </div>
                <h3 style="margin-bottom: 0.5rem;">Hero Section</h3>
                <p style="color: var(--light-text);">Edit main title, subtitle, and buttons.</p>
            </div>
        </a>

        <!-- Services -->
        <a href="services.php" class="content-card" style="text-decoration: none; color: inherit; transition: transform 0.2s;">
            <div class="card-body" style="text-align: center; padding: 2rem;">
                <div class="icon" style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fa-solid fa-laptop-medical"></i>
                </div>
                <h3 style="margin-bottom: 0.5rem;">Services</h3>
                <p style="color: var(--light-text);">Manage service offerings and descriptions.</p>
            </div>
        </a>

        <!-- Testimonials -->
        <a href="testimonials.php" class="content-card" style="text-decoration: none; color: inherit; transition: transform 0.2s;">
            <div class="card-body" style="text-align: center; padding: 2rem;">
                <div class="icon" style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fa-solid fa-star"></i>
                </div>
                <h3 style="margin-bottom: 0.5rem;">Testimonials</h3>
                <p style="color: var(--light-text);">Add or remove client reviews.</p>
            </div>
        </a>

        <!-- Pricing -->
        <a href="pricing.php" class="content-card" style="text-decoration: none; color: inherit; transition: transform 0.2s;">
            <div class="card-body" style="text-align: center; padding: 2rem;">
                <div class="icon" style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fa-solid fa-tags"></i>
                </div>
                <h3 style="margin-bottom: 0.5rem;">Pricing Plans</h3>
                <p style="color: var(--light-text);">Update pricing packages and features.</p>
            </div>
        </a>

        <!-- FAQ -->
        <a href="faq.php" class="content-card" style="text-decoration: none; color: inherit; transition: transform 0.2s;">
            <div class="card-body" style="text-align: center; padding: 2rem;">
                <div class="icon" style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fa-solid fa-circle-question"></i>
                </div>
                <h3 style="margin-bottom: 0.5rem;">FAQ</h3>
                <p style="color: var(--light-text);">Manage frequently asked questions.</p>
            </div>
        </a>

        <!-- Process -->
        <a href="process.php" class="content-card" style="text-decoration: none; color: inherit; transition: transform 0.2s;">
            <div class="card-body" style="text-align: center; padding: 2rem;">
                <div class="icon" style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fa-solid fa-list-ol"></i>
                </div>
                <h3 style="margin-bottom: 0.5rem;">How It Works</h3>
                <p style="color: var(--light-text);">Edit the service process steps.</p>
            </div>
        </a>
    </div>
</div>

<style>
.content-card:hover {
    transform: translateY(-5px);
}
.content-card .card-body {
    background: var(--white);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
