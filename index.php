<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

// Get statistics for the stats section
$database = new Database();
$db = $database->connect();

// Get total customers count
$stmt = $db->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
$stmt->execute();
$customerCount = $stmt->fetch()['total'];

// Get completed tickets count
$stmt = $db->prepare("SELECT COUNT(*) as total FROM tickets WHERE status = 'completed'");
$stmt->execute();
$completedTickets = $stmt->fetch()['total'];

// Get published knowledge base articles
$stmt = $db->prepare("SELECT * FROM articles WHERE is_published = 1 ORDER BY created_at DESC");
$stmt->execute();
$kbArticles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Local PC & Laptop Repair Services | Troubleshooting & Fixing</title>
    <meta name="description"
        content="Expert PC and laptop repair services in your local area. Hardware troubleshooting, software fixes, and personal service. We come to you or meet locally.">
    <link rel="stylesheet" href="<?= getThemeCss() ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-container">
            <a href="#" class="logo">
                <i class="fa-solid fa-microchip"></i>
                <span>LocalTechFix</span>
            </a>
            <nav class="nav">
                <ul class="nav-list">
                    <li><a href="#hero" class="nav-link">Home</a></li>
                    <li><a href="#services" class="nav-link">Services</a></li>
                    <li><a href="#process" class="nav-link">How it Works</a></li>
                    <li><a href="#contact" class="nav-link">Contact Us</a></li>
                    <li><a href="knowledge-base.php" class="nav-link">Knowledge Base</a></li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin/dashboard.php" class="nav-link btn-primary">Admin Panel</a></li>
                        <?php else: ?>
                            <li><a href="customer/dashboard.php" class="nav-link btn-primary">User Panel</a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="login.php" class="nav-link">Login</a></li>
                        <li><a href="register.php" class="nav-link btn-primary">Sign Up</a></li>
                    <?php endif; ?>
                    <li>
                        <button class="theme-toggle" aria-label="Toggle dark mode">
                            <i class="fa-solid fa-moon"></i>
                        </button>
                    </li>

                </ul>
                <button class="mobile-menu-btn" aria-label="Toggle menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </nav>
        </div>
    </header>

<?php
// Fetch CMS Content
$heroContent = getSectionContent('hero');
$statsContent = getSectionContent('stats');
$servicesContent = getSectionContent('services');
$testimonialsContent = getSectionContent('testimonials');
$pricingContent = getSectionContent('pricing');
$faqContent = getSectionContent('faq');
$processContent = getSectionContent('process');
$contactContent = getSectionContent('contact');

