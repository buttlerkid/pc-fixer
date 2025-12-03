<?php
require_once __DIR__ . '/includes/functions.php';
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
                    <li><a href="login.php" class="nav-link">Login</a></li>
                    <li><a href="register.php" class="nav-link btn-primary">Sign Up</a></li>
                    <li>
                        <button class="theme-toggle" aria-label="Toggle dark mode">
                            <i class="fa-solid fa-moon"></i>
                        </button>
                    </li>
                    <li>
                        <button class="style-toggle" aria-label="Switch Theme" title="Switch Theme">
                            <i class="fa-solid fa-palette"></i>
                        </button>
                    </li>
                </ul>
                <button class="mobile-menu-btn" aria-label="Toggle menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="hero" class="hero">
        <div class="container hero-container">
            <div class="hero-content">
                <h1>Fast & Reliable PC Repair in <span class="highlight">Your Town</span></h1>
                <p>Don't let computer problems slow you down. I provide expert troubleshooting and repair services for
                    desktops and laptops. Personal, local, and trusted.</p>
                <div class="hero-buttons">
                    <a href="#contact" class="btn btn-primary">Get a Quote</a>
                    <a href="#services" class="btn btn-secondary">View Services</a>
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
                <h2>Our Track Record</h2>
                <p>Numbers that speak for themselves.</p>
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
                <h2>My Services</h2>
                <p>Comprehensive solutions for your hardware and software needs.</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="icon-box">
                        <i class="fa-solid fa-laptop-medical"></i>
                    </div>
                    <h3>Hardware Repair</h3>
                    <p>Screen replacements, battery swaps, keyboard fixes, and component upgrades for laptops and
                        desktops.</p>
                </div>
                <div class="service-card">
                    <div class="icon-box">
                        <i class="fa-brands fa-windows"></i>
                    </div>
                    <h3>Software Troubleshooting</h3>
                    <p>OS installation, virus removal, driver issues, and performance optimization to make your PC run
                        like new.</p>
                </div>
                <div class="service-card">
                    <div class="icon-box">
                        <i class="fa-solid fa-network-wired"></i>
                    </div>
                    <h3>Network Setup</h3>
                    <p>Wi-Fi configuration, router setup, and connectivity troubleshooting for your home or small
                        office.</p>
                </div>
                <div class="service-card">
                    <div class="icon-box">
                        <i class="fa-solid fa-database"></i>
                    </div>
                    <h3>Data Recovery</h3>
                    <p>Recover lost files from failing hard drives or accidental deletions. Backup solutions also
                        available.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials section-padding bg-light">
        <div class="container">
            <div class="section-header">
                <h2>What Clients Say</h2>
                <p>Trusted by neighbors and local businesses.</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="stars">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <p class="quote">"Fixed my laptop screen in 24 hours! Saved me from buying a new computer. Highly
                        recommended!"</p>
                    <div class="author">
                        <div class="author-info">
                            <h4>Sarah J.</h4>
                            <span>Local Resident</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="stars">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <p class="quote">"My PC was running so slow I couldn't work. After the tune-up, it's like a brand
                        new machine."</p>
                    <div class="author">
                        <div class="author-info">
                            <h4>Mike T.</h4>
                            <span>Small Business Owner</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="stars">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star-half-stroke"></i>
                    </div>
                    <p class="quote">"Super friendly and explained everything in plain English. No tech jargon, just
                        results."</p>
                    <div class="author">
                        <div class="author-info">
                            <h4>Emily R.</h4>
                            <span>Teacher</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="pricing section-padding">
        <div class="container">
            <div class="section-header">
                <h2>Transparent Pricing</h2>
                <p>No hidden fees. You know what you pay.</p>
            </div>
            <div class="pricing-wrapper">
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Diagnostics</h3>
                        <div class="price">Free*</div>
                        <p>with any repair service</p>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fa-solid fa-check"></i> Full Hardware Scan</li>
                        <li><i class="fa-solid fa-check"></i> Software Analysis</li>
                        <li><i class="fa-solid fa-check"></i> No Obligation Quote</li>
                    </ul>
                    <a href="#contact" class="btn btn-secondary btn-block">Book Now</a>
                    <p class="pricing-note">* $40 fee if no repair is performed.</p>
                </div>
                <div class="pricing-card popular">
                    <div class="badge">Most Popular</div>
                    <div class="pricing-header">
                        <h3>Virus Removal</h3>
                        <div class="price">$89</div>
                        <p>Flat rate</p>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fa-solid fa-check"></i> Deep System Clean</li>
                        <li><i class="fa-solid fa-check"></i> Malware Removal</li>
                        <li><i class="fa-solid fa-check"></i> Antivirus Installation</li>
                        <li><i class="fa-solid fa-check"></i> Performance Tune-up</li>
                    </ul>
                    <a href="#contact" class="btn btn-primary btn-block">Book Now</a>
                </div>
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>System Setup</h3>
                        <div class="price">$120</div>
                        <p>Starting at</p>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fa-solid fa-check"></i> New PC Setup</li>
                        <li><i class="fa-solid fa-check"></i> Data Transfer</li>
                        <li><i class="fa-solid fa-check"></i> Printer & WiFi Setup</li>
                        <li><i class="fa-solid fa-check"></i> Email Configuration</li>
                    </ul>
                    <a href="#contact" class="btn btn-secondary btn-block">Book Now</a>
                </div>
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
                <h2>Frequently Asked Questions</h2>
                <p>Common questions about my services.</p>
            </div>
            <div class="faq-container">
                <div class="faq-item">
                    <button class="faq-question">
                        Do you come to my house?
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Yes! I offer mobile services within a 15-mile radius. For more complex hardware repairs, I
                            may need to take the device to my workshop and return it once fixed.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question">
                        How long do repairs usually take?
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Most software issues and standard hardware replacements are completed within 24-48 hours.
                            Special order parts may take a few days to arrive.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question">
                        Is there a warranty on repairs?
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Absolutely. I offer a 30-day warranty on all labor and pass through the manufacturer's
                            warranty on any new parts installed.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question">
                        What if you can't fix it?
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="faq-answer">
                        <p>If I can't fix the issue, you don't pay for the repair. A small diagnostic fee may still
                            apply depending on the time spent investigating.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Process Section -->
    <section id="process" class="process section-padding">
        <div class="container">
            <div class="section-header">
                <h2>How It Works</h2>
                <p>Simple, transparent, and convenient service.</p>
            </div>
            <div class="process-steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Contact Me</h3>
                    <p>Fill out the form below or call to describe your issue.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Diagnosis</h3>
                    <p>We meet locally or I come to you to diagnose the problem.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Repair</h3>
                    <p>I fix the issue efficiently and keep you updated.</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Done!</h3>
                    <p>You get your device back in working order. Satisfaction guaranteed.</p>
                </div>
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
    <footer class="footer">
        <div class="container footer-container">
            <div class="footer-brand">
                <i class="fa-solid fa-microchip"></i>
                <span>LocalTechFix</span>
            </div>
            <p class="copyright">&copy; 2023 LocalTechFix. All rights reserved.</p>
            <div class="social-links">
                <a href="#"><i class="fa-brands fa-facebook"></i></a>
                <a href="#"><i class="fa-brands fa-twitter"></i></a>
                <a href="#"><i class="fa-brands fa-instagram"></i></a>
            </div>
        </div>
    </footer>

    <!-- Floating Action Button -->
    <a href="#contact" class="fab" aria-label="Contact us">
        <i class="fa-solid fa-phone"></i>
    </a>

    <script src="assets/js/script.js"></script>
</body>

</html>
