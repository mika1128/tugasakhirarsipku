// Public JavaScript Functions

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