// ====================================================================================================
// DEFINISI FUNGSI GLOBAL (DIJAMIN TERSEDIA SEBELUM DOMContentLoaded)
// ====================================================================================================

// Semua fungsi yang akan dipanggil secara global atau dari atribut onclick di HTML
// harus didefinisikan di sini, di luar blok DOMContentLoaded.

window.showTab = function(tabName) {
    // Sembunyikan semua konten tab
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    // Hapus kelas aktif dari semua tombol tab
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Tampilkan tab yang dipilih
    document.getElementById(tabName).classList.add('active');

    // Tambahkan kelas aktif ke tombol yang diklik
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

    // Muat data untuk tab tertentu
    switch(tabName) {
        case 'kelola-agenda':
            window.loadAgendaData();
            break;
        case 'riwayat-agenda':
            window.loadHistoryData();
            break;
        case 'keluarga':
            window.loadKeluargaData();
            break;
        case 'arsip-vital':
            window.loadArsipVitalData();
            break;
        case 'arsip-inactive':
            window.loadArsipInactiveData();
            break;
        case 'dashboard':
            // Anda bisa memuat statistik di sini jika ada fungsi loadStatistics() yang sebenarnya
            // Untuk saat ini, kita biarkan kosong atau hanya menampilkan notifikasi jika diperlukan
            break;
    }
};

// Load agenda data
window.loadAgendaData = function() {
    const tbody = document.querySelector('#agenda-table tbody');
    tbody.innerHTML = '<tr><td colspan="10" class="loading"><i class="fas fa-spinner fa-spin"></i><br>Memuat data agenda...</td></tr>';

    fetch('/ArsipKu/pages/get_agenda.php')
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
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
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="window.editAgenda(${agenda.id})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="window.deleteAgenda(${agenda.id})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(err => {
            tbody.innerHTML = `<tr><td colspan="10" class="empty-state">Gagal memuat data agenda: ${err.message}. Pastikan file PHP ada dan tidak ada error server.</td></tr>`;
            console.error("Fetch Error for Agenda:", err);
        });
};


window.loadHistoryData = function() {
    const tbody = document.querySelector('#history-table tbody');
    tbody.innerHTML = '<tr><td colspan="6" class="loading"><i class="fas fa-spinner fa-spin"></i><br>Memuat riwayat agenda...</td></tr>';

    fetch('/ArsipKu/pages/get_history.php')
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
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
                        <td>${agenda.end_date || agenda.start_date}</td> <td><span class="status-badge ${statusClass}">${agenda.status}</span></td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="window.viewDetails(${agenda.id})"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-warning btn-sm" onclick="window.archiveAgenda(${agenda.id})"><i class="fas fa-archive"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="window.deleteAgendaFromHistory(${agenda.id})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(err => {
            tbody.innerHTML = `<tr><td colspan="6" class="empty-state">Gagal memuat riwayat data: ${err.message}. Pastikan file PHP ada dan tidak ada error server.</td></tr>`;
            console.error("Fetch Error for History:", err);
        });
};

