<?php
/**
 * Helper Functions
 * File: includes/helpers.php
 */

class Helpers {
    
    /**
     * Standard JSON response
     */
    public static function jsonResponse($status, $message, $data = null, $error = null) {
        $response = [
            'status' => $status,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        if ($error !== null) {
            $response['error'] = $error;
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    /**
     * Validate required fields
     */
    public static function validateRequired($data, $required_fields) {
        $missing = [];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }
        return $missing;
    }
    
    /**
     * Handle file upload
     */
    public static function handleFileUpload($file, $upload_dir, $allowed_types = []) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'File upload error'];
        }
        
        // Create directory if not exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $target_path = $upload_dir . $filename;
        
        // Validate file type if specified
        if (!empty($allowed_types) && !in_array($extension, $allowed_types)) {
            return ['success' => false, 'message' => 'File type not allowed'];
        }
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            return ['success' => true, 'filename' => $filename];
        }
        
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
    
    /**
     * Delete file safely
     */
    public static function deleteFile($file_path) {
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        return true; // File doesn't exist, consider it deleted
    }
    
    /**
     * Sanitize input
     */
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Check if user has permission
     */
    public static function hasPermission($required_role = 'user') {
        if (!isset($_SESSION['role'])) {
            return false;
        }
        
        $role_hierarchy = ['user' => 1, 'admin' => 2];
        $user_level = $role_hierarchy[$_SESSION['role']] ?? 0;
        $required_level = $role_hierarchy[$required_role] ?? 0;
        
        return $user_level >= $required_level;
    }
}
?>