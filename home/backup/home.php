<?php

require_once __DIR__ . '/../includes/auth.php'; // Pastikan path benar
$auth = new Auth();
$auth->requireLogin(); // Redirect ke login jika belum login

// Pastikan SESSION user data ada sebelum digunakan
$userIdSesi = $_SESSION['user_id'] ?? null;
$namaLengkap = $_SESSION['full_name'] ?? 'User';
$usernameSesi = $_SESSION['username'] ?? 'user';
$emailSesi = $_SESSION['email'] ?? 'user@example.com';

// Path placeholder avatar berdasarkan username
$userAvatarPath = 'https://via.placeholder.com/36/4285F4/FFFFFF?text=' . strtoupper(substr($usernameSesi, 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Sistem Manajemen Dokumen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="../assets/css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app-container">
        <main class="main-content full-width">
            <header class="main-header">
                <div class="header-left">
                    <span class="app-title">ArsipKu</span>
                </div>

                <nav class="main-navbar">
                    <a href="#" class="navbar-item active" data-target-section="disarankan-content" onclick="showSection(event, 'disarankan-content')">
                        Terbaru
                    </a>
                    <a href="#" class="navbar-item" data-target-section="dokumen-main" onclick="showSection(event, 'dokumen-main')">
                        Dokumen
                    </a>
                    <a href="#" class="navbar-item" data-target-section="agenda" onclick="showSection(event, 'agenda')">
                        Agenda
                    </a>
                    <a href="#" class="navbar-item" data-target-section="riwayat" onclick="showSection(event, 'riwayat')">
                        Riwayat
                    </a>
                </nav>

                <div class="search-bar-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" placeholder="Search" class="search-input">
                </div>
            </header>

            <div class="greeting-text-area" style="padding: 20px 30px 0; color: #e8eaed; font-size: 24px; font-weight: 500;"
                 data-full-name="<?= htmlspecialchars($namaLengkap) ?>" id="greetingText">
            </div>

            <div class="content-tabs">
                <button class="tab-button active" data-target-section="disarankan-content">Terbaru</button>
                <button class="tab-button" data-target-section="notifikasi-content">Notifikasi</button>
            </div>

            <section class="content-section active" id="disarankan-content">
                <h1 class="section-title">Terbaru</h1>
                <h3 class="section-subtitle">Yang baru-baru ini Anda akses</h3>

                <div class="document-grid" id="documentGrid">
                </div>
                </section>

            <section class="content-section" id="notifikasi-content">
                <h1 class="section-title">Notifikasi</h1>
                <h3 class="section-subtitle">Belum ada notifikasi baru.</h3>
            </section>

            <section class="content-section" id="dokumen-main">
                <h1 class="section-title">Dokumen</h1>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number" id="totalDocs">0</div>
                        <div class="stat-label">Total Dokumen</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="todayUploads">0</div>
                        <div class="stat-label">Upload Hari Ini</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="totalSize">0 MB</div> <div class="stat-label">Total Ukuran</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="activeUsers">0</div>
                        <div class="stat-label">Pengguna Aktif</div>
                    </div>
                </div>
                <p>Daftar dokumen lengkap akan ditampilkan di sini setelah Anda mengunggah.</p>
                 <div class="document-grid" id="documentGridDokumenSaya">
                </div>
            </section>

            <section class="content-section" id="agenda">
                <div class="card">
                    <h2 class="section-title"><i class="fas fa-calendar-alt"></i> Agenda & Jadwal Anda</h2>
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
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="9" class="loading"> <i class="fas fa-spinner fa-spin"></i><br>
                                        Memuat data agenda...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="content-section" id="riwayat">
                <div class="card">
                    <h2 class="section-title"><i class="fas fa-history"></i> Riwayat Agenda & Aktivitas</h2>

                    <div class="table-container">
                        <table id="history-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Judul</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="loading"> <i class="fas fa-spinner fa-spin"></i><br>
                                        Memuat riwayat agenda...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="content-section" id="pengguna">
                <h1 class="section-title">👥 Manajemen Pengguna</h1>
                <p>Manajemen pengguna akan segera hadir...</p>
            </section>

        </main>
    </div>

    <button class="floating-action-button" onclick="triggerFileUpload()">
        <i class="fas fa-plus"></i>
    </button>

    <input type="file" id="fileInput" multiple style="display: none;" onchange="handleFileSelect(event)">

    <script src="../assets/js/home.js"></script>
</body>
</html>