// NEW: Load Keluarga Data
window.loadKeluargaData = function() {
    const tbody = document.querySelector('#keluarga-table tbody');
    tbody.innerHTML = '<tr><td colspan="7" class="loading"><i class="fas fa-spinner fa-spin"></i><br>Memuat data dokumen keluarga...</td></tr>';

    fetch('/ArsipKu/pages/get_keluarga_dokumen.php')
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(response => {
            if (response.status === 'error') {
                tbody.innerHTML = `<tr><td colspan="7" class="empty-state">Error: ${response.message}</td></tr>`;
                console.error("Server Error:", response.error);
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
                            <button class="btn btn-warning btn-sm" onclick="window.editKeluarga(${item.id})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="window.deleteKeluarga(${item.id})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(err => {
            tbody.innerHTML = `<tr><td colspan="7" class="empty-state">Gagal memuat data dokumen keluarga: ${err.message}. Pastikan file PHP ada dan tidak ada error server.</td></tr>`;
            console.error("Fetch Error for Keluarga Dokumen:", err);
        });
};

// NEW: Load Arsip Vital Data
window.loadArsipVitalData = function() {
    const tbody = document.querySelector('#arsip-vital-table tbody');
    tbody.innerHTML = '<tr><td colspan="8" class="loading"><i class="fas fa-spinner fa-spin"></i><br>Memuat data arsip vital...</td></tr>';

    fetch('/ArsipKu/pages/get_arsip_vital.php')
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(response => {
            if (response.status === 'error') {
                tbody.innerHTML = `<tr><td colspan="8" class="empty-state">Error: ${response.message}</td></tr>`;
                console.error("Server Error:", response.error);
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
                } else if (item.status === 'rusak') {
                    statusClass = 'status-danger';
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
                            <button class="btn btn-primary btn-sm" onclick="window.viewArsipDetails(${item.id}, 'vital')"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-warning btn-sm" onclick="window.editArsip(${item.id}, 'vital')"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="window.deleteArsip(${item.id}, 'vital')"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(err => {
            tbody.innerHTML = `<tr><td colspan="8" class="empty-state">Gagal memuat data arsip vital: ${err.message}. Pastikan file PHP ada dan tidak ada error server.</td></tr>`;
            console.error("Fetch Error for Arsip Vital:", err);
        });
};

// NEW: Load Arsip Inactive Data
window.loadArsipInactiveData = function() {
    const tbody = document.querySelector('#arsip-inactive-table tbody');
    tbody.innerHTML = '<tr><td colspan="8" class="loading"><i class="fas fa-spinner fa-spin"></i><br>Memuat data arsip inactive...</td></tr>';

    fetch('/ArsipKu/pages/get_arsip_inactive.php')
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(response => {
            if (response.status === 'error') {
                tbody.innerHTML = `<tr><td colspan="8" class="empty-state">Error: ${response.message}</td></tr>`;
                console.error("Server Error:", response.error);
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
                } else if (item.status === 'rusak') {
                    statusClass = 'status-danger';
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
                            <button class="btn btn-primary btn-sm" onclick="window.viewArsipDetails(${item.id}, 'inactive')"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-warning btn-sm" onclick="window.editArsip(${item.id}, 'inactive')"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="window.deleteArsip(${item.id}, 'inactive')"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(err => {
            tbody.innerHTML = `<tr><td colspan="8" class="empty-state">Gagal memuat data arsip inactive: ${err.message}. Pastikan file PHP ada dan tidak ada error server.</td></tr>`;
            console.error("Fetch Error for Arsip Inactive:", err);
        });
};

// Agenda management functions (existing)
window.showAddAgendaModal = function() {
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
};

// Modified closeModal to accept modalId
window.closeModal = function(modalId = 'agendaModal') {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none'; // Sembunyikan modal
    }
};

// --- Fungsi Edit Agenda --- (existing)
window.editAgenda = function(id) {
    const modal = document.getElementById('agendaModal');
    const form = document.getElementById('agendaForm');
    const modalTitle = document.getElementById('modalTitle');

    // Ubah judul modal menjadi Edit
    modalTitle.textContent = 'Edit Agenda';

    // Ambil data agenda dari server berdasarkan ID
    fetch(`/ArsipKu/pages/get_agenda_by_id.php?id=${id}`)
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(response => {
            if (response.status === 'success' && response.data) {
                const agenda = response.data;
                document.getElementById('agendaId').value = agenda.id;
                document.getElementById('title').value = agenda.title;
                document.getElementById('description').value = agenda.description;

                document.getElementById('startDate').value = agenda.start_date ? new Date(agenda.start_date).toISOString().slice(0, 16) : '';
                document.getElementById('endDate').value = agenda.end_date ? new Date(agenda.end_date).toISOString().slice(0, 16) : '';

                document.getElementById('location').value = agenda.location || '';
                document.getElementById('priority').value = agenda.priority;
                document.getElementById('status').value = agenda.status;

                modal.style.display = 'block';
            } else {
                window.showNotification(response.message || 'Gagal memuat data agenda untuk diedit.', 'error');
                console.error("Error fetching agenda for edit:", response.error);
            }
        })
        .catch(error => {
            window.showNotification(`Gagal terhubung ke server untuk memuat data agenda: ${error.message}. Pastikan file PHP ada dan tidak ada error server.`, 'error');
            console.error("Fetch Error for edit:", error);
        });
};

// Fungsi deleteAgenda yang menyebabkan error
window.deleteAgenda = function(id) { // Pastikan ini terikat ke window
    if (confirm(`Apakah Anda yakin ingin menghapus agenda dengan ID ${id}?`)) {
        fetch('/ArsipKu/pages/delete_agenda.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            if (data.status === 'success') {
                window.showNotification(data.message, 'success');
                window.loadAgendaData(); // Refresh tabel agenda
            } else {
                window.showNotification(data.message || 'Gagal menghapus agenda.', 'error');
                console.error("Server Response Error:", data.error);
            }
        })
        .catch(error => {
            window.showNotification(`Gagal terhubung ke server untuk menghapus agenda: ${error.message}. Pastikan file PHP ada dan tidak ada error server.`, 'error');
            console.error("Fetch Error:", error);
        });
    }
};


window.deleteAgendaFromHistory = function(id) {
    if (confirm(`Apakah Anda yakin ingin menghapus agenda dengan ID ${id} dari riwayat?`)) {
        fetch('/ArsipKu/pages/delete_agenda.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            if (data.status === 'success') {
                window.showNotification(data.message, 'success');
                window.loadHistoryData();
            } else {
                window.showNotification(data.message || 'Gagal menghapus agenda dari riwayat.', 'error');
                console.error("Server Response Error:", data.error);
            }
        })
        .catch(error => {
            window.showNotification(`Gagal terhubung ke server untuk menghapus agenda dari riwayat: ${error.message}. Pastikan file PHP ada dan tidak ada error server.`, 'error');
            console.error("Fetch Error:", error);
        });
    }
};

