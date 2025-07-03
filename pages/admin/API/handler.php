<?php
// File: admin/api/handler.php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

$auth = new Auth();
$auth->requireAdmin();

$db = new Database();
$conn = $db->getConnection();

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action'];

try {
    switch ($action) {
        case 'get_agenda':
            $response = getAgenda($conn);
            break;
        case 'get_users':
            $response = getUsers($conn);
            break;
        case 'get_history':
            $response = getHistory($conn);
            break;
        case 'get_statistics':
            $response = getStatistics($conn);
            break;
        case 'add_agenda':
            $response = addAgenda($conn, $_POST);
            break;
        case 'update_agenda':
            $response = updateAgenda($conn, $_POST);
            break;
        case 'delete_agenda':
            $response = deleteAgenda($conn, $_POST['id']);
            break;
        case 'add_user':
            $response = addUser($conn, $_POST);
            break;
        case 'update_user':
            $response = updateUser($conn, $_POST);
            break;
        case 'delete_user':
            $response = deleteUser($conn, $_POST['id']);
            break;
        case 'update_settings':
            $response = updateSettings($conn, $_POST);
            break;
        default:
            $response = ['success' => false, 'message' => 'Action not found'];
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);

// AGENDA FUNCTIONS
function getAgenda($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT a.*, u.username, u.nama as user_nama 
            FROM agenda a 
            LEFT JOIN users u ON a.user_id = u.id 
            ORDER BY a.tanggal_mulai DESC
        ");
        $stmt->execute();
        $agenda = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $agenda
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error fetching agenda: ' . $e->getMessage()
        ];
    }
}

function addAgenda($conn, $data) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO agenda (user_id, judul, deskripsi, tanggal_mulai, tanggal_berakhir, lokasi, status, prioritas, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $data['user_id'],
            $data['judul'],
            $data['deskripsi'],
            $data['tanggal_mulai'],
            $data['tanggal_berakhir'],
            $data['lokasi'],
            $data['status'] ?? 'aktif',
            $data['prioritas'] ?? 'sedang'
        ]);
        
        return [
            'success' => true,
            'message' => 'Agenda berhasil ditambahkan',
            'data' => ['id' => $conn->lastInsertId()]
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error adding agenda: ' . $e->getMessage()
        ];
    }
}

function updateAgenda($conn, $data) {
    try {
        $stmt = $conn->prepare("
            UPDATE agenda 
            SET judul = ?, deskripsi = ?, tanggal_mulai = ?, tanggal_berakhir = ?, 
                lokasi = ?, status = ?, prioritas = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([
            $data['judul'],
            $data['deskripsi'],
            $data['tanggal_mulai'],
            $data['tanggal_berakhir'],
            $data['lokasi'],
            $data['status'],
            $data['prioritas'],
            $data['id']
        ]);
        
        return [
            'success' => true,
            'message' => 'Agenda berhasil diperbarui'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error updating agenda: ' . $e->getMessage()
        ];
    }
}

function deleteAgenda($conn, $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM agenda WHERE id = ?");
        $stmt->execute([$id]);
        
        return [
            'success' => true,
            'message' => 'Agenda berhasil dihapus'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error deleting agenda: ' . $e->getMessage()
        ];
    }
}

// USER FUNCTIONS
function getUsers($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT id, username, email, nama, role, status, created_at 
            FROM users 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $users
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error fetching users: ' . $e->getMessage()
        ];
    }
}

function addUser($conn, $data) {
    try {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$data['username'], $data['email']]);
        if ($stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Username atau email sudah digunakan'
            ];
        }
        
        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password, nama, role, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt->execute([
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['nama'],
            $data['role'] ?? 'user',
            $data['status'] ?? 'aktif'
        ]);
        
        return [
            'success' => true,
            'message' => 'Pengguna berhasil ditambahkan',
            'data' => ['id' => $conn->lastInsertId()]
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error adding user: ' . $e->getMessage()
        ];
    }
}

function updateUser($conn, $data) {
    try {
        $sql = "UPDATE users SET username = ?, email = ?, nama = ?, role = ?, status = ?, updated_at = NOW() WHERE id = ?";
        $params = [$data['username'], $data['email'], $data['nama'], $data['role'], $data['status'], $data['id']];
        
        // Update password if provided
        if (!empty($data['password'])) {
            $sql = "UPDATE users SET username = ?, email = ?, password = ?, nama = ?, role = ?, status = ?, updated_at = NOW() WHERE id = ?";
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $params = [$data['username'], $data['email'], $hashedPassword, $data['nama'], $data['role'], $data['status'], $data['id']];
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        return [
            'success' => true,
            'message' => 'Pengguna berhasil diperbarui'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error updating user: ' . $e->getMessage()
        ];
    }
}

function deleteUser($conn, $id) {
    try {
        // Don't allow deleting the last admin
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin' AND id != ?");
        $stmt->execute([$id]);
        $adminCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user['role'] === 'admin' && $adminCount == 0) {
            return [
                'success' => false,
                'message' => 'Tidak dapat menghapus admin terakhir'
            ];
        }
        
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        return [
            'success' => true,
            'message' => 'Pengguna berhasil dihapus'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error deleting user: ' . $e->getMessage()
        ];
    }
}

// HISTORY FUNCTIONS
function getHistory($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT a.*, u.username, u.nama as user_nama 
            FROM agenda a 
            LEFT JOIN users u ON a.user_id = u.id 
            WHERE a.status IN ('selesai', 'dibatalkan') 
            ORDER BY a.tanggal_berakhir DESC
        ");
        $stmt->execute();
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $history
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error fetching history: ' . $e->getMessage()
        ];
    }
}

// STATISTICS FUNCTIONS
function getStatistics($conn) {
    try {
        $stats = [];
        
        // Total users
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total agenda
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM agenda");
        $stmt->execute();
        $stats['total_agenda'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Active agenda
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM agenda WHERE status = 'aktif'");
        $stmt->execute();
        $stats['active_agenda'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Completed agenda
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM agenda WHERE status = 'selesai'");
        $stmt->execute();
        $stats['completed_agenda'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Active users today
        $stmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) as count FROM agenda WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $stats['active_users_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Agenda created this month
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM agenda WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
        $stmt->execute();
        $stats['agenda_this_month'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        return [
            'success' => true,
            'data' => $stats
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error fetching statistics: ' . $e->getMessage()
        ];
    }
}

// SETTINGS FUNCTIONS
function updateSettings($conn, $data) {
    try {
        // Create settings table if not exists
        $conn->exec("
            CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(255) UNIQUE,
                setting_value TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Update settings
        $settings = [
            'app_name' => $data['app_name'] ?? 'ArsipKu',
            'admin_email' => $data['admin_email'] ?? 'admin@arsipku.com',
            'timezone' => $data['timezone'] ?? 'Asia/Jakarta'
        ];
        
        foreach ($settings as $key => $value) {
            $stmt = $conn->prepare("
                INSERT INTO settings (setting_key, setting_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = ?
            ");
            $stmt->execute([$key, $value, $value]);
        }
        
        return [
            'success' => true,
            'message' => 'Pengaturan berhasil disimpan'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error updating settings: ' . $e->getMessage()
        ];
    }
}
?>