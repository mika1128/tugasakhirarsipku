<?php
// pages/add_arsip_vital.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireAdmin();

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_surat = $_POST['nomor_surat'] ?? '';
    $berita_acara_surat = $_POST['berita_acara_surat'] ?? '';
    $status = $_POST['status'] ?? 'aktif';
    $tahun_dibuat = $_POST['tahun_dibuat'] ?? null;
    $gambar_surat_file = null;

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
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal mengunggah gambar/surat.'];
            echo json_encode($response);
            exit();
        }
    }

    try {
        $stmt = $conn->prepare("INSERT INTO arsip_vital (nomor_surat, berita_acara_surat, gambar_surat, status, tahun_dibuat) VALUES (:nomor_surat, :berita_acara_surat, :gambar_surat, :status, :tahun_dibuat)");
        $stmt->bindParam(':nomor_surat', $nomor_surat);
        $stmt->bindParam(':berita_acara_surat', $berita_acara_surat);
        $stmt->bindParam(':gambar_surat', $gambar_surat_file);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':tahun_dibuat', $tahun_dibuat, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Arsip vital berhasil ditambahkan.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal menambahkan arsip vital.'];
        }

    } catch (PDOException $e) {
        $response = ['status' => 'error', 'message' => 'Kesalahan database: ' . $e->getMessage(), 'error' => $e->getMessage()];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode permintaan tidak valid.'];
}

echo json_encode($response);
?>