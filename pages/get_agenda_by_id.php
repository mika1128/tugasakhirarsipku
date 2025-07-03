<?php
// get_agenda_by_id.php - Untuk mengisi form edit

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

$id = $_GET['id'] ?? null; // Ambil ID dari parameter GET

// --- BAGIAN INI YANG DIUBAH UNTUK KEMUDAHAN (TIDAK AMAN) ---
$user_id = 1; // Contoh: Pastikan ini adalah ID user yang valid di database Anda
// -----------------------------------------------------------

if (empty($id)) {
    echo json_encode(['status' => 'error', 'message' => 'ID agenda tidak ditemukan.']);
    exit;
}

try {
    // Ambil data agenda berdasarkan ID dan user_id (untuk keamanan)
    $stmt = $conn->prepare("
        SELECT
            id,
            user_id,
            title,
            description,
            start_date,
            end_date,
            location,
            status,
            priority
        FROM agenda
        WHERE id = :id AND user_id = :user_id
    ");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':user_id', $user_id); // Gunakan user_id tetap
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Agenda tidak ditemukan atau Anda tidak memiliki akses.',
            'data' => null
        ]);
    }

} catch (Exception $e) {
    error_log("Error getting agenda by ID: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal mengambil data agenda',
        'error' => $e->getMessage()
    ]);
}
?>