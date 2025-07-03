<?php
// pages/delete_arsip_vital.php
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
        $response = ['status' => 'error', 'message' => 'ID arsip vital tidak disediakan.'];
        echo json_encode($response);
        exit();
    }

    try {
        // Get file name to delete from server
        $stmt = $conn->prepare("SELECT gambar_surat FROM arsip_vital WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $gambar_surat_to_delete = $stmt->fetchColumn();

        $stmt = $conn->prepare("DELETE FROM arsip_vital WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Delete the actual file from the server
            if ($gambar_surat_to_delete && file_exists(__DIR__ . '/../uploads/arsip_vital/' . $gambar_surat_to_delete)) {
                unlink(__DIR__ . '/../uploads/arsip_vital/' . $gambar_surat_to_delete);
            }
            $response = ['status' => 'success', 'message' => 'Arsip vital berhasil dihapus.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal menghapus arsip vital.'];
        }

    } catch (PDOException $e) {
        $response = ['status' => 'error', 'message' => 'Kesalahan database: ' . $e->getMessage(), 'error' => $e->getMessage()];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode permintaan tidak valid.'];
}

echo json_encode($response);
?>
