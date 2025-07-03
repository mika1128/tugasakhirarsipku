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
    'total_arsip_inactive' => 0
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

    // Placeholder for new stats - replace with actual table names if they exist
    // Asumsi tabel 'keluarga_dokumen', 'arsip_vital', 'arsip_inactive'
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM keluarga_dokumen"); 
    $stmt->execute();
    $stats['total_keluarga_dokumen'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM arsip_vital"); 
    $stmt->execute();
    $stats['total_arsip_vital'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM arsip_inactive"); 
    $stmt->execute();
    $stats['total_arsip_inactive'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];


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
                </div>
                <div class="card">
                    <h2><i class="fas fa-chart-line"></i> Ringkasan Sistem</h2>
                    <p>Selamat datang di Dashboard Administrator ArsipKu. Dari sini Anda dapat mengelola seluruh sistem, mulai dari pengguna, agenda, hingga pengaturan sistem.</p>

                    <div style="margin-top: 20px;">
                        <a href="#" class="btn btn-primary" onclick="showTab('kelola-agenda')">
                            <i class="fas fa-calendar-plus"></i> Kelola Agenda
                        </a>
                        <a href="#" class="btn btn-primary" onclick="showTab('keluarga')">
                            <i class="fas fa-users"></i> Kelola Keluarga
                        </a>
                        <a href="#" class="btn btn-primary" onclick="showTab('arsip-vital')">
                            <i class="fas fa-file-invoice"></i> Kelola Arsip Vital
                        </a>
                        <a href="#" class="btn btn-primary" onclick="showTab('arsip-inactive')">
                            <i class="fas fa-folder-minus"></i> Kelola Arsip Inactive
                        </a>
                    </div>
                </div>
            </section>

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
        </main>
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
                <input type="hidden" id="arsipType" name="type"> <div class="form-group">
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
</body>
</html>