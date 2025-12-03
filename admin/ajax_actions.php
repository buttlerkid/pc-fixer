<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

// Ensure user is admin
if (!isLoggedIn() || !isAdmin()) {
    ob_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

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
$db = new Database();
$conn = $db->connect();

switch ($action) {
    case 'delete_message':
        $messageId = (int)($data['message_id'] ?? 0);
        $ticketId = (int)($data['ticket_id'] ?? 0);
        
        if ($messageId && $ticketId) {
            $stmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND ticket_id = ?");
            if ($stmt->execute([$messageId, $ticketId])) {
                ob_clean();
                echo json_encode(['success' => true, 'message' => 'Message deleted']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete message']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        }
        break;

    case 'send_message':
        $ticketId = (int)($data['ticket_id'] ?? 0);
        $content = sanitize($data['message'] ?? '');
        $adminId = getUserId();
        
        if ($ticketId && !empty($content)) {
            $stmt = $conn->prepare("INSERT INTO messages (ticket_id, author_id, content, is_admin) VALUES (?, ?, ?, 1)");
            if ($stmt->execute([$ticketId, $adminId, $content])) {
                $messageId = $conn->lastInsertId();
                
                // Fetch the new message to return formatted HTML
                $stmt = $conn->prepare("SELECT m.*, u.name as author_name FROM messages m JOIN users u ON m.author_id = u.id WHERE m.id = ?");
                $stmt->execute([$messageId]);
                $message = $stmt->fetch();
                
                // Send email notification (async or just trigger it)
                // For simplicity, we'll do it here but catch errors so AJAX doesn't fail
                try {
                    $stmt = $conn->prepare("SELECT t.title, u.email as customer_email FROM tickets t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
                    $stmt->execute([$ticketId]);
                    $ticketInfo = $stmt->fetch();
                    
                    if ($ticketInfo) {
                        $subject = "New Reply on Ticket: #$ticketId - " . $ticketInfo['title'];
                        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                        $baseUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2);
                        $body = "<h2>New Reply</h2><p>An admin has replied to your ticket.</p><p><strong>Message:</strong><br>" . nl2br($content) . "</p><p><a href='" . $baseUrl . "/customer/ticket-detail.php?id=$ticketId'>View Ticket</a></p>";
                        sendEmail($ticketInfo['customer_email'], $subject, $body);
                    }
                } catch (Exception $e) {
                    // Ignore email errors for AJAX response
                }

                $html = '
                <div class="message admin" id="message-' . $message['id'] . '">
                    <div class="message-header">
                        <span class="message-author"><i class="fa-solid fa-shield-halved"></i> ' . htmlspecialchars($message['author_name']) . '</span>
                        <div>
                            <span class="message-time" style="margin-right: 0.5rem;">' . formatDate($message['created_at']) . '</span>
                            <button onclick="deleteMessage(' . $message['id'] . ')" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; line-height: 1; background-color: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div>' . nl2br(htmlspecialchars($message['content'])) . '</div>
                </div>';
                
                echo json_encode(['success' => true, 'message' => 'Reply sent', 'html' => $html]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send message']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        }
        break;

    case 'update_ticket':
        $ticketId = (int)($data['ticket_id'] ?? 0);
        $status = $data['status'] ?? null;
        $priority = $data['priority'] ?? null;
        
        if ($ticketId && ($status || $priority)) {
            // Build dynamic query
            $fields = [];
            $params = [];
            
            if ($status) {
                $fields[] = "status = ?";
                $params[] = $status;
            }
            
            if ($priority) {
                $fields[] = "priority = ?";
                $params[] = $priority;
            }
            
            $params[] = $ticketId;
            $sql = "UPDATE tickets SET " . implode(', ', $fields) . " WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            if ($stmt->execute($params)) {
                
                // Fetch updated ticket info for email and response
                $stmt = $conn->prepare("SELECT t.*, u.email as customer_email FROM tickets t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
                $stmt->execute([$ticketId]);
                $ticketInfo = $stmt->fetch();
                
                // Send email notification
                try {
                    if ($ticketInfo) {
                        $subject = "Ticket Updated: #$ticketId - " . $ticketInfo['title'];
                        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                        $baseUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2);
                        
                        $changes = [];
                        if ($status) $changes[] = "<strong>New Status:</strong> " . ucfirst($status);
                        if ($priority) $changes[] = "<strong>New Priority:</strong> " . ucfirst($priority);
                        
                        $body = "<h2>Ticket Updated</h2><p>Your ticket has been updated.</p><p>" . implode('<br>', $changes) . "</p><p><a href='" . $baseUrl . "/customer/ticket-detail.php?id=$ticketId'>View Ticket</a></p>";
                        sendEmail($ticketInfo['customer_email'], $subject, $body);
                    }
                } catch (Exception $e) {
                    // Ignore email errors
                }

                echo json_encode([
                    'success' => true, 
                    'message' => 'Ticket updated',
                    'statusBadge' => getStatusBadge($ticketInfo['status']),
                    'priorityBadge' => getPriorityBadge($ticketInfo['priority'])
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update ticket']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
