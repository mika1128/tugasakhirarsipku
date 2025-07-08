<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireAdmin();

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak dikenal.'];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $submission_type = $_GET['type'] ?? '';
    
    $query = "SELECT * FROM berkas_submissions";
    $params = [];
    
    if (!empty($submission_type)) {
        $query .= " WHERE submission_type = :submission_type";
        $params[':submission_type'] = $submission_type;
    }
    
    $query .= " ORDER BY tanggal_submit DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'status' => 'success',
        'data' => $submissions,
        'total' => count($submissions)
    ];
    
} catch (PDOException $e) {
    error_log("Error loading berkas submissions: " . $e->getMessage());
    $response = ['status' => 'error', 'message' => 'Kesalahan database: ' . $e->getMessage()];
}

echo json_encode($response);
?>