<?php
/**
 * Helper Functions
 * Utility functions used throughout the application
 */

/**
 * Sanitize user input
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}



/**
 * Redirect to a URL
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate($date, $format = 'M d, Y g:i A') {
    return date($format, strtotime($date));
}

/**
 * Get status badge HTML
 * @param string $status Ticket status
 * @return string HTML for status badge
 */
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'in_progress' => '<span class="badge badge-info">In Progress</span>',
        'waiting_parts' => '<span class="badge badge-secondary">Waiting for Parts</span>',
        'completed' => '<span class="badge badge-success">Completed</span>',
        'cancelled' => '<span class="badge badge-danger">Cancelled</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-default">' . ucfirst($status) . '</span>';
}

/**
 * Get priority badge HTML
 * @param string $priority Ticket priority
 * @return string HTML for priority badge
 */
function getPriorityBadge($priority) {
    $badges = [
        'low' => '<span class="badge badge-success">Low</span>',
        'medium' => '<span class="badge badge-info">Medium</span>',
        'high' => '<span class="badge badge-warning">High</span>',
        'urgent' => '<span class="badge badge-danger">Urgent</span>'
    ];
    
    return $badges[$priority] ?? '<span class="badge badge-default">' . ucfirst($priority) . '</span>';
}

/**
 * Validate CSRF token
 * @param string $token Token to validate
 * @return bool True if valid, false otherwise
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF token field
 * @return string HTML input field with CSRF token
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

/**
 * Upload file
 * @param array $file File from $_FILES
 * @param int $ticketId Ticket ID
 * @return array Result with success status and message
 */
function uploadFile($file, $ticketId) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large (max 5MB)'];
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_FILE_TYPES)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }
    
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = UPLOAD_DIR . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Save to database
        $db = new Database();
        $conn = $db->connect();
        
        $stmt = $conn->prepare("INSERT INTO files (ticket_id, filename, filepath, filesize) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ticketId, $file['name'], $filename, $file['size']]);
        
        return ['success' => true, 'message' => 'File uploaded successfully', 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Failed to save file'];
}

/**
 * Get file extension icon class
 * @param string $filename Filename
 * @return string Font Awesome icon class
 */
function getFileIcon($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $icons = [
        'pdf' => 'fa-file-pdf',
        'doc' => 'fa-file-word',
        'docx' => 'fa-file-word',
        'jpg' => 'fa-file-image',
        'jpeg' => 'fa-file-image',
        'png' => 'fa-file-image',
        'gif' => 'fa-file-image',
        'txt' => 'fa-file-alt',
        'log' => 'fa-file-alt'
    ];
    
    return $icons[$extension] ?? 'fa-file';
}

/**
 * Format file size
 * @param int $bytes File size in bytes
 * @return string Truncated text
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Truncate text
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @return string Truncated text
 */
function truncate($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

/**
 * Get a setting value from the database
 */
function getSetting($key, $default = null) {
    $db = new Database();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    
    return $result ? $result['setting_value'] : $default;
}

/**
 * Update a setting value in the database
 */
function updateSetting($key, $value) {
    $db = new Database();
    $conn = $db->connect();
    
    // Check if setting exists
    $stmt = $conn->prepare("SELECT id FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    
    if ($stmt->fetch()) {
        $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        return $stmt->execute([$value, $key]);
    } else {
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        return $stmt->execute([$key, $value]);
    }
}

/**
 * Get the current theme CSS file path
 */
function getThemeCss() {
    return 'assets/css/modern.css?v=' . time();
}

/**
 * Send an email using SimpleSMTP
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @return array Result with success status and message
 */
function sendEmail($to, $subject, $body) {
    require_once 'SimpleSMTP.php';
    
    $enabled = getSetting('email_enabled', '0');
    if ($enabled !== '1') {
        return ['success' => false, 'message' => 'Email sending is disabled'];
    }
    
    $host = getSetting('smtp_host');
    $port = getSetting('smtp_port');
    $user = getSetting('smtp_user');
    $pass = getSetting('smtp_pass');
    $fromEmail = getSetting('smtp_from_email');
    $fromName = getSetting('smtp_from_name');
    
    $smtp = new SimpleSMTP($host, $port, $user, $pass);
    
    if ($smtp->send($to, $subject, $body, $fromEmail, $fromName)) {
        return ['success' => true, 'message' => 'Email sent successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to send email. Check logs.'];
    }
}
