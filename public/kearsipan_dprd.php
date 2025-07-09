<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kearsipan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/public.css">
</head>
<body>
    <header class="main-header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-archive"></i>
                <span>ArsipKu</span>
            </div>
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="kearsipan_dprd.php" class="active">Kearsipan</a></li>
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
        <div class="content-page">
            <h1><i class="fas fa-archive"></i> Sistem Kearsipan </h1>

            <h2>Retensi Arsip</h2>
            <table style="width: 100%; border-collapse: collapse; margin: 1rem 0;">
                <thead>
                    <tr style="background: #667eea; color: white;">
                        <th style="padding: 1rem; border: 1px solid #ddd;">Jenis Dokumen</th>
                        <th style="padding: 1rem; border: 1px solid #ddd;">Masa Simpan Aktif</th>
                        <th style="padding: 1rem; border: 1px solid #ddd;">Masa Simpan Inaktif</th>
                        <th style="padding: 1rem; border: 1px solid #ddd;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 0.5rem; border: 1px solid #ddd;">Surat Keputusan</td>
                        <td style="padding: 0.5rem; border: 1px solid #ddd;">5 tahun</td>
                        <td style="padding: 0.5rem; border: 1px solid #ddd;">Permanen</td>
                        <td style="padding: 0.5rem; border: 1px solid #ddd;">Disimpan permanent</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem; border: 1px solid #ddd;">Dokumen Administrasi</td>
                        <td style="padding: 0.5rem; border: 1px solid #ddd;">2 tahun</td>
                        <td style="padding: 0.5rem; border: 1px solid #ddd;">3 tahun</td>
                        <td style="padding: 0.5rem; border: 1px solid #ddd;">Dapat dimusnahkan</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem; border: 1px solid #ddd;">Laporan Keuangan</td>
                        <td style="padding: 0.5rem; border: 1px solid #ddd;">3 tahun</td>
                        <td style="padding: 0.5rem; border: 1px solid #ddd;">7 tahun</td>
                        <td style="padding: 0.5rem; border: 1px solid #ddd;">Untuk audit</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem; border: 1px solid #ddd;">Surat Menyurat Biasa</td>
                        <td style="padding: 0.5rem; border: 1px solid #ddd;">1 tahun</td>
                        <td style="padding: 0.5rem; border: 1px solid #ddd;">2 tahun</td>
                        <td style="padding: 0.5rem; border: 1px solid #ddd;">Dapat dimusnahkan</td>
                    </tr>
                </tbody>
            </table>

            <h2>Layanan Kearsipan untuk Masyarakat</h2>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon berkas-masyarakat">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Berkas Keluarga</h3>
                    <p>Pengajuan dokumen keluarga dan administrasi kependudukan</p>
                    <a href="berkas_masyarakat.php" class="btn">Ajukan Sekarang</a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon berkas-masuk">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h3>Berkas Vital</h3>
                    <p>Submit dokumen penting untuk diarsipkan secara permanen</p>
                    <a href="berkas_masuk.php" class="btn btn-success">Submit Berkas</a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon berkas-keluar">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <h3>Berkas Inactive</h3>
                    <p>Arsipkan dokumen yang sudah tidak aktif digunakan</p>
                    <a href="berkas_keluar.php" class="btn btn-warning">Kirim Berkas</a>
                </div>
            </div>

            <p style="margin-top: 2rem; padding: 1rem; background: rgba(102, 126, 234, 0.1); border-radius: 8px;">
                <i class="fas fa-phone"></i>
                <strong>Butuh Bantuan?</strong> Tim kearsipan kami siap membantu Anda. 
                Hubungi kami melalui fitur <a href="chat.php">chat</a> untuk konsultasi langsung.
            </p>
        </div>
    </main>

    <footer class="main-footer">
        <p>&copy; 2025 ArsipKu. Semua hak cipta dilindungi.</p>
    </footer>
</body>
</html>