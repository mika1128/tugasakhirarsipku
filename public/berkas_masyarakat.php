<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Berkas Masyarakat dan Keluarga</title>
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
                    <li><a href="index.php" >Beranda</a></li>
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
            <h1><i class="fas fa-users"></i> Pengajuan Berkas Masyarakat dan Keluarga</h1>
            <p>Silakan isi formulir di bawah ini untuk mengajukan berkas keluarga Anda. Admin akan memproses pengajuan Anda dalam 1-3 hari kerja.</p>

            <div id="alertContainer"></div>

            <form id="berkasForm" enctype="multipart/form-data">
                <input type="hidden" name="submission_type" value="masyarakat">
                
                <div class="form-group">
                    <label for="nama_pemohon">Nama Lengkap Pemohon <span style="color: red;">*</span></label>
                    <input type="text" id="nama_pemohon" name="nama_pemohon" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="nik">NIK (Nomor Induk Kependudukan) <span style="color: red;">*</span></label>
                    <input type="text" id="nik" name="nik" class="form-control" pattern="[0-9]{16}" maxlength="16" required>
                    <small>Masukkan 16 digit NIK sesuai KTP</small>
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
                    <label for="jenis_berkas">Jenis Berkas yang Diajukan <span style="color: red;">*</span></label>
                    <select id="jenis_berkas" name="jenis_berkas" class="form-control" required>
                        <option value="">Pilih jenis berkas</option>
                        <option value="Surat Keterangan Domisili">Surat Keterangan Domisili</option>
                        <option value="Surat Keterangan Tidak Mampu">Surat Keterangan Tidak Mampu</option>
                        <option value="Surat Keterangan Usaha">Surat Keterangan Usaha</option>
                        <option value="Akta Kelahiran">Akta Kelahiran</option>
                        <option value="Akta Kematian">Akta Kematian</option>
                        <option value="Kartu Keluarga">Kartu Keluarga</option>
                        <option value="Surat Nikah">Surat Nikah</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan/Keperluan <span style="color: red;">*</span></label>
                    <textarea id="keterangan" name="keterangan" class="form-control" rows="3" placeholder="Jelaskan keperluan dan tujuan pengajuan berkas ini" required></textarea>
                </div>

                <div class="form-group">
                    <label for="file_dokumen">Dokumen Pendukung (Opsional)</label>
                    <div class="file-upload-area" onclick="document.getElementById('file_dokumen').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #667eea; margin-bottom: 1rem;"></i>
                        <p>Klik untuk memilih file atau seret file ke sini</p>
                        <small>Format yang didukung: PDF, JPG, PNG, DOC, DOCX (Maksimal 10MB)</small>
                    </div>
                    <input type="file" id="file_dokumen" name="file_dokumen" style="display: none;" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <div id="selectedFile" style="margin-top: 10px; color: #667eea;"></div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i>
                        Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>

        <div class="form-container">
            <h2><i class="fas fa-info-circle"></i> Informasi Penting</h2>
            <ul>
                <li>Pastikan semua data yang diisi sudah benar dan sesuai dengan dokumen resmi</li>
                <li>Pengajuan akan diproses dalam 1-3 hari kerja</li>
                <li>Anda akan mendapat notifikasi melalui email setelah berkas diproses</li>
                <li>Untuk pertanyaan lebih lanjut, silakan hubungi kami melalui fitur chat</li>
                <li>Berkas yang sudah disetujui dapat diambil di kantor atau dikirim sesuai permintaan</li>
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