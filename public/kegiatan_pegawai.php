<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kegiatan Pegawai - DPRD Kearsipan</title>
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
                    <li><a href="tentang_dprd.php">Tentang DPRD</a></li>
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
        <div class="content-page">
            <h1><i class="fas fa-calendar-alt"></i> Kegiatan Pegawai DPRD</h1>
            <p>Berikut adalah jadwal kegiatan dan agenda pegawai DPRD yang dapat diakses oleh masyarakat.</p>
            
            <div id="kegiatanContainer" class="kegiatan-grid">
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    Memuat kegiatan pegawai...
                </div>
            </div>
        </div>
    </main>

    <footer class="main-footer">
        <p>&copy; 2025 Sistem Informasi Kearsipan DPRD. Semua hak cipta dilindungi.</p>
    </footer>

    <script>
        // Load kegiatan data from agenda
        async function loadKegiatan() {
            try {
                const response = await fetch('../pages/get_agenda.php');
                const result = await response.json();
                
                const container = document.getElementById('kegiatanContainer');
                
                if (result.status === 'success' && result.data.length > 0) {
                    container.innerHTML = '';
                    
                    // Filter only active and future events
                    const activeEvents = result.data.filter(agenda => {
                        const endDate = new Date(agenda.end_date);
                        const now = new Date();
                        return endDate >= now || agenda.status === 'in_progress';
                    });
                    
                    if (activeEvents.length === 0) {
                        container.innerHTML = `
                            <div class="kegiatan-card" style="text-align: center; grid-column: 1 / -1;">
                                <i class="fas fa-calendar-times" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                                <h3>Tidak Ada Kegiatan Terjadwal</h3>
                                <p>Saat ini tidak ada kegiatan pegawai yang terjadwal. Silakan periksa kembali nanti.</p>
                            </div>
                        `;
                        return;
                    }
                    
                    activeEvents.forEach(agenda => {
                        const startDate = new Date(agenda.start_date);
                        const endDate = new Date(agenda.end_date);
                        const statusClass = agenda.status === 'in_progress' ? 'status-diproses' : 
                                          agenda.status === 'complete' ? 'status-selesai' : 'status-pending';
                        
                        const kegiatanCard = document.createElement('div');
                        kegiatanCard.className = 'kegiatan-card';
                        kegiatanCard.innerHTML = `
                            <div class="kegiatan-date">
                                <i class="fas fa-calendar"></i>
                                ${formatDate(startDate)} - ${formatDate(endDate)}
                            </div>
                            <h3>${agenda.title}</h3>
                            <p>${agenda.description}</p>
                            <div class="kegiatan-location">
                                <i class="fas fa-map-marker-alt"></i>
                                ${agenda.location || 'Lokasi belum ditentukan'}
                            </div>
                            <div style="margin-top: 1rem;">
                                <span class="status-badge ${statusClass}">${formatStatus(agenda.status)}</span>
                                <span class="status-badge" style="background: #667eea; color: white; margin-left: 0.5rem;">
                                    ${formatPriority(agenda.priority)}
                                </span>
                            </div>
                        `;
                        container.appendChild(kegiatanCard);
                    });
                } else {
                    container.innerHTML = `
                        <div class="kegiatan-card" style="text-align: center; grid-column: 1 / -1;">
                            <i class="fas fa-calendar-times" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                            <h3>Tidak Ada Kegiatan</h3>
                            <p>Belum ada kegiatan pegawai yang terjadwal saat ini.</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading kegiatan:', error);
                document.getElementById('kegiatanContainer').innerHTML = `
                    <div class="kegiatan-card" style="text-align: center; grid-column: 1 / -1;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #f44336; margin-bottom: 1rem;"></i>
                        <h3>Gagal Memuat Data</h3>
                        <p>Terjadi kesalahan saat memuat data kegiatan. Silakan coba lagi nanti.</p>
                    </div>
                `;
            }
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = { 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            return date.toLocaleDateString('id-ID', options);
        }

        function formatStatus(status) {
            switch(status) {
                case 'pending': return 'Terjadwal';
                case 'in_progress': return 'Sedang Berlangsung';
                case 'complete': return 'Selesai';
                default: return status;
            }
        }

        function formatPriority(priority) {
            switch(priority) {
                case 'high': return 'Prioritas Tinggi';
                case 'medium': return 'Prioritas Sedang';
                case 'low': return 'Prioritas Rendah';
                default: return priority;
            }
        }

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', loadKegiatan);
    </script>
</body>
</html>