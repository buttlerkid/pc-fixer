-- Homepage Content Table
CREATE TABLE IF NOT EXISTS homepage_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(50) NOT NULL,
    key_name VARCHAR(50) NOT NULL,
    content_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_content (section, key_name)
);

-- Services Table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    icon VARCHAR(50) NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Testimonials Table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100),
    content TEXT NOT NULL,
    rating INT DEFAULT 5,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Pricing Plans Table
CREATE TABLE IF NOT EXISTS pricing_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price VARCHAR(50) NOT NULL,
    frequency VARCHAR(50),
    features TEXT, -- JSON array of features
    is_popular BOOLEAN DEFAULT 0,
    cta_text VARCHAR(50) DEFAULT 'Book Now',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- FAQ Table
CREATE TABLE IF NOT EXISTS faq (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Process Steps Table
CREATE TABLE IF NOT EXISTS process_steps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    step_number INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Initial Content (Hero Section)
INSERT INTO homepage_content (section, key_name, content_value) VALUES
('hero', 'title_prefix', 'Fast & Reliable PC Repair in'),
('hero', 'title_highlight', 'Your Town'),
('hero', 'description', 'Don''t let computer problems slow you down. I provide expert troubleshooting and repair services for desktops and laptops. Personal, local, and trusted.'),
('hero', 'btn_primary_text', 'Get a Quote'),
('hero', 'btn_primary_link', '#contact'),
('hero', 'btn_secondary_text', 'View Services'),
('hero', 'btn_secondary_link', '#services'),
('stats', 'header_title', 'Our Track Record'),
('stats', 'header_subtitle', 'Numbers that speak for themselves.'),
('services', 'header_title', 'My Services'),
('services', 'header_subtitle', 'Comprehensive solutions for your hardware and software needs.'),
('testimonials', 'header_title', 'What Clients Say'),
('testimonials', 'header_subtitle', 'Trusted by neighbors and local businesses.'),
('pricing', 'header_title', 'Transparent Pricing'),
('pricing', 'header_subtitle', 'No hidden fees. You know what you pay.'),
('faq', 'header_title', 'Frequently Asked Questions'),
('faq', 'header_subtitle', 'Common questions about my services.'),
('process', 'header_title', 'How It Works'),
('process', 'header_subtitle', 'Simple, transparent, and convenient service.'),
('contact', 'header_title', 'Get In Touch'),
('contact', 'header_subtitle', 'Ready to fix your computer? Reach out today!'),
('contact', 'address', 'Serving Your Town & Surrounding Areas'),
('contact', 'phone', '(555) 123-4567'),
('contact', 'email', 'support@localtechfix.com')
ON DUPLICATE KEY UPDATE content_value = VALUES(content_value);

-- Insert Initial Services
INSERT INTO services (icon, title, description, display_order) VALUES
('fa-solid fa-laptop-medical', 'Hardware Repair', 'Screen replacements, battery swaps, keyboard fixes, and component upgrades for laptops and desktops.', 1),
('fa-brands fa-windows', 'Software Troubleshooting', 'OS installation, virus removal, driver issues, and performance optimization to make your PC run like new.', 2),
('fa-solid fa-network-wired', 'Network Setup', 'Wi-Fi configuration, router setup, and connectivity troubleshooting for your home or small office.', 3),
('fa-solid fa-database', 'Data Recovery', 'Recover lost files from failing hard drives or accidental deletions. Backup solutions also available.', 4);

-- Insert Initial Testimonials
INSERT INTO testimonials (name, role, content, rating, display_order) VALUES
('Sarah J.', 'Local Resident', 'Fixed my laptop screen in 24 hours! Saved me from buying a new computer. Highly recommended!', 5, 1),
('Mike T.', 'Small Business Owner', 'My PC was running so slow I couldn''t work. After the tune-up, it''s like a brand new machine.', 5, 2),
('Emily R.', 'Teacher', 'Super friendly and explained everything in plain English. No tech jargon, just results.', 5, 3);

-- Insert Initial Pricing Plans
INSERT INTO pricing_plans (name, price, frequency, features, is_popular, cta_text, display_order) VALUES
('Diagnostics', 'Free*', 'with any repair service', '["Full Hardware Scan", "Software Analysis", "No Obligation Quote"]', 0, 'Book Now', 1),
('Virus Removal', '$89', 'Flat rate', '["Deep System Clean", "Malware Removal", "Antivirus Installation", "Performance Tune-up"]', 1, 'Book Now', 2),
('System Setup', '$120', 'Starting at', '["New PC Setup", "Data Transfer", "Printer & WiFi Setup", "Email Configuration"]', 0, 'Book Now', 3);

-- Insert Initial FAQs
INSERT INTO faq (question, answer, display_order) VALUES
('Do you come to my house?', 'Yes! I offer mobile services within a 15-mile radius. For more complex hardware repairs, I may need to take the device to my workshop and return it once fixed.', 1),
('How long do repairs usually take?', 'Most software issues and standard hardware replacements are completed within 24-48 hours. Special order parts may take a few days to arrive.', 2),
('Is there a warranty on repairs?', 'Absolutely. I offer a 30-day warranty on all labor and pass through the manufacturer''s warranty on any new parts installed.', 3),
('What if you can''t fix it?', 'If I can''t fix the issue, you don''t pay for the repair. A small diagnostic fee may still apply depending on the time spent investigating.', 4);

-- Insert Initial Process Steps
INSERT INTO process_steps (step_number, title, description) VALUES
(1, 'Contact Me', 'Fill out the form below or call to describe your issue.'),
(2, 'Diagnosis', 'We meet locally or I come to you to diagnose the problem.'),
(3, 'Repair', 'I fix the issue efficiently and keep you updated.'),
(4, 'Done!', 'You get your device back in working order. Satisfaction guaranteed.');
