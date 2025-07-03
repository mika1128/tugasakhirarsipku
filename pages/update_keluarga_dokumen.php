<?php
// pages/update_keluarga_dokumen.php
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
    $nama_dokumen = $_POST['nama_dokumen'] ?? '';
    $deskripsi_dokumen = $_POST['deskripsi_dokumen'] ?? '';
    $tanggal_dibuat = $_POST['tanggal_dibuat'] ?? '';
    $status = $_POST['status'] ?? 'aktif';
    $dokumen_file = null;

    if (!$id) {
        $response = ['status' => 'error', 'message' => 'ID dokumen keluarga tidak disediakan.'];
        echo json_encode($response);
        exit();
    }

    // Get existing document file name
    $stmt = $conn->prepare("SELECT dokumen FROM keluarga_dokumen WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $existing_dokumen = $stmt->fetchColumn();
    $dokumen_file = $existing_dokumen; // Keep existing if no new file uploaded

    // Handle file upload
    if (isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/keluarga/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = uniqid() . '_' . basename($_FILES['dokumen']['name']);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['dokumen']['tmp_name'], $targetFilePath)) {
            $dokumen_file = $fileName;
            // Optionally, delete old file if it exists
            if ($existing_dokumen && file_exists($uploadDir . $existing_dokumen)) {
                unlink($uploadDir . $existing_dokumen);
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal mengunggah dokumen baru.'];
            echo json_encode($response);
            exit();
        }
    }

    try {
        $stmt = $conn->prepare("UPDATE keluarga_dokumen SET nama_dokumen = :nama_dokumen, dokumen = :dokumen, deskripsi_dokumen = :deskripsi_dokumen, tanggal_dibuat = :tanggal_dibuat, status = :status WHERE id = :id");
        $stmt->bindParam(':nama_dokumen', $nama_dokumen);
        $stmt->bindParam(':dokumen', $dokumen_file);
        $stmt->bindParam(':deskripsi_dokumen', $deskripsi_dokumen);
        $stmt->bindParam(':tanggal_dibuat', $tanggal_dibuat);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Dokumen keluarga berhasil diperbarui.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal memperbarui dokumen keluarga.'];
        }

    } catch (PDOException $e) {
        $response = ['status' => 'error', 'message' => 'Kesalahan database: ' . $e->getMessage(), 'error' => $e->getMessage()];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode permintaan tidak valid.'];
}

echo json_encode($response);
?>