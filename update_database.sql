-- Create settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default theme setting if it doesn't exist
INSERT INTO settings (setting_key, setting_value) 
SELECT 'site_theme', 'default' 
WHERE NOT EXISTS (SELECT 1 FROM settings WHERE setting_key = 'site_theme');
