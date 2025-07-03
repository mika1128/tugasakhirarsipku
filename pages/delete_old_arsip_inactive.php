<?php
// pages/delete_old_inactive_arsip.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireAdmin();

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Define a threshold year for "old" archives (e.g., older than 5 years ago)
    $currentYear = date('Y');
    $thresholdYear = $currentYear - 5; // Example: delete archives older than 5 years

    try {
        // Get list of files to delete before deleting records
        $stmt = $conn->prepare("SELECT gambar_surat FROM arsip_inactive WHERE tahun_dibuat < :threshold_year");
        $stmt->bindParam(':threshold_year', $thresholdYear, PDO::PARAM_INT);
        $stmt->execute();
        $files_to_delete = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $conn->prepare("DELETE FROM arsip_inactive WHERE tahun_dibuat < :threshold_year");
        $stmt->bindParam(':threshold_year', $thresholdYear, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $deleted_count = $stmt->rowCount();
            // Delete actual files from the server
            $uploadDir = __DIR__ . '/../uploads/arsip_inactive/';
            foreach ($files_to_delete as $file) {
                if ($file && file_exists($uploadDir . $file)) {
                    unlink($uploadDir . $file);
                }
            }
            $response = ['status' => 'success', 'message' => "Berhasil menghapus {$deleted_count} arsip inactive lama (dibuat sebelum tahun {$thresholdYear})."];
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal menghapus arsip inactive lama.'];
        }

    } catch (PDOException $e) {
        $response = ['status' => 'error', 'message' => 'Kesalahan database: ' . $e->getMessage(), 'error' => $e->getMessage()];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode permintaan tidak valid.'];
}

echo json_encode($response);
?>