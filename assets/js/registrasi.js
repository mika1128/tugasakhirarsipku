/**
 * Script untuk halaman registrasi
 * File: assets/js/registrasi.js
 */

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const name = document.getElementById('name');
    const username = document.getElementById('username');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const submitBtn = document.querySelector('button[type="submit"]');

    // Set fokus ke nama saat halaman dimuat
    name?.focus();

    if (form) {
        form.addEventListener('submit', function (e) {
            clearError();

            const namaVal = name?.value.trim();
            const usernameVal = username?.value.trim();
            const emailVal = email?.value.trim();
            const passVal = password?.value.trim();
            const confirmVal = confirmPassword?.value.trim();

            if (!namaVal || !usernameVal || !emailVal || !passVal || !confirmVal) {
                e.preventDefault();
                showError('Semua field wajib diisi!');
                return;
            }

            if (usernameVal.length < 3) {
                e.preventDefault();
                showError('Username minimal 3 karakter!');
                return;
            }

            if (!validateEmail(emailVal)) {
                e.preventDefault();
                showError('Format email tidak valid!');
                return;
            }

            if (passVal.length < 6) {
                e.preventDefault();
                showError('Password minimal 6 karakter!');
                return;
            }

            if (passVal !== confirmVal) {
                e.preventDefault();
                showError('Konfirmasi password tidak cocok!');
                return;
            }

            setLoading();
        });
    }

    // Tambahkan validasi on input
    [name, username, email, password, confirmPassword].forEach(el => {
        el?.addEventListener('input', clearError);
    });

    // Pindah fokus saat tekan Enter
    username?.addEventListener('keypress', e => {
        if (e.key === 'Enter') email.focus();
    });
    email?.addEventListener('keypress', e => {
        if (e.key === 'Enter') password.focus();
    });
    password?.addEventListener('keypress', e => {
        if (e.key === 'Enter') confirmPassword.focus();
    });
    confirmPassword?.addEventListener('keypress', e => {
        if (e.key === 'Enter') form.submit();
    });

    // Toggle mata sandi
    addToggle(password);
    addToggle(confirmPassword);

    // Deteksi Caps Lock
    detectCaps(password);
    detectCaps(confirmPassword);

    // Fungsi Validasi
    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function showError(msg) {
        let errorDiv = document.querySelector('.error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.style.cssText = `
                background: rgba(255, 80, 80, 0.2);
                color: #ff6b6b;
                padding: 12px;
                border-radius: 8px;
                text-align: center;
                margin-bottom: 15px;
            `;
            form.insertBefore(errorDiv, form.firstChild);
        }
        errorDiv.textContent = msg;
        errorDiv.style.display = 'block';
        errorDiv.scrollIntoView({ behavior: 'smooth' });
    }

    function clearError() {
        const errorDiv = document.querySelector('.error-message');
        if (errorDiv) errorDiv.style.display = 'none';
    }

    function setLoading() {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Mendaftarkan...';
        submitBtn.style.opacity = '0.6';
        submitBtn.style.cursor = 'not-allowed';
    }

    function addToggle(input) {
        if (!input) return;

        const parent = input.parentElement;
        parent.classList.add('form-group');

        const toggle = document.createElement('span');
        toggle.innerHTML = '👁️';
        toggle.className = 'toggle-password';
        parent.appendChild(toggle);

        input.style.paddingRight = '44px';

        toggle.addEventListener('click', () => {
            if (input.type === 'password') {
                input.type = 'text';
                toggle.innerHTML = '🙈';
            } else {
                input.type = 'password';
                toggle.innerHTML = '👁️';
            }
        });
    }

    function detectCaps(input) {
        let warning = null;

        input.addEventListener('keyup', function (e) {
            const isCaps = e.getModifierState && e.getModifierState('CapsLock');
            if (isCaps) {
                if (!warning) {
                    warning = document.createElement('div');
                    warning.textContent = '⚠️ Caps Lock aktif';
                    warning.style.cssText = `
                        color: #ffb347;
                        font-size: 12px;
                        margin-top: 4px;
                    `;
                    input.parentElement.appendChild(warning);
                }
            } else if (warning) {
                warning.remove();
                warning = null;
            }
        });

        input.addEventListener('blur', () => {
            if (warning) {
                warning.remove();
                warning = null;
            }
        });
    }
});
