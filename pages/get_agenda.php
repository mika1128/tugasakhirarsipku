<?php
// get_agenda.php

require_once __DIR__ . '/../config/database.php'; // sesuaikan path jika perlu

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("
       SELECT
        a.id,
        a.user_id,
        u.username, /* Add this line to select username */
        a.title,
        a.description,
        a.start_date,
        a.end_date,
        a.location,
        a.status,
        a.priority
        FROM agenda a
        JOIN users u ON a.user_id = u.id /* Add this line for the JOIN */
        ORDER BY a.start_date ASC
    ");

    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch as associative array

    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal mengambil data agenda',
        'error' => $e->getMessage()
    ]);
}
?>