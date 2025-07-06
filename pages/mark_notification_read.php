<?php
/**
 * Mark Notification as Read
 * File: pages/mark_notification_read.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $notificationId = $input['id'] ?? null;

    if (!$notificationId) {
        $response = ['status' => 'error', 'message' => 'ID notifikasi tidak disediakan.'];
        echo json_encode($response);
        exit();
    }

    try {
        // For now, we'll just return success since we're generating notifications dynamically
        // In a real implementation, you would update a notifications table
        
        // Log the action
        $auth->logActivity(
            $_SESSION['user_id'], 
            'mark_read', 
            'notification', 
            null, 
            "Marked notification as read: {$notificationId}"
        );
        
        $response = [
            'status' => 'success', 
            'message' => 'Notifikasi berhasil ditandai sebagai dibaca'
        ];

    } catch (Exception $e) {
        error_log("Error marking notification as read: " . $e->getMessage());
        $response = [
            'status' => 'error', 
            'message' => 'Gagal menandai notifikasi sebagai dibaca',
            'error' => $e->getMessage()
        ];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode permintaan tidak valid.'];
}

echo json_encode($response);
?>