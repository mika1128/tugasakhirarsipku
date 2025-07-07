<?php
/**
 * Get Notifications with Enhanced Details
 * File: pages/get_notifications.php
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
    // Get current user ID
    $user_id = $_SESSION['user_id'];
    
    // Initialize notifications array
    $notifications = [];
    
    // 1. Check for overdue documents (Keluarga documents that are expired)
    try {
        $stmt = $conn->prepare("
            SELECT 
                id,
                nama_dokumen,
                tanggal_dibuat,
                status
            FROM keluarga_dokumen 
            WHERE status = 'kadaluarsa' OR 
                  (status = 'aktif' AND tanggal_dibuat < DATE_SUB(NOW(), INTERVAL 2 YEAR))
            ORDER BY tanggal_dibuat DESC
            LIMIT 5
        ");
        $stmt->execute();
        $expiredDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($expiredDocs as $doc) {
            $notifications[] = [
                'id' => 'keluarga_' . $doc['id'],
                'title' => 'Dokumen Keluarga Kadaluarsa',
                'message' => "Dokumen '{$doc['nama_dokumen']}' telah kadaluarsa atau perlu diperbaharui.",
                'details' => "Nama Admin: Administrator\nJenis Surat: Dokumen Keluarga\nKelompok Surat: Keluarga\nTanggal Dibuat: {$doc['tanggal_dibuat']}\nStatus: {$doc['status']}\nWaktu Notifikasi: " . date('Y-m-d H:i:s'),
                'priority' => 'high',
                'icon' => 'fas fa-exclamation-triangle',
                'created_at' => date('Y-m-d H:i:s'),
                'action_url' => '/ArsipKu/home/home.php#keluarga'
            ];
        }
    } catch (PDOException $e) {
        error_log("Error checking keluarga documents: " . $e->getMessage());
    }
    
    // 2. Check for old vital archives (older than 10 years)
    try {
        $currentYear = date('Y');
        $thresholdYear = $currentYear - 10;
        
        $stmt = $conn->prepare("
            SELECT 
                id,
                nomor_surat,
                berita_acara_surat,
                tahun_dibuat,
                status
            FROM arsip_vital 
            WHERE tahun_dibuat < :threshold_year AND status = 'aktif'
            ORDER BY tahun_dibuat ASC
            LIMIT 5
        ");
        $stmt->bindParam(':threshold_year', $thresholdYear, PDO::PARAM_INT);
        $stmt->execute();
        $oldVitalDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($oldVitalDocs as $doc) {
            $age = $currentYear - $doc['tahun_dibuat'];
            $notifications[] = [
                'id' => 'vital_' . $doc['id'],
                'title' => 'Arsip Vital Sudah Lama',
                'message' => "Surat No. {$doc['nomor_surat']} sudah berusia {$age} tahun dan mungkin perlu review.",
                'details' => "Nama Admin: Administrator\nJenis Surat: Arsip Vital\nKelompok Surat: Vital\nNomor Surat: {$doc['nomor_surat']}\nTahun Dibuat: {$doc['tahun_dibuat']}\nUsia: {$age} tahun\nStatus: {$doc['status']}\nWaktu Notifikasi: " . date('Y-m-d H:i:s'),
                'priority' => 'medium',
                'icon' => 'fas fa-clock',
                'created_at' => date('Y-m-d H:i:s'),
                'action_url' => '/ArsipKu/home/home.php#arsip-vital'
            ];
        }
    } catch (PDOException $e) {
        error_log("Error checking vital archives: " . $e->getMessage());
    }
    
    // 3. Check for overdue agenda
    try {
        $stmt = $conn->prepare("
            SELECT 
                a.id,
                a.title,
                a.start_date,
                a.end_date,
                a.status,
                a.priority,
                u.full_name as admin_name
            FROM agenda a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.end_date < NOW() AND a.status IN ('pending', 'in_progress')
            ORDER BY a.end_date DESC
            LIMIT 5
        ");
        $stmt->execute();
        $overdueAgenda = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($overdueAgenda as $agenda) {
            $daysPast = floor((time() - strtotime($agenda['end_date'])) / (60 * 60 * 24));
            $notifications[] = [
                'id' => 'agenda_' . $agenda['id'],
                'title' => 'Agenda Terlambat',
                'message' => "Agenda '{$agenda['title']}' sudah melewati batas waktu {$daysPast} hari yang lalu.",
                'details' => "Nama Admin: " . ($agenda['admin_name'] ?? 'Administrator') . "\nJenis: Agenda\nKelompok: Jadwal\nJudul: {$agenda['title']}\nTanggal Berakhir: {$agenda['end_date']}\nStatus: {$agenda['status']}\nPrioritas: {$agenda['priority']}\nTerlambat: {$daysPast} hari\nWaktu Notifikasi: " . date('Y-m-d H:i:s'),
                'priority' => $agenda['priority'] === 'high' ? 'high' : 'medium',
                'icon' => 'fas fa-calendar-times',
                'created_at' => date('Y-m-d H:i:s'),
                'action_url' => '/ArsipKu/home/home.php#agenda'
            ];
        }
    } catch (PDOException $e) {
        error_log("Error checking agenda: " . $e->getMessage());
    }
    
    // 4. Check for inactive archives that might need attention
    try {
        $stmt = $conn->prepare("
            SELECT 
                id,
                nomor_surat,
                berita_acara_surat,
                tahun_dibuat,
                status
            FROM arsip_inactive 
            WHERE status = 'rusak'
            ORDER BY tahun_dibuat DESC
            LIMIT 3
        ");
        $stmt->execute();
        $damagedDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($damagedDocs as $doc) {
            $notifications[] = [
                'id' => 'inactive_' . $doc['id'],
                'title' => 'Arsip Rusak Ditemukan',
                'message' => "Surat No. {$doc['nomor_surat']} dalam kondisi rusak dan perlu perhatian.",
                'details' => "Nama Admin: Administrator\nJenis Surat: Arsip Inactive\nKelompok Surat: Inactive\nNomor Surat: {$doc['nomor_surat']}\nTahun Dibuat: {$doc['tahun_dibuat']}\nStatus: {$doc['status']}\nKondisi: Rusak - Perlu Perbaikan\nWaktu Notifikasi: " . date('Y-m-d H:i:s'),
                'priority' => 'high',
                'icon' => 'fas fa-exclamation-circle',
                'created_at' => date('Y-m-d H:i:s'),
                'action_url' => '/ArsipKu/home/home.php#arsip-inactive'
            ];
        }
    } catch (PDOException $e) {
        error_log("Error checking inactive archives: " . $e->getMessage());
    }
    
    // 5. Check for documents that need renewal (created more than 1 year ago)
    try {
        $stmt = $conn->prepare("
            SELECT 
                id,
                nama_dokumen,
                tanggal_dibuat,
                status
            FROM keluarga_dokumen 
            WHERE status = 'aktif' AND tanggal_dibuat < DATE_SUB(NOW(), INTERVAL 1 YEAR)
            ORDER BY tanggal_dibuat ASC
            LIMIT 3
        ");
        $stmt->execute();
        $renewalDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($renewalDocs as $doc) {
            $daysSinceCreated = floor((time() - strtotime($doc['tanggal_dibuat'])) / (60 * 60 * 24));
            $notifications[] = [
                'id' => 'renewal_' . $doc['id'],
                'title' => 'Dokumen Perlu Diperbaharui',
                'message' => "Dokumen '{$doc['nama_dokumen']}' sudah berusia {$daysSinceCreated} hari dan mungkin perlu diperbaharui.",
                'details' => "Nama Admin: Administrator\nJenis Surat: Dokumen Keluarga\nKelompok Surat: Keluarga\nNama Dokumen: {$doc['nama_dokumen']}\nTanggal Dibuat: {$doc['tanggal_dibuat']}\nUsia Dokumen: {$daysSinceCreated} hari\nStatus: {$doc['status']}\nRekomendasi: Perlu Review\nWaktu Notifikasi: " . date('Y-m-d H:i:s'),
                'priority' => 'medium',
                'icon' => 'fas fa-sync-alt',
                'created_at' => date('Y-m-d H:i:s'),
                'action_url' => '/ArsipKu/home/home.php#keluarga'
            ];
        }
    } catch (PDOException $e) {
        error_log("Error checking renewal documents: " . $e->getMessage());
    }
    
    // 6. Add system welcome notification
    $notifications[] = [
        'id' => 'system_welcome',
        'title' => 'Selamat Datang di ArsipKu',
        'message' => 'Sistem manajemen dokumen Anda siap digunakan. Pastikan untuk memeriksa dokumen secara berkala.',
        'details' => "Nama Admin: Administrator\nJenis: Sistem\nKelompok: Informasi\nPesan: Sistem berjalan normal\nFitur Tersedia: Upload, Download, Pencarian, Notifikasi\nWaktu Login: " . date('Y-m-d H:i:s'),
        'priority' => 'low',
        'icon' => 'fas fa-info-circle',
        'created_at' => date('Y-m-d H:i:s'),
        'action_url' => null
    ];
    
    // Sort notifications by priority and date
    usort($notifications, function($a, $b) {
        $priorityOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
        $aPriority = $priorityOrder[$a['priority']] ?? 1;
        $bPriority = $priorityOrder[$b['priority']] ?? 1;
        
        if ($aPriority === $bPriority) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        }
        
        return $bPriority - $aPriority;
    });
    
    // Limit to 15 most important notifications
    $notifications = array_slice($notifications, 0, 15);
    
    $response = [
        'status' => 'success',
        'data' => $notifications,
        'total' => count($notifications),
        'message' => count($notifications) > 0 ? 'Notifikasi berhasil dimuat' : 'Tidak ada notifikasi baru'
    ];

} catch (Exception $e) {
    error_log("General error in get_notifications.php: " . $e->getMessage());
    $response = [
        'status' => 'error',
        'message' => 'Gagal mengambil notifikasi',
        'error' => $e->getMessage()
    ];
}

echo json_encode($response);
?>