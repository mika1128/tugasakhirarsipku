<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->getConnection();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $sender_type = $input['sender_type'] ?? 'public';
    $sender_name = $input['sender_name'] ?? 'Anonim';
    $sender_email = $input['sender_email'] ?? '';
    $message = $input['message'] ?? '';
    
    if (empty($message)) {
        $response = ['status' => 'error', 'message' => 'Pesan tidak boleh kosong.'];
        echo json_encode($response);
        exit();
    }
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO chat_messages (sender_type, sender_name, sender_email, message) 
            VALUES (:sender_type, :sender_name, :sender_email, :message)
        ");
        
        $stmt->bindParam(':sender_type', $sender_type);
        $stmt->bindParam(':sender_name', $sender_name);
        $stmt->bindParam(':sender_email', $sender_email);
        $stmt->bindParam(':message', $message);
        
        if ($stmt->execute()) {
            $response = [
                'status' => 'success', 
                'message' => 'Pesan berhasil dikirim.',
                'message_id' => $conn->lastInsertId()
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal mengirim pesan.'];
        }
        
    } catch (PDOException $e) {
        error_log("Error sending message: " . $e->getMessage());
        $response = ['status' => 'error', 'message' => 'Kesalahan database: ' . $e->getMessage()];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode permintaan tidak valid.'];
}

echo json_encode($response);
?>