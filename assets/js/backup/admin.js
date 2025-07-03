function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected tab
    document.getElementById(tabName).classList.add('active');

    // Add active class to clicked button
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

    // Load data for specific tabs
    switch(tabName) {
        case 'kelola-agenda':
            loadAgendaData();
            break;
        case 'riwayat-agenda':
            loadHistoryData();
            break;
        case 'dashboard':
            // Anda bisa memuat statistik di sini jika ada fungsi loadStatistics() yang sebenarnya
            // Untuk saat ini, kita biarkan kosong atau hanya menampilkan notifikasi jika diperlukan
            break;
    }
}

// Event listeners for tab buttons
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tabName = btn.getAttribute('data-tab');
        showTab(tabName);
    });
});

// Load agenda data
function loadAgendaData() {
    const tbody = document.querySelector('#agenda-table tbody');
    tbody.innerHTML = '<tr><td colspan="10" class="loading"><i class="fas fa-spinner fa-spin"></i><br>Memuat data agenda...</td></tr>';

    fetch('/ArsipKu/pages/get_agenda.php')
        .then(res => res.json())
        .then(response => {
            if (response.status === 'error') {
                tbody.innerHTML = `<tr><td colspan="10" class="empty-state">Error: ${response.message}</td></tr>`;
                console.error("Server Error:", response.error);
                return;
            }

            const data = response.data;
            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" class="empty-state">Tidak ada data agenda</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            data.forEach((agenda, index) => {
                let statusClass = 'status-pending'; // Default
                // --- PERUBAHAN DI SINI UNTUK STATUS ---
                if (agenda.status === 'in_progress') {
                    statusClass = 'status-active'; // Menggunakan class CSS 'status-active' untuk in_progress
                } else if (agenda.status === 'complete') {
                    statusClass = 'status-inactive'; // Menggunakan class CSS 'status-inactive' untuk complete
                }
                // Jika statusnya 'pending', statusClass akan tetap 'status-pending' (default)
                // ------------------------------------

                const priorityClass = agenda.priority === 'high' ? 'priority-high' :
                                      agenda.priority === 'medium' ? 'priority-medium' :
                                      'priority-low';

                // Format tanggal untuk input datetime-local agar bisa diisi di form edit
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
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editAgenda(${agenda.id})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="deleteAgenda(${agenda.id})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(err => {
            tbody.innerHTML = `<tr><td colspan="10" class="empty-state">Gagal memuat data: ${err.message}</td></tr>`;
            console.error("Fetch Error:", err);
        });
}


