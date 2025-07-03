<?php
// update_agenda.php - VERSI SIMPLIFIKASI (TIDAK AMAN UNTUK PRODUKSI!)

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->getConnection();

    $input = json_decode(file_get_contents('php://input'), true);

    $id = $input['id'] ?? null;
    $title = $input['title'] ?? '';
    $description = $input['description'] ?? '';
    $start_date = $input['start_date'] ?? '';
    $end_date = $input['end_date'] ?? '';
    $location = $input['location'] ?? null;
    $priority = $input['priority'] ?? 'medium';
    $status = $input['status'] ?? 'pending';

    // --- BAGIAN INI YANG DIUBAH UNTUK KEMUDAHAN (TIDAK AMAN) ---
    // User ID akan diset secara manual/tetap untuk tujuan demonstrasi
    // SANGAT DISARANKAN UNTUK MENGAMBIL USER_ID DARI SESI UNTUK APLIKASI NYATA
    $user_id = 1; // Contoh: Pastikan ini adalah ID user yang valid di database Anda
    // -----------------------------------------------------------

    if (empty($id) || empty($title) || empty($description) || empty($start_date) || empty($end_date)) {
        echo json_encode(['status' => 'error', 'message' => 'ID, judul, deskripsi, tanggal mulai, dan tanggal berakhir harus diisi.']);
        exit;
    }

    try {
        $query = "UPDATE agenda SET 
                    title = :title, 
                    description = :description, 
                    start_date = :start_date, 
                    end_date = :end_date, 
                    location = :location, 
                    priority = :priority, 
                    status = :status 
                  WHERE id = :id AND user_id = :user_id"; // Pastikan hanya bisa mengedit agenda miliknya (walaupun user_id tetap)
        $stmt = $conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':priority', $priority);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':user_id', $user_id); // Gunakan user_id tetap

        if ($stmt->execute()) {
            // Periksa apakah ada baris yang terpengaruh (agenda ditemukan dan diupdate)
            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Agenda berhasil diperbarui.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Agenda tidak ditemukan atau tidak ada perubahan.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui agenda.']);
        }

    } catch (PDOException $e) {
        error_log("Error updating agenda: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan database saat memperbarui agenda.', 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak diizinkan.']);
}
?>