$services = getServices();
$testimonials = getTestimonials();
$pricingPlans = getPricingPlans();
$faqs = getFAQs();
$processSteps = getProcessSteps();
?>

    <!-- Hero Section -->
    <section id="hero" class="hero">
        <div class="container hero-container">
            <div class="hero-content">
                <h1><?= htmlspecialchars($heroContent['title_prefix'] ?? 'Fast & Reliable PC Repair in') ?> <span class="highlight"><?= htmlspecialchars($heroContent['title_highlight'] ?? 'Your Town') ?></span></h1>
                <p><?= htmlspecialchars($heroContent['description'] ?? "Don't let computer problems slow you down. I provide expert troubleshooting and repair services for desktops and laptops. Personal, local, and trusted.") ?></p>
                <div class="hero-buttons">
                    <a href="<?= htmlspecialchars($heroContent['btn_primary_link'] ?? '#contact') ?>" class="btn btn-primary"><?= htmlspecialchars($heroContent['btn_primary_text'] ?? 'Get a Quote') ?></a>
                    <a href="<?= htmlspecialchars($heroContent['btn_secondary_link'] ?? '#services') ?>" class="btn btn-secondary"><?= htmlspecialchars($heroContent['btn_secondary_text'] ?? 'View Services') ?></a>
                </div>
            </div>
            <div class="hero-image">
                <div class="image-placeholder">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section id="stats" class="stats section-padding bg-light">
        <div class="container">
            <div class="section-header">
                <h2><?= htmlspecialchars($statsContent['header_title'] ?? 'Our Track Record') ?></h2>
                <p><?= htmlspecialchars($statsContent['header_subtitle'] ?? 'Numbers that speak for themselves.') ?></p>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="stat-number"><?= number_format($customerCount) ?>+</div>
                    <div class="stat-label">Customers Helped</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>
                    <div class="stat-number"><?= number_format($completedTickets) ?>+</div>
                    <div class="stat-label">Tickets Resolved</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div class="stat-number">24</div>
                    <div class="stat-label">Hours Avg Response</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Satisfaction Rate</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services section-padding">
        <div class="container">
            <div class="section-header">
                <h2><?= htmlspecialchars($servicesContent['header_title'] ?? 'My Services') ?></h2>
                <p><?= htmlspecialchars($servicesContent['header_subtitle'] ?? 'Comprehensive solutions for your hardware and software needs.') ?></p>
            </div>
            <div class="services-grid">
                <?php foreach ($services as $service): ?>
                <div class="service-card">
                    <div class="icon-box">
                        <i class="<?= htmlspecialchars($service['icon']) ?>"></i>
                    </div>
                    <h3><?= htmlspecialchars($service['title']) ?></h3>
                    <p><?= htmlspecialchars($service['description']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials section-padding bg-light">
        <div class="container">
            <div class="section-header">
                <h2><?= htmlspecialchars($testimonialsContent['header_title'] ?? 'What Clients Say') ?></h2>
                <p><?= htmlspecialchars($testimonialsContent['header_subtitle'] ?? 'Trusted by neighbors and local businesses.') ?></p>
            </div>
            <div class="testimonials-grid">
                <?php foreach ($testimonials as $testimonial): ?>
                <div class="testimonial-card">
                    <div class="stars">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <?php if ($i < $testimonial['rating']): ?>
                                <i class="fa-solid fa-star"></i>
                            <?php else: ?>
                                <i class="fa-regular fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <p class="quote">"<?= htmlspecialchars($testimonial['content']) ?>"</p>
                    <div class="author">
                        <div class="author-info">
                            <h4><?= htmlspecialchars($testimonial['name']) ?></h4>
                            <span><?= htmlspecialchars($testimonial['role']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="pricing section-padding">
        <div class="container">
            <div class="section-header">
                <h2><?= htmlspecialchars($pricingContent['header_title'] ?? 'Transparent Pricing') ?></h2>
                <p><?= htmlspecialchars($pricingContent['header_subtitle'] ?? 'No hidden fees. You know what you pay.') ?></p>
            </div>
            <div class="pricing-wrapper">
                <?php foreach ($pricingPlans as $plan): ?>
                <div class="pricing-card <?= $plan['is_popular'] ? 'popular' : '' ?>">
                    <?php if ($plan['is_popular']): ?>
                        <div class="badge">Most Popular</div>
                    <?php endif; ?>
                    <div class="pricing-header">
                        <h3><?= htmlspecialchars($plan['name']) ?></h3>
                        <div class="price"><?= htmlspecialchars($plan['price']) ?></div>
                        <p><?= htmlspecialchars($plan['frequency']) ?></p>
                    </div>
                    <ul class="pricing-features">
                        <?php 
                        $features = json_decode($plan['features'], true) ?? [];
                        foreach ($features as $feature): 
                        ?>
                        <li><i class="fa-solid fa-check"></i> <?= htmlspecialchars($feature) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="#contact" class="btn <?= $plan['is_popular'] ? 'btn-primary' : 'btn-secondary' ?> btn-block"><?= htmlspecialchars($plan['cta_text']) ?></a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Knowledge Base Section -->
    <section id="knowledge-base" class="faq section-padding">
        <div class="container">
            <div class="section-header">
                <h2>Knowledge Base</h2>
                <p>Helpful articles and guides.</p>
            </div>
            <div class="faq-container">
                <?php if (empty($kbArticles)): ?>
                    <p style="text-align: center; color: var(--light-text);">No articles available at the moment.</p>
                <?php else: ?>
                    <?php foreach ($kbArticles as $article): ?>
                        <div class="faq-item">
                            <button class="faq-question">
                                <?= htmlspecialchars($article['title']) ?>
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>
                            <div class="faq-answer">
                                <div style="padding: 1rem 0;">
                                    <?= $article['content'] // Content is rich text from editor ?>
                                </div>
                                <div style="font-size: 0.875rem; color: var(--light-text); margin-top: 1rem; border-top: 1px solid var(--border-color); padding-top: 0.5rem;">
                                    Last updated: <?= formatDate($article['updated_at']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="faq section-padding bg-light">
        <div class="container">
            <div class="section-header">
                <h2><?= htmlspecialchars($faqContent['header_title'] ?? 'Frequently Asked Questions') ?></h2>
                <p><?= htmlspecialchars($faqContent['header_subtitle'] ?? 'Common questions about my services.') ?></p>
            </div>
            <div class="faq-container">
                <?php foreach ($faqs as $faq): ?>
                <div class="faq-item">
                    <button class="faq-question">
                        <?= htmlspecialchars($faq['question']) ?>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="faq-answer">
                        <p><?= htmlspecialchars($faq['answer']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Process Section -->
    <section id="process" class="process section-padding">
        <div class="container">
            <div class="section-header">
                <h2><?= htmlspecialchars($processContent['header_title'] ?? 'How It Works') ?></h2>
                <p><?= htmlspecialchars($processContent['header_subtitle'] ?? 'Simple, transparent, and convenient service.') ?></p>
            </div>
            <div class="process-steps">
                <?php foreach ($processSteps as $step): ?>
                <div class="step">
                    <div class="step-number"><?= $step['step_number'] ?></div>
                    <h3><?= htmlspecialchars($step['title']) ?></h3>
                    <p><?= htmlspecialchars($step['description']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact section-padding bg-light">
        <div class="container">
            <div class="contact-wrapper">
                <div class="contact-info">
                    <h2>Get In Touch</h2>
                    <p>Ready to fix your computer? Reach out today!</p>
                    <ul class="contact-details">
                        <li>
                            <i class="fa-solid fa-location-dot"></i>
                            <span>Serving Your Town & Surrounding Areas</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-phone"></i>
                            <span>(555) 123-4567</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-envelope"></i>
                            <span>support@localtechfix.com</span>
                        </li>
                    </ul>
                </div>
                <div class="contact-form-container">
                    <form class="contact-form">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" required placeholder="Your Name">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required placeholder="Your Email">
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" required
                                placeholder="Describe your issue..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="file-upload">Upload Files (Images, Logs)</label>
                            <div class="file-upload-wrapper">
                                <input type="file" id="file-upload" name="file-upload" multiple>
                                <span class="file-upload-text">Choose files or drag here</span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <!-- Footer -->
    <?php include 'includes/public_footer.php'; ?>

    <!-- Floating Action Button -->
    <a href="#contact" class="fab" aria-label="Contact us">
        <i class="fa-solid fa-phone"></i>
    </a>

    <script src="assets/js/script.js"></script>
</body>

</html>
