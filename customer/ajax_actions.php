<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

// Ensure user is logged in
requireLogin();

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate CSRF token
$data = json_decode(file_get_contents('php://input'), true);
if (!validateCsrfToken($data['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Close session to prevent locking
session_write_close();

$action = $data['action'] ?? '';
$userId = getUserId();
$db = new Database();
$conn = $db->connect();

switch ($action) {
    case 'send_message':
        $ticketId = (int)($data['ticket_id'] ?? 0);
        $content = sanitize($data['message'] ?? '');
        
        if ($ticketId && !empty($content)) {
            // Verify ticket belongs to user
            $stmt = $conn->prepare("SELECT id, title FROM tickets WHERE id = ? AND user_id = ?");
            $stmt->execute([$ticketId, $userId]);
            $ticket = $stmt->fetch();

            if ($ticket) {
                $stmt = $conn->prepare("INSERT INTO messages (ticket_id, author_id, content, is_admin) VALUES (?, ?, ?, 0)");
                if ($stmt->execute([$ticketId, $userId, $content])) {
                    $messageId = $conn->lastInsertId();
                    
                    // Fetch the new message to return formatted HTML
                    $stmt = $conn->prepare("SELECT m.*, u.name as author_name FROM messages m JOIN users u ON m.author_id = u.id WHERE m.id = ?");
                    $stmt->execute([$messageId]);
                    $message = $stmt->fetch();
                    
                    // Send email notification to admin
                    try {
                        $adminEmail = getSetting('smtp_from_email');
                        $subject = "New Reply on Ticket: #$ticketId - " . $ticket['title'];
                        
                        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                        $baseUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2);
                        
                        $body = "<h2>New Reply</h2>
                                 <p>Customer <strong>" . htmlspecialchars($_SESSION['user_name']) . "</strong> has replied to a ticket.</p>
                                 <p><strong>Message:</strong><br>" . nl2br($content) . "</p>
                                 <p><a href='" . $baseUrl . "/admin/ticket-detail.php?id=$ticketId'>View Ticket in Admin Panel</a></p>";
                                 
                        sendEmail($adminEmail, $subject, $body);
                    } catch (Exception $e) {
                        // Ignore email errors
                    }

                    $html = '
                    <div class="message" id="message-' . $message['id'] . '">
                        <div class="message-header">
                            <span class="message-author">' . htmlspecialchars($message['author_name']) . '</span>
                            <span class="message-time">' . formatDate($message['created_at']) . '</span>
                        </div>
                        <div>' . nl2br(htmlspecialchars($message['content'])) . '</div>
                    </div>';
                    
                    echo json_encode(['success' => true, 'message' => 'Reply sent', 'html' => $html]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Ticket not found or access denied']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
