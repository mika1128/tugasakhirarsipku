<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Kearsipan DPRD</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/public.css">
</head>
<body>
    <header class="main-header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-archive"></i>
                <span>DPRD Kearsipan</span>
            </div>
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php" class="active">Beranda</a></li>
                    <li><a href="kearsipan_dprd.php">Kearsipan</a></li>
                    <li><a href="kegiatan_pegawai.php">Kegiatan</a></li>
                    <li><a href="chat.php">Hubungi Kami</a></li>
                    <li><a href="../pages/login.php" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <section class="hero-section">
            <h1>Selamat Datang di Sistem Informasi Kearsipan DPRD</h1>
            <p>Layanan digital untuk mempermudah pengurusan berkas dan dokumen Anda</p>
        </section>

        <section id="services" class="services-grid">
            <div class="service-card">
                <div class="service-icon berkas-masyarakat">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Berkas Masyarakat/Keluarga</h3>
                <p>Pengajuan dan pengurusan dokumen keluarga seperti surat keterangan, akta, dan dokumen lainnya</p>
                <a href="berkas_masyarakat.php" class="btn">
                    <i class="fas fa-file-plus"></i>
                    Ajukan Berkas
                </a>
            </div>

            <div class="service-card">
                <div class="service-icon berkas-masuk">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3>Berkas Masuk (Vital)</h3>
                <p>Pengajuan dokumen masuk yang bersifat vital dan penting untuk diarsipkan</p>
                <a href="berkas_masuk.php" class="btn btn-success">
                    <i class="fas fa-upload"></i>
                    Submit Berkas
                </a>
            </div>

            <div class="service-card">
                <div class="service-icon berkas-keluar">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <h3>Berkas Keluar (Inactive)</h3>
                <p>Pengajuan dokumen keluar atau berkas yang sudah tidak aktif untuk diarsipkan</p>
                <a href="berkas_keluar.php" class="btn btn-warning">
                    <i class="fas fa-share"></i>
                    Kirim Berkas
                </a>
            </div>

            <div class="service-card">
                <div class="service-icon kegiatan">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3>Kegiatan Pegawai</h3>
                <p>Lihat jadwal dan kegiatan pegawai DPRD yang sedang berlangsung</p>
                <a href="kegiatan_pegawai.php" class="btn">
                    <i class="fas fa-calendar"></i>
                    Lihat Jadwal
                </a>
            </div>

            <div class="service-card">
                <div class="service-icon chat">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>Chat / Konsultasi</h3>
                <p>Hubungi admin untuk konsultasi dan tanya jawab seputar layanan kearsipan</p>
                <a href="chat.php" class="btn btn-secondary">
                    <i class="fas fa-comment"></i>
                    Mulai Chat
                </a>
            </div>
        </section>
    </main>

    <footer class="main-footer">
        <p>&copy; 2025 Sistem Informasi Kearsipan DPRD. Semua hak cipta dilindungi.</p>
    </footer>

    <script src="../assets/js/public.js"></script>
    <script>
        // Global search functionality
        document.getElementById('globalSearch').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            
            // Search in service cards
            const serviceCards = document.querySelectorAll('.service-card');
            serviceCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const description = card.querySelector('p').textContent.toLowerCase();
                
                if (title.includes(query) || description.includes(query) || query === '') {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>