<?php
// get_history.php

require_once __DIR__ . '/../config/database.php';
// require_once __DIR__ . '/../includes/auth.php'; // Aktifkan jika Anda ingin mengamankan ini

header('Content-Type: application/json');

// $auth = new Auth();
// $auth->requireLogin(); // Memastikan hanya pengguna yang login yang bisa melihat riwayat

try {
    $db = new Database();
    $conn = $db->getConnection();

    // --- BAGIAN INI YANG DIUBAH UNTUK KEMUDAHAN (TIDAK AMAN UNTUK AKSES) ---
    $user_id = 1; // Contoh: Untuk admin yang melihat semua riwayat. Jika per user, ambil dari sesi.
    // ----------------------------------------------------------------------

    // Mengambil agenda yang statusnya 'complete' atau 'cancelled' (sesuaikan dengan ENUM Anda)
    // dan/atau agenda yang end_date-nya sudah lewat
    $stmt = $conn->prepare("
       SELECT
        a.id,
        a.user_id,
        u.username,
        a.title,
        a.description,
        a.start_date,
        a.end_date,
        a.location,
        a.status,
        a.priority
        FROM agenda a
        JOIN users u ON a.user_id = u.id
        WHERE a.status = 'complete' OR a.status = 'cancelled' OR a.end_date < NOW()
        ORDER BY a.end_date DESC
    ");

    // Jika Anda ingin ini hanya untuk admin yang melihat semua, atau user_id tetap
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);

} catch (Exception $e) {
    error_log("Error getting history: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal mengambil data riwayat agenda',
        'error' => $e->getMessage()
    ]);
}
?>