<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();

$error = '';

// Tangani form login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $loginResult = $auth->login($username, $password, $remember);

    if ($loginResult['status'] === 'success') {
        $role = $loginResult['user']['role'];

        if ($role === 'admin') {
            header('Location: ../pages/admin.php');
        } else {
            header('Location: /ArsipKu/home/home.php');
        }
        exit();
    } else {
        $error = $loginResult['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Manajemen Dokumen</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <?php if (!empty($error)) : ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username atau Email</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Ingat saya</label>
            </div>

            <div class="form-group">
                <button type="submit">Login</button>
            </div>
            <p>Belum punya akun? <a href="../pages/registrasi.php">Daftar di sini</a></p>
        </form>
    </div>

    <script src="../assets/js/login.js"></script>
</body>
</html>
