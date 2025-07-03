<?php
/**
 * Agenda Controller
 * File: pages/controllers/AgendaController.php
 */

require_once __DIR__ . '/../../includes/BaseController.php';

class AgendaController extends BaseController {
    
    public function getAll() {
        $this->validateMethod(['GET']);
        
        $query = "
            SELECT a.*, u.username 
            FROM agenda a 
            JOIN users u ON a.user_id = u.id 
            ORDER BY a.start_date ASC
        ";
        
        $stmt = $this->executeQuery($query);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        Helpers::jsonResponse('success', 'Data retrieved successfully', $data);
    }
    
    public function getById($id) {
        $this->validateMethod(['GET']);
        $user_id = $this->getCurrentUserId();
        
        $query = "
            SELECT * FROM agenda 
            WHERE id = :id AND user_id = :user_id
        ";
        
        $stmt = $this->executeQuery($query, [
            ':id' => $id,
            ':user_id' => $user_id
        ]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            Helpers::jsonResponse('error', 'Agenda not found');
        }
        
        Helpers::jsonResponse('success', 'Data retrieved successfully', $data);
    }
    
    public function create() {
        $this->validateMethod(['POST']);
        $user_id = $this->getCurrentUserId();
        $input = $this->getJsonInput();
        
        // Validate required fields
        $required = ['title', 'description', 'start_date', 'end_date'];
        $missing = Helpers::validateRequired($input, $required);
        
        if (!empty($missing)) {
            Helpers::jsonResponse('error', 'Missing required fields: ' . implode(', ', $missing));
        }
        
        // Sanitize input
        $data = Helpers::sanitize($input);
        
        $query = "
            INSERT INTO agenda (user_id, title, description, start_date, end_date, location, priority, status) 
            VALUES (:user_id, :title, :description, :start_date, :end_date, :location, :priority, :status)
        ";
        
        $params = [
            ':user_id' => $user_id,
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':location' => $data['location'] ?? null,
            ':priority' => $data['priority'] ?? 'medium',
            ':status' => $data['status'] ?? 'pending'
        ];
        
        $this->executeQuery($query, $params);
        
        Helpers::jsonResponse('success', 'Agenda created successfully');
    }
    
    public function update($id) {
        $this->validateMethod(['POST']);
        $user_id = $this->getCurrentUserId();
        $input = $this->getJsonInput();
        
        // Validate required fields
        $required = ['title', 'description', 'start_date', 'end_date'];
        $missing = Helpers::validateRequired($input, $required);
        
        if (!empty($missing)) {
            Helpers::jsonResponse('error', 'Missing required fields: ' . implode(', ', $missing));
        }
        
        // Sanitize input
        $data = Helpers::sanitize($input);
        
        $query = "
            UPDATE agenda SET 
                title = :title, 
                description = :description, 
                start_date = :start_date, 
                end_date = :end_date, 
                location = :location, 
                priority = :priority, 
                status = :status 
            WHERE id = :id AND user_id = :user_id
        ";
        
        $params = [
            ':id' => $id,
            ':user_id' => $user_id,
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':location' => $data['location'] ?? null,
            ':priority' => $data['priority'] ?? 'medium',
            ':status' => $data['status'] ?? 'pending'
        ];
        
        $stmt = $this->executeQuery($query, $params);
        
        if ($stmt->rowCount() === 0) {
            Helpers::jsonResponse('error', 'Agenda not found or no changes made');
        }
        
        Helpers::jsonResponse('success', 'Agenda updated successfully');
    }
    
    public function delete($id) {
        $this->validateMethod(['POST']);
        $user_id = $this->getCurrentUserId();
        
        $query = "DELETE FROM agenda WHERE id = :id AND user_id = :user_id";
        $stmt = $this->executeQuery($query, [':id' => $id, ':user_id' => $user_id]);
        
        if ($stmt->rowCount() === 0) {
            Helpers::jsonResponse('error', 'Agenda not found');
        }
        
        Helpers::jsonResponse('success', 'Agenda deleted successfully');
    }
}

// Handle routing
$controller = new AgendaController();
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

switch ($action) {
    case 'get':
        if ($id) {
            $controller->getById($id);
        } else {
            $controller->getAll();
        }
        break;
    case 'create':
        $controller->create();
        break;
    case 'update':
        $controller->update($id);
        break;
    case 'delete':
        $controller->delete($id);
        break;
    default:
        Helpers::jsonResponse('error', 'Invalid action');
}
?>