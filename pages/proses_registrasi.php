<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Inisialisasi koneksi database
$db = new Database();
$pdo = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan bersihkan data
    $full_name         = htmlspecialchars(trim($_POST['name'] ?? ''));
    $username          = htmlspecialchars(trim($_POST['username'] ?? ''));
    $email             = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password          = $_POST['password'] ?? '';
    $confirm_password  = $_POST['confirm_password'] ?? '';

    // Validasi awal
    if (empty($full_name) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "Semua field wajib diisi.";
        header("Location: registrasi.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Format email tidak valid.";
        header("Location: registrasi.php");
        exit;
    }

    if (strlen($username) < 3 || strtolower($username) === 'root') {
        $_SESSION['error'] = "Username tidak valid atau terlalu pendek.";
        header("Location: registrasi.php");
        exit;
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = "Password minimal 6 karakter.";
        header("Location: registrasi.php");
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Konfirmasi password tidak cocok.";
        header("Location: registrasi.php");
        exit;
    }

    try {
        // Cek apakah username atau email sudah digunakan
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email
        ]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['error'] = "Username atau email sudah digunakan.";
            header("Location: registrasi.php");
            exit;
        }

        // Simpan ke database
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, is_active)
                               VALUES (:username, :email, :password, :full_name, :role, :is_active)");
        $stmt->execute([
            ':username'   => $username,
            ':email'      => $email,
            ':password'   => $hashedPassword,
            ':full_name'  => $full_name,
            ':role'       => 'user',
            ':is_active'  => 1
        ]);

        $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
        header("Location: login.php");
        exit;

    } catch (PDOException $e) {
        error_log("Registrasi error: " . $e->getMessage());
        $_SESSION['error'] = "Terjadi kesalahan saat registrasi.";
        header("Location: registrasi.php");
        exit;
    }
} else {
    header("Location: registrasi.php");
    exit;
}
