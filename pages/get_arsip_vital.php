<?php
// pages/get_arsip_vital.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
//$auth->requireAdmin();
header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

try {
    $stmt = $conn->prepare("SELECT * FROM arsip_vital ORDER BY id DESC");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = ['status' => 'success', 'data' => $data];

} catch (PDOException $e) {
    $response = ['status' => 'error', 'message' => 'Gagal mengambil data arsip vital: ' . $e->getMessage(), 'error' => $e->getMessage()];
}

echo json_encode($response);
?>