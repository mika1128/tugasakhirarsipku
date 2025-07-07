<?php
/**
 * Get All Documents from All Categories
 * File: pages/get_all_documents.php
 */

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

try {
    // Initialize array for all documents
    $allDocuments = [];
    
    // 1. Get all keluarga documents (with error handling)
    try {
        $stmt = $conn->prepare("
            SELECT 
                id,
                nama_dokumen as title,
                dokumen as file_name,
                deskripsi_dokumen as description,
                tanggal_dibuat as created_date,
                status,
                'keluarga' as category,
                'fas fa-users' as icon,
                '#4CAF50' as color
            FROM keluarga_dokumen 
            ORDER BY id DESC
        ");
        $stmt->execute();
        $keluargaDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $allDocuments = array_merge($allDocuments, $keluargaDocs);
    } catch (PDOException $e) {
        error_log("Keluarga table error: " . $e->getMessage());
    }
    
    // 2. Get all arsip vital documents (with error handling)
    try {
        $stmt = $conn->prepare("
            SELECT 
                id,
                CONCAT('Surat No. ', nomor_surat) as title,
                gambar_surat as file_name,
                berita_acara_surat as description,
                CONCAT(tahun_dibuat, '-01-01') as created_date,
                status,
                'arsip_vital' as category,
                'fas fa-file-invoice' as icon,
                '#FF9800' as color
            FROM arsip_vital 
            ORDER BY id DESC
        ");
        $stmt->execute();
        $arsipVitalDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $allDocuments = array_merge($allDocuments, $arsipVitalDocs);
    } catch (PDOException $e) {
        error_log("Arsip vital table error: " . $e->getMessage());
    }
    
    // 3. Get all arsip inactive documents (with error handling)
    try {
        $stmt = $conn->prepare("
            SELECT 
                id,
                CONCAT('Surat No. ', nomor_surat) as title,
                gambar_surat as file_name,
                berita_acara_surat as description,
                CONCAT(tahun_dibuat, '-01-01') as created_date,
                status,
                'arsip_inactive' as category,
                'fas fa-folder-minus' as icon,
                '#9E9E9E' as color
            FROM arsip_inactive 
            ORDER BY id DESC
        ");
        $stmt->execute();
        $arsipInactiveDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $allDocuments = array_merge($allDocuments, $arsipInactiveDocs);
    } catch (PDOException $e) {
        error_log("Arsip inactive table error: " . $e->getMessage());
    }
    
    // 4. Get all agenda (with error handling)
    try {
        $stmt = $conn->prepare("
            SELECT 
                a.id,
                a.title,
                NULL as file_name,
                a.description,
                a.start_date as created_date,
                a.status,
                'agenda' as category,
                'fas fa-calendar-alt' as icon,
                '#2196F3' as color
            FROM agenda a
            ORDER BY a.id DESC
        ");
        $stmt->execute();
        $agendaDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $allDocuments = array_merge($allDocuments, $agendaDocs);
    } catch (PDOException $e) {
        error_log("Agenda table error: " . $e->getMessage());
    }
    
    // Sort by created_date (most recent first) if we have documents
    if (!empty($allDocuments)) {
        usort($allDocuments, function($a, $b) {
            $dateA = strtotime($a['created_date'] ?? '1970-01-01');
            $dateB = strtotime($b['created_date'] ?? '1970-01-01');
            return $dateB - $dateA;
        });
        
        // Format the data for frontend
        foreach ($allDocuments as &$doc) {
            // Ensure all required fields exist
            $doc['id'] = $doc['id'] ?? 0;
            $doc['title'] = $doc['title'] ?? 'Untitled';
            $doc['description'] = $doc['description'] ?? '';
            $doc['status'] = $doc['status'] ?? 'unknown';
            $doc['category'] = $doc['category'] ?? 'unknown';
            $doc['icon'] = $doc['icon'] ?? 'fas fa-file';
            $doc['color'] = $doc['color'] ?? '#666666';
            
            // Format date safely
            $doc['formatted_date'] = 'Unknown';
            if (!empty($doc['created_date'])) {
                $timestamp = strtotime($doc['created_date']);
                if ($timestamp !== false) {
                    $doc['formatted_date'] = date('d M Y', $timestamp);
                }
            }
            
            // Set file URL based on category
            $doc['file_url'] = null;
            if (!empty($doc['file_name'])) {
                switch ($doc['category']) {
                    case 'keluarga':
                        $doc['file_url'] = '/ArsipKu/uploads/keluarga/' . $doc['file_name'];
                        break;
                    case 'arsip_vital':
                        $doc['file_url'] = '/ArsipKu/uploads/arsip_vital/' . $doc['file_name'];
                        break;
                    case 'arsip_inactive':
                        $doc['file_url'] = '/ArsipKu/uploads/arsip_inactive/' . $doc['file_name'];
                        break;
                }
            }
            
            // Set status badge class
            switch ($doc['status']) {
                case 'aktif':
                case 'in_progress':
                    $doc['status_class'] = 'status-active';
                    break;
                case 'complete':
                case 'inaktif':
                    $doc['status_class'] = 'status-inactive';
                    break;
                case 'pending':
                    $doc['status_class'] = 'status-pending';
                    break;
                default:
                    $doc['status_class'] = 'status-info';
            }
            
            // Set thumbnail URL
            $doc['thumbnail_url'] = 'https://via.placeholder.com/200x120/3c4043/9aa0a6?text=' . strtoupper($doc['category']);
            if (!empty($doc['file_url']) && !empty($doc['file_name'])) {
                $extension = strtolower(pathinfo($doc['file_name'], PATHINFO_EXTENSION));
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $doc['thumbnail_url'] = $doc['file_url'];
                }
            }
        }
    } else {
        $allDocuments = [];
    }
    
    $response = [
        'status' => 'success', 
        'data' => $allDocuments,
        'total' => count($allDocuments),
        'message' => count($allDocuments) > 0 ? 'Data berhasil dimuat' : 'Belum ada dokumen'
    ];

} catch (Exception $e) {
    error_log("General error in get_all_documents.php: " . $e->getMessage());
    $response = [
        'status' => 'error', 
        'message' => 'Gagal mengambil data dokumen', 
        'error' => $e->getMessage()
    ];
}

echo json_encode($response);
?>