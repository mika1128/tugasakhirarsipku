<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berkas Inactive </title>
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
        <div class="form-container">
            <h1><i class="fas fa-paper-plane"></i> Pengajuan Berkas Keluar (Inactive)</h1>
            <p>Formulir ini untuk mengajukan berkas keluar atau dokumen yang sudah tidak aktif untuk diarsipkan.</p>

            <div id="alertContainer"></div>

            <form id="berkasForm" enctype="multipart/form-data">
                <input type="hidden" name="submission_type" value="keluar">
                
                <div class="form-group">
                    <label for="nama_pemohon">Nama Pemohon/Instansi <span style="color: red;">*</span></label>
                    <input type="text" id="nama_pemohon" name="nama_pemohon" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="nik">NIK/NPWP/Nomor Identitas <span style="color: red;">*</span></label>
                    <input type="text" id="nik" name="nik" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="email">Email <span style="color: red;">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="telepon">Nomor Telepon</label>
                    <input type="tel" id="telepon" name="telepon" class="form-control">
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat Lengkap <span style="color: red;">*</span></label>
                    <textarea id="alamat" name="alamat" class="form-control" rows="3" required></textarea>
                </div>

                <div class="form-group">
                    <label for="jenis_berkas">Jenis Berkas Keluar <span style="color: red;">*</span></label>
                    <select id="jenis_berkas" name="jenis_berkas" class="form-control" required>
                        <option value="">Pilih jenis berkas</option>
                        <option value="Surat Izin Usaha">Surat Izin Usaha</option>
                        <option value="Dokumen Proyek Selesai">Dokumen Proyek Selesai</option>
                        <option value="Laporan Keuangan Lama">Laporan Keuangan Lama</option>
                        <option value="Kontrak Berakhir">Kontrak Berakhir</option>
                        <option value="Surat Menyurat Lama">Surat Menyurat Lama</option>
                        <option value="Dokumen Administrasi">Dokumen Administrasi</option>
                        <option value="Arsip Kegiatan">Arsip Kegiatan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="keterangan">Alasan Pengarsipan <span style="color: red;">*</span></label>
                    <textarea id="keterangan" name="keterangan" class="form-control" rows="4" placeholder="Jelaskan mengapa berkas ini perlu diarsipkan sebagai dokumen inactive" required></textarea>
                </div>

                <div class="form-group">
                    <label for="file_dokumen">Upload Berkas <span style="color: red;">*</span></label>
                    <div class="file-upload-area" onclick="document.getElementById('file_dokumen').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #feca57; margin-bottom: 1rem;"></i>
                        <p>Klik untuk memilih file atau seret file ke sini</p>
                        <small>Format yang didukung: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Maksimal 15MB)</small>
                    </div>
                    <input type="file" id="file_dokumen" name="file_dokumen" style="display: none;" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
                    <div id="selectedFile" style="margin-top: 10px; color: #feca57;"></div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-warning" style="width: 100%;">
                        <i class="fas fa-share"></i>
                        Kirim Berkas Inactive
                    </button>
                </div>
            </form>
        </div>

        <div class="form-container">
            <h2><i class="fas fa-folder-minus"></i> Tentang Berkas Inactive</h2>
            <ul>
                <li><strong>Berkas Inactive</strong> adalah dokumen yang sudah tidak aktif digunakan</li>
                <li>Dokumen disimpan untuk referensi dan keperluan audit</li>
                <li>Masa simpan berkas inactive biasanya 5-10 tahun sesuai ketentuan</li>
                <li>Berkas dapat dimusnahkan setelah melewati masa simpan yang ditentukan</li>
                <li>Akses terhadap berkas memerlukan persetujuan admin</li>
                <li>Status pemrosesan akan dikirim melalui email</li>
            </ul>
        </div>
    </main>

    <footer class="main-footer">
        <p>&copy; 2025 ArsipKu. Semua hak cipta dilindungi.</p>
    </footer>

    <script src="../assets/js/public.js"></script>
    <script>
        // File upload handling
        document.getElementById('file_dokumen').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const selectedFileDiv = document.getElementById('selectedFile');
            
            if (file) {
                selectedFileDiv.innerHTML = `<i class="fas fa-file"></i> ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            } else {
                selectedFileDiv.innerHTML = '';
            }
        });

        // Form submission
        document.getElementById('berkasForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitBerkas(this);
        });
    </script>
</body>
</html>