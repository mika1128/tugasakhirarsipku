<?php
// archive_agenda.php - Untuk mengubah status agenda menjadi 'complete' atau 'archived'

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->getConnection();

    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null; // ID agenda yang akan diarsipkan
    $archive_all = $input['archive_all'] ?? false; // Flag untuk mengarsipkan semua yang selesai

    // --- BAGIAN INI YANG DIUBAH UNTUK KEMUDAHAN (TIDAK AMAN UNTUK AKSES) ---
    $user_id = 1; // Contoh: Admin
    // ----------------------------------------------------------------------

    try {
        if ($archive_all) {
            // Arsipkan semua agenda yang 'in_progress' atau 'pending' dan end_date-nya sudah lewat
            // Atau semua yang 'pending'/'in_progress' dan belum 'complete'/'cancelled'
            $query = "UPDATE agenda SET status = 'complete' WHERE status != 'complete' AND status != 'cancelled' AND (end_date < NOW() OR status = 'pending' OR status = 'in_progress')";
            $stmt = $conn->prepare($query);
            // Untuk skenario produksi, mungkin juga perlu user_id
            // $query = "UPDATE agenda SET status = 'complete' WHERE user_id = :user_id AND (status != 'complete' AND status != 'cancelled') AND (end_date < NOW() OR status = 'pending' OR status = 'in_progress')";
            // $stmt->bindParam(':user_id', $user_id);

        } elseif ($id) {
            // Arsipkan agenda individual
            $query = "UPDATE agenda SET status = 'complete' WHERE id = :id AND user_id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $user_id);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Permintaan tidak valid.']);
            exit;
        }

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Agenda berhasil diarsipkan.']);
            } else {
                echo json_encode(['status' => 'info', 'message' => 'Tidak ada agenda yang perlu diarsipkan.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal mengarsipkan agenda.']);
        }

    } catch (PDOException $e) {
        error_log("Error archiving agenda: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan database saat mengarsipkan agenda.', 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak diizinkan.']);
}
?>