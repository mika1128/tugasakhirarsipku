<?php
// pages/delete_keluarga_dokumen.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireAdmin();

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;

    if (!$id) {
        $response = ['status' => 'error', 'message' => 'ID dokumen keluarga tidak disediakan.'];
        echo json_encode($response);
        exit();
    }

    try {
        // Get file name to delete from server
        $stmt = $conn->prepare("SELECT dokumen FROM keluarga_dokumen WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $dokumen_to_delete = $stmt->fetchColumn();

        $stmt = $conn->prepare("DELETE FROM keluarga_dokumen WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Delete the actual file from the server
            if ($dokumen_to_delete && file_exists(__DIR__ . '/../uploads/keluarga/' . $dokumen_to_delete)) {
                unlink(__DIR__ . '/../uploads/keluarga/' . $dokumen_to_delete);
            }
            $response = ['status' => 'success', 'message' => 'Dokumen keluarga berhasil dihapus.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal menghapus dokumen keluarga.'];
        }

    } catch (PDOException $e) {
        $response = ['status' => 'error', 'message' => 'Kesalahan database: ' . $e->getMessage(), 'error' => $e->getMessage()];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode permintaan tidak valid.'];
}

echo json_encode($response);
?>