window.refreshAgendaData = function() {
    window.loadAgendaData();
    window.showNotification('Data agenda berhasil diperbarui', 'success');
};

window.viewDetails = function(id) {
    alert(`Menampilkan detail agenda ID: ${id}`);
};

window.archiveAgenda = function(id) {
    if (confirm(`Apakah Anda ingin mengarsipkan agenda dengan ID ${id}?`)) {
        fetch('/ArsipKu/pages/archive_agenda.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            if (data.status === 'success' || data.status === 'info') {
                window.showNotification(data.message, data.status);
                window.loadHistoryData();
            } else {
                window.showNotification(data.message || 'Gagal mengarsipkan agenda.', 'error');
                console.error("Server Response Error:", data.error);
            }
        })
        .catch(error => {
            window.showNotification(`Gagal terhubung ke server untuk mengarsipkan agenda: ${error.message}. Pastikan file PHP ada dan tidak ada error server.`, 'error');
            console.error("Fetch Error:", error);
        });
    }
};

window.archiveCompletedAgenda = function() {
    if (confirm('Apakah Anda yakin ingin mengarsipkan semua agenda yang sudah selesai? Tindakan ini akan mengubah statusnya menjadi "complete".')) {
        window.showNotification('Memproses pengarsipan...', 'info');
        fetch('/ArsipKu/pages/archive_agenda.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ archive_all: true })
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            if (data.status === 'success' || data.status === 'info') {
                window.showNotification(data.message, data.status);
                window.loadHistoryData();
                window.loadAgendaData();
            } else {
                window.showNotification(data.message || 'Gagal mengarsipkan semua agenda selesai.', 'error');
                console.error("Server Response Error:", data.error);
            }
        })
        .catch(error => {
            window.showNotification(`Gagal terhubung ke server untuk mengarsipkan semua agenda: ${error.message}. Pastikan file PHP ada dan tidak ada error server.`, 'error');
            console.error("Fetch Error:", error);
        });
    }
};