function loadHistoryData() {
    const tbody = document.querySelector('#history-table tbody');
    tbody.innerHTML = '<tr><td colspan="6" class="loading"><i class="fas fa-spinner fa-spin"></i><br>Memuat riwayat agenda...</td></tr>';

    fetch('/ArsipKu/pages/get_history.php') // Memanggil skrip PHP baru
        .then(res => res.json())
        .then(response => {
            if (response.status === 'error') {
                tbody.innerHTML = `<tr><td colspan="6" class="empty-state">Error: ${response.message}</td></tr>`;
                console.error("Server Error:", response.error);
                return;
            }

            const data = response.data;
            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="empty-state">Tidak ada riwayat agenda</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            data.forEach((agenda, index) => {
                let statusClass = 'status-inactive'; // Default untuk riwayat (misal: complete/cancelled)
                // Anda bisa menambahkan logika status class di sini jika ada status lain di riwayat
                if (agenda.status === 'complete') {
                    statusClass = 'status-inactive'; // Asumsi complete itu inactive/selesai
                } else if (agenda.status === 'cancelled') {
                     statusClass = 'status-inactive'; // Asumsi cancelled itu inactive/selesai
                }
                // Anda juga bisa menambahkan badge prioritas jika ingin menampilkannya di riwayat

                tbody.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            <div class="table-avatar">${agenda.username ? agenda.username.slice(0, 2).toUpperCase() : 'US'}</div>
                        </td>
                        <td>${agenda.title}</td>
                        <td>${agenda.end_date || agenda.start_date}</td> <td><span class="status-badge ${statusClass}">${agenda.status}</span></td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="viewDetails(${agenda.id})"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-warning btn-sm" onclick="archiveAgenda(${agenda.id})"><i class="fas fa-archive"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="deleteAgendaFromHistory(${agenda.id})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(err => {
            tbody.innerHTML = `<tr><td colspan="6" class="empty-state">Gagal memuat riwayat data: ${err.message}</td></tr>`;
            console.error("Fetch Error:", err);
        });
}

// Agenda management functions
function showAddAgendaModal() {
    const modal = document.getElementById('agendaModal');
    const form = document.getElementById('agendaForm');
    const modalTitle = document.getElementById('modalTitle');

    modalTitle.textContent = 'Tambah Agenda Baru'; // Set judul modal
    form.reset(); // Reset form untuk memastikan kosong saat tambah baru
    document.getElementById('agendaId').value = ''; // Kosongkan ID untuk mode tambah

    // Pastikan input datetime-local memiliki format yang benar jika ada nilai default
    document.getElementById('startDate').value = '';
    document.getElementById('endDate').value = '';

    // Set default status ke 'pending' saat tambah agenda baru
    document.getElementById('status').value = 'pending';

    modal.style.display = 'block'; // Tampilkan modal
}

function closeModal() {
    const modal = document.getElementById('agendaModal');
    modal.style.display = 'none'; // Sembunyikan modal
}

// --- Fungsi Edit Agenda ---
function editAgenda(id) {
    const modal = document.getElementById('agendaModal');
    const form = document.getElementById('agendaForm');
    const modalTitle = document.getElementById('modalTitle');

    // Ubah judul modal menjadi Edit
    modalTitle.textContent = 'Edit Agenda';

    // Ambil data agenda dari server berdasarkan ID
    fetch(`/ArsipKu/pages/get_agenda_by_id.php?id=${id}`) // Memanggil skrip PHP baru
        .then(res => res.json())
        .then(response => {
            if (response.status === 'success' && response.data) {
                const agenda = response.data;
                // Isi form dengan data agenda yang akan diedit
                document.getElementById('agendaId').value = agenda.id;
                document.getElementById('title').value = agenda.title;
                document.getElementById('description').value = agenda.description;

                // Format tanggal untuk input datetime-local
                document.getElementById('startDate').value = agenda.start_date ? new Date(agenda.start_date).toISOString().slice(0, 16) : '';
                document.getElementById('endDate').value = agenda.end_date ? new Date(agenda.end_date).toISOString().slice(0, 16) : '';

                document.getElementById('location').value = agenda.location || '';
                document.getElementById('priority').value = agenda.priority;
                document.getElementById('status').value = agenda.status; // Mengisi status sesuai dari database

                modal.style.display = 'block'; // Tampilkan modal
            } else {
                showNotification(response.message || 'Gagal memuat data agenda untuk diedit.', 'error');
                console.error("Error fetching agenda for edit:", response.error);
            }
        })
        .catch(error => {
            showNotification('Gagal terhubung ke server untuk memuat data agenda.', 'error');
            console.error("Fetch Error for edit:", error);
        });
}

function deleteAgendaFromHistory(id) {
    if (confirm(`Apakah Anda yakin ingin menghapus agenda dengan ID ${id} dari riwayat?`)) {
        fetch('/ArsipKu/pages/delete_agenda.php', { // Menggunakan script delete_agenda.php yang sudah ada
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showNotification(data.message, 'success');
                loadHistoryData(); // Refresh tabel riwayat
            } else {
                showNotification(data.message || 'Gagal menghapus agenda dari riwayat.', 'error');
                console.error("Server Response Error:", data.error);
            }
        })
        .catch(error => {
            showNotification('Gagal terhubung ke server untuk menghapus agenda dari riwayat.', 'error');
            console.error("Fetch Error:", error);
        });
    }
}

function refreshAgendaData() {
    loadAgendaData();
    showNotification('Data agenda berhasil diperbarui', 'success');
}

function viewDetails(id) {
    alert(`Menampilkan detail agenda ID: ${id}`); // Implementasikan modal detail nanti jika diperlukan
}

function archiveAgenda(id) { // Ini untuk tombol arsip per-item di riwayat
    if (confirm(`Apakah Anda ingin mengarsipkan agenda dengan ID ${id}?`)) {
        fetch('/ArsipKu/pages/archive_agenda.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' || data.status === 'info') {
                showNotification(data.message, data.status);
                loadHistoryData(); // Refresh tabel riwayat
            } else {
                showNotification(data.message || 'Gagal mengarsipkan agenda.', 'error');
                console.error("Server Response Error:", data.error);
            }
        })
        .catch(error => {
            showNotification('Gagal terhubung ke server untuk mengarsipkan agenda.', 'error');
            console.error("Fetch Error:", error);
        });
    }
}

function archiveCompletedAgenda() { // Ini untuk tombol "Arsipkan Semua yang Selesai"
    if (confirm('Apakah Anda yakin ingin mengarsipkan semua agenda yang sudah selesai? Tindakan ini akan mengubah statusnya menjadi "complete".')) {
        showNotification('Memproses pengarsipan...', 'info');
        fetch('/ArsipKu/pages/archive_agenda.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ archive_all: true })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' || data.status === 'info') {
                showNotification(data.message, data.status);
                loadHistoryData(); // Refresh tabel riwayat
                loadAgendaData(); // Juga refresh tabel agenda utama karena statusnya mungkin berubah
            } else {
                showNotification(data.message || 'Gagal mengarsipkan semua agenda selesai.', 'error');
                console.error("Server Response Error:", data.error);
            }
        })
        .catch(error => {
            showNotification('Gagal terhubung ke server untuk mengarsipkan semua agenda.', 'error');
            console.error("Fetch Error:", error);
        });
    }
}

