<?php
/**
 * Konfigurasi Database
 * File: config/database.php
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'sistem_dokumen';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    public $conn;

    // Koneksi ke database
    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
            die();
        }

        return $this->conn;
    }

    // Test koneksi database
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            if ($conn) {
                return ['status' => 'success', 'message' => 'Database connected successfully'];
            }
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // Tutup koneksi
    public function closeConnection() {
        $this->conn = null;
    }
}

// Konfigurasi aplikasi
class Config {
    const APP_NAME = 'Sistem Manajemen Dokumen';
    const APP_VERSION = '1.0.0';
    const TIMEZONE = 'Asia/Jakarta';
    
    // Path dan URL
    const BASE_PATH = __DIR__ . '/../';
    const UPLOAD_PATH = 'uploads/';
    const MAX_FILE_SIZE = 10485760; // 10MB dalam bytes
    
    // File yang diizinkan
    const ALLOWED_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'jpg', 'jpeg', 'png', 'gif', 'bmp',
        'txt', 'rtf', 'csv',
        'zip', 'rar', '7z'
    ];
    
    // MIME types yang diizinkan
    const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/bmp',
        'text/plain',
        'text/rtf',
        'text/csv',
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed'
    ];
    
    // Session settings
    const SESSION_TIMEOUT = 3600; // 1 jam
    const REMEMBER_ME_TIMEOUT = 2592000; // 30 hari
    
    // Security
    const CSRF_TOKEN_LENGTH = 32;
    const PASSWORD_HASH_ALGO = PASSWORD_BCRYPT;
    const PASSWORD_HASH_OPTIONS = ['cost' => 12];
    
    // Pagination
    const ITEMS_PER_PAGE = 20;
    
    // Log levels
    const LOG_LEVEL_ERROR = 'ERROR';
    const LOG_LEVEL_WARNING = 'WARNING';
    const LOG_LEVEL_INFO = 'INFO';
    const LOG_LEVEL_DEBUG = 'DEBUG';
}

// Inisialisasi timezone
date_default_timezone_set(Config::TIMEZONE);

// Error reporting untuk development
if ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    define('DEBUG_MODE', true);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    define('DEBUG_MODE', false);
}
?>