window.deleteOldAgenda = function() {
    if (confirm('Apakah Anda yakin ingin menghapus semua data agenda yang sudah lama? Tindakan ini tidak dapat dibatalkan!')) {
        window.showNotification('Memproses penghapusan data lama...', 'info');
        fetch('/ArsipKu/pages/delete_old_agenda.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            if (data.status === 'success' || data.status === 'info') {
                window.showNotification(data.message, data.status);
                window.loadHistoryData();
            } else {
                window.showNotification(data.message || 'Gagal menghapus data agenda lama.', 'error');
                console.error("Server Response Error:", data.error);
            }
        })
        .catch(error => {
            window.showNotification(`Gagal terhubung ke server untuk menghapus data lama: ${error.message}. Pastikan file PHP ada dan tidak ada error server.`, 'error');
            console.error("Fetch Error:", error);
        });
    }
};

// NEW: Keluarga Management Functions
window.showAddKeluargaModal = function() {
    const modal = document.getElementById('keluargaModal');
    const form = document.getElementById('keluargaForm');
    const modalTitle = document.getElementById('keluargaModalTitle');

    modalTitle.textContent = 'Tambah Dokumen Keluarga Baru';
    form.reset();
    document.getElementById('keluargaId').value = '';
    document.getElementById('statusKeluarga').value = 'aktif';
    document.getElementById('tanggalDibuat').valueAsDate = new Date();

    modal.style.display = 'block';
};

window.editKeluarga = function(id) {
    const modal = document.getElementById('keluargaModal');
    const form = document.getElementById('keluargaForm');
    const modalTitle = document.getElementById('keluargaModalTitle');

    modalTitle.textContent = 'Edit Dokumen Keluarga';

    fetch(`/ArsipKu/pages/get_keluarga_dokumen_by_id.php?id=${id}`)
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(response => {
            if (response.status === 'success' && response.data) {
                const item = response.data;
                document.getElementById('keluargaId').value = item.id;
                document.getElementById('namaDokumen').value = item.nama_dokumen;
                document.getElementById('deskripsiDokumen').value = item.deskripsi_dokumen;
                document.getElementById('tanggalDibuat').value = item.tanggal_dibuat;
                document.getElementById('statusKeluarga').value = item.status;

                modal.style.display = 'block';
            } else {
                window.showNotification(response.message || 'Gagal memuat data dokumen keluarga untuk diedit.', 'error');
                console.error("Error fetching keluarga for edit:", response.error);
            }
        })
        .catch(error => {
            window.showNotification(`Gagal terhubung ke server untuk memuat data dokumen keluarga: ${error.message}. Pastikan file PHP ada dan tidak ada error server.`, 'error');
            console.error("Fetch Error for edit:", error);
        });
};

window.deleteKeluarga = function(id) {
    if (confirm(`Apakah Anda yakin ingin menghapus dokumen keluarga dengan ID ${id}?`)) {
        fetch('/ArsipKu/pages/delete_keluarga_dokumen.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            if (data.status === 'success') {
                window.showNotification(data.message, 'success');
                window.loadKeluargaData();
            } else {
                window.showNotification(data.message || 'Gagal menghapus dokumen keluarga.', 'error');
                console.error("Server Response Error:", data.error);
            }
        })
        .catch(error => {
            window.showNotification(`Gagal terhubung ke server untuk menghapus dokumen keluarga: ${error.message}. Pastikan file PHP ada dan tidak ada error server.`, 'error');
            console.error("Fetch Error:", error);
        });
    }
};

// NEW: Arsip (Vital/Inactive) Management Functions
window.showAddArsipModal = function(type) {
    const modal = document.getElementById('arsipModal');
    const form = document.getElementById('arsipForm');
    const modalTitle = document.getElementById('arsipModalTitle');

    modalTitle.textContent = `Tambah Arsip ${type === 'vital' ? 'Vital' : 'Inactive'} Baru`;
    form.reset();
    document.getElementById('arsipId').value = '';
    document.getElementById('arsipType').value = type;
    document.getElementById('statusArsip').value = 'aktif';
    document.getElementById('tahunDibuat').value = new Date().getFullYear();

    modal.style.display = 'block';
};