function deleteOldAgenda() { // Ini untuk tombol "Hapus Data Lama"
    if (confirm('Apakah Anda yakin ingin menghapus semua data agenda yang sudah lama? Tindakan ini tidak dapat dibatalkan!')) {
        showNotification('Memproses penghapusan data lama...', 'info');
        fetch('/ArsipKu/pages/delete_old_agenda.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({}) // Tidak perlu mengirim ID, karena ini menghapus semua yang lama
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' || data.status === 'info') {
                showNotification(data.message, data.status);
                loadHistoryData(); // Refresh tabel riwayat
            } else {
                showNotification(data.message || 'Gagal menghapus data agenda lama.', 'error');
                console.error("Server Response Error:", data.error);
            }
        })
        .catch(error => {
            showNotification('Gagal terhubung ke server untuk menghapus data lama.', 'error');
            console.error("Fetch Error:", error);
        });
    }
}


function backupDatabase() {
    if (confirm('Apakah Anda ingin membuat backup database?')) {
        // Show loading
        showNotification('Memproses backup database...', 'info');

        setTimeout(() => {
            // In real implementation, this would trigger database backup
            alert('Backup database berhasil dibuat');
            showNotification('Backup database berhasil dibuat', 'success');
        }, 2000);
    }
}

function resetSystem() {
    const confirmText = prompt('Untuk reset sistem, ketik "RESET" (huruf besar):');
    if (confirmText === 'RESET') {
        if (confirm('PERINGATAN: Ini akan menghapus SEMUA data sistem! Apakah Anda yakin?')) {
            alert('Sistem berhasil direset. Anda akan dialihkan ke halaman login.');
            // In real implementation, this would reset the system and redirect
            window.location.href = '../login.php';
        }
    } else if (confirmText !== null) {
        alert('Teks konfirmasi salah. Reset sistem dibatalkan.');
    }
}

