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
    
    $id = $input['id'] ?? null;
    $status = $input['status'] ?? '';
    $keterangan_admin = $input['keterangan_admin'] ?? '';
    $admin_id = $_SESSION['user_id'];
    
    if (!$id || !$status) {
        $response = ['status' => 'error', 'message' => 'ID dan status harus diisi.'];
        echo json_encode($response);
        exit();
    }
    
    try {
        $stmt = $conn->prepare("
            UPDATE berkas_submissions 
            SET status = :status, keterangan_admin = :keterangan_admin, processed_by = :processed_by, tanggal_diproses = NOW() 
            WHERE id = :id
        ");
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':keterangan_admin', $keterangan_admin);
        $stmt->bindParam(':processed_by', $admin_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $response = [
                'status' => 'success', 
                'message' => 'Status berkas berhasil diupdate.'
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal mengupdate status berkas.'];
        }
        
    } catch (PDOException $e) {
        error_log("Error updating berkas status: " . $e->getMessage());
        $response = ['status' => 'error', 'message' => 'Kesalahan database: ' . $e->getMessage()];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode permintaan tidak valid.'];
}

echo json_encode($response);
?>