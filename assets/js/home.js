class HomeManager {
    constructor() {
        this.sidebar = null;
        this.sidebarOverlay = null;
        this.navToggle = null;
        this.tabButtons = null;
        this.contentSections = null;
        this.fileInput = null;
        this.uploadArea = null;

        // Binding methods
        this.handleFileSelect = this.handleFileSelect.bind(this);
        this.handleDragOver = this.handleDragOver.bind(this);
        this.handleDragLeave = this.handleDragLeave.bind(this);
        this.handleDrop = this.handleDrop.bind(this);
        this.handleResize = this.handleResize.bind(this);
        this.searchDocuments = this.searchDocuments.bind(this);
    }

    /**
     * Inisialisasi home saat DOM sudah siap
     */
    init() {
        console.log('Home initializing...');
        this.cacheElements();
        this.updateGreeting();
        this.bindEvents();
        this.initializeDefaultState();
        this.updateStats();
        this.loadNotifications();
        console.log('Home initialized successfully');
    }

    /**
     * Cache semua elemen DOM yang diperlukan
     */
    cacheElements() {
        this.tabButtons = document.querySelectorAll('.tab-button');
        this.contentSections = document.querySelectorAll('.main-content .content-section');
        this.fileInput = document.getElementById('fileInput');
        this.uploadArea = document.querySelector('.upload-area');
        this.searchInput = document.querySelector('.search-input');

        console.log('Elements cached:', {
            tabButtons: this.tabButtons.length,
            contentSections: this.contentSections.length,
            fileInput: !!this.fileInput,
            uploadArea: !!this.uploadArea,
            searchInput: !!this.searchInput
        });
    }

    /**
     * Initialize default state
     */
    initializeDefaultState() {
        const defaultSection = 'disarankan-content';
        this.setActiveSection(defaultSection);
        this.loadRecentDocuments();
        this.loadAllDocuments(); // Load all documents for search functionality
    }

    /**
     * Menampilkan salam berdasarkan waktu saat ini
     */
    updateGreeting() {
        const greetingElement = document.getElementById('greetingText');
        if (!greetingElement) return;

        const hour = new Date().getHours();
        let greeting = "Selamat Datang";

        if (hour >= 5 && hour < 12) {
            greeting = "Selamat Pagi";
        } else if (hour >= 12 && hour < 15) {
            greeting = "Selamat Siang";
        } else if (hour >= 15 && hour < 18) {
            greeting = "Selamat Sore";
        } else {
            greeting = "Selamat Malam";
        }

        const namaLengkap = greetingElement.dataset.fullName || 'Pengguna';
        greetingElement.textContent = `${greeting}, ${namaLengkap}!`;
    }

    /**
     * Bind semua event listeners
     */
    bindEvents() {
        // Event untuk navbar items
        document.querySelectorAll('.main-navbar .navbar-item').forEach(item => {
            item.addEventListener('click', (event) => this.handleNavbarClick(event));
        });

        // Event untuk tab buttons
        this.tabButtons.forEach(button => {
            button.addEventListener('click', (event) => this.handleTabClick(event));
        });

        // Event untuk file upload
        if (this.fileInput) {
            this.fileInput.addEventListener('change', this.handleFileSelect);
            console.log('File input event listener attached');
        }

        // Event untuk drag & drop
        if (this.uploadArea) {
            this.uploadArea.addEventListener('click', () => this.triggerFileUpload());
            this.uploadArea.addEventListener('dragover', this.handleDragOver);
            this.uploadArea.addEventListener('dragleave', this.handleDragLeave);
            this.uploadArea.addEventListener('drop', this.handleDrop);
            console.log('Upload area event listeners attached');
        }

        // Event untuk floating action button
        const fabButton = document.querySelector('.floating-action-button');
        if (fabButton) {
            fabButton.addEventListener('click', () => this.triggerFileUpload());
            console.log('FAB event listener attached');
        }

        // Event untuk search input (debounced)
        if (this.searchInput) {
            this.searchInput.addEventListener('input', this.debounce((e) => this.searchDocuments(e.target.value), 300));
            console.log('Search input event listener attached');
        }
    }

    /**
     * Handle klik pada navbar item
     */
    handleNavbarClick(event) {
        event.preventDefault();

        const clickedItem = event.currentTarget;
        const sectionId = clickedItem.dataset.targetSection;

        if (!sectionId) return;

        // Remove active dari semua navbar items
        document.querySelectorAll('.main-navbar .navbar-item').forEach(item => {
            item.classList.remove('active');
        });
        // Add active ke item yang diklik
        clickedItem.classList.add('active');

        this.setActiveSection(sectionId);
    }

    /**
     * Handle klik pada tab button (untuk Terbaru, Notifikasi)
     */
    handleTabClick(event) {
        const clickedButton = event.currentTarget;
        const targetSectionId = clickedButton.dataset.targetSection;

        if (!targetSectionId) return;

        // Update active tab
        this.tabButtons.forEach(btn => btn.classList.remove('active'));
        clickedButton.classList.add('active');

        // Update active content untuk tab-specific sections
        document.getElementById('disarankan-content')?.classList.remove('active');
        document.getElementById('notifikasi-content')?.classList.remove('active');
        document.getElementById(targetSectionId)?.classList.add('active');

        // Load notifications if switching to notifications tab
        if (targetSectionId === 'notifikasi-content') {
            this.loadNotifications();
        }
    }

    /**
     * Set section aktif dan update navbar state
     */
    setActiveSection(sectionId) {
        // Remove active dari semua navbar items
        document.querySelectorAll('.main-navbar .navbar-item').forEach(item => {
            item.classList.remove('active');
        });

        // Add active ke item yang sesuai di navbar
        const correspondingNavbarItem = document.querySelector(`.main-navbar .navbar-item[data-target-section="${sectionId}"]`);
        if (correspondingNavbarItem) {
            correspondingNavbarItem.classList.add('active');
        }

        // Hide semua content sections
        this.contentSections.forEach(section => {
            section.classList.remove('active');
        });

        // Show target section
        const targetSection = document.getElementById(sectionId);
        if (targetSection) {
            targetSection.classList.add('active');

            // Update page title
            const itemText = correspondingNavbarItem ? correspondingNavbarItem.textContent.trim() : sectionId;
            document.title = `Home - ${itemText}`;

            // Handle tab sections (Terbaru, Notifikasi)
            if (sectionId === 'disarankan-content' || sectionId === 'notifikasi-content') {
                this.tabButtons.forEach(btn => btn.classList.remove('active'));
                document.querySelector(`.tab-button[data-target-section="${sectionId}"]`)?.classList.add('active');
                document.getElementById('disarankan-content')?.classList.toggle('active', sectionId === 'disarankan-content');
                document.getElementById('notifikasi-content')?.classList.toggle('active', sectionId === 'notifikasi-content');
            } else {
                document.getElementById('disarankan-content')?.classList.remove('active');
                document.getElementById('notifikasi-content')?.classList.remove('active');
                this.tabButtons.forEach(btn => btn.classList.remove('active'));
            }

            // Load data for specific sections
            if (sectionId === 'agenda') {
                this.loadAgendaData();
            } else if (sectionId === 'riwayat') {
                this.loadHistoryData();
            } else if (sectionId === 'keluarga') {
                this.loadKeluargaData();
            } else if (sectionId === 'arsip-vital') {
                this.loadArsipVitalData();
            } else if (sectionId === 'arsip-inactive') {
                this.loadArsipInactiveData();
            } else if (sectionId === 'dokumen-main') {
                this.loadAllDocuments();
            }
        }
    }

    /**
     * Load recent documents
     */
    loadRecentDocuments() {
        const documentGrid = document.getElementById('documentGrid');
        if (!documentGrid) return;

        documentGrid.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i><br>Memuat dokumen terbaru...</div>';

        fetch('/ArsipKu/pages/get_recent_documents.php')
            .then(res => res.json())
            .then(response => {
                if (response.status === 'success' && response.data) {
                    this.renderDocuments(response.data, documentGrid);
                } else {
                    documentGrid.innerHTML = '<div class="empty-state"><i class="fas fa-folder-open"></i><h3>Belum ada dokumen</h3><p>Mulai dengan mengunggah dokumen pertama Anda</p></div>';
                }
            })
            .catch(error => {
                console.error('Error loading recent documents:', error);
                documentGrid.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>Gagal memuat dokumen</h3><p>Terjadi kesalahan saat memuat data</p></div>';
            });
    }

    /**
     * Load all documents for the main document section
     */
    loadAllDocuments() {
        const documentGrid = document.getElementById('documentGridDokumenSaya');
        if (!documentGrid) return;

        documentGrid.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i><br>Memuat semua dokumen...</div>';

        fetch('/ArsipKu/pages/get_all_documents.php')
            .then(res => res.json())
            .then(response => {
                if (response.status === 'success' && response.data) {
                    this.renderDocuments(response.data, documentGrid);
                    this.updateStats();
                } else {
                    documentGrid.innerHTML = '<div class="empty-state"><i class="fas fa-folder-open"></i><h3>Belum ada dokumen</h3><p>Mulai dengan mengunggah dokumen pertama Anda</p></div>';
                }
            })
            .catch(error => {
                console.error('Error loading all documents:', error);
                documentGrid.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>Gagal memuat dokumen</h3><p>Terjadi kesalahan saat memuat data</p></div>';
            });
    }

    /**
     * Load notifications
     */
    loadNotifications() {
        const notificationContent = document.getElementById('notifikasi-content');
        if (!notificationContent) return;

        // Clear existing content except title and subtitle
        const existingGrid = notificationContent.querySelector('.notification-grid');
        if (existingGrid) {
            existingGrid.remove();
        }

        const notificationGrid = document.createElement('div');
        notificationGrid.className = 'notification-grid';
        notificationGrid.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i><br>Memuat notifikasi...</div>';
        notificationContent.appendChild(notificationGrid);

        fetch('/ArsipKu/pages/get_notifications.php')
            .then(res => res.json())
            .then(response => {
                if (response.status === 'success' && response.data && response.data.length > 0) {
                    this.renderNotifications(response.data, notificationGrid);
                } else {
                    notificationGrid.innerHTML = '<div class="empty-state"><i class="fas fa-bell-slash"></i><h3>Belum ada notifikasi</h3><p>Notifikasi akan muncul di sini</p></div>';
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                notificationGrid.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>Gagal memuat notifikasi</h3><p>Terjadi kesalahan saat memuat data</p></div>';
            });
    }

    /**
     * Render notifications
     */
    renderNotifications(notifications, container) {
        container.innerHTML = '';
        
        notifications.forEach(notification => {
            const notificationCard = document.createElement('div');
            notificationCard.className = 'notification-card';
            
            const priorityClass = notification.priority === 'high' ? 'priority-high' : 
                                notification.priority === 'medium' ? 'priority-medium' : 'priority-low';
            
            notificationCard.innerHTML = `
                <div class="notification-header">
                    <div class="notification-icon ${priorityClass}">
                        <i class="${notification.icon || 'fas fa-bell'}"></i>
                    </div>
                    <div class="notification-meta">
                        <span class="notification-time">${this.formatDate(notification.created_at)}</span>
                        <span class="notification-priority ${priorityClass}">${notification.priority}</span>
                    </div>
                </div>
                <div class="notification-content">
                    <h4 class="notification-title">${notification.title}</h4>
                    <p class="notification-message">${notification.message}</p>
                    ${notification.details ? `<div class="notification-details">${notification.details}</div>` : ''}
                </div>
                <div class="notification-actions">
                    <button class="btn btn-sm btn-primary" onclick="window.home.markAsRead(${notification.id})">
                        <i class="fas fa-check"></i> Tandai Dibaca
                    </button>
                    ${notification.action_url ? `<a href="${notification.action_url}" class="btn btn-sm btn-secondary">Lihat Detail</a>` : ''}
                </div>
            `;
            
            container.appendChild(notificationCard);
        });
    }

    /**
     * Mark notification as read
     */
    markAsRead(notificationId) {
        fetch('/ArsipKu/pages/mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: notificationId })
        })
        .then(res => res.json())
        .then(response => {
            if (response.status === 'success') {
                this.loadNotifications(); // Reload notifications
                this.showNotification('Notifikasi ditandai sebagai dibaca', 'success');
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }

    /**
     * Render documents
     */
    renderDocuments(documents, container) {
        container.innerHTML = '';
        
        documents.forEach(doc => {
            const docCard = this.createDocumentCard(doc);
            container.appendChild(docCard);
        });
    }

    /**
     * Create document card
     */
    createDocumentCard(doc) {
        const docCard = document.createElement('div');
        docCard.className = 'document-card-gd recent-document-card';
        docCard.dataset.id = doc.id;
        docCard.dataset.category = doc.category;

        docCard.innerHTML = `
            <div class="doc-thumbnail">
                <img src="${doc.thumbnail_url}" alt="${doc.title}" onerror="this.src='https://via.placeholder.com/200x120/3c4043/9aa0a6?text=${encodeURIComponent(doc.category.toUpperCase())}'">
                <div class="doc-category-badge" style="background: ${doc.color}">${doc.category.toUpperCase()}</div>
            </div>
            <div class="doc-info">
                <h4 class="doc-title">${doc.title}</h4>
                <p class="doc-description">${doc.description || 'Tidak ada deskripsi'}</p>
                <div class="doc-meta">
                    <div class="doc-date">
                        <i class="fas fa-calendar"></i>
                        <span>${doc.formatted_date}</span>
                    </div>
                    <span class="status-badge ${doc.status_class}">${doc.status}</span>
                </div>
                <div class="doc-actions">
                    <button class="btn btn-primary btn-sm" onclick="window.home.viewDocument('${doc.id}', '${doc.category}')">
                        <i class="fas fa-eye"></i> Lihat Detail
                    </button>
                </div>
            </div>
            <button class="doc-options" onclick="window.home.showDocumentOptions(event, '${doc.id}', '${doc.category}')">
                <i class="fas fa-ellipsis-v"></i>
            </button>
        `;

        return docCard;
    }

    /**
     * Show document options menu
     */
    showDocumentOptions(event, documentId, category) {
        event.stopPropagation();
        
        const existingMenu = document.querySelector('.context-menu');
        if (existingMenu) {
            existingMenu.remove();
        }

        const contextMenu = document.createElement('div');
        contextMenu.className = 'context-menu';
        contextMenu.innerHTML = `
            <div class="context-menu-item" onclick="window.home.viewDocument('${documentId}', '${category}')">
                <i class="fas fa-eye"></i> Lihat Detail
            </div>
            <div class="context-menu-item" onclick="window.home.downloadDocument('${documentId}', '${category}')">
                <i class="fas fa-download"></i> Download
            </div>
            <div class="context-menu-divider"></div>
            <div class="context-menu-item context-menu-danger" onclick="window.home.deleteDocument('${documentId}', '${category}')">
                <i class="fas fa-trash"></i> Hapus
            </div>
        `;

        const rect = event.target.getBoundingClientRect();
        contextMenu.style.cssText = `
            position: fixed;
            top: ${rect.bottom + 5}px;
            left: ${rect.left}px;
            z-index: 10000;
        `;

        document.body.appendChild(contextMenu);

        const closeMenu = (e) => {
            if (!contextMenu.contains(e.target)) {
                contextMenu.remove();
                document.removeEventListener('click', closeMenu);
            }
        };

        setTimeout(() => {
            document.addEventListener('click', closeMenu);
        }, 10);
    }

    /**
     * View document
     */
    viewDocument(documentId, category) {
        this.showNotification(`Membuka detail dokumen ${category}...`, 'info');
        // Implement view logic here
    }

    /**
     * Download document
     */
    downloadDocument(documentId, category) {
        this.showNotification('Memulai download...', 'info');
        
        const downloadUrl = `/ArsipKu/pages/download_document.php?id=${documentId}&category=${category}`;
        
        // Create temporary link and trigger download
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        this.showNotification('Download dimulai', 'success');
    }

    /**
     * Delete document
     */
    deleteDocument(documentId, category) {
        if (confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) {
            this.showNotification('Menghapus dokumen...', 'info');
            // Implement delete logic here
        }
    }

    /**
     * Search documents
     */
    searchDocuments(query) {
        console.log('Searching for:', query);
        
        const documentGrids = [
            document.getElementById('documentGrid'),
            document.getElementById('documentGridDokumenSaya')
        ];

        documentGrids.forEach(grid => {
            if (!grid) return;
            
            const cards = grid.querySelectorAll('.document-card-gd');
            
            if (!query.trim()) {
                cards.forEach(card => card.style.display = '');
                return;
            }

            cards.forEach(card => {
                const title = card.querySelector('.doc-title')?.textContent.toLowerCase() || '';
                const description = card.querySelector('.doc-description')?.textContent.toLowerCase() || '';
                const searchText = `${title} ${description}`;

                if (searchText.includes(query.toLowerCase())) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Also search in table sections
        this.searchInTables(query);
    }

    /**
     * Search in tables
     */
    searchInTables(query) {
        const tables = ['agenda-table', 'history-table', 'keluarga-table', 'arsip-vital-table', 'arsip-inactive-table'];
        
        tables.forEach(tableId => {
            const table = document.getElementById(tableId);
            if (!table) return;
            
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                if (row.querySelector('.loading') || row.querySelector('.empty-state')) return;
                
                const cells = row.querySelectorAll('td');
                let rowText = '';
                cells.forEach(cell => {
                    rowText += cell.textContent.toLowerCase() + ' ';
                });
                
                if (!query.trim() || rowText.includes(query.toLowerCase())) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    /**
     * Trigger file upload dialog
     */
    triggerFileUpload() {
        console.log('Triggering file upload...');
        if (this.fileInput) {
            this.fileInput.click();
        } else {
            console.error('File input not available');
            this.showNotification('Upload tidak tersedia saat ini', 'error');
        }
    }

    /**
     * Handle file selection
     */
    handleFileSelect(event) {
        console.log('File select event triggered');
        const files = Array.from(event.target.files);

        if (files.length === 0) {
            console.log('No files selected');
            return;
        }

        console.log(`Files selected: ${files.length}`);

        files.forEach((file, index) => {
            console.log(`${index + 1}. ${file.name} (${file.type}, ${this.formatFileSize(file.size)})`);
        });

        const validFiles = this.validateFiles(files);

        if (validFiles.length > 0) {
            this.processFiles(validFiles);
        } else {
            this.showNotification('Tidak ada file valid yang dapat diupload', 'warning');
        }
    }

    /**
     * Handle drag over event
     */
    handleDragOver(event) {
        event.preventDefault();
        event.stopPropagation();
        event.currentTarget.classList.add('dragover');
        event.dataTransfer.dropEffect = 'copy';
    }

    /**
     * Handle drag leave event
     */
    handleDragLeave(event) {
        event.preventDefault();
        event.stopPropagation();
        event.currentTarget.classList.remove('dragover');
    }

    /**
     * Handle drop event
     */
    handleDrop(event) {
        console.log('Drop event triggered');
        event.preventDefault();
        event.stopPropagation();
        event.currentTarget.classList.remove('dragover');

        const files = Array.from(event.dataTransfer.files);

        if (files.length > 0) {
            console.log(`Dropped files: ${files.length}`);
            const validFiles = this.validateFiles(files);
            if (validFiles.length > 0) {
                this.processFiles(validFiles);
            }
        }
    }

    /**
     * Handle window resize
     */
    handleResize() {
        // Optional: Handle responsive behavior
    }

    /**
     * Validate uploaded files
     */
    validateFiles(files) {
        const maxSize = 10 * 1024 * 1024; // 10MB
        const allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf', 'text/plain', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ];

        return files.filter(file => {
            if (file.size > maxSize) {
                this.showNotification(`File ${file.name} terlalu besar. Maksimal 10MB.`, 'warning');
                return false;
            }

            if (!allowedTypes.includes(file.type)) {
                this.showNotification(`Format file ${file.name} tidak didukung.`, 'warning');
                return false;
            }

            return true;
        });
    }

    /**
     * Process validated files
     */
    async processFiles(files) {
        console.log(`Processing ${files.length} files...`);

        files.forEach(file => {
            const fileObj = {
                id: Date.now() + Math.random(),
                name: file.name,
                type: file.type,
                size: file.size,
                uploadDate: new Date(),
                ownerAvatar: document.querySelector('.user-avatar img')?.src || 'https://via.placeholder.com/36/4285F4/FFFFFF?text=U',
                ownerName: document.getElementById('greetingText')?.dataset.fullName || 'Anda',
                file: file
            };

            this.addDocumentToGrid(fileObj);
            this.showNotification(`✅ ${file.name} berhasil diunggah!`, 'success');
        });

        this.updateStats();

        // Reset file input
        if (this.fileInput) {
            this.fileInput.value = '';
        }
    }

    /**
     * Add document to grid
     */
    addDocumentToGrid(file) {
        // Add to disarankan grid
        const disarankanGrid = document.getElementById('documentGrid');
        if (disarankanGrid) {
            const docCard = this.createDocumentCard(file);
            disarankanGrid.prepend(docCard);
        }

        // Add to dokumen-saya grid
        const dokumenSayaGrid = document.getElementById('documentGridDokumenSaya');
        if (dokumenSayaGrid) {
            const docCardDokumenSaya = this.createDocumentCard(file);
            dokumenSayaGrid.prepend(docCardDokumenSaya);
        }
    }

    /**
     * Update statistics
     */
    updateStats() {
        const totalDocsDisarankan = document.getElementById('documentGrid')?.querySelectorAll('.document-card-gd').length || 0;
        const totalDocsDokumenSaya = document.getElementById('documentGridDokumenSaya')?.querySelectorAll('.document-card-gd').length || 0;
        const totalDocs = Math.max(totalDocsDisarankan, totalDocsDokumenSaya);

        this.animateNumber('totalDocs', totalDocs);
        this.animateNumber('todayUploads', totalDocs);
        this.animateNumber('activeUsers', 1);

        let totalSize = 0;
        document.querySelectorAll('.document-card-gd').forEach(card => {
            totalSize += 1;
        });
        document.getElementById('totalSize').textContent = `${totalSize} MB`;
    }

    /**
     * Format date for display
     */
    formatDate(date) {
        if (!(date instanceof Date)) {
            date = new Date(date);
        }

        const now = new Date();
        const diff = now - date;
        const minutes = Math.floor(diff / (1000 * 60));
        const hours = Math.floor(diff / (1000 * 60 * 60));
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));

        if (minutes < 1) return 'Baru saja';
        if (minutes < 60) return `${minutes} menit lalu`;
        if (hours < 24) return `${hours} jam lalu`;
        if (days < 7) return `${days} hari lalu`;

        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    }

    /**
     * Format file size
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /**
     * Show notification to user
     */
    showNotification(message, type = 'info') {
        document.querySelectorAll('.notification').forEach(n => n.remove());

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;

        if (!document.getElementById('notification-styles')) {
            const style = document.createElement('style');
            style.id = 'notification-styles';
            style.textContent = `
                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 12px 20px;
                    border-radius: 8px;
                    color: white;
                    font-weight: 500;
                    z-index: 10000;
                    transform: translateX(100%);
                    animation: slideIn 0.3s ease-out forwards;
                    min-width: 250px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                }
                .notification-success { background: #4caf50; }
                .notification-error { background: #f44336; }
                .notification-warning { background: #ff9800; }
                .notification-info { background: #2196f3; }
                @keyframes slideIn {
                    to { transform: translateX(0); }
                }
            `;
            document.head.appendChild(style);
        }

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    /**
     * Animate number for stats
     */
    animateNumber(elementId, finalNumber) {
        const element = document.getElementById(elementId);
        if (!element) return;

        const startNumber = parseInt(element.textContent) || 0;
        const duration = 1000;
        const startTime = performance.now();

        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const currentNumber = Math.floor(startNumber + (finalNumber - startNumber) * progress);

            element.textContent = currentNumber;

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        requestAnimationFrame(animate);
    }

    /**
     * Utility: Debounce function
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Load data methods for different sections
    loadAgendaData() {
        const tbody = document.querySelector('#agenda-table tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '<tr><td colspan="9" class="loading"><i class="fas fa-spinner fa-spin"></i><br>Memuat data agenda...</td></tr>';

        fetch('/ArsipKu/pages/get_agenda.php')
            .then(res => res.json())
            .then(response => {
                if (response.status === 'error') {
                    tbody.innerHTML = `<tr><td colspan="9" class="empty-state">Error: ${response.message}</td></tr>`;
                    return;
                }

                const data = response.data;
                if (!data || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="9" class="empty-state">Tidak ada data agenda</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                data.forEach((agenda, index) => {
                    let statusClass = 'status-pending';
                    if (agenda.status === 'in_progress') {
                        statusClass = 'status-active';
                    } else if (agenda.status === 'complete') {
                        statusClass = 'status-inactive';
                    }

                    const priorityClass = agenda.priority === 'high' ? 'priority-high' :
                                          agenda.priority === 'medium' ? 'priority-medium' :
                                          'priority-low';

                    const formattedStartDate = agenda.start_date ? new Date(agenda.start_date).toISOString().slice(0, 16) : '';
                    const formattedEndDate = agenda.end_date ? new Date(agenda.end_date).toISOString().slice(0, 16) : '';

                    tbody.innerHTML += `
                        <tr>
                            <td>${index + 1}</td>
                            <td><div class="table-avatar">${agenda.username ? agenda.username.slice(0, 2).toUpperCase() : 'US'}</div></td>
                            <td>${agenda.title}</td>
                            <td>${agenda.description}</td>
                            <td>${formattedStartDate.replace('T', ' ')}</td>
                            <td>${formattedEndDate.replace('T', ' ')}</td>
                            <td>${agenda.location || '-'}</td>
                            <td><span class="status-badge ${statusClass}">${agenda.status}</span></td>
                            <td><span class="priority-badge ${priorityClass}">${agenda.priority}</span></td>
                        </tr>
                    `;
                });
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="9" class="empty-state">Gagal memuat data: ${err.message}</td></tr>`;
                console.error("Fetch Error:", err);
            });
    }

    loadHistoryData() {
        const tbody = document.querySelector('#history-table tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '<tr><td colspan="5" class="loading"><i class="fas fa-spinner fa-spin"></i><br>Memuat riwayat agenda...</td></tr>';

        fetch('/ArsipKu/pages/get_history.php')
            .then(res => res.json())
            .then(response => {
                if (response.status === 'error') {
                    tbody.innerHTML = `<tr><td colspan="5" class="empty-state">Error: ${response.message}</td></tr>`;
                    return;
                }

                const data = response.data;
                if (!data || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="empty-state">Tidak ada riwayat agenda</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                data.forEach((agenda, index) => {
                    let statusClass = 'status-inactive';
                    if (agenda.status === 'complete') {
                        statusClass = 'status-inactive';
                    } else if (agenda.status === 'cancelled') {
                         statusClass = 'status-inactive';
                    }

                    tbody.innerHTML += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>
                                <div class="table-avatar">${agenda.username ? agenda.username.slice(0, 2).toUpperCase() : 'US'}</div>
                            </td>
                            <td>${agenda.title}</td>
                            <td>${agenda.end_date || agenda.start_date}</td>
                            <td><span class="status-badge ${statusClass}">${agenda.status}</span></td>
                        </tr>
                    `;
                });
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="5" class="empty-state">Gagal memuat riwayat data: ${err.message}</td></tr>`;
                console.error("Fetch Error:", err);
            });
    }

    loadKeluargaData() {
        const tbody = document.querySelector('#keluarga-table tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '<tr><td colspan="7" class="loading"><i class="fas fa-spinner fa-spin"></i><br>Memuat data dokumen keluarga...</td></tr>';

        fetch('/ArsipKu/pages/get_keluarga_dokumen.php')
            .then(res => res.json())
            .then(response => {
                if (response.status === 'error') {
                    tbody.innerHTML = `<tr><td colspan="7" class="empty-state">Error: ${response.message}</td></tr>';
                    return;
                }

                const data = response.data;
                if (!data || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="empty-state">Tidak ada data dokumen keluarga</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                data.forEach((item, index) => {
                    let statusClass = item.status === 'aktif' ? 'status-active' : 'status-inactive';
                    const fileLink = item.dokumen ? `<a href="/ArsipKu/uploads/keluarga/${item.dokumen}" target="_blank"><i class="fas fa-file"></i> Lihat Dokumen</a>` : 'Tidak Ada Dokumen';

                    tbody.innerHTML += `
                        <tr>
                            <td>${item.id}</td>
                            <td>${item.nama_dokumen}</td>
                            <td>${fileLink}</td>
                            <td>${item.deskripsi_dokumen || '-'}</td>
                            <td>${item.tanggal_dibuat}</td>
                            <td><span class="status-badge ${statusClass}">${item.status}</span></td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="window.home.viewDocument('${item.id}', 'keluarga')"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                    `;
                });
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="7" class="empty-state">Gagal memuat data dokumen keluarga: ${err.message}</td></tr>`;
                console.error("Fetch Error:", err);
            });
    }

    loadArsipVitalData() {
        const tbody = document.querySelector('#arsip-vital-table tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '<tr><td colspan="8" class="loading"><i class="fas fa-spinner fa-spin"></i><br>Memuat data arsip vital...</td></tr>';

        fetch('/ArsipKu/pages/get_arsip_vital.php')
            .then(res => res.json())
            .then(response => {
                if (response.status === 'error') {
                    tbody.innerHTML = `<tr><td colspan="8" class="empty-state">Error: ${response.message}</td></tr>`;
                    return;
                }

                const data = response.data;
                if (!data || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="empty-state">Tidak ada data arsip vital</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                data.forEach((item, index) => {
                    let statusClass = 'status-info';
                    if (item.status === 'aktif') {
                        statusClass = 'status-active';
                    } else if (item.status === 'inaktif') {
                        statusClass = 'status-inactive';
                    }

                    const fileLink = item.gambar_surat ? `<a href="/ArsipKu/uploads/arsip_vital/${item.gambar_surat}" target="_blank"><i class="fas fa-file-alt"></i> Lihat File</a>` : 'Tidak Ada File';
                    const lamaSurat = item.tahun_dibuat ? (new Date().getFullYear() - item.tahun_dibuat) : '-';

                    tbody.innerHTML += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.nomor_surat}</td>
                            <td>${item.berita_acara_surat || '-'}</td>
                            <td>${fileLink}</td>
                            <td><span class="status-badge ${statusClass}">${item.status}</span></td>
                            <td>${item.tahun_dibuat}</td>
                            <td>${lamaSurat} tahun</td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="window.home.viewDocument('${item.id}', 'arsip_vital')"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                    `;
                });
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="8" class="empty-state">Gagal memuat data arsip vital: ${err.message}</td></tr>`;
                console.error("Fetch Error:", err);
            });
    }

    loadArsipInactiveData() {
        const tbody = document.querySelector('#arsip-inactive-table tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '<tr><td colspan="8" class="loading"><i class="fas fa-spinner fa-spin"></i><br>Memuat data arsip inactive...</td></tr>';

        fetch('/ArsipKu/pages/get_arsip_inactive.php')
            .then(res => res.json())
            .then(response => {
                if (response.status === 'error') {
                    tbody.innerHTML = `<tr><td colspan="8" class="empty-state">Error: ${response.message}</td></tr>`;
                    return;
                }

                const data = response.data;
                if (!data || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="empty-state">Tidak ada data arsip inactive</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                data.forEach((item, index) => {
                    let statusClass = 'status-info';
                    if (item.status === 'aktif') {
                        statusClass = 'status-active';
                    } else if (item.status === 'inaktif') {
                        statusClass = 'status-inactive';
                    }

                    const fileLink = item.gambar_surat ? `<a href="/ArsipKu/uploads/arsip_inactive/${item.gambar_surat}" target="_blank"><i class="fas fa-file-alt"></i> Lihat File</a>` : 'Tidak Ada File';
                    const lamaSurat = item.tahun_dibuat ? (new Date().getFullYear() - item.tahun_dibuat) : '-';

                    tbody.innerHTML += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.nomor_surat}</td>
                            <td>${item.berita_acara_surat || '-'}</td>
                            <td>${fileLink}</td>
                            <td><span class="status-badge ${statusClass}">${item.status}</span></td>
                            <td>${item.tahun_dibuat}</td>
                            <td>${lamaSurat} tahun</td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="window.home.viewDocument('${item.id}', 'arsip_inactive')"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                    `;
                });
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="8" class="empty-state">Gagal memuat data arsip inactive: ${err.message}</td></tr>`;
                console.error("Fetch Error:", err);
            });
    }
}

// Global functions for compatibility
window.showSection = function(event, sectionId) {
    if (window.home) {
        const targetElement = event.currentTarget;
        if (targetElement.classList.contains('navbar-item')) {
            window.home.handleNavbarClick(event);
        } else if (targetElement.classList.contains('tab-button')) {
            window.home.handleTabClick(event);
        }
    }
};

window.triggerFileUpload = function() {
    if (window.home) {
        window.home.triggerFileUpload();
    }
};

window.handleFileSelect = function(event) {
    if (window.home) {
        window.home.handleFileSelect(event);
    }
};

// Initialize home
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM Content Loaded, initializing home...');
    const home = new HomeManager();

    setTimeout(() => {
        home.init();
        window.home = home;

        const defaultNavItem = document.querySelector('.main-navbar .navbar-item[data-target-section="disarankan-content"]');
        if (defaultNavItem) {
            defaultNavItem.click();
        }
    }, 100);
});

if (typeof module !== 'undefined' && module.exports) {
    module.exports = HomeManager;
}
                }
            }
            )
    }
}