// Notification system
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    `;

    // Add notification styles if not exists
    if (!document.getElementById('notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                background: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 16px rgba(0,0,0,0.1);
                display: flex;
                align-items: center;
                gap: 10px;
                z-index: 1000;
                min-width: 300px;
                animation: slideIn 0.3s ease;
            }
            .notification-success { border-left: 4px solid #28a745; }
            .notification-error { border-left: 4px solid #dc3545; }
            .notification-info { border-left: 4px solid #17a2b8; }
            .notification button {
                background: none;
                border: none;
                color: #666;
                cursor: pointer;
                margin-left: auto;
            }
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }

    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Mobile menu toggle
function toggleMobileMenu() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('mobile-open');
}

// Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl + 1-6 for quick tab navigation
    if (e.ctrlKey && e.key >= '1' && e.key <= '6') {
        e.preventDefault();
        const tabs = ['dashboard', 'kelola-agenda', 'riwayat-agenda'];
        const tabIndex = parseInt(e.key) - 1;
        if (tabs[tabIndex]) {
            showTab(tabs[tabIndex]);
        }
    }
});

// Add data export functionality
function exportData(type) {
    switch(type) {
        case 'users':
            exportUsersToCSV();
            break;
        case 'agenda':
            exportAgendaToCSV();
            break;
        case 'history':
            exportHistoryToCSV();
            break;
    }
}

function exportUsersToCSV() {
    // Sample CSV export for users
    const csvContent = "No,Username,Email,Nama,Role,Status\n1,admin,admin@arsipku.com,Administrator,Admin,Aktif\n2,john_doe,john@example.com,John Doe,User,Aktif";
    downloadCSV(csvContent, 'users_export.csv');
}

function exportAgendaToCSV() {
    // Sample CSV export for agenda
    const csvContent = "ID,User,Judul,Tanggal Mulai,Tanggal Berakhir,Status\n1,john_doe,Rapat Bulanan,2025-07-01 09:00,2025-07-01 11:00,in_progress"; // Diubah
    downloadCSV(csvContent, 'agenda_export.csv');
}

function exportHistoryToCSV() {
    // Sample CSV export for history
    const csvContent = "ID,User,Judul,Tanggal,Status\n1,john_doe,Meeting Mingguan Tim A,2025-06-25 10:00,complete"; // Diubah
    downloadCSV(csvContent, 'history_export.csv');
}

function downloadCSV(content, filename) {
    const blob = new Blob([content], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', filename);
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    showNotification(`File ${filename} berhasil diunduh`, 'success');
}

// Initialize dashboard (jalankan saat DOM selesai dimuat)
document.addEventListener('DOMContentLoaded', function() {
    // Hapus atau komentari baris loadStatistics() yang tidak terdefinisi
    // loadStatistics();

    // Tambahkan event listener untuk tombol tutup modal
    document.querySelector('.close-button').addEventListener('click', closeModal);

    // Menutup modal saat klik di luar area modal
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('agendaModal');
        if (event.target === modal) {
            closeModal();
        }
    });

    // Tangani submit form agenda (sudah dimodifikasi untuk tambah dan edit)
    const agendaForm = document.getElementById('agendaForm');
    if (agendaForm) { // Pastikan form ada sebelum menambahkan event listener
        agendaForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Mencegah form dari submit default (reload halaman)

            const formData = new FormData(agendaForm);
            const jsonData = {};
            formData.forEach((value, key) => {
                jsonData[key] = value;
            });

            // Tentukan URL dan metode berdasarkan apakah ada ID agenda (mode edit)
            const agendaId = document.getElementById('agendaId').value;
            let url, method;

            if (agendaId) { // Jika ada agendaId, berarti ini mode edit
                url = '/ArsipKu/pages/update_agenda.php';
                method = 'POST';
            } else { // Jika tidak ada agendaId, berarti ini mode tambah
                url = '/ArsipKu/pages/add_agenda.php';
                method = 'POST';
            }

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(jsonData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification(data.message, 'success');
                    closeModal(); // Tutup modal setelah berhasil
                    loadAgendaData(); // Refresh data di tabel
                } else {
                    showNotification(data.message || 'Terjadi kesalahan saat menyimpan agenda.', 'error');
                    console.error("Server Response Error:", data.error);
                }
            })
            .catch(error => {
                showNotification('Gagal terhubung ke server untuk menyimpan agenda.', 'error');
                console.error("Fetch Error:", error);
            });
        });
    }


    // Add mobile menu button for responsive design
    if (window.innerWidth <= 768) {
        const header = document.querySelector('.header h1');
        const menuBtn = document.createElement('button');
        menuBtn.innerHTML = '<i class="fas fa-bars"></i>';
        menuBtn.className = 'btn btn-primary';
        menuBtn.onclick = toggleMobileMenu;
        header.insertAdjacentElement('beforebegin', menuBtn);
    }

    // Show welcome notification
    setTimeout(() => {
        showNotification('Selamat datang di Dashboard Administrator!', 'success');
    }, 1000);

    // Inisialisasi tab dashboard saat pertama kali dimuat
    showTab('dashboard'); // Memastikan tab dashboard aktif secara default saat halaman dimuat
});

// Handle window resize
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        document.querySelector('.sidebar').classList.remove('mobile-open');
    }
});