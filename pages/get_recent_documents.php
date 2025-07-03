<?php
/**
 * Get Recent Documents from All Categories
 * File: pages/get_recent_documents.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

try {
    // Get recent documents from all categories
    $recentDocuments = [];
    
    // 1. Get recent keluarga documents
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
        LIMIT 5
    ");
    $stmt->execute();
    $keluargaDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Get recent arsip vital documents
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
        LIMIT 5
    ");
    $stmt->execute();
    $arsipVitalDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Get recent arsip inactive documents
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
        LIMIT 5
    ");
    $stmt->execute();
    $arsipInactiveDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. Get recent agenda
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
        LIMIT 5
    ");
    $stmt->execute();
    $agendaDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Combine all documents
    $allDocuments = array_merge($keluargaDocs, $arsipVitalDocs, $arsipInactiveDocs, $agendaDocs);
    
    // Sort by created_date (most recent first)
    usort($allDocuments, function($a, $b) {
        return strtotime($b['created_date']) - strtotime($a['created_date']);
    });
    
    // Take only the most recent 10 documents
    $recentDocuments = array_slice($allDocuments, 0, 10);
    
    // Format the data for frontend
    foreach ($recentDocuments as &$doc) {
        $doc['formatted_date'] = date('d M Y', strtotime($doc['created_date']));
        $doc['file_url'] = null;
        
        // Set file URL based on category
        if ($doc['file_name']) {
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
    }
    
    $response = [
        'status' => 'success', 
        'data' => $recentDocuments,
        'total' => count($recentDocuments)
    ];

} catch (PDOException $e) {
    $response = [
        'status' => 'error', 
        'message' => 'Gagal mengambil data dokumen terbaru: ' . $e->getMessage(), 
        'error' => $e->getMessage()
    ];
}

echo json_encode($response);
?>