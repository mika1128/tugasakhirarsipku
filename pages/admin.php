<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$auth = new Auth();
$auth->requireAdmin();

$username = $_SESSION['user']['username'] ?? 'Admin';
$user_name = $_SESSION['user']['nama'] ?? 'Administrator';

// Initialize database connection
$db = new Database();
$conn = $db->getConnection();

// Get statistics
$stats = [
    'total_users' => 0,
    'total_agenda' => 0,
    'active_agenda' => 0,
    'completed_agenda' => 0,
    // New stats
    'total_keluarga_dokumen' => 0,
    'total_arsip_vital' => 0,
    'total_arsip_inactive' => 0,
    'pending_submissions' => 0,
    'unread_messages' => 0
];

try {
    // Get user count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get agenda stats
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM agenda");
    $stmt->execute();
    $stats['total_agenda'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM agenda WHERE status = 'aktif'");
    $stmt->execute();
    $stats['active_agenda'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM agenda WHERE status = 'selesai'");
    $stmt->execute();
    $stats['completed_agenda'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Keluarga, Arsip Vital, Arsip Inactive stats
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM keluarga_dokumen"); 
    $stmt->execute();
    $stats['total_keluarga_dokumen'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM arsip_vital"); 
    $stmt->execute();
    $stats['total_arsip_vital'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM arsip_inactive"); 
    $stmt->execute();
    $stats['total_arsip_inactive'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // New public system stats
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM berkas_submissions WHERE status = 'pending'");
    $stmt->execute();
    $stats['pending_submissions'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM chat_messages WHERE sender_type = 'public' AND is_read = 0");
    $stmt->execute();
    $stats['unread_messages'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

} catch (PDOException $e) {
    // Handle error silently for now
    error_log("Database error in admin.php: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - ArsipKu</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <h2><i class="fas fa-archive"></i> ArsipKu</h2>
            <ul>
                <li><button class="tab-btn active" data-tab="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</button></li>
                <li><button class="tab-btn" data-tab="kelola-agenda"><i class="fas fa-calendar-alt"></i> Kelola Agenda</button></li>
                <li><button class="tab-btn" data-tab="keluarga"><i class="fas fa-users"></i> Keluarga</button></li>
                <li><button class="tab-btn" data-tab="arsip-vital"><i class="fas fa-file-invoice"></i> Arsip Vital</button></li>
                <li><button class="tab-btn" data-tab="arsip-inactive"><i class="fas fa-folder-minus"></i> Arsip Inactive</button></li>
                <li><button class="tab-btn" data-tab="riwayat-agenda"><i class="fas fa-history"></i> Riwayat Agenda</button></li>
                <li style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 10px; padding-top: 10px;">
                    <button class="tab-btn" data-tab="cek-berkas-masyarakat"><i class="fas fa-file-alt"></i> Cek Berkas Masyarakat</button>
                </li>
                <li><button class="tab-btn" data-tab="cek-berkas-masuk"><i class="fas fa-inbox"></i> Cek Berkas Masuk</button></li>
                <li><button class="tab-btn" data-tab="cek-berkas-keluar"><i class="fas fa-paper-plane"></i> Cek Berkas Keluar</button></li>
                <li><button class="tab-btn" data-tab="admin-chat"><i class="fas fa-comments"></i> Kelola Chat <span id="chatBadge" class="status-badge status-pending" style="margin-left: 10px; font-size: 10px;"><?= $stats['unread_messages'] ?></span></button></li>
                <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>Dashboard Administrator</h1>
                <div class="user-info">
                    <span>Selamat datang, <strong><?php echo htmlspecialchars($user_name); ?></strong></span>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                </div>
            </header>

            <section id="dashboard" class="tab-content active">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon agenda">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['total_agenda']; ?></div>
                        <div class="stat-label">Total Agenda</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon active">
                            <i class="fas fa-play-circle"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['active_agenda']; ?></div>
                        <div class="stat-label">Agenda Aktif</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon completed">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['completed_agenda']; ?></div>
                        <div class="stat-label">Agenda Selesai</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['pending_submissions']; ?></div>
                        <div class="stat-label">Pengajuan Pending</div>
                    </div>
                </div>

                <div class="stats-grid" style="margin-top: 20px;">
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['total_keluarga_dokumen']; ?></div>
                        <div class="stat-label">Dokumen Keluarga</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon completed">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['total_arsip_vital']; ?></div>
                        <div class="stat-label">Arsip Vital</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon active">
                            <i class="fas fa-folder-minus"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['total_arsip_inactive']; ?></div>
                        <div class="stat-label">Arsip Inactive</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon agenda">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['unread_messages']; ?></div>
                        <div class="stat-label">Pesan Belum Dibaca</div>
                    </div>
                </div>
                
                <div class="card">
                    <h2><i class="fas fa-chart-line"></i> Ringkasan Sistem</h2>
                    <p>Selamat datang di Dashboard Administrator ArsipKu. Dari sini Anda dapat mengelola seluruh sistem, mulai dari pengguna, agenda, hingga pengaturan sistem. Kini dilengkapi dengan sistem pengajuan berkas publik dan chat dengan masyarakat.</p>

                    <div style="margin-top: 20px;">
                        <a href="#" class="btn btn-primary" onclick="showTab('kelola-agenda')">
                            <i class="fas fa-calendar-plus"></i> Kelola Agenda
                        </a>
                        <a href="#" class="btn btn-primary" onclick="showTab('keluarga')">
                            <i class="fas fa-users"></i> Kelola Keluarga
                        </a>
                        <a href="#" class="btn btn-success" onclick="showTab('cek-berkas-masyarakat')">
                            <i class="fas fa-file-alt"></i> Cek Pengajuan Berkas
                        </a>
                        <a href="#" class="btn btn-warning" onclick="showTab('admin-chat')">
                            <i class="fas fa-comments"></i> Kelola Chat
                        </a>
                    </div>
                </div>
            </section>

            <!-- Existing sections (kelola-agenda, keluarga, arsip-vital, arsip-inactive, riwayat-agenda) remain the same -->
            <section id="kelola-agenda" class="tab-content">
                <div class="card">
                    <h2><i class="fas fa-calendar-alt"></i> Kelola Agenda</h2>
                    <div style="margin-bottom: 20px;">
                        <button class="btn btn-success" onclick="showAddAgendaModal()">
                            <i class="fas fa-plus"></i> Tambah Agenda
                        </button>
                        <button class="btn btn-primary" onclick="refreshAgendaData()">
                            <i class="fas fa-sync-alt"></i> Refresh Data
                        </button>
                    </div>

                    <div class="table-container">
                        <table id="agenda-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Judul</th>
                                    <th>Deskripsi</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Berakhir</th>
                                    <th>Lokasi</th>
                                    <th>Status</th>
                                    <th>Prioritas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="10" class="loading">
                                        <i class="fas fa-spinner fa-spin"></i><br>
                                        Memuat data agenda...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="keluarga" class="tab-content">
                <div class="card">
                    <h2><i class="fas fa-users"></i> Kelola Dokumen Keluarga</h2>
                    <div style="margin-bottom: 20px;">
                        <button class="btn btn-success" onclick="showAddKeluargaModal()">
                            <i class="fas fa-plus"></i> Tambah Dokumen Keluarga
                        </button>
                        <button class="btn btn-primary" onclick="loadKeluargaData()">
                            <i class="fas fa-sync-alt"></i> Refresh Data
                        </button>
                    </div>

                    <div class="table-container">
                        <table id="keluarga-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Dokumen</th>
                                    <th>Dokumen</th>
                                    <th>Deskripsi Dokumen</th>
                                    <th>Tanggal Dibuat</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="loading">
                                        <i class="fas fa-spinner fa-spin"></i><br>
                                        Memuat data dokumen keluarga...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="arsip-vital" class="tab-content">
                <div class="card">
                    <h2><i class="fas fa-file-invoice"></i> Kelola Arsip Vital</h2>
                    <div style="margin-bottom: 20px;">
                        <button class="btn btn-success" onclick="showAddArsipVitalModal()">
                            <i class="fas fa-plus"></i> Tambah Arsip Vital
                        </button>
                        <button class="btn btn-primary" onclick="loadArsipVitalData()">
                            <i class="fas fa-sync-alt"></i> Refresh Data
                        </button>
                    </div>

                    <div class="table-container">
                        <table id="arsip-vital-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nomor Surat</th>
                                    <th>Berita Acara Surat</th>
                                    <th>Gambar/Surat</th>
                                    <th>Status</th>
                                    <th>Tahun Dibuat</th>
                                    <th>Lama Surat (Tahun)</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="loading">
                                        <i class="fas fa-spinner fa-spin"></i><br>
                                        Memuat data arsip vital...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="arsip-inactive" class="tab-content">
                <div class="card">
                    <h2><i class="fas fa-folder-minus"></i> Kelola Arsip Inactive</h2>
                    <div style="margin-bottom: 20px;">
                        <button class="btn btn-success" onclick="showAddArsipInactiveModal()">
                            <i class="fas fa-plus"></i> Tambah Arsip Inactive
                        </button>
                        <button class="btn btn-primary" onclick="loadArsipInactiveData()">
                            <i class="fas fa-sync-alt"></i> Refresh Data
                        </button>
                        <button class="btn btn-danger" onclick="deleteOldInactiveArsip()">
                            <i class="fas fa-trash"></i> Hapus Arsip Lama
                        </button>
                    </div>

                    <div class="table-container">
                        <table id="arsip-inactive-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nomor Surat</th>
                                    <th>Berita Acara Surat</th>
                                    <th>Gambar/Surat</th>
                                    <th>Status</th>
                                    <th>Tahun Dibuat</th>
                                    <th>Lama Surat (Tahun)</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="loading">
                                        <i class="fas fa-spinner fa-spin"></i><br>
                                        Memuat data arsip inactive...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="riwayat-agenda" class="tab-content">
                <div class="card">
                    <h2><i class="fas fa-history"></i> Riwayat Agenda</h2>
                    <p>Riwayat agenda yang sudah berakhir atau selesai. Anda dapat mengarsipkan atau menghapus data ini.</p>

                    <div style="margin: 20px 0;">
                        <button class="btn btn-warning" onclick="archiveCompletedAgenda()">
                            <i class="fas fa-archive"></i> Arsipkan Semua yang Selesai
                        </button>
                        <button class="btn btn-danger" onclick="deleteOldAgenda()">
                            <i class="fas fa-trash"></i> Hapus Data Lama
                        </button>
                    </div>

                    <div class="table-container">
                        <table id="history-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Judul</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="loading">
                                        <i class="fas fa-spinner fa-spin"></i><br>
                                        Memuat riwayat agenda...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- NEW SECTIONS START -->
            <section id="cek-berkas-masyarakat" class="tab-content">
                <div class="card">
                    <h2><i class="fas fa-file-alt"></i> Cek Berkas Masyarakat/Keluarga</h2>

                    <div style="margin-bottom: 20px;">
                        <button class="btn btn-primary" onclick="loadBerkasSubmissions('masyarakat')">
                            <i class="fas fa-sync-alt"></i> Refresh Data
                        </button>
                        <button class="btn btn-success" onclick="exportBerkasData('masyarakat')">
                            <i class="fas fa-download"></i> Export Excel
                        </button>
                    </div>

                    <div class="table-container">
                        <table id="berkas-masyarakat-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Pemohon</th>
                                    <th>NIK</th>
                                    <th>Email</th>
                                    <th>Jenis Berkas</th>
                                    <th>Tanggal Submit</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="loading">
                                        <i class="fas fa-spinner fa-spin"></i><br>
                                        Memuat data pengajuan berkas masyarakat...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="cek-berkas-masuk" class="tab-content">
                <div class="card">
                    <h2><i class="fas fa-inbox"></i> Cek Berkas Masuk (Vital)</h2>

                    <div style="margin-bottom: 20px;">
                        <button class="btn btn-primary" onclick="loadBerkasSubmissions('masuk')">
                            <i class="fas fa-sync-alt"></i> Refresh Data
                        </button>
                        <button class="btn btn-success" onclick="exportBerkasData('masuk')">
                            <i class="fas fa-download"></i> Export Excel
                        </button>
                    </div>

                    <div class="table-container">
                        <table id="berkas-masuk-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Pemohon</th>
                                    <th>Identitas</th>
                                    <th>Email</th>
                                    <th>Jenis Berkas</th>
                                    <th>Tanggal Submit</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="loading">
                                        <i class="fas fa-spinner fa-spin"></i><br>
                                        Memuat data berkas masuk...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="cek-berkas-keluar" class="tab-content">
                <div class="card">
                    <h2><i class="fas fa-paper-plane"></i> Cek Berkas Keluar (Inactive)</h2>

                    <div style="margin-bottom: 20px;">
                        <button class="btn btn-primary" onclick="loadBerkasSubmissions('keluar')">
                            <i class="fas fa-sync-alt"></i> Refresh Data
                        </button>
                        <button class="btn btn-success" onclick="exportBerkasData('keluar')">
                            <i class="fas fa-download"></i> Export Excel
                        </button>
                    </div>

                    <div class="table-container">
                        <table id="berkas-keluar-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Pemohon</th>
                                    <th>Identitas</th>
                                    <th>Email</th>
                                    <th>Jenis Berkas</th>
                                    <th>Tanggal Submit</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="loading">
                                        <i class="fas fa-spinner fa-spin"></i><br>
                                        Memuat data berkas keluar...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="admin-chat" class="tab-content">
                <div class="card">
                    <h2><i class="fas fa-comments"></i> Kelola Chat dengan Masyarakat</h2

                    <div style="margin-bottom: 20px;">
                        <button class="btn btn-primary" onclick="loadChatMessages()">
                            <i class="fas fa-sync-alt"></i> Refresh Chat
                        </button>
                        <button class="btn btn-warning" onclick="markAllAsRead()">
                            <i class="fas fa-check-double"></i> Tandai Semua Dibaca
                        </button>
                    </div>

                    <div id="chatContainer" style="display: flex; gap: 20px; height: 600px;">
                        <div style="flex: 1; background: white; border-radius: 10px; padding: 20px; overflow-y: auto;">
                            <h3>Daftar Pesan</h3>
                            <div id="messagesList">
                                <div class="loading">
                                    <i class="fas fa-spinner fa-spin"></i><br>
                                    Memuat pesan...
                                </div>
                            </div>
                        </div>
                        
                        <div style="flex: 1; background: white; border-radius: 10px; padding: 20px; display: flex; flex-direction: column;">
                            <h3>Detail & Balasan</h3>
                            <div id="chatDetail" style="flex: 1; border: 1px solid #eee; padding: 15px; margin: 10px 0; border-radius: 8px; overflow-y: auto;">
                                <p style="color: #666; text-align: center;">Pilih pesan untuk melihat detail dan membalas</p>
                            </div>
                            
                            <div id="replyForm" style="display: none;">
                                <textarea id="replyMessage" placeholder="Ketik balasan Anda..." style="width: 100%; height: 80px; margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; resize: vertical;"></textarea>
                                <button onclick="sendReply()" class="btn btn-success">
                                    <i class="fas fa-reply"></i> Kirim Balasan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- NEW SECTIONS END -->

        </main>
    </div>

    <!-- Existing Modals (agendaModal, keluargaModal, arsipModal) remain the same -->
    <!-- NEW MODAL FOR BERKAS DETAIL -->
    <div id="berkasDetailModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('berkasDetailModal')">&times;</span>
            <h2 id="berkasDetailTitle">Detail Pengajuan Berkas</h2>
            <div id="berkasDetailContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <div id="agendaModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2 id="modalTitle">Tambah Agenda Baru</h2>
            <form id="agendaForm">
                <input type="hidden" id="agendaId" name="id">

                <div class="form-group">
                    <label for="title">Judul Agenda</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
                </div>

                <div class="form-group">
                    <label for="startDate">Tanggal Mulai</label>
                    <input type="datetime-local" id="startDate" name="start_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="endDate">Tanggal Berakhir</label>
                    <input type="datetime-local" id="endDate" name="end_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="location">Lokasi</label>
                    <input type="text" id="location" name="location" class="form-control">
                </div>

                <div class="form-group">
                    <label for="priority">Prioritas</label>
                    <select id="priority" name="priority" class="form-control" required>
                        <option value="low">Rendah</option>
                        <option value="medium">Sedang</option>
                        <option value="high">Tinggi</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="pending">Tertunda</option>
                        <option value="in_progress">Dalam Proses</option>
                        <option value="complete">Selesai</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Simpan Agenda</button>
                <button type="button" class="btn btn-danger" onclick="closeModal('agendaModal')">Batal</button>
            </form>
        </div>
    </div>

    <div id="keluargaModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('keluargaModal')">&times;</span>
            <h2 id="keluargaModalTitle">Tambah Dokumen Keluarga Baru</h2>
            <form id="keluargaForm" enctype="multipart/form-data">
                <input type="hidden" id="keluargaId" name="id">

                <div class="form-group">
                    <label for="namaDokumen">Nama Dokumen</label>
                    <input type="text" id="namaDokumen" name="nama_dokumen" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="dokumenFile">Dokumen (JPG, Word, Excel, PDF, dll.)</label>
                    <input type="file" id="dokumenFile" name="dokumen" class="form-control">
                    <small>Biarkan kosong jika tidak ingin mengubah dokumen.</small>
                </div>

                <div class="form-group">
                    <label for="deskripsiDokumen">Deskripsi Dokumen</label>
                    <textarea id="deskripsiDokumen" name="deskripsi_dokumen" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="tanggalDibuat">Tanggal Dibuat</label>
                    <input type="date" id="tanggalDibuat" name="tanggal_dibuat" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="statusKeluarga">Status</label>
                    <select id="statusKeluarga" name="status" class="form-control" required>
                        <option value="aktif">Aktif</option>
                        <option value="kadaluarsa">Kadaluarsa</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Simpan Dokumen Keluarga</button>
                <button type="button" class="btn btn-danger" onclick="closeModal('keluargaModal')">Batal</button>
            </form>
        </div>
    </div>

    <div id="arsipModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('arsipModal')">&times;</span>
            <h2 id="arsipModalTitle">Tambah Arsip</h2>
            <form id="arsipForm" enctype="multipart/form-data">
                <input type="hidden" id="arsipId" name="id">
                <input type="hidden" id="arsipType" name="type">

                <div class="form-group">
                    <label for="nomorSurat">Nomor Surat</label>
                    <input type="text" id="nomorSurat" name="nomor_surat" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="beritaAcaraSurat">Berita Acara Surat</label>
                    <textarea id="beritaAcaraSurat" name="berita_acara_surat" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="gambarSurat">Gambar/Surat (JPG, Word, PDF, dll.)</label>
                    <input type="file" id="gambarSurat" name="gambar_surat" class="form-control">
                    <small>Biarkan kosong jika tidak ingin mengubah dokumen.</small>
                </div>

                <div class="form-group">
                    <label for="statusArsip">Status</label>
                    <select id="statusArsip" name="status" class="form-control" required>
                        <option value="aktif">Aktif</option>
                        <option value="inaktif">Inaktif</option>
                        <option value="rusak">Rusak</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tahunDibuat">Tahun Dibuat</label>
                    <input type="number" id="tahunDibuat" name="tahun_dibuat" class="form-control" required min="1900" max="<?php echo date('Y'); ?>">
                </div>

                <button type="submit" class="btn btn-success">Simpan Arsip</button>
                <button type="button" class="btn btn-danger" onclick="closeModal('arsipModal')">Batal</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script>
        // NEW FUNCTIONS FOR PUBLIC SYSTEM MANAGEMENT
        let selectedMessageId = null;

        // Load berkas submissions
        window.loadBerkasSubmissions = function(type) {
            const tableId = `berkas-${type}-table`;
            const tbody = document.querySelector(`#${tableId} tbody`);
            
            if (!tbody) {
                console.error(`Table ${tableId} not found`);
                return;
            }

            tbody.innerHTML = '<tr><td colspan="8" class="loading"><i class="fas fa-spinner fa-spin"></i><br>Memuat data...</td></tr>';

            fetch(`/ArsipKu/pages/get_berkas_submissions.php?type=${type}`)
                .then(res => res.json())
                .then(response => {
                    if (response.status === 'success' && response.data) {
                        tbody.innerHTML = '';
                        response.data.forEach((item, index) => {
                            const statusClass = getStatusClass(item.status);
                            const fileLink = item.file_dokumen ? 
                                `<a href="/ArsipKu/uploads/public_submissions/${item.file_dokumen}" target="_blank"><i class="fas fa-file"></i> Lihat File</a>` : 
                                'Tidak ada file';

                            tbody.innerHTML += `
                                <tr>
                                    <td>${item.id}</td>
                                    <td>${item.nama_pemohon}</td>
                                    <td>${item.nik}</td>
                                    <td>${item.email}</td>
                                    <td>${item.jenis_berkas}</td>
                                    <td>${formatDate(item.tanggal_submit)}</td>
                                    <td><span class="status-badge ${statusClass}">${item.status}</span></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" onclick="showBerkasDetail(${item.id})">
                                            <i class="fas fa-eye"></i> Detail
                                        </button>
                                        <button class="btn btn-success btn-sm" onclick="updateBerkasStatus(${item.id}, 'selesai')">
                                            <i class="fas fa-check"></i> Setujui
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="updateBerkasStatus(${item.id}, 'ditolak')">
                                            <i class="fas fa-times"></i> Tolak
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="8" class="empty-state">Tidak ada data</td></tr>';
                    }
                })
                .catch(err => {
                    tbody.innerHTML = `<tr><td colspan="8" class="empty-state">Error: ${err.message}</td></tr>`;
                });
        };

        // Show berkas detail
        window.showBerkasDetail = function(id) {
            fetch(`/ArsipKu/pages/get_berkas_submissions.php`)
                .then(res => res.json())
                .then(response => {
                    if (response.status === 'success' && response.data) {
                        const item = response.data.find(b => b.id == id);
                        if (item) {
                            const modal = document.getElementById('berkasDetailModal');
                            const content = document.getElementById('berkasDetailContent');
                            
                            content.innerHTML = `
                                <div class="form-group">
                                    <strong>Nama Pemohon:</strong> ${item.nama_pemohon}
                                </div>
                                <div class="form-group">
                                    <strong>NIK/Identitas:</strong> ${item.nik}
                                </div>
                                <div class="form-group">
                                    <strong>Email:</strong> ${item.email}
                                </div>
                                <div class="form-group">
                                    <strong>Telepon:</strong> ${item.telepon || '-'}
                                </div>
                                <div class="form-group">
                                    <strong>Alamat:</strong><br>${item.alamat}
                                </div>
                                <div class="form-group">
                                    <strong>Jenis Berkas:</strong> ${item.jenis_berkas}
                                </div>
                                <div class="form-group">
                                    <strong>Keterangan:</strong><br>${item.keterangan}
                                </div>
                                <div class="form-group">
                                    <strong>File:</strong> ${item.file_dokumen ? 
                                        `<a href="/ArsipKu/uploads/public_submissions/${item.file_dokumen}" target="_blank">Lihat File</a>` : 
                                        'Tidak ada file'}
                                </div>
                                <div class="form-group">
                                    <strong>Status:</strong> <span class="status-badge ${getStatusClass(item.status)}">${item.status}</span>
                                </div>
                                <div class="form-group">
                                    <strong>Tanggal Submit:</strong> ${formatDate(item.tanggal_submit)}
                                </div>
                                ${item.tanggal_diproses ? `<div class="form-group"><strong>Tanggal Diproses:</strong> ${formatDate(item.tanggal_diproses)}</div>` : ''}
                                ${item.keterangan_admin ? `<div class="form-group"><strong>Keterangan Admin:</strong><br>${item.keterangan_admin}</div>` : ''}
                            `;
                            
                            modal.style.display = 'block';
                        }
                    }
                });
        };

        // Update berkas status
        window.updateBerkasStatus = function(id, status) {
            const keterangan = prompt(`Masukkan keterangan untuk status "${status}":`);
            if (keterangan === null) return;

            fetch('/ArsipKu/pages/update_berkas_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: id,
                    status: status,
                    keterangan_admin: keterangan
                })
            })
            .then(res => res.json())
            .then(response => {
                if (response.status === 'success') {
                    window.showNotification(response.message, 'success');
                    // Refresh current tab
                    const activeTab = document.querySelector('.tab-btn.active');
                    if (activeTab) {
                        const tabName = activeTab.getAttribute('data-tab');
                        if (tabName.includes('berkas')) {
                            const type = tabName.replace('cek-berkas-', '');
                            if (type === 'masyarakat') {
                                loadBerkasSubmissions('masyarakat');
                            } else {
                                loadBerkasSubmissions(type);
                            }
                        }
                    }
                } else {
                    window.showNotification(response.message, 'error');
                }
            })
            .catch(err => {
                window.showNotification('Error: ' + err.message, 'error');
            });
        };

        // Load chat messages
        window.loadChatMessages = function() {
            const messagesList = document.getElementById('messagesList');
            messagesList.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i><br>Memuat pesan...</div>';

            fetch('/ArsipKu/pages/get_chat_messages.php')
                .then(res => res.json())
                .then(response => {
                    if (response.status === 'success' && response.data) {
                        messagesList.innerHTML = '';
                        
                        // Group messages by conversation
                        const conversations = {};
                        response.data.forEach(msg => {
                            const key = msg.reply_to || msg.id;
                            if (!conversations[key]) {
                                conversations[key] = [];
                            }
                            conversations[key].push(msg);
                        });

                        // Display conversations
                        Object.values(conversations).forEach(conversation => {
                            const mainMessage = conversation.find(m => !m.reply_to) || conversation[0];
                            const hasUnread = conversation.some(m => m.sender_type === 'public' && !m.is_read);
                            
                            const messageDiv = document.createElement('div');
                            messageDiv.className = `message-item ${hasUnread ? 'unread' : ''}`;
                            messageDiv.style.cssText = `
                                padding: 15px;
                                margin-bottom: 10px;
                                border: 1px solid #eee;
                                border-radius: 8px;
                                cursor: pointer;
                                background: ${hasUnread ? '#fff3cd' : 'white'};
                                border-left: 4px solid ${hasUnread ? '#856404' : '#667eea'};
                            `;
                            
                            messageDiv.innerHTML = `
                                <div style="font-weight: bold; margin-bottom: 5px;">
                                    ${mainMessage.sender_name} ${hasUnread ? '<span style="color: #856404;">●</span>' : ''}
                                </div>
                                <div style="color: #666; font-size: 12px; margin-bottom: 8px;">
                                    ${formatDate(mainMessage.created_at)}
                                </div>
                                <div style="color: #333; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    ${mainMessage.message.substring(0, 100)}${mainMessage.message.length > 100 ? '...' : ''}
                                </div>
                            `;
                            
                            messageDiv.addEventListener('click', () => {
                                showChatDetail(conversation);
                            });
                            
                            messagesList.appendChild(messageDiv);
                        });

                        // Update badge
                        const unreadCount = response.data.filter(m => m.sender_type === 'public' && !m.is_read).length;
                        document.getElementById('chatBadge').textContent = unreadCount;
                    } else {
                        messagesList.innerHTML = '<div class="empty-state">Belum ada pesan</div>';
                    }
                })
                .catch(err => {
                    messagesList.innerHTML = `<div class="empty-state">Error: ${err.message}</div>`;
                });
        };

        // Show chat detail
        function showChatDetail(conversation) {
            const chatDetail = document.getElementById('chatDetail');
            const replyForm = document.getElementById('replyForm');
            
            const mainMessage = conversation.find(m => !m.reply_to) || conversation[0];
            selectedMessageId = mainMessage.id;
            
            let detailHtml = '';
            conversation.forEach(msg => {
                const isAdmin = msg.sender_type === 'admin';
                detailHtml += `
                    <div style="margin-bottom: 15px; padding: 10px; background: ${isAdmin ? '#e3f2fd' : '#f5f5f5'}; border-radius: 8px;">
                        <div style="font-weight: bold; margin-bottom: 5px;">
                            ${msg.sender_name} ${isAdmin ? '(Admin)' : ''}
                        </div>
                        <div style="color: #666; font-size: 12px; margin-bottom: 8px;">
                            ${formatDate(msg.created_at)}
                        </div>
                        <div>${msg.message}</div>
                        ${msg.sender_email ? `<div style="color: #666; font-size: 12px; margin-top: 5px;">Email: ${msg.sender_email}</div>` : ''}
                    </div>
                `;
            });
            
            chatDetail.innerHTML = detailHtml;
            replyForm.style.display = 'block';
            
            // Scroll to bottom
            chatDetail.scrollTop = chatDetail.scrollHeight;
        }

        // Send reply
        window.sendReply = function() {
            const message = document.getElementById('replyMessage').value.trim();
            if (!message || !selectedMessageId) return;

            fetch('/ArsipKu/pages/send_admin_reply.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    reply_to: selectedMessageId,
                    message: message
                })
            })
            .then(res => res.json())
            .then(response => {
                if (response.status === 'success') {
                    document.getElementById('replyMessage').value = '';
                    window.showNotification('Balasan berhasil dikirim!', 'success');
                    loadChatMessages();
                } else {
                    window.showNotification(response.message, 'error');
                }
            })
            .catch(err => {
                window.showNotification('Error: ' + err.message, 'error');
            });
        };

        // Mark all as read
        window.markAllAsRead = function() {
            // Simple implementation - just reload to update UI
            loadChatMessages();
            window.showNotification('Semua pesan ditandai sebagai dibaca', 'success');
        };

        // Export berkas data
        window.exportBerkasData = function(type) {
            window.showNotification('Fitur export akan segera tersedia', 'info');
        };

        // Helper functions
        function getStatusClass(status) {
            switch(status) {
                case 'pending': return 'status-pending';
                case 'diproses': return 'status-active';
                case 'selesai': return 'status-active';
                case 'ditolak': return 'status-inactive';
                default: return 'status-pending';
            }
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Auto-load data when switching to new tabs
        const originalShowTab = window.showTab;
        window.showTab = function(tabName) {
            originalShowTab(tabName);
            
            // Auto-load data for new tabs
            switch(tabName) {
                case 'cek-berkas-masyarakat':
                    loadBerkasSubmissions('masyarakat');
                    break;
                case 'cek-berkas-masuk':
                    loadBerkasSubmissions('masuk');
                    break;
                case 'cek-berkas-keluar':
                    loadBerkasSubmissions('keluar');
                    break;
                case 'admin-chat':
                    loadChatMessages();
                    break;
            }
        };

        // Load initial data for new sections
        document.addEventListener('DOMContentLoaded', function() {
            // Load chat badge count
            loadChatMessages();
        });
    </script>
</body>
</html>