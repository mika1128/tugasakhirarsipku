// Public JavaScript Functions

// Enhanced search functionality for all document types
function searchAllDocuments(query) {
    if (!query) return;
    
    // Search in different document types
    const searchPromises = [
        searchKeluargaDocuments(query),
        searchArsipVital(query),
        searchArsipInactive(query),
        searchAgenda(query)
    ];
    
    Promise.all(searchPromises).then(results => {
        displaySearchResults(results, query);
    }).catch(error => {
        console.error('Search error:', error);
        showAlert('Terjadi kesalahan saat mencari. Silakan coba lagi.', 'error');
    });
}

// Search in keluarga documents
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

// Search in arsip vital
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

// Search in arsip inactive
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

// Search in agenda
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

// Display search results
function displaySearchResults(results, query) {
    const allResults = results.flat();
    
    if (allResults.length === 0) {
        showAlert(`Tidak ditemukan hasil untuk "${query}"`, 'info');
        return;
    }
    
    // Create search results modal
    const modal = document.createElement('div');
    modal.className = 'search-modal';
    modal.innerHTML = `
        <div class="search-modal-content">
            <div class="search-modal-header">
                <h3>Hasil Pencarian: "${query}"</h3>
                <button class="close-search" onclick="this.closest('.search-modal').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="search-results">
                ${allResults.map(result => `
                    <div class="search-result-item" data-type="${result.type}">
                        <div class="result-type">${getTypeLabel(result.type)}</div>
                        <h4>${result.title}</h4>
                        <p>${result.description || 'Tidak ada deskripsi'}</p>
                        <div class="result-meta">
                            <span class="status-badge">${result.status || 'N/A'}</span>
                            ${result.tanggal_dibuat ? `<span class="result-date">${result.tanggal_dibuat}</span>` : ''}
                            ${result.tahun_dibuat ? `<span class="result-date">${result.tahun_dibuat}</span>` : ''}
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
    
    // Add modal styles
    if (!document.getElementById('search-modal-styles')) {
        const style = document.createElement('style');
        style.id = 'search-modal-styles';
        style.textContent = `
            .search-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
            }
            .search-modal-content {
                background: #2f3032;
                border-radius: 15px;
                max-width: 800px;
                width: 90%;
                max-height: 80vh;
                overflow: hidden;
                display: flex;
                flex-direction: column;
            }
            .search-modal-header {
                padding: 20px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .search-modal-header h3 {
                color: #e8eaed;
                margin: 0;
            }
            .close-search {
                background: none;
                border: none;
                color: #9aa0a6;
                font-size: 20px;
                cursor: pointer;
                padding: 5px;
            }
            .close-search:hover {
                color: #e8eaed;
            }
            .search-results {
                padding: 20px;
                overflow-y: auto;
                max-height: 60vh;
            }
            .search-result-item {
                background: #202124;
                border-radius: 10px;
                padding: 15px;
                margin-bottom: 15px;
                border: 1px solid rgba(255, 255, 255, 0.05);
            }
            .result-type {
                background: #4285f4;
                color: white;
                padding: 4px 8px;
                border-radius: 12px;
                font-size: 12px;
                display: inline-block;
                margin-bottom: 10px;
            }
            .search-result-item h4 {
                color: #e8eaed;
                margin-bottom: 8px;
            }
            .search-result-item p {
                color: #9aa0a6;
                margin-bottom: 10px;
            }
            .result-meta {
                display: flex;
                gap: 10px;
                align-items: center;
            }
            .result-date {
                color: #8ab4f8;
                font-size: 12px;
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(modal);
    
    // Close modal when clicking outside
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.remove();
        }
    });
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

// Global function to submit berkas
async function submitBerkas(form) {
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
    
    try {
        const formData = new FormData(form);
        
        const response = await fetch('submit-berkas.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showAlert(result.message, 'success');
            form.reset();
            document.getElementById('selectedFile').innerHTML = '';
        } else {
            showAlert(result.message, 'error');
        }
        
    } catch (error) {
        console.error('Error submitting berkas:', error);
        showAlert('Terjadi kesalahan saat mengirim pengajuan. Silakan coba lagi.', 'error');
    } finally {
        // Reset button
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    }
}

// Function to show alerts
function showAlert(message, type) {
    // Remove existing alerts
    document.querySelectorAll('.alert').forEach(alert => alert.remove());
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        ${message}
    `;
    
    // Insert at the top of alert container or main container
    const alertContainer = document.getElementById('alertContainer');
    if (alertContainer) {
        alertContainer.appendChild(alertDiv);
    } else {
        const mainContainer = document.querySelector('.main-container');
        mainContainer.insertBefore(alertDiv, mainContainer.firstChild);
    }
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
    
    // Scroll to alert
    alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// File upload drag and drop functionality
document.addEventListener('DOMContentLoaded', function() {
    const uploadAreas = document.querySelectorAll('.file-upload-area');
    
    uploadAreas.forEach(uploadArea => {
        const fileInput = uploadArea.nextElementSibling;
        if (!fileInput || fileInput.type !== 'file') return;
        
        // Drag and drop events
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                updateFileDisplay(fileInput);
            }
        });
    });
});

