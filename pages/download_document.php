<?php
/**
 * Download Document Handler
 * File: pages/download_document.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? null;
    $category = $_GET['category'] ?? null;

    if (!$id || !$category) {
        $response = ['status' => 'error', 'message' => 'Parameter tidak lengkap.'];
        echo json_encode($response);
        exit();
    }

    try {
        $fileName = null;
        $filePath = null;
        $originalName = null;
        $mimeType = null;

        // Get file information based on category
        switch ($category) {
            case 'keluarga':
                $stmt = $conn->prepare("SELECT nama_dokumen, dokumen FROM keluarga_dokumen WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($data && $data['dokumen']) {
                    $fileName = $data['dokumen'];
                    $originalName = $data['nama_dokumen'];
                    $filePath = __DIR__ . '/../uploads/keluarga/' . $fileName;
                }
                break;

            case 'arsip_vital':
                $stmt = $conn->prepare("SELECT nomor_surat, gambar_surat FROM arsip_vital WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($data && $data['gambar_surat']) {
                    $fileName = $data['gambar_surat'];
                    $originalName = 'Surat_' . $data['nomor_surat'];
                    $filePath = __DIR__ . '/../uploads/arsip_vital/' . $fileName;
                }
                break;

            case 'arsip_inactive':
                $stmt = $conn->prepare("SELECT nomor_surat, gambar_surat FROM arsip_inactive WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($data && $data['gambar_surat']) {
                    $fileName = $data['gambar_surat'];
                    $originalName = 'Surat_' . $data['nomor_surat'];
                    $filePath = __DIR__ . '/../uploads/arsip_inactive/' . $fileName;
                }
                break;

            default:
                $response = ['status' => 'error', 'message' => 'Kategori tidak valid.'];
                echo json_encode($response);
                exit();
        }

        // Check if file exists
        if (!$fileName || !file_exists($filePath)) {
            $response = ['status' => 'error', 'message' => 'File tidak ditemukan.'];
            echo json_encode($response);
            exit();
        }

        // Get file extension and determine MIME type
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $downloadName = $originalName;

        // Add appropriate extension if not present
        if (!pathinfo($originalName, PATHINFO_EXTENSION)) {
            $downloadName .= '.' . $fileExtension;
        }

        // Determine MIME type based on file extension
        $mimeTypes = [
            // Microsoft Office
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            
            // PDF
            'pdf' => 'application/pdf',
            
            // Images
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'webp' => 'image/webp',
            
            // Text
            'txt' => 'text/plain',
            'rtf' => 'application/rtf',
            'csv' => 'text/csv',
            
            // Archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            '7z' => 'application/x-7z-compressed'
        ];

        $mimeType = $mimeTypes[$fileExtension] ?? 'application/octet-stream';

        // Log download activity
        $auth->logActivity($_SESSION['user_id'], 'download', $category, $id, 'Download file: ' . $downloadName);

        // Set headers for file download
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Read and output file
        readfile($filePath);
        exit();

    } catch (PDOException $e) {
        $response = ['status' => 'error', 'message' => 'Kesalahan database: ' . $e->getMessage()];
        echo json_encode($response);
        exit();
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => 'Kesalahan sistem: ' . $e->getMessage()];
        echo json_encode($response);
        exit();
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode permintaan tidak valid.'];
    echo json_encode($response);
    exit();
}
?>