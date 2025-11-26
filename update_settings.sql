INSERT INTO settings (setting_key, setting_value) VALUES 
('email_enabled', '0'),
('smtp_host', 'smtp.example.com'),
('smtp_port', '587'),
('smtp_user', ''),
('smtp_pass', ''),
('smtp_from_email', 'noreply@localtechfix.com'),
('smtp_from_name', 'LocalTechFix Support')
ON DUPLICATE KEY UPDATE setting_value=setting_value;
