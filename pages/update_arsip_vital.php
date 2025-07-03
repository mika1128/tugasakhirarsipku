<?php
// pages/update_arsip_vital.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireAdmin();

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nomor_surat = $_POST['nomor_surat'] ?? '';
    $berita_acara_surat = $_POST['berita_acara_surat'] ?? '';
    $status = $_POST['status'] ?? 'aktif';
    $tahun_dibuat = $_POST['tahun_dibuat'] ?? null;
    $gambar_surat_file = null;

    if (!$id) {
        $response = ['status' => 'error', 'message' => 'ID arsip vital tidak disediakan.'];
        echo json_encode($response);
        exit();
    }

    // Get existing file name
    $stmt = $conn->prepare("SELECT gambar_surat FROM arsip_vital WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $existing_gambar_surat = $stmt->fetchColumn();
    $gambar_surat_file = $existing_gambar_surat; // Keep existing if no new file uploaded

    // Handle file upload
    if (isset($_FILES['gambar_surat']) && $_FILES['gambar_surat']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/arsip_vital/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = uniqid() . '_' . basename($_FILES['gambar_surat']['name']);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['gambar_surat']['tmp_name'], $targetFilePath)) {
            $gambar_surat_file = $fileName;
            // Optionally, delete old file if it exists
            if ($existing_gambar_surat && file_exists($uploadDir . $existing_gambar_surat)) {
                unlink($uploadDir . $existing_gambar_surat);
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal mengunggah gambar/surat baru.'];
            echo json_encode($response);
            exit();
        }
    }

    try {
        $stmt = $conn->prepare("UPDATE arsip_vital SET nomor_surat = :nomor_surat, berita_acara_surat = :berita_acara_surat, gambar_surat = :gambar_surat, status = :status, tahun_dibuat = :tahun_dibuat WHERE id = :id");
        $stmt->bindParam(':nomor_surat', $nomor_surat);
        $stmt->bindParam(':berita_acara_surat', $berita_acara_surat);
        $stmt->bindParam(':gambar_surat', $gambar_surat_file);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':tahun_dibuat', $tahun_dibuat, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Arsip vital berhasil diperbarui.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal memperbarui arsip vital.'];
        }

    } catch (PDOException $e) {
        $response = ['status' => 'error', 'message' => 'Kesalahan database: ' . $e->getMessage(), 'error' => $e->getMessage()];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode permintaan tidak valid.'];
}

echo json_encode($response);
?>