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
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="tentang_dprd.php">Tentang DPRD</a></li>
                    <li><a href="kearsipan_dprd.php">Kearsipan</a></li>
                    <li><a href="kegiatan_pegawai.php">Kegiatan</a></li>
                    <li><a href="chat.php" class="active">Hubungi Kami</a></li>
                    <li><a href="../pages/login.php" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </a></li>
                </ul>
        </div>
    </header>

    <main class="main-container">
        <div class="content-page" style="text-align: center; margin-bottom: 2rem;">
            <h1><i class="fas fa-comments"></i> Layanan Komunikasi DPRD</h1>
            <p>Kami menyediakan berbagai saluran komunikasi untuk memudahkan masyarakat dalam mendapatkan informasi, konsultasi, dan bantuan terkait layanan kearsipan DPRD. Tim customer service kami siap membantu Anda dengan respon yang cepat dan solusi yang tepat.</p>
            
            <div style="margin: 2rem 0;">
                <button onclick="toggleChatModal()" class="btn" style="font-size: 1.1rem; padding: 1rem 2rem;">
                    <i class="fas fa-comment-dots"></i>
                    Mulai Chat dengan Admin
                </button>
            </div>
        </div>

        <!-- Chat Modal -->
        <div id="chatModal" class="chat-modal" style="display: none;">
            <div class="chat-modal-content">
                <div class="chat-modal-header">
                    <h3><i class="fas fa-user-tie"></i> Chat dengan Admin DPRD</h3>
                    <button onclick="toggleChatModal()" class="close-chat-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="chat-container">
            <div class="chat-header">
                    <h4>Admin DPRD Kearsipan</h4>
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
            </div>
        </div>

        <div class="form-container">
            <h2><i class="fas fa-envelope"></i> Formulir Pesan Terstruktur</h2>
            <p>Jika Anda memiliki pertanyaan yang kompleks atau memerlukan dokumentasi resmi, silakan gunakan formulir di bawah ini. Pesan Anda akan diproses oleh tim yang tepat dan mendapat respons dalam bentuk yang lebih formal sesuai dengan kebutuhan Anda.</p>
            
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
            <h2><i class="fas fa-question-circle"></i> Informasi Umum dan Pertanyaan Populer</h2>
            <p>Berikut adalah kumpulan informasi dan jawaban atas pertanyaan-pertanyaan yang paling sering diajukan oleh masyarakat terkait layanan kearsipan DPRD. Informasi ini disusun berdasarkan pengalaman dan interaksi nyata dengan pengguna layanan kami.</p>
            
            <div style="margin-bottom: 2rem;">
                <h3>Prosedur Pengajuan Berkas Keluarga</h3>
                <p>Untuk mengajukan berkas keluarga, Anda dapat memanfaatkan layanan online kami melalui halaman <a href="berkas_masyarakat.php">Berkas Masyarakat/Keluarga</a>. Proses pengajuan dirancang untuk memberikan kemudahan maksimal kepada masyarakat, dimana Anda hanya perlu mengisi formulir secara lengkap dan mengunggah dokumen pendukung yang diperlukan. Tim kami akan memproses pengajuan Anda dengan standar pelayanan yang telah ditetapkan, dan Anda akan mendapat notifikasi perkembangan melalui email yang terdaftar.</p>
            </div>

            <div style="margin-bottom: 2rem;">
                <h3>Estimasi Waktu Pemrosesan Berkas</h3>
                <p>Waktu pemrosesan berkas bervariasi tergantung pada jenis dan kompleksitas dokumen yang diajukan. Untuk berkas masyarakat dan keluarga, kami berkomitmen menyelesaikan dalam rentang 1-3 hari kerja dengan mempertimbangkan kelengkapan dokumen dan verifikasi yang diperlukan. Berkas masuk yang bersifat vital memerlukan waktu pemrosesan 2-5 hari kerja karena melibatkan proses verifikasi yang lebih mendalam dan koordinasi dengan berbagai pihak terkait. Sementara untuk berkas keluar atau dokumen inactive, prosesnya relatif lebih cepat yaitu 1-2 hari kerja karena sifatnya yang lebih administratif.</p>
            </div>

            <div style="margin-bottom: 2rem;">
                <h3>Sistem Monitoring Status Pengajuan</h3>
                <p>Kami memahami pentingnya transparansi dalam proses penanganan berkas, oleh karena itu sistem notifikasi otomatis telah diimplementasikan untuk memberikan update status pengajuan Anda melalui email yang terdaftar. Setiap perubahan status, mulai dari penerimaan berkas, proses verifikasi, hingga penyelesaian, akan diinformasikan secara real-time. Selain itu, Anda juga dapat menghubungi tim customer service kami melalui fitur chat untuk mendapatkan informasi status terkini atau klarifikasi tambahan yang mungkin diperlukan.</p>
            </div>

            <div style="margin-bottom: 2rem;">
                <h3>Spesifikasi Format File yang Didukung</h3>
                <p>Sistem kami mendukung berbagai format file yang umum digunakan untuk memudahkan masyarakat dalam mengunggah dokumen. Format yang dapat diterima meliputi dokumen PDF untuk file yang sudah final, format Microsoft Office seperti DOC dan DOCX untuk dokumen teks, XLS dan XLSX untuk spreadsheet, serta format gambar JPG, JPEG, dan PNG untuk dokumen yang berupa scan atau foto. Ukuran file maksimal yang dapat diunggah bervariasi antara 10-20MB tergantung pada jenis pengajuan, dengan pertimbangan keseimbangan antara kualitas dokumen dan efisiensi sistem.</p>
            </div>
        </div>
    </main>

    <footer class="main-footer">
        <p>&copy; 2025 Sistem Informasi Kearsipan DPRD. Semua hak cipta dilindungi.</p>
    </footer>

    <script>
        // Chat Modal Functions
        function toggleChatModal() {
            const modal = document.getElementById('chatModal');
            if (modal.style.display === 'none' || modal.style.display === '') {
                modal.style.display = 'flex';
                loadMessages();
            } else {
                modal.style.display = 'none';
            }
        }
        
        // Close modal when clicking outside
        document.getElementById('chatModal').addEventListener('click', function(e) {
            if (e.target === this) {
                toggleChatModal();
            }
        });
        
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
        // Removed auto-load, now only loads when chat modal is opened
    </script>
    
    <style>
        /* Chat Modal Styles */
        .chat-modal {
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
        
        .chat-modal-content {
            background: #2f3032;
            border-radius: 15px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .chat-modal-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #4285f4, #8ab4f8);
            color: white;
        }
        
        .chat-modal-header h3 {
            margin: 0;
            font-size: 1.2rem;
        }
        
        .close-chat-btn {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: background 0.3s ease;
        }
        
        .close-chat-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .chat-modal .chat-container {
            height: 400px;
            margin: 0;
        }
        
        .chat-modal .chat-header {
            background: #3c4043;
            padding: 15px;
        }
        
        .chat-modal .chat-header h4 {
            margin: 0;
            color: #e8eaed;
            font-size: 1rem;
        }
        
        .chat-modal .chat-header small {
            color: #9aa0a6;
        }
    </style>
</body>
</html>