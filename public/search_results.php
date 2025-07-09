<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian</title>
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
                    <li><a href="kearsipan_dprd.php">Kearsipan</a></li>
                    <li><a href="kegiatan_pegawai.php">Kegiatan</a></li>
                    <li><a href="chat.php">Hubungi Kami</a></li>
                </ul>
                <a href="../pages/login.php" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </a>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <div class="content-page">
            <h1><i class="fas fa-search"></i> Hasil Pencarian</h1>
            
            <!-- Search Bar -->
            <div class="search-bar-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" placeholder="Cari dokumen, agenda, atau layanan..." class="search-input" id="searchInput">
                <button onclick="performSearch()" class="btn" style="margin-left: 10px;">
                    <i class="fas fa-search"></i>
                    Cari
                </button>
            </div>
            
            <div id="searchResults">
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    Memuat hasil pencarian...
                </div>
            </div>
        </div>
    </main>

    <footer class="main-footer">
        <p>&copy; 2025 ArsipKu. Semua hak cipta dilindungi.</p>
    </footer>

    <script src="../assets/js/public.js"></script>
    <script>
        // Get search query from URL
        const urlParams = new URLSearchParams(window.location.search);
        const query = urlParams.get('q');
        
        if (query) {
            document.getElementById('searchInput').value = query;
            performSearch();
        }
        
        function performSearch() {
            const searchQuery = document.getElementById('searchInput').value.trim();
            if (!searchQuery) {
                showAlert('Masukkan kata kunci pencarian', 'warning');
                return;
            }
            
            // Update URL
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('q', searchQuery);
            window.history.pushState({}, '', newUrl);
            
            // Perform search
            searchAllDocuments(searchQuery);
        }
        
        // Enhanced search function for this page
        async function searchAllDocuments(query) {
            const resultsContainer = document.getElementById('searchResults');
            resultsContainer.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Mencari...</div>';
            
            try {
                const searchPromises = [
                    searchKeluargaDocuments(query),
                    searchArsipVital(query),
                    searchArsipInactive(query),
                    searchAgenda(query)
                ];
                
                const results = await Promise.all(searchPromises);
                const allResults = results.flat();
                
                if (allResults.length === 0) {
                    resultsContainer.innerHTML = `
                        <div class="content-page" style="text-align: center;">
                            <i class="fas fa-search" style="font-size: 4rem; color: #9aa0a6; margin-bottom: 1rem;"></i>
                            <h3>Tidak ada hasil ditemukan</h3>
                            <p>Tidak ditemukan hasil untuk pencarian "${query}". Coba gunakan kata kunci yang berbeda.</p>
                        </div>
                    `;
                    return;
                }
                
                // Group results by type
                const groupedResults = {
                    keluarga: allResults.filter(r => r.type === 'keluarga'),
                    arsip_vital: allResults.filter(r => r.type === 'arsip_vital'),
                    arsip_inactive: allResults.filter(r => r.type === 'arsip_inactive'),
                    agenda: allResults.filter(r => r.type === 'agenda')
                };
                
                let html = `<div class="content-page"><h2>Ditemukan ${allResults.length} hasil untuk "${query}"</h2></div>`;
                
                Object.entries(groupedResults).forEach(([type, items]) => {
                    if (items.length > 0) {
                        html += `
                            <div class="content-page">
                                <h3><i class="fas ${getTypeIcon(type)}"></i> ${getTypeLabel(type)} (${items.length})</h3>
                                <div class="search-results-grid">
                                    ${items.map(item => `
                                        <div class="search-result-card">
                                            <div class="result-header">
                                                <span class="result-type">${getTypeLabel(type)}</span>
                                                <span class="status-badge status-${item.status || 'info'}">${item.status || 'N/A'}</span>
                                            </div>
                                            <h4>${item.title}</h4>
                                            <p>${item.description || 'Tidak ada deskripsi'}</p>
                                            <div class="result-footer">
                                                ${item.tanggal_dibuat ? `<span><i class="fas fa-calendar"></i> ${item.tanggal_dibuat}</span>` : ''}
                                                ${item.tahun_dibuat ? `<span><i class="fas fa-calendar"></i> ${item.tahun_dibuat}</span>` : ''}
                                                ${item.start_date ? `<span><i class="fas fa-calendar"></i> ${new Date(item.start_date).toLocaleDateString('id-ID')}</span>` : ''}
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        `;
                    }
                });
                
                resultsContainer.innerHTML = html;
                
            } catch (error) {
                console.error('Search error:', error);
                resultsContainer.innerHTML = `
                    <div class="content-page" style="text-align: center;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: #f44336; margin-bottom: 1rem;"></i>
                        <h3>Terjadi Kesalahan</h3>
                        <p>Gagal melakukan pencarian. Silakan coba lagi.</p>
                    </div>
                `;
            }
        }
        
        function getTypeIcon(type) {
            switch (type) {
                case 'keluarga': return 'fa-users';
                case 'arsip_vital': return 'fa-file-invoice';
                case 'arsip_inactive': return 'fa-folder-minus';
                case 'agenda': return 'fa-calendar-alt';
                default: return 'fa-file';
            }
        }
        
        function getTypeLabel(type) {
            switch (type) {
                case 'keluarga': return 'Dokumen Keluarga';
                case 'arsip_vital': return 'Arsip Vital';
                case 'arsip_inactive': return 'Arsip Inactive';
                case 'agenda': return 'Agenda';
                default: return 'Dokumen';
            }
        }
        
        // Search functions (same as in public.js)
        async function searchKeluargaDocuments(query) {
            try {
                const response = await fetch('../pages/get_keluarga_dokumen.php');
                const result = await response.json();
                
                if (result.status === 'success') {
                    return result.data.filter(doc => 
                        doc.nama_dokumen.toLowerCase().includes(query.toLowerCase()) ||
                        (doc.deskripsi_dokumen && doc.deskripsi_dokumen.toLowerCase().includes(query.toLowerCase()))
                    ).map(doc => ({
                        ...doc,
                        type: 'keluarga',
                        title: doc.nama_dokumen,
                        description: doc.deskripsi_dokumen
                    }));
                }
                return [];
            } catch (error) {
                console.error('Error searching keluarga documents:', error);
                return [];
            }
        }
        
        async function searchArsipVital(query) {
            try {
                const response = await fetch('../pages/get_arsip_vital.php');
                const result = await response.json();
                
                if (result.status === 'success') {
                    return result.data.filter(doc => 
                        doc.nomor_surat.toLowerCase().includes(query.toLowerCase()) ||
                        (doc.berita_acara_surat && doc.berita_acara_surat.toLowerCase().includes(query.toLowerCase()))
                    ).map(doc => ({
                        ...doc,
                        type: 'arsip_vital',
                        title: `Surat No. ${doc.nomor_surat}`,
                        description: doc.berita_acara_surat
                    }));
                }
                return [];
            } catch (error) {
                console.error('Error searching arsip vital:', error);
                return [];
            }
        }
        
        async function searchArsipInactive(query) {
            try {
                const response = await fetch('../pages/get_arsip_inactive.php');
                const result = await response.json();
                
                if (result.status === 'success') {
                    return result.data.filter(doc => 
                        doc.nomor_surat.toLowerCase().includes(query.toLowerCase()) ||
                        (doc.berita_acara_surat && doc.berita_acara_surat.toLowerCase().includes(query.toLowerCase()))
                    ).map(doc => ({
                        ...doc,
                        type: 'arsip_inactive',
                        title: `Surat No. ${doc.nomor_surat}`,
                        description: doc.berita_acara_surat
                    }));
                }
                return [];
            } catch (error) {
                console.error('Error searching arsip inactive:', error);
                return [];
            }
        }
        
        async function searchAgenda(query) {
            try {
                const response = await fetch('../pages/get_agenda.php');
                const result = await response.json();
                
                if (result.status === 'success') {
                    return result.data.filter(agenda => 
                        agenda.title.toLowerCase().includes(query.toLowerCase()) ||
                        agenda.description.toLowerCase().includes(query.toLowerCase()) ||
                        (agenda.location && agenda.location.toLowerCase().includes(query.toLowerCase()))
                    ).map(agenda => ({
                        ...agenda,
                        type: 'agenda',
                        title: agenda.title,
                        description: agenda.description
                    }));
                }
                return [];
            } catch (error) {
                console.error('Error searching agenda:', error);
                return [];
            }
        }
        
        // Enter key search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    </script>
    
    <style>
        .search-results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }
        
        .search-result-card {
            background: #202124;
            border-radius: 10px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: transform 0.3s ease;
        }
        
        .search-result-card:hover {
            transform: translateY(-2px);
        }
        
        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .result-type {
            background: #4285f4;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        
        .search-result-card h4 {
            color: #e8eaed;
            margin-bottom: 0.5rem;
        }
        
        .search-result-card p {
            color: #9aa0a6;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        
        .result-footer {
            display: flex;
            gap: 1rem;
            font-size: 0.9rem;
            color: #8ab4f8;
        }
        
        .result-footer span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
    </style>
</body>
</html>