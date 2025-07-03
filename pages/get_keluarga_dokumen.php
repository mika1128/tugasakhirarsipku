<?php
// pages/get_keluarga_dokumen.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireAdmin(); // Hanya admin yang bisa mengakses

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

try {
    $stmt = $conn->prepare("SELECT * FROM keluarga_dokumen ORDER BY id DESC");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = ['status' => 'success', 'data' => $data];

} catch (PDOException $e) {
    $response = ['status' => 'error', 'message' => 'Gagal mengambil data dokumen keluarga: ' . $e->getMessage(), 'error' => $e->getMessage()];
}

echo json_encode($response);
?>