window.showAddArsipVitalModal = function() {
    window.showAddArsipModal('vital');
};

window.showAddArsipInactiveModal = function() {
    window.showAddArsipModal('inactive');
};

window.editArsip = function(id, type) {
    const modal = document.getElementById('arsipModal');
    const form = document.getElementById('arsipForm');
    const modalTitle = document.getElementById('arsipModalTitle');

    modalTitle.textContent = `Edit Arsip ${type === 'vital' ? 'Vital' : 'Inactive'}`;
    document.getElementById('arsipType').value = type;

    const fetchUrl = type === 'vital' ? `/ArsipKu/pages/get_arsip_vital_by_id.php?id=${id}` : `/ArsipKu/pages/get_arsip_inactive_by_id.php?id=${id}`;

    fetch(fetchUrl)
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(response => {
            if (response.status === 'success' && response.data) {
                const item = response.data;
                document.getElementById('arsipId').value = item.id;
                document.getElementById('nomorSurat').value = item.nomor_surat;
                document.getElementById('beritaAcaraSurat').value = item.berita_acara_surat;
                document.getElementById('statusArsip').value = item.status;
                document.getElementById('tahunDibuat').value = item.tahun_dibuat;

                modal.style.display = 'block';
            } else {
                window.showNotification(response.message || `Gagal memuat data arsip ${type} untuk diedit.`, 'error');
                console.error(`Error fetching arsip ${type} for edit:`, response.error);
            }
        })
        .catch(error => {
            window.showNotification(`Gagal terhubung ke server untuk memuat data arsip ${type}: ${error.message}. Pastikan file PHP ada dan tidak ada error server.`, 'error');
            console.error("Fetch Error for edit:", error);
        });
};

window.deleteArsip = function(id, type) {
    if (confirm(`Apakah Anda yakin ingin menghapus arsip ${type} dengan ID ${id}?`)) {
        const deleteUrl = type === 'vital' ? '/ArsipKu/pages/delete_arsip_vital.php' : '/ArsipKu/pages/delete_arsip_inactive.php';
        const loadFunction = type === 'vital' ? window.loadArsipVitalData : window.loadArsipInactiveData;

        fetch(deleteUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            if (data.status === 'success') {
                window.showNotification(data.message, 'success');
                loadFunction();
            } else {
                window.showNotification(data.message || `Gagal menghapus arsip ${type}.`, 'error');
                console.error("Server Response Error:", data.error);
            }
        })
        .catch(error => {
            window.showNotification(`Gagal terhubung ke server untuk menghapus arsip ${type}: ${error.message}. Pastikan file PHP ada dan tidak ada error server.`, 'error');
            console.error("Fetch Error:", error);
        });
    }
};

window.viewArsipDetails = function(id, type) {
    alert(`Menampilkan detail arsip ${type} ID: ${id}`);
};

window.deleteOldInactiveArsip = function() {
    if (confirm('Apakah Anda yakin ingin menghapus semua arsip inactive yang sudah lama? Tindakan ini tidak dapat dibatalkan!')) {
        window.showNotification('Memproses penghapusan arsip inactive lama...', 'info');
        fetch('/ArsipKu/pages/delete_old_inactive_arsip.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            if (data.status === 'success' || data.status === 'info') {
                window.showNotification(data.message, data.status);
                window.loadArsipInactiveData();
            } else {
                window.showNotification(data.message || 'Gagal menghapus arsip inactive lama.', 'error');
                console.error("Server Response Error:", data.error);
            }
        })
        .catch(error => {
            window.showNotification(`Gagal terhubung ke server untuk menghapus arsip inactive lama: ${error.message}. Pastikan file PHP ada dan tidak ada error server.`, 'error');
            console.error("Fetch Error:", error);
        });
    }
};


