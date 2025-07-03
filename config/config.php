<?php
/**
 * Configuration Class
 * File: config/Config.php
 */

class Config {
    // Database Configuration
    const DB_HOST = 'localhost';
    const DB_NAME = 'sistem_dokumen';
    const DB_USER = 'root';
    const DB_PASS = '';
    const DB_CHARSET = 'utf8mb4';

    // Application Configuration
    const BASE_URL = 'http://localhost/ArsipKu/';
    const BASE_PATH = __DIR__ . '/../';
    
    // Upload Configuration
    const UPLOAD_PATH = 'uploads/documents/';
    const MAX_FILE_SIZE = 10485760; // 10MB in bytes
    
    // Allowed File Types
    const ALLOWED_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'txt', 'rtf', 'odt', 'ods', 'odp',
        'jpg', 'jpeg', 'png', 'gif', 'bmp',
        'zip', 'rar', '7z'
    ];
    
    const ALLOWED_MIME_TYPES = [
        // PDF
        'application/pdf',
        
        // Microsoft Office
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        
        // Text files
        'text/plain',
        'text/rtf',
        'application/rtf',
        
        // OpenDocument
        'application/vnd.oasis.opendocument.text',
        'application/vnd.oasis.opendocument.spreadsheet',
        'application/vnd.oasis.opendocument.presentation',
        
        // Images
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/bmp',
        
        // Archives
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed'
    ];
    
    // Pagination
    const ITEMS_PER_PAGE = 10;
    
    // Session Configuration
    const SESSION_NAME = 'doc_management_session';
    const SESSION_LIFETIME = 3600; // 1 hour
    
    // Security
    const HASH_ALGORITHM = 'sha256';
    const ENCRYPTION_KEY = 'your-secret-encryption-key-here';
    
    // Application Settings
    const APP_NAME = 'Document Management System';
    const APP_VERSION = '1.0.0';
    const TIMEZONE = 'Asia/Jakarta';
    
    // Email Configuration (if needed)
    const SMTP_HOST = 'localhost';
    const SMTP_PORT = 587;
    const SMTP_USER = '';
    const SMTP_PASS = '';
    const SMTP_FROM = 'noreply@documentmanagement.com';
    
    // Logging
    const LOG_PATH = 'logs/';
    const LOG_LEVEL = 'INFO'; // DEBUG, INFO, WARNING, ERROR
    
    // Cache
    const CACHE_ENABLED = false;
    const CACHE_LIFETIME = 3600;
    
    /**
     * Get configuration value
     */
    public static function get($key, $default = null) {
        $reflection = new ReflectionClass(__CLASS__);
        $constants = $reflection->getConstants();
        
        return isset($constants[$key]) ? $constants[$key] : $default;
    }
    
    /**
     * Get upload directory path
     */
    public static function getUploadPath() {
        return self::BASE_PATH . self::UPLOAD_PATH;
    }
    
    /**
     * Get full upload URL
     */
    public static function getUploadUrl() {
        return self::BASE_URL . self::UPLOAD_PATH;
    }
    
    /**
     * Check if file extension is allowed
     */
    public static function isAllowedExtension($extension) {
        return in_array(strtolower($extension), self::ALLOWED_EXTENSIONS);
    }
    
    /**
     * Check if MIME type is allowed
     */
    public static function isAllowedMimeType($mimeType) {
        return in_array($mimeType, self::ALLOWED_MIME_TYPES);
    }
    
    /**
     * Format file size limit for display
     */
    public static function getMaxFileSizeFormatted() {
        $bytes = self::MAX_FILE_SIZE;
        $units = array('B', 'KB', 'MB', 'GB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
?>