<?php

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $db = new Database();
    $conn = $db->getConnection();

    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
-
    $user_id = 1; 


    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'ID agenda tidak ditemukan.']);
        exit;
    }

    try {
        $query = "DELETE FROM agenda WHERE id = :id AND user_id = :user_id"; // Hapus berdasarkan ID dan user_id
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $user_id); // Gunakan user_id tetap

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Agenda berhasil dihapus.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Agenda tidak ditemukan atau sudah dihapus.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus agenda.']);
        }

    } catch (PDOException $e) {
        error_log("Error deleting agenda: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan database saat menghapus agenda.', 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak diizinkan.']);
}
?>