window.backupDatabase = function() {
    if (confirm('Apakah Anda ingin membuat backup database?')) {
        window.showNotification('Memproses backup database...', 'info');

        setTimeout(() => {
            alert('Backup database berhasil dibuat');
            window.showNotification('Backup database berhasil dibuat', 'success');
        }, 2000);
    }
};

window.resetSystem = function() {
    const confirmText = prompt('Untuk reset sistem, ketik "RESET" (huruf besar):');
    if (confirmText === 'RESET') {
        if (confirm('PERINGATAN: Ini akan menghapus SEMUA data sistem! Apakah Anda yakin?')) {
            alert('Sistem berhasil direset. Anda akan dialihkan ke halaman login.');
            window.location.href = '../login.php';
        }
    } else if (confirmText !== null) {
        alert('Teks konfirmasi salah. Reset sistem dibatalkan.');
    }
};

// Notification system (existing)
window.showNotification = function(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    `;

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

    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
};

// Mobile menu toggle (existing)
window.toggleMobileMenu = function() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('mobile-open');
};

// Add keyboard shortcuts (existing)
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key >= '1' && e.key <= '9') {
        e.preventDefault();
        const tabs = ['dashboard', 'kelola-agenda', 'keluarga', 'arsip-vital', 'arsip-inactive', 'riwayat-agenda'];
        const tabIndex = parseInt(e.key) - 1;
        if (tabs[tabIndex]) {
            window.showTab(tabs[tabIndex]); // Panggil melalui window
        }
    }
});

// Add data export functionality (existing, no changes requested)
window.exportData = function(type) {
    switch(type) {
        case 'users':
            window.exportUsersToCSV();
            break;
        case 'agenda':
            window.exportAgendaToCSV();
            break;
        case 'history':
            window.exportHistoryToCSV();
            break;
    }
};

window.exportUsersToCSV = function() {
    const csvContent = "No,Username,Email,Nama,Role,Status\n1,admin,admin@arsipku.com,Administrator,Admin,Aktif\n2,john_doe,john@example.com,John Doe,User,Aktif";
    window.downloadCSV(csvContent, 'users_export.csv');
};

window.exportAgendaToCSV = function() {
    const csvContent = "ID,User,Judul,Tanggal Mulai,Tanggal Berakhir,Status\n1,john_doe,Rapat Bulanan,2025-07-01 09:00,2025-07-01 11:00,in_progress";
    window.downloadCSV(csvContent, 'agenda_export.csv');
};

window.exportHistoryToCSV = function() {
    const csvContent = "ID,User,Judul,Tanggal,Status\n1,john_doe,Meeting Mingguan Tim A,2025-06-25 10:00,complete";
    window.downloadCSV(csvContent, 'history_export.csv');
};

window.downloadCSV = function(content, filename) {
    const blob = new Blob([content], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', filename);
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.showNotification(`File ${filename} berhasil diunduh`, 'success');
};

// ====================================================================================================
// INITIALIZATION KETIKA DOM SELESAI DIMUAT
// ====================================================================================================
document.addEventListener('DOMContentLoaded', function() {
    // Event listeners for tab buttons
    // Pindahkan kembali ke dalam DOMContentLoaded untuk memastikan elemen sudah ada
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tabName = btn.getAttribute('data-tab');
            window.showTab(tabName);
        });
    });

    document.querySelectorAll('.close-button').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const modal = e.target.closest('.modal');
            if (modal) {
                window.closeModal(modal.id);
            }
        });
    });

    window.addEventListener('click', function(event) {
        const agendaModal = document.getElementById('agendaModal');
        const keluargaModal = document.getElementById('keluargaModal');
        const arsipModal = document.getElementById('arsipModal');

        if (event.target === agendaModal) {
            window.closeModal('agendaModal');
        } else if (event.target === keluargaModal) {
            window.closeModal('keluargaModal');
        } else if (event.target === arsipModal) {
            window.closeModal('arsipModal');
        }
    });

    const agendaForm = document.getElementById('agendaForm');
    if (agendaForm) {
        agendaForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(agendaForm);
            const jsonData = {};
            formData.forEach((value, key) => {
                jsonData[key] = value;
            });

            const agendaId = document.getElementById('agendaId').value;
            let url, method;

            if (agendaId) {
                url = '/ArsipKu/pages/update_agenda.php';
                method = 'POST';
            } else {
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
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    window.showNotification(data.message, 'success');
                    window.closeModal('agendaModal');
                    window.loadAgendaData();
                } else {
                    window.showNotification(data.message || 'Terjadi kesalahan saat menyimpan agenda.', 'error');
                    console.error("Server Response Error:", data.error);
                }
            })
            .catch(error => {
                window.showNotification(`Gagal terhubung ke server untuk menyimpan agenda: ${error.message}. Pastikan file PHP ada dan tidak ada error server.`, 'error');
                console.error("Fetch Error:", error);
            });
        });
    }

    const keluargaForm = document.getElementById('keluargaForm');
    if (keluargaForm) {
        keluargaForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(keluargaForm);

            const keluargaId = document.getElementById('keluargaId').value;
            let url = keluargaId ? '/ArsipKu/pages/update_keluarga_dokumen.php' : '/ArsipKu/pages/add_keluarga_dokumen.php';

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    window.showNotification(data.message, 'success');
                    window.closeModal('keluargaModal');
                    window.loadKeluargaData();
                } else {
                    window.showNotification(data.message || 'Terjadi kesalahan saat menyimpan dokumen keluarga.', 'error');
                    console.error("Server Response Error:", data.error);
                }
            })
            .catch(error => {
                window.showNotification(`Gagal terhubung ke server untuk menyimpan dokumen keluarga: ${error.message}. Pastikan file PHP ada dan tidak ada error server.`, 'error');
                console.error("Fetch Error:", error);
            });
        });
    }

    const arsipForm = document.getElementById('arsipForm');
    if (arsipForm) {
        arsipForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(arsipForm);
            const arsipId = document.getElementById('arsipId').value;
            const arsipType = document.getElementById('arsipType').value;

            let url;
            if (arsipId) {
                url = arsipType === 'vital' ? '/ArsipKu/pages/update_arsip_vital.php' : '/ArsipKu/pages/update_arsip_inactive.php';
            } else {
                url = arsipType === 'vital' ? '/ArsipKu/pages/add_arsip_vital.php' : '/ArsipKu/pages/add_arsip_inactive.php';
            }

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    window.showNotification(data.message, 'success');
                    window.closeModal('arsipModal');
                    if (arsipType === 'vital') {
                        window.loadArsipVitalData();
                    } else {
                        window.loadArsipInactiveData();
                    }
                } else {
                    window.showNotification(data.message || 'Terjadi kesalahan saat menyimpan arsip.', 'error');
                    console.error("Server Response Error:", data.error);
                }
            })
            .catch(error => {
                window.showNotification(`Gagal terhubung ke server untuk menyimpan arsip: ${error.message}. Pastikan file PHP ada dan tidak ada error server.`, 'error');
                console.error("Fetch Error:", error);
            });
        });
    }


    if (window.innerWidth <= 768) {
        const header = document.querySelector('.header h1');
        const menuBtn = document.createElement('button');
        menuBtn.innerHTML = '<i class="fas fa-bars"></i>';
        menuBtn.className = 'btn btn-primary';
        menuBtn.onclick = window.toggleMobileMenu; // Panggil melalui window
        header.insertAdjacentElement('beforebegin', menuBtn);
    }

    setTimeout(() => {
        window.showNotification('Selamat datang di Dashboard Administrator!', 'success');
    }, 1000);

    window.showTab('dashboard'); // Panggil melalui window
});

window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        document.querySelector('.sidebar').classList.remove('mobile-open');
    }
});
