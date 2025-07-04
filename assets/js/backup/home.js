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
        // Tidak perlu binding metode CRUD agenda jika hanya view
        // Metode agenda/riwayat yang tersisa (loadAgendaData, loadHistoryData) akan diakses langsung
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

        // Tidak perlu cache elemen modal jika hanya view
        // this.agendaModal = document.getElementById('agendaModal');
        // this.agendaForm = document.getElementById('agendaForm');
        // this.modalTitle = document.getElementById('modalTitle');
        // this.closeButton = document.querySelector('.modal .close-button');


        console.log('Elements cached:', {
            tabButtons: this.tabButtons.length,
            contentSections: this.contentSections.length,
            fileInput: !!this.fileInput,
            uploadArea: !!this.uploadArea
            // agendaModal: !!this.agendaModal // Dihapus untuk view only
        });
    }

    /**
     * Initialize default state
     */
    initializeDefaultState() {
        const defaultSection = 'disarankan-content';
        this.setActiveSection(defaultSection);
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
        } else {
            console.error('File input not found!');
        }

        // Event untuk drag & drop
        if (this.uploadArea) {
            this.uploadArea.addEventListener('click', () => this.triggerFileUpload());
            this.uploadArea.addEventListener('dragover', this.handleDragOver);
            this.uploadArea.addEventListener('dragleave', this.handleDragLeave);
            this.uploadArea.addEventListener('drop', this.handleDrop);
            console.log('Upload area event listeners attached');
        } else {
            console.error('Upload area not found!');
        }

        // Event untuk floating action button
        const fabButton = document.querySelector('.floating-action-button');
        if (fabButton) {
            fabButton.addEventListener('click', () => this.triggerFileUpload());
            console.log('FAB event listener attached');
        }

        // Event untuk search input (debounced)
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce((e) => this.searchDocuments(e.target.value), 300));
        }

        // Tidak ada event listeners untuk modal/agenda jika hanya view
        // if (this.closeButton) { // Dihapus
        //     this.closeButton.addEventListener('click', this.closeModal);
        // }
        // window.addEventListener('click', (event) => { // Dihapus
        //     if (event.target === this.agendaModal) {
        //         this.closeModal();
        //     }
        // });
        // if (this.agendaForm) { // Dihapus
        //     this.agendaForm.addEventListener('submit', (e) => this.handleAgendaFormSubmit(e));
        // }
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

            // Load data for agenda/history sections
            if (sectionId === 'agenda') {
                this.loadAgendaData();
            } else if (sectionId === 'riwayat') {
                this.loadHistoryData();
            }
        }
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

        // Hapus dokumen dummy saat ada unggahan baru
        document.querySelectorAll('.document-card-gd[data-dummy="true"]').forEach(doc => doc.remove());

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
     * Helper function to create a document card DOM element
     */
    createDocumentCard(file) {
        const docCard = document.createElement('div');
        docCard.className = 'document-card-gd';
        docCard.dataset.id = file.id;
        if (!file.file) {
            docCard.dataset.dummy = 'true';
        }

        let thumbnailUrl = 'https://via.placeholder.com/200x120/3c4043/9aa0a6?text=' + encodeURIComponent(this.getFileType(file.type));

        if (file.type.includes('image') && file.file instanceof File) {
            thumbnailUrl = URL.createObjectURL(file.file);
        } else if (file.type.includes('image') && file.thumbnail) {
            thumbnailUrl = file.thumbnail;
        }

        docCard.innerHTML = `
            <div class="doc-thumbnail">
                <img src="${thumbnailUrl}" alt="${file.name}" onerror="this.src='https://via.placeholder.com/200x120/3c4043/9aa0a6?text=${encodeURIComponent(this.getFileType(file.type))}'">
            </div>
            <div class="doc-info">
                <h4 class="doc-title">${file.name}</h4>
                <div class="doc-meta">
                    <img src="${file.ownerAvatar || 'https://via.placeholder.com/24/F0F0F0/000000?text=U'}" alt="${file.ownerName || 'User'}" class="doc-owner-avatar">
                    <span class="doc-owner-name">${file.ownerName || 'Anda'}</span>
                    <span class="doc-date">${this.formatDate(file.uploadDate)}</span>
                </div>
            </div>
            <button class="doc-options" onclick="showDocumentOptions(event, '${file.id}')"><i class="fas fa-ellipsis-v"></i></button>
        `;

        // Add click handler to open document
        docCard.addEventListener('click', () => this.openDocument(file.id));

        return docCard;
    }

    /**
     * Update statistics
     */
    updateStats() {
        const totalDocsDisarankan = document.getElementById('documentGrid')?.querySelectorAll('.document-card-gd').length || 0;
        const totalDocsDokumenSaya = document.getElementById('documentGridDokumenSaya')?.querySelectorAll('.document-card-gd').length || 0;
        const totalDocs = Math.max(totalDocsDisarankan, totalDocsDokumenSaya); // Avoid double counting

        this.animateNumber('totalDocs', totalDocs);
        this.animateNumber('todayUploads', totalDocs); // For demo, all uploads are "today"
        this.animateNumber('activeUsers', 1);

        // Calculate total size (simplified)
        let totalSize = 0;
        document.querySelectorAll('.document-card-gd').forEach(card => {
            // This is a simplified calculation
            totalSize += 1; // 1MB per document for demo
        });
        document.getElementById('totalSize').textContent = `${totalSize} MB`;
    }

    /**
     * Get file type string
     */
    getFileType(type) {
        if (type.includes('pdf')) return 'PDF';
        if (type.includes('word') || type.includes('document')) return 'DOC';
        if (type.includes('sheet') || type.includes('excel')) return 'XLS';
        if (type.includes('presentation') || type.includes('powerpoint')) return 'PPT';
        if (type.includes('image')) return 'IMG';
        if (type.includes('text')) return 'TXT';
        return 'FILE';
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
        // Remove existing notifications
        document.querySelectorAll('.notification').forEach(n => n.remove());

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;

        // Add styles if not exists (ini seharusnya ada di CSS, tapi untuk demo cepat bisa di sini)
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

        // Auto remove after 3 seconds
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

    /**
     * Search functionality
     */
    searchDocuments(query) {
        console.log('Searching for:', query);
        const cards = document.querySelectorAll('.document-card-gd');

        if (!query.trim()) {
            cards.forEach(card => card.style.display = '');
            return;
        }

        cards.forEach(card => {
            const title = card.querySelector('.doc-title')?.textContent.toLowerCase() || '';
            const owner = card.querySelector('.doc-owner-name')?.textContent.toLowerCase() || '';
            const searchText = `${title} ${owner}`;

            if (searchText.includes(query.toLowerCase())) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    /**
     * Open document
     */
    openDocument(documentId) {
        console.log('Opening document:', documentId);
        this.showNotification('Membuka dokumen...', 'info');
        // Implement document opening logic here
    }

    // ============================================
    // FUNGSI AGENDA/RIWAYAT (dari admin.js - DIMODIFIKASI UNTUK VIEW ONLY)
    // ============================================

    loadAgendaData() {
        const tbody = document.querySelector('#agenda-table tbody');
        tbody.innerHTML = '<tr><td colspan="9" class="loading"><i class="fas fa-spinner fa-spin"></i><br>Memuat data agenda...</td></tr>';

        fetch('/ArsipKu/pages/get_agenda.php')
            .then(res => res.json())
            .then(response => {
                if (response.status === 'error') {
                    tbody.innerHTML = `<tr><td colspan="9" class="empty-state">Error: ${response.message}</td></tr>`;
                    console.error("Server Error:", response.error);
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
        tbody.innerHTML = '<tr><td colspan="5" class="loading"><i class="fas fa-spinner fa-spin"></i><br>Memuat riwayat agenda...</td></tr>';

        fetch('/ArsipKu/pages/get_history.php')
            .then(res => res.json())
            .then(response => {
                if (response.status === 'error') {
                    tbody.innerHTML = `<tr><td colspan="5" class="empty-state">Error: ${response.message}</td></tr>`;
                    console.error("Server Error:", response.error);
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

    // Semua fungsi CRUD Agenda (showAddAgendaModal, closeModal, editAgenda, deleteAgenda,
    // deleteAgendaFromHistory, refreshAgendaData, viewDetails, archiveAgenda,
    // archiveCompletedAgenda, deleteOldAgenda, handleAgendaFormSubmit)
    // dihapus dari sini karena ini adalah mode view only.

    // Jika Anda ingin menjaga fungsi-fungsi ini tetapi membuatnya tidak aktif,
    // Anda bisa mengosongkan isinya atau menampilkan notifikasi bahwa fitur tidak tersedia.
}

// ============================================
// FUNGSI GLOBAL UNTUK KOMPATIBILITAS HTML
// (Arahkan ke instance HomeManager)
// ============================================

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
    console.log('Global triggerFileUpload called');
    if (window.home) {
        window.home.triggerFileUpload();
    } else {
        console.error('Home not initialized');
    }
};

window.handleFileSelect = function(event) {
    if (window.home) {
        window.home.handleFileSelect(event);
    }
};

window.handleDragOver = function(event) {
    if (window.home) {
        window.home.handleDragOver(event);
    }
};

window.handleDragLeave = function(event) {
    if (window.home) {
        window.home.handleDragLeave(event);
    }
};

window.handleDrop = function(event) {
    if (window.home) {
        window.home.handleDrop(event);
    }
};

window.showDocumentOptions = function(event, documentId) {
    event.stopPropagation();
    console.log('Document options for:', documentId);

    const existingMenu = document.querySelector('.context-menu');
    if (existingMenu) {
        existingMenu.remove();
    }

    const contextMenu = document.createElement('div');
    contextMenu.className = 'context-menu';
    contextMenu.innerHTML = `
        <div class="context-menu-item" onclick="window.home.downloadDocument('${documentId}')">
            <span>📥</span> Download
        </div>
        <div class="context-menu-item" onclick="window.home.shareDocument('${documentId}')">
            <span>🔗</span> Share
        </div>
        <div class="context-menu-item" onclick="window.home.renameDocument('${documentId}')">
            <span>✏️</span> Rename
        </div>
        <div class="context-menu-divider"></div>
        <div class="context-menu-item context-menu-danger" onclick="window.home.deleteDocument('${documentId}')">
            <span>🗑️</span> Delete
        </div>
    `;

    const rect = event.target.getBoundingClientRect();
    contextMenu.style.cssText = `
        position: fixed;
        top: ${rect.bottom + 5}px;
        left: ${rect.left}px;
        z-index: 10000;
        background: #2f3032;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        padding: 8px 0;
        min-width: 180px;
    `;

    if (!document.getElementById('context-menu-styles')) {
        const style = document.createElement('style');
        style.id = 'context-menu-styles';
        style.textContent = `
            .context-menu-item {
                padding: 10px 16px;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 12px;
                transition: background-color 0.2s;
                color: #e8eaed;
                font-size: 14px;
            }
            .context-menu-item:hover {
                background-color: rgba(255, 255, 255, 0.08);
            }
            .context-menu-danger {
                color: #f44336;
            }
            .context-menu-danger:hover {
                background-color: rgba(244, 67, 54, 0.1);
            }
            .context-menu-divider {
                height: 1px;
                background-color: rgba(255, 255, 255, 0.1);
                margin: 4px 0;
            }
        `;
        document.head.appendChild(style);
    }

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
};

window.downloadDocument = function(documentId) { if (window.home) { window.home.downloadDocument(documentId); } };
window.shareDocument = function(documentId) { if (window.home) { window.home.shareDocument(documentId); } };
window.renameDocument = function(documentId) { if (window.home) { window.home.renameDocument(documentId); } };
window.deleteDocument = function(documentId) { if (window.home) { window.home.deleteDocument(documentId); } };
window.openDocument = function(documentId) { if (window.home) { window.home.openDocument(documentId); } };


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