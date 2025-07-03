<?php
// pages/add_keluarga_dokumen.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireAdmin();

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_dokumen = $_POST['nama_dokumen'] ?? '';
    $deskripsi_dokumen = $_POST['deskripsi_dokumen'] ?? '';
    $tanggal_dibuat = $_POST['tanggal_dibuat'] ?? '';
    $status = $_POST['status'] ?? 'aktif';
    $dokumen_file = null;

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
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal mengunggah dokumen.'];
            echo json_encode($response);
            exit();
        }
    }

    try {
        $stmt = $conn->prepare("INSERT INTO keluarga_dokumen (nama_dokumen, dokumen, deskripsi_dokumen, tanggal_dibuat, status) VALUES (:nama_dokumen, :dokumen, :deskripsi_dokumen, :tanggal_dibuat, :status)");
        $stmt->bindParam(':nama_dokumen', $nama_dokumen);
        $stmt->bindParam(':dokumen', $dokumen_file);
        $stmt->bindParam(':deskripsi_dokumen', $deskripsi_dokumen);
        $stmt->bindParam(':tanggal_dibuat', $tanggal_dibuat);
        $stmt->bindParam(':status', $status);

        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Dokumen keluarga berhasil ditambahkan.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal menambahkan dokumen keluarga.'];
        }

    } catch (PDOException $e) {
        $response = ['status' => 'error', 'message' => 'Kesalahan database: ' . $e->getMessage(), 'error' => $e->getMessage()];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode permintaan tidak valid.'];
}

echo json_encode($response);
?>