// Function to update file display
function updateFileDisplay(fileInput) {
    const file = fileInput.files[0];
    const selectedFileDiv = document.getElementById('selectedFile');
    
    if (file && selectedFileDiv) {
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        selectedFileDiv.innerHTML = `
            <i class="fas fa-file"></i> 
            ${file.name} 
            <span style="color: #666;">(${fileSize} MB)</span>
        `;
    }
}

// Form validation
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = '#f44336';
            isValid = false;
        } else {
            field.style.borderColor = '#e1e8ed';
        }
    });
    
    // Validate email
    const emailField = form.querySelector('[type="email"]');
    if (emailField && emailField.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailField.value)) {
            emailField.style.borderColor = '#f44336';
            showAlert('Format email tidak valid.', 'error');
            isValid = false;
        }
    }
    
    // Validate NIK (16 digits)
    const nikField = form.querySelector('[name="nik"]');
    if (nikField && nikField.value) {
        const nikRegex = /^[0-9]{16}$/;
        if (!nikRegex.test(nikField.value)) {
            nikField.style.borderColor = '#f44336';
            showAlert('NIK harus terdiri dari 16 digit angka.', 'error');
            isValid = false;
        }
    }
    
    return isValid;
}

// Smooth scrolling for anchor links
document.addEventListener('DOMContentLoaded', function() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Auto-resize textarea
document.addEventListener('DOMContentLoaded', function() {
    // Global search functionality
    const globalSearch = document.getElementById('globalSearch');
    if (globalSearch) {
        globalSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    searchAllDocuments(query);
                }
            }
        });
    }
    
    const textareas = document.querySelectorAll('textarea');
    
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });
});

// Form character counting
document.addEventListener('DOMContentLoaded', function() {
    const textInputs = document.querySelectorAll('input[maxlength], textarea[maxlength]');
    
    textInputs.forEach(input => {
        const maxLength = input.getAttribute('maxlength');
        if (maxLength) {
            const counter = document.createElement('small');
            counter.style.cssText = 'display: block; text-align: right; color: #666; margin-top: 5px;';
            input.parentNode.appendChild(counter);
            
            function updateCounter() {
                const remaining = maxLength - input.value.length;
                counter.textContent = `${input.value.length}/${maxLength} karakter`;
                counter.style.color = remaining < 10 ? '#f44336' : '#666';
            }
            
            input.addEventListener('input', updateCounter);
            updateCounter();
        }
    });
});

// Loading overlay
function showLoadingOverlay(message = 'Memproses...') {
    const overlay = document.createElement('div');
    overlay.id = 'loadingOverlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        color: white;
        font-size: 1.2rem;
    `;
    overlay.innerHTML = `
        <div style="text-align: center;">
            <i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i>
            <div>${message}</div>
        </div>
    `;
    
    document.body.appendChild(overlay);
}

function hideLoadingOverlay() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.remove();
    }
}

// Print functionality
function printPage() {
    window.print();
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showAlert('Teks berhasil disalin ke clipboard!', 'success');
    }).catch(() => {
        showAlert('Gagal menyalin teks.', 'error');
    });
}

// Back to top button
document.addEventListener('DOMContentLoaded', function() {
    const backToTopButton = document.createElement('button');
    backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopButton.style.cssText = `
        position: fixed;
        bottom: 20px;
        left: 20px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #667eea;
        color: white;
        border: none;
        cursor: pointer;
        display: none;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
    `;
    
    backToTopButton.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    
    document.body.appendChild(backToTopButton);
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTopButton.style.display = 'block';
        } else {
            backToTopButton.style.display = 'none';
        }
    });
});

// Auto-save form data (for longer forms)
function autoSaveFormData(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        // Load saved data
        const savedValue = localStorage.getItem(`autosave_${formId}_${input.name}`);
        if (savedValue && !input.value) {
            input.value = savedValue;
        }
        
        // Save on change
        input.addEventListener('input', () => {
            localStorage.setItem(`autosave_${formId}_${input.name}`, input.value);
        });
    });
    
    // Clear saved data on successful submit
    form.addEventListener('submit', () => {
        inputs.forEach(input => {
            localStorage.removeItem(`autosave_${formId}_${input.name}`);
        });
    });
}

// Mobile responsive navigation
document.addEventListener('DOMContentLoaded', function() {
    const navMenu = document.querySelector('.nav-menu');
    const headerContent = document.querySelector('.header-content');
    
    if (window.innerWidth <= 768 && navMenu) {
        const menuToggle = document.createElement('button');
        menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
        menuToggle.style.cssText = `
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #333;
            cursor: pointer;
            display: block;
        `;
        
        menuToggle.addEventListener('click', () => {
            navMenu.style.display = navMenu.style.display === 'flex' ? 'none' : 'flex';
            navMenu.style.flexDirection = 'column';
            navMenu.style.position = 'absolute';
            navMenu.style.top = '100%';
            navMenu.style.left = '0';
            navMenu.style.right = '0';
            navMenu.style.background = 'white';
            navMenu.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            navMenu.style.padding = '1rem';
        });
        
        headerContent.appendChild(menuToggle);
    }
});