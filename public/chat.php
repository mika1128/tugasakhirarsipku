<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat dengan Admin - DPRD Kearsipan</title>
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
        </div>
    </header>

    <main class="main-container">
        <div class="content-page">
            <h1><i class="fas fa-comments"></i> Chat dengan Admin DPRD</h1>
            <p>Hubungi admin untuk pertanyaan, konsultasi, atau bantuan terkait layanan kearsipan DPRD.</p>
        </div>

        <div class="chat-container">
            <div class="chat-header">
                <h3><i class="fas fa-user-tie"></i> Admin DPRD Kearsipan</h3>
                <small>Tim kami siap membantu Anda</small>
            </div>
            
            <div class="chat-messages" id="chatMessages">
                <div class="message admin">
                    <div>Selamat datang di layanan chat DPRD Kearsipan! Bagaimana kami dapat membantu Anda hari ini?</div>
                    <div class="message-info">Admin • Sekarang</div>
                </div>
            </div>
            
            <div class="chat-input">
                <input type="text" id="messageInput" placeholder="Ketik pesan Anda di sini..." maxlength="500">
                <button onclick="sendMessage()" class="btn">
                    <i class="fas fa-paper-plane"></i>
                    Kirim
                </button>
            </div>
        </div>

        <div class="form-container">
            <h2><i class="fas fa-envelope"></i> Kirim Pesan Detail</h2>
            <p>Untuk pertanyaan yang lebih detail, silakan isi formulir di bawah ini:</p>
            
            <form id="detailMessageForm">
                <div class="form-group">
                    <label for="senderName">Nama Lengkap <span style="color: red;">*</span></label>
                    <input type="text" id="senderName" name="sender_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="senderEmail">Email <span style="color: red;">*</span></label>
                    <input type="email" id="senderEmail" name="sender_email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="messageSubject">Subjek</label>
                    <select id="messageSubject" name="message_subject" class="form-control">
                        <option value="">Pilih topik pertanyaan</option>
                        <option value="Pengajuan Berkas">Pengajuan Berkas</option>
                        <option value="Status Pengajuan">Status Pengajuan</option>
                        <option value="Informasi Layanan">Informasi Layanan</option>
                        <option value="Teknis Website">Teknis Website</option>
                        <option value="Keluhan">Keluhan</option>
                        <option value="Saran">Saran</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="detailMessage">Pesan Detail <span style="color: red;">*</span></label>
                    <textarea id="detailMessage" name="message" class="form-control" rows="5" placeholder="Jelaskan pertanyaan atau kendala Anda secara detail..." required></textarea>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-secondary" style="width: 100%;">
                        <i class="fas fa-envelope"></i>
                        Kirim Pesan Detail
                    </button>
                </div>
            </form>
        </div>

        <div class="content-page">
            <h2><i class="fas fa-question-circle"></i> Pertanyaan yang Sering Diajukan (FAQ)</h2>
            
            <div style="margin-bottom: 2rem;">
                <h3>Q: Bagaimana cara mengajukan berkas keluarga?</h3>
                <p><strong>A:</strong> Anda dapat mengajukan berkas keluarga melalui halaman <a href="berkas_masyarakat.php">Berkas Masyarakat/Keluarga</a>. Isi formulir dengan lengkap dan upload dokumen pendukung jika diperlukan.</p>
            </div>

            <div style="margin-bottom: 2rem;">
                <h3>Q: Berapa lama waktu pemrosesan berkas?</h3>
                <p><strong>A:</strong> Waktu pemrosesan berbeda-beda tergantung jenis berkas:</p>
                <ul>
                    <li>Berkas Masyarakat/Keluarga: 1-3 hari kerja</li>
                    <li>Berkas Masuk (Vital): 2-5 hari kerja</li>
                    <li>Berkas Keluar (Inactive): 1-2 hari kerja</li>
                </ul>
            </div>

            <div style="margin-bottom: 2rem;">
                <h3>Q: Bagaimana cara mengecek status pengajuan?</h3>
                <p><strong>A:</strong> Anda akan mendapat notifikasi melalui email setiap ada update status pengajuan. Anda juga dapat menghubungi kami melalui chat untuk menanyakan status.</p>
            </div>

            <div style="margin-bottom: 2rem;">
                <h3>Q: Format file apa saja yang bisa diupload?</h3>
                <p><strong>A:</strong> Format yang didukung meliputi PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, dan PNG dengan ukuran maksimal 10-20MB tergantung jenis pengajuan.</p>
            </div>
        </div>
    </main>

    <footer class="main-footer">
        <p>&copy; 2025 Sistem Informasi Kearsipan DPRD. Semua hak cipta dilindungi.</p>
    </footer>

    <script>
        // Load existing messages
        async function loadMessages() {
            try {
                const response = await fetch('get_messages.php');
                const result = await response.json();
                
                const chatMessages = document.getElementById('chatMessages');
                
                if (result.status === 'success' && result.data.length > 0) {
                    // Clear existing messages except welcome message
                    chatMessages.innerHTML = `
                        <div class="message admin">
                            <div>Selamat datang di layanan chat DPRD Kearsipan! Bagaimana kami dapat membantu Anda hari ini?</div>
                            <div class="message-info">Admin • Sekarang</div>
                        </div>
                    `;
                    
                    result.data.forEach(message => {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `message ${message.sender_type}`;
                        
                        const date = new Date(message.created_at);
                        const timeString = date.toLocaleString('id-ID');
                        
                        messageDiv.innerHTML = `
                            <div>${message.message}</div>
                            <div class="message-info">${message.sender_name} • ${timeString}</div>
                        `;
                        
                        chatMessages.appendChild(messageDiv);
                    });
                    
                    // Scroll to bottom
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            } catch (error) {
                console.error('Error loading messages:', error);
            }
        }

        // Send quick message
        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            
            if (!message) return;
            
            // Add message to chat immediately
            const chatMessages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message user';
            messageDiv.innerHTML = `
                <div>${message}</div>
                <div class="message-info">Anda • Sekarang</div>
            `;
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            // Clear input
            messageInput.value = '';
            
            // Send to server
            fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    sender_type: 'public',
                    sender_name: 'Pengunjung',
                    sender_email: '',
                    message: message
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    // Auto reply
                    setTimeout(() => {
                        const replyDiv = document.createElement('div');
                        replyDiv.className = 'message admin';
                        replyDiv.innerHTML = `
                            <div>Terima kasih atas pesan Anda. Tim admin akan segera merespon pertanyaan Anda. Untuk respon yang lebih cepat, silakan isi formulir detail di bawah chat ini.</div>
                            <div class="message-info">Admin • Sekarang</div>
                        `;
                        chatMessages.appendChild(replyDiv);
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
            });
        }

        // Handle enter key in chat input
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Handle detail message form
        document.getElementById('detailMessageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                sender_type: 'public',
                sender_name: document.getElementById('senderName').value,
                sender_email: document.getElementById('senderEmail').value,
                message_subject: document.getElementById('messageSubject').value,
                message: document.getElementById('detailMessage').value
            };
            
            // Combine subject and message
            const fullMessage = formData.message_subject ? 
                `[${formData.message_subject}] ${formData.message}` : 
                formData.message;
            
            fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    sender_type: formData.sender_type,
                    sender_name: formData.sender_name,
                    sender_email: formData.sender_email,
                    message: fullMessage
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    showAlert('Pesan berhasil dikirim! Admin akan merespon melalui email dalam 1x24 jam.', 'success');
                    this.reset();
                } else {
                    showAlert('Gagal mengirim pesan. Silakan coba lagi.', 'error');
                }
            })
            .catch(error => {
                console.error('Error sending detail message:', error);
                showAlert('Terjadi kesalahan. Silakan coba lagi.', 'error');
            });
        });

        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
            
            // Insert at the top of main container
            const mainContainer = document.querySelector('.main-container');
            mainContainer.insertBefore(alertDiv, mainContainer.firstChild);
            
            // Remove after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Load messages when page loads
        document.addEventListener('DOMContentLoaded', loadMessages);
    </script>
</body>
</html>