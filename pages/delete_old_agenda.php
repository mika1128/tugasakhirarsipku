<?php
// delete_old_agenda.php

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->getConnection();

    // --- BAGIAN INI YANG DIUBAH UNTUK KEMUDAHAN (TIDAK AMAN UNTUK AKSES) ---
    $user_id = 1; // Contoh: Admin
    // ----------------------------------------------------------------------

    // Tentukan batas waktu, misalnya agenda yang lebih dari 6 bulan lalu
    $threshold_date = date('Y-m-d H:i:s', strtotime('-6 months')); // Hapus yang lebih lama dari 6 bulan

    try {
        // Hapus agenda yang sudah complete/cancelled dan end_date-nya lebih lama dari threshold_date
        $query = "DELETE FROM agenda WHERE (status = 'complete' OR status = 'cancelled') AND end_date < :threshold_date";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':threshold_date', $threshold_date);
        // Jika hanya admin yang bisa menghapus milik mereka, tambahkan AND user_id = :user_id

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => $stmt->rowCount() . ' agenda lama berhasil dihapus.']);
            } else {
                echo json_encode(['status' => 'info', 'message' => 'Tidak ada agenda lama yang perlu dihapus.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus agenda lama.']);
        }

    } catch (PDOException $e) {
        error_log("Error deleting old agenda: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan database saat menghapus agenda lama.', 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak diizinkan.']);
}
?>