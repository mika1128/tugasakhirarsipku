<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT sender_type, sender_name, sender_email, message, created_at 
        FROM chat_messages 
        ORDER BY created_at ASC 
        LIMIT 50
    ");
    
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'status' => 'success',
        'data' => $messages,
        'total' => count($messages)
    ];
    
} catch (PDOException $e) {
    error_log("Error loading messages: " . $e->getMessage());
    $response = ['status' => 'error', 'message' => 'Kesalahan database: ' . $e->getMessage()];
}

echo json_encode($response);
?>