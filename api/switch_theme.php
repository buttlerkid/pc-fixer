<?php
require_once __DIR__ . '/../includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $theme = $data['theme'] ?? '';

    if (in_array($theme, ['default', 'modern'])) {
        // Set cookie for 30 days
        setcookie('site_theme', $theme, time() + (86400 * 30), "/");
        $_SESSION['site_theme'] = $theme;
        
        echo json_encode(['success' => true, 'theme' => $theme]);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid theme']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
