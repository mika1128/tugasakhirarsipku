<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->getConnection();
    
    $submission_type = $_POST['submission_type'] ?? '';
    $nama_pemohon = $_POST['nama_pemohon'] ?? '';
    $nik = $_POST['nik'] ?? '';
    $email = $_POST['email'] ?? '';
    $telepon = $_POST['telepon'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $jenis_berkas = $_POST['jenis_berkas'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    $file_dokumen = null;

    // Validate required fields
    if (empty($submission_type) || empty($nama_pemohon) || empty($nik) || empty($email) || empty($alamat) || empty($jenis_berkas) || empty($keterangan)) {
        $response = ['status' => 'error', 'message' => 'Semua field yang wajib diisi harus dilengkapi.'];
        echo json_encode($response);
        exit();
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = ['status' => 'error', 'message' => 'Format email tidak valid.'];
        echo json_encode($response);
        exit();
    }

    // Handle file upload
    if (isset($_FILES['file_dokumen']) && $_FILES['file_dokumen']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/public_submissions/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        $fileExtension = strtolower(pathinfo($_FILES['file_dokumen']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedTypes)) {
            $response = ['status' => 'error', 'message' => 'Format file tidak didukung. Gunakan: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG'];
            echo json_encode($response);
            exit();
        }

        $maxSize = 20 * 1024 * 1024; // 20MB
        if ($_FILES['file_dokumen']['size'] > $maxSize) {
            $response = ['status' => 'error', 'message' => 'Ukuran file terlalu besar. Maksimal 20MB.'];
            echo json_encode($response);
            exit();
        }

        $fileName = uniqid() . '_' . basename($_FILES['file_dokumen']['name']);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['file_dokumen']['tmp_name'], $targetFilePath)) {
            $file_dokumen = $fileName;
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal mengunggah file dokumen.'];
            echo json_encode($response);
            exit();
        }
    }

    try {
        $stmt = $conn->prepare("
            INSERT INTO berkas_submissions 
            (submission_type, nama_pemohon, nik, email, telepon, alamat, jenis_berkas, keterangan, file_dokumen, status) 
            VALUES (:submission_type, :nama_pemohon, :nik, :email, :telepon, :alamat, :jenis_berkas, :keterangan, :file_dokumen, :status)
        ");
        
        $status = 'pending';
        
        $stmt->bindParam(':submission_type', $submission_type);
        $stmt->bindParam(':nama_pemohon', $nama_pemohon);
        $stmt->bindParam(':nik', $nik);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telepon', $telepon);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':jenis_berkas', $jenis_berkas);
        $stmt->bindParam(':keterangan', $keterangan);
        $stmt->bindParam(':file_dokumen', $file_dokumen);
        $stmt->bindParam(':status', $status);

        if ($stmt->execute()) {
            $submission_id = $conn->lastInsertId();
            
            // Send notification message to admin
            $notification_message = "Pengajuan berkas baru:\n";
            $notification_message .= "Jenis: " . ucfirst($submission_type) . "\n";
            $notification_message .= "Nama: {$nama_pemohon}\n";
            $notification_message .= "Jenis Berkas: {$jenis_berkas}\n";
            $notification_message .= "Email: {$email}";
            
            $stmt_notif = $conn->prepare("
                INSERT INTO chat_messages (sender_type, sender_name, sender_email, message) 
                VALUES ('public', :sender_name, :sender_email, :message)
            ");
            $stmt_notif->bindParam(':sender_name', $nama_pemohon);
            $stmt_notif->bindParam(':sender_email', $email);
            $stmt_notif->bindParam(':message', $notification_message);
            $stmt_notif->execute();
            
            $response = [
                'status' => 'success', 
                'message' => 'Pengajuan berkas berhasil dikirim! Anda akan mendapat notifikasi melalui email dalam 1-3 hari kerja.',
                'submission_id' => $submission_id
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal menyimpan pengajuan berkas.'];
        }

    } catch (PDOException $e) {
        error_log("Error submitting berkas: " . $e->getMessage());
        $response = ['status' => 'error', 'message' => 'Kesalahan database: ' . $e->getMessage()];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode permintaan tidak valid.'];
}

echo json_encode($response);
?>