<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berkas Masuk (Vital) - DPRD Kearsipan</title>
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
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="tentang-dprd.php">Tentang DPRD</a></li>
                    <li><a href="kearsipan-dprd.php">Kearsipan</a></li>
                    <li><a href="kegiatan-pegawai.php">Kegiatan</a></li>
                    <li><a href="chat.php">Hubungi Kami</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <div class="form-container">
            <h1><i class="fas fa-inbox"></i> Pengajuan Berkas Masuk (Vital)</h1>
            <p>Formulir ini untuk mengajukan berkas masuk yang bersifat vital dan penting untuk diarsipkan oleh DPRD.</p>

            <div id="alertContainer"></div>

            <form id="berkasForm" enctype="multipart/form-data">
                <input type="hidden" name="submission_type" value="masuk">
                
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
                    <label for="jenis_berkas">Jenis Berkas Masuk <span style="color: red;">*</span></label>
                    <select id="jenis_berkas" name="jenis_berkas" class="form-control" required>
                        <option value="">Pilih jenis berkas</option>
                        <option value="Proposal Kerjasama">Proposal Kerjasama</option>
                        <option value="Surat Permohonan">Surat Permohonan</option>
                        <option value="Dokumen Kontrak">Dokumen Kontrak</option>
                        <option value="Laporan Kegiatan">Laporan Kegiatan</option>
                        <option value="Dokumen Anggaran">Dokumen Anggaran</option>
                        <option value="Surat Keputusan">Surat Keputusan</option>
                        <option value="Dokumen Legal">Dokumen Legal</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="keterangan">Deskripsi/Ringkasan Berkas <span style="color: red;">*</span></label>
                    <textarea id="keterangan" name="keterangan" class="form-control" rows="4" placeholder="Jelaskan secara detail isi berkas yang akan diarsipkan" required></textarea>
                </div>

                <div class="form-group">
                    <label for="file_dokumen">Upload Berkas <span style="color: red;">*</span></label>
                    <div class="file-upload-area" onclick="document.getElementById('file_dokumen').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #43e97b; margin-bottom: 1rem;"></i>
                        <p>Klik untuk memilih file atau seret file ke sini</p>
                        <small>Format yang didukung: PDF, DOC, DOCX, XLS, XLSX (Maksimal 20MB)</small>
                    </div>
                    <input type="file" id="file_dokumen" name="file_dokumen" style="display: none;" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                    <div id="selectedFile" style="margin-top: 10px; color: #43e97b;"></div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-success" style="width: 100%;">
                        <i class="fas fa-upload"></i>
                        Submit Berkas Vital
                    </button>
                </div>
            </form>
        </div>

        <div class="form-container">
            <h2><i class="fas fa-shield-alt"></i> Ketentuan Berkas Vital</h2>
            <ul>
                <li><strong>Berkas Vital</strong> adalah dokumen yang sangat penting dan strategis bagi DPRD</li>
                <li>Dokumen akan disimpan dengan sistem keamanan tinggi</li>
                <li>Akses terhadap berkas vital terbatas dan memerlukan otorisasi khusus</li>
                <li>Masa simpan berkas vital adalah permanen</li>
                <li>Berkas akan diverifikasi keabsahan dan kelengkapannya</li>
                <li>Pemberitahuan status verifikasi akan dikirim melalui email</li>
            </ul>
        </div>
    </main>

    <footer class="main-footer">
        <p>&copy; 2025 Sistem Informasi Kearsipan DPRD. Semua hak cipta dilindungi.</p>
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