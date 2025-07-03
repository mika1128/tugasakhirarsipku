<?php
// Misalnya ini menangani session error atau flash message
session_start();
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun</title>
    <link rel="stylesheet" href="../assets/css/registrasi.css">
</head>
<body>
    <div class="form-container">
        <form action="proses_registrasi.php" method="post">
            <h2>Registrasi Akun</h2>

            <?php if (!empty($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" id="name" name="name" placeholder="Nama lengkap" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Username unik" required>
            </div>

            <div class="form-group">
                <label for="email">Email Aktif</label>
                <input type="email" id="email" name="email" placeholder="nama@email.com" required>
            </div>

            <div class="form-group">
                <label for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Konfirmasi Kata Sandi</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
            </div>

            <button type="submit">Daftar</button>

            <p class="form-note">
                Sudah punya akun? <a href="login.php">Login di sini</a>
            </p>
        </form>
    </div>

    <script src="../assets/js/registrasi.js"></script>
</body>
</html>
