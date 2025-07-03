<?php
/**
 * Sistem Autentikasi
 * File: includes/auth.php
 */

require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($username, $password, $remember = false) {
        try {
            $query = "SELECT id, username, email, password, full_name, role, is_active
                      FROM users
                      WHERE (username = :username_param OR email = :email_param) AND is_active = 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username_param', $username);
            $stmt->bindParam(':email_param', $username);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['last_activity'] = time();

                    $this->updateLastLogin($user['id']);
                    $this->logActivity($user['id'], 'login', 'system', null, 'User login ke sistem');

                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        setcookie('remember_token', $token, time() + Config::REMEMBER_ME_TIMEOUT, '/');
                        $this->saveRememberToken($user['id'], $token);
                    }

                    return [
                        'status' => 'success',
                        'message' => 'Login berhasil',
                        'user' => [
                            'id' => $user['id'],
                            'username' => $user['username'],
                            'full_name' => $user['full_name'],
                            'role' => $user['role']
                        ]
                    ];
                } else {
                    return ['status' => 'error', 'message' => 'Password salah'];
                }
            } else {
                return ['status' => 'error', 'message' => 'Username atau email tidak ditemukan'];
            }

        } catch (PDOException $e) {
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'system', null, 'User logout dari sistem');
        }

        session_unset();
        session_destroy();

        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }

        return ['status' => 'success', 'message' => 'Logout berhasil'];
    }

    public function register($data) {
        try {
            $validation = $this->validateRegistration($data);
            if (!$validation['valid']) {
                return ['status' => 'error', 'message' => $validation['message']];
            }

            $hashed_password = password_hash($data['password'], Config::PASSWORD_HASH_ALGO, Config::PASSWORD_HASH_OPTIONS);

            $query = "INSERT INTO users (username, email, password, full_name, role) 
                      VALUES (:username, :email, :password, :full_name, :role)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':full_name', $data['full_name']);
            $stmt->bindParam(':role', $data['role']);

            if ($stmt->execute()) {
                $user_id = $this->conn->lastInsertId();
                $this->logActivity($user_id, 'register', 'system', null, 'User baru terdaftar');
                return ['status' => 'success', 'message' => 'Registrasi berhasil', 'user_id' => $user_id];
            } else {
                return ['status' => 'error', 'message' => 'Gagal menyimpan data user'];
            }

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['status' => 'error', 'message' => 'Username atau email sudah digunakan'];
            }
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    private function validateRegistration($data) {
        $errors = [];

        if (empty($data['username'])) {
            $errors[] = 'Username tidak boleh kosong';
        } elseif (strlen($data['username']) < 3) {
            $errors[] = 'Username minimal 3 karakter';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $errors[] = 'Username hanya boleh mengandung huruf, angka, dan underscore';
        }

        if (empty($data['email'])) {
            $errors[] = 'Email tidak boleh kosong';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        }

        if (empty($data['password'])) {
            $errors[] = 'Password tidak boleh kosong';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'Password minimal 6 karakter';
        }

        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = 'Konfirmasi password tidak cocok';
        }

        if (empty($data['full_name'])) {
            $errors[] = 'Nama lengkap tidak boleh kosong';
        }

        return [
            'valid' => empty($errors),
            'message' => implode(', ', $errors)
        ];
    }

    public function isLoggedIn() {
        if (isset($_SESSION['user_id']) && isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > Config::SESSION_TIMEOUT) {
                $this->logout();
                return false;
            }
            $_SESSION['last_activity'] = time();
            return true;
        }
        return false;
    }

    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'full_name' => $_SESSION['full_name'],
                'role' => $_SESSION['role'],
                'email' => $_SESSION['email']
            ];
        }
        return null;
    }

    private function updateLastLogin($user_id) {
        $query = "UPDATE users SET last_login = NOW() WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    }

    public function logActivity($user_id, $action_type, $target_type, $target_id = null, $description = '') {
        try {
            $query = "INSERT INTO activities (user_id, action_type, target_type, target_id, description, ip_address, user_agent) 
                      VALUES (:user_id, :action_type, :target_type, :target_id, :description, :ip_address, :user_agent)";
            
            $stmt = $this->conn->prepare($query);

            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':action_type', $action_type);
            $stmt->bindValue(':target_type', $target_type);
            $stmt->bindValue(':target_id', $target_id, is_null($target_id) ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':description', $description);
            $stmt->bindValue(':ip_address', $ip_address);
            $stmt->bindValue(':user_agent', $user_agent);

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error logging activity: " . $e->getMessage());
            return false;
        }
    }

    private function saveRememberToken($user_id, $token) {
        // Implementasi opsional
    }

    public function requireLogin($redirect_url = '/ArsipKu/pages/login.php') {
        if (!$this->isLoggedIn()) {
            header("Location: $redirect_url");
            exit();
        }
    }

    public function requireAdmin($redirect_url = '/ArsipKu/home/home.php') {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            header("Location: $redirect_url"); // Jangan arahkan ke admin lagi!
            exit();
        }
    }

    public function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(Config::CSRF_TOKEN_LENGTH));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    // Fungsi tambahan untuk redirect sesuai role
    public function getRedirectUrlByRole($role) {
        switch ($role) {
            case 'admin':
                return '/ArsipKu/pages/admin.php';
            default:
                return '/ArsipKu/home/home.php';
        }
    }

    public function redirectAfterLogin() {
        if ($this->isLoggedIn()) {
            $url = $this->getRedirectUrlByRole($_SESSION['role']);
            header("Location: $url");
            exit();
        }
    }
}
?>
