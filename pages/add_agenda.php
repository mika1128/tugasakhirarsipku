<?php
// add_agenda.php - VERSI SIMPLIFIKASI (TIDAK AMAN UNTUK PRODUKSI!)

require_once __DIR__ . '/../config/database.php'; // Pastikan path ini benar

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->getConnection();

    // Mengambil data JSON dari request body
    $input = json_decode(file_get_contents('php://input'), true);

    // Validasi data
    $title = $input['title'] ?? '';
    $description = $input['description'] ?? '';
    $start_date = $input['start_date'] ?? '';
    $end_date = $input['end_date'] ?? '';
    $location = $input['location'] ?? null; // Lokasi bisa null
    $priority = $input['priority'] ?? 'medium'; // Default priority
    $status = $input['status'] ?? 'pending'; // Default status

    // --- BAGIAN INI YANG DIUBAH UNTUK KEMUDAHAN (TIDAK AMAN) ---
    // User ID akan diset secara manual/tetap untuk tujuan demonstrasi
    // SANGAT DISARANKAN UNTUK MENGAMBIL USER_ID DARI SESI UNTUK APLIKASI NYATA
    $user_id = 1; // Contoh: Gunakan user_id 1 (biasanya admin atau user default)
                  // Anda bisa mengubah ini ke ID user yang ada di database Anda
    // -----------------------------------------------------------

    if (empty($title) || empty($description) || empty($start_date) || empty($end_date)) {
        echo json_encode(['status' => 'error', 'message' => 'Judul, deskripsi, tanggal mulai, dan tanggal berakhir harus diisi.']);
        exit;
    }

    try {
        $query = "INSERT INTO agenda (user_id, title, description, start_date, end_date, location, priority, status) VALUES (:user_id, :title, :description, :start_date, :end_date, :location, :priority, :status)";
        $stmt = $conn->prepare($query);

        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':priority', $priority);
        $stmt->bindParam(':status', $status);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Agenda berhasil ditambahkan.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan agenda.']);
        }

    } catch (PDOException $e) {
        error_log("Error adding agenda: " . $e->getMessage()); // Catat error untuk debugging
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan database saat menambah agenda.', 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak diizinkan.']);
}
?>