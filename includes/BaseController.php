<?php
/**
 * Base Controller Class
 * File: includes/BaseController.php
 */

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';

abstract class BaseController {
    protected $db;
    protected $conn;
    protected $auth;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->auth = new Auth();
        
        // Ensure JSON response
        header('Content-Type: application/json');
    }
    
    /**
     * Validate request method
     */
    protected function validateMethod($allowed_methods = ['POST']) {
        if (!in_array($_SERVER['REQUEST_METHOD'], $allowed_methods)) {
            Helpers::jsonResponse('error', 'Method not allowed');
        }
    }
    
    /**
     * Get current user ID
     */
    protected function getCurrentUserId() {
        if (!$this->auth->isLoggedIn()) {
            Helpers::jsonResponse('error', 'Unauthorized');
        }
        return $_SESSION['user_id'];
    }
    
    /**
     * Require admin access
     */
    protected function requireAdmin() {
        if (!$this->auth->isAdmin()) {
            Helpers::jsonResponse('error', 'Admin access required');
        }
    }
    
    /**
     * Get JSON input
     */
    protected function getJsonInput() {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Helpers::jsonResponse('error', 'Invalid JSON input');
        }
        return $input;
    }
    
    /**
     * Execute database query safely
     */
    protected function executeQuery($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            Helpers::jsonResponse('error', 'Database operation failed');
        }
    }
}
?>