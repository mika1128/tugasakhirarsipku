<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireAdmin();

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT 
            id,
            sender_type, 
            sender_name, 
            sender_email, 
            message, 
            reply_to,
            is_read,
            created_at,
            admin_id
        FROM chat_messages 
        ORDER BY created_at DESC
    ");
    
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'status' => 'success',
        'data' => $messages,
        'total' => count($messages)
    ];
    
} catch (PDOException $e) {
    error_log("Error loading chat messages: " . $e->getMessage());
    $response = ['status' => 'error', 'message' => 'Kesalahan database: ' . $e->getMessage()];
}

echo json_encode($response);
?>