<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireAdmin();

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->getConnection();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $reply_to = $input['reply_to'] ?? null;
    $message = $input['message'] ?? '';
    $admin_id = $_SESSION['user_id'];
    $admin_name = $_SESSION['full_name'] ?? 'Admin';
    
    if (empty($message)) {
        $response = ['status' => 'error', 'message' => 'Pesan balasan tidak boleh kosong.'];
        echo json_encode($response);
        exit();
    }
    
    try {
        // Insert reply message
        $stmt = $conn->prepare("
            INSERT INTO chat_messages (sender_type, sender_name, sender_email, message, reply_to, admin_id) 
            VALUES ('admin', :sender_name, '', :message, :reply_to, :admin_id)
        ");
        
        $stmt->bindParam(':sender_name', $admin_name);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':reply_to', $reply_to, PDO::PARAM_INT);
        $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            // Mark original message as read
            if ($reply_to) {
                $stmt_read = $conn->prepare("UPDATE chat_messages SET is_read = 1 WHERE id = :id");
                $stmt_read->bindParam(':id', $reply_to, PDO::PARAM_INT);
                $stmt_read->execute();
            }
            
            $response = [
                'status' => 'success', 
                'message' => 'Balasan berhasil dikirim.',
                'reply_id' => $conn->lastInsertId()
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal mengirim balasan.'];
        }
        
    } catch (PDOException $e) {
        error_log("Error sending admin reply: " . $e->getMessage());
        $response = ['status' => 'error', 'message' => 'Kesalahan database: ' . $e->getMessage()];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode permintaan tidak valid.'];
}

echo json_encode($response);
?>