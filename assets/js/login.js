/**
 * Script untuk halaman login
 * File: assets/js/script.js
 */

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('form');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const submitButton = document.querySelector('button[type="submit"]');
    const errorMessage = document.querySelector('.error-message');

    // Fokus otomatis ke field username saat halaman dimuat
    if (usernameInput) {
        usernameInput.focus();
    }

    // Validasi form sebelum submit
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = usernameInput.value.trim();
            const password = passwordInput.value.trim();

            // Hapus pesan error sebelumnya
            hideErrorMessage();

            // Validasi input kosong
            if (!username || !password) {
                e.preventDefault();
                showErrorMessage('Username dan password harus diisi!');
                return false;
            }

            // Validasi panjang minimum
            if (username.length < 3) {
                e.preventDefault();
                showErrorMessage('Username minimal 3 karakter!');
                return false;
            }

            if (password.length < 6) {
                e.preventDefault();
                showErrorMessage('Password minimal 6 karakter!');
                return false;
            }

            // Tampilkan loading state
            showLoadingState();
        });
    }

    // Event listener untuk input fields
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            this.value = this.value.trim();
            hideErrorMessage();
        });

        // Enter key navigation
        usernameInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                passwordInput.focus();
            }
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            hideErrorMessage();
        });

        // Enter key submit
        passwordInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                loginForm.submit();
            }
        });
    }

    // Toggle password visibility
    addPasswordToggle();

    // Remember me functionality
    handleRememberMe();

    // Auto-hide error message after 5 seconds
    if (errorMessage && errorMessage.textContent.trim()) {
        setTimeout(function() {
            hideErrorMessage();
        }, 5000);
    }

    /**
     * Tampilkan pesan error
     */
    function showErrorMessage(message) {
        let errorDiv = document.querySelector('.error-message');
        
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            loginForm.insertBefore(errorDiv, loginForm.firstChild);
        }
        
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    /**
     * Sembunyikan pesan error
     */
    function hideErrorMessage() {
        const errorDiv = document.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
    }

    /**
     * Tampilkan loading state saat form disubmit
     */
    function showLoadingState() {
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Sedang Login...';
            submitButton.style.cursor = 'not-allowed';
            submitButton.style.opacity = '0.7';
        }
    }

    /**
     * Reset loading state
     */
    function resetLoadingState() {
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = 'Login';
            submitButton.style.cursor = 'pointer';
            submitButton.style.opacity = '1';
        }
    }

    /**
     * Tambahkan toggle untuk show/hide password
     */
    function addPasswordToggle() {
        const passwordGroup = passwordInput.closest('.form-group');
        
        // Buat container untuk input dan toggle
        const inputContainer = document.createElement('div');
        inputContainer.style.position = 'relative';
        
        // Pindahkan input ke dalam container
        passwordInput.parentNode.insertBefore(inputContainer, passwordInput);
        inputContainer.appendChild(passwordInput);
        
        // Buat toggle button
        const toggleButton = document.createElement('button');
        toggleButton.type = 'button';
        toggleButton.innerHTML = '👁️';
        toggleButton.style.cssText = `
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        `;
        
        // Tambahkan padding kanan ke input
        passwordInput.style.paddingRight = '40px';
        
        inputContainer.appendChild(toggleButton);
        
        // Event listener untuk toggle
        toggleButton.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.innerHTML = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleButton.innerHTML = '👁️';
            }
        });
    }

    /**
     * Handle remember me functionality
     */
    function handleRememberMe() {
        const rememberCheckbox = document.getElementById('remember');
        
        // Load saved username jika ada
        const savedUsername = localStorage.getItem('rememberedUsername');
        if (savedUsername && usernameInput) {
            usernameInput.value = savedUsername;
            if (rememberCheckbox) {
                rememberCheckbox.checked = true;
            }
        }
        
        // Save/remove username berdasarkan checkbox
        if (loginForm) {
            loginForm.addEventListener('submit', function() {
                if (rememberCheckbox && rememberCheckbox.checked) {
                    localStorage.setItem('rememberedUsername', usernameInput.value.trim());
                } else {
                    localStorage.removeItem('rememberedUsername');
                }
            });
        }
    }

    /**
     * Handle caps lock detection
     */
    function handleCapsLock() {
        let capsLockWarning = null;
        
        passwordInput.addEventListener('keypress', function(e) {
            const char = String.fromCharCode(e.which);
            const isCapsLock = char.toUpperCase() === char && char.toLowerCase() !== char && !e.shiftKey;
            
            if (isCapsLock) {
                if (!capsLockWarning) {
                    capsLockWarning = document.createElement('div');
                    capsLockWarning.textContent = '⚠️ Caps Lock aktif';
                    capsLockWarning.style.cssText = `
                        color: #ff6b35;
                        font-size: 12px;
                        margin-top: 5px;
                        font-weight: 500;
                    `;
                    passwordInput.parentNode.appendChild(capsLockWarning);
                }
            } else {
                if (capsLockWarning) {
                    capsLockWarning.remove();
                    capsLockWarning = null;
                }
            }
        });
        
        passwordInput.addEventListener('blur', function() {
            if (capsLockWarning) {
                capsLockWarning.remove();
                capsLockWarning = null;
            }
        });
    }
    
    // Aktifkan caps lock detection
    handleCapsLock();

    // Reset loading state jika ada error
    if (errorMessage && errorMessage.textContent.trim()) {
        resetLoadingState();
    }
});