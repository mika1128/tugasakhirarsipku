<?php
/**
 * Document Manager Class
 * File: includes/DocumentManager.php
 */

require_once __DIR__ . '/..config/database.php';

class DocumentManager {
    private $db;
    private $conn;
    private $auth;

    public function __construct($auth = null) {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->auth = $auth;
    }

    // Upload dokumen
    public function uploadDocument($file, $user_id, $description = '', $tags = '', $category = '') {
        try {
            // Validasi file
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                return ['status' => 'error', 'message' => $validation['message']];
            }

            // Buat direktori upload jika belum ada
            $upload_dir = Config::BASE_PATH . Config::UPLOAD_PATH;
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Generate nama file unik
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $stored_name = uniqid() . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $stored_name;

            // Upload file
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Simpan info ke database
                $query = "INSERT INTO documents (user_id, original_name, stored_name, file_path, file_type, file_size, mime_type, description, tags, category) 
                         VALUES (:user_id, :original_name, :stored_name, :file_path, :file_type, :file_size, :mime_type, :description, :tags, :category)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':original_name', $file['name']);
                $stmt->bindParam(':stored_name', $stored_name);
                $stmt->bindParam(':file_path', Config::UPLOAD_PATH . $stored_name);
                $stmt->bindParam(':file_type', $file_extension);
                $stmt->bindParam(':file_size', $file['size']);
                $stmt->bindParam(':mime_type', $file['type']);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':tags', $tags);
                $stmt->bindParam(':category', $category);

                if ($stmt->execute()) {
                    $document_id = $this->conn->lastInsertId();
                    
                    // Log aktivitas
                    if ($this->auth) {
                        $this->auth->logActivity($user_id, 'upload', 'document', $document_id, 'Upload dokumen: ' . $file['name']);
                    }

                    return [
                        'status' => 'success',
                        'message' => 'Dokumen berhasil diupload',
                        'document_id' => $document_id,
                        'document' => [
                            'id' => $document_id,
                            'original_name' => $file['name'],
                            'file_type' => $file_extension,
                            'file_size' => $file['size'],
                            'mime_type' => $file['type']
                        ]
                    ];
                } else {
                    // Hapus file jika gagal insert ke database
                    unlink($file_path);
                    return ['status' => 'error', 'message' => 'Gagal menyimpan info dokumen ke database'];
                }
            } else {
                return ['status' => 'error', 'message' => 'Gagal mengupload file'];
            }

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Validasi file upload
    private function validateFile($file) {
        $errors = [];

        // Cek error upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errors[] = 'File terlalu besar';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errors[] = 'File tidak terupload sempurna';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errors[] = 'Tidak ada file yang diupload';
                    break;
                default:
                    $errors[] = 'Error upload file';
                    break;
            }
        }

        // Cek ukuran file
        if ($file['size'] > Config::MAX_FILE_SIZE) {
            $errors[] = 'Ukuran file maksimal ' . $this->formatBytes(Config::MAX_FILE_SIZE);
        }

        // Cek ekstensi file
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, Config::ALLOWED_EXTENSIONS)) {
            $errors[] = 'Tipe file tidak diizinkan. Ekstensi yang diizinkan: ' . implode(', ', Config::ALLOWED_EXTENSIONS);
        }

        // Cek MIME type
        if (!in_array($file['type'], Config::ALLOWED_MIME_TYPES)) {
            $errors[] = 'MIME type file tidak diizinkan';
        }

        return [
            'valid' => empty($errors),
            'message' => implode(', ', $errors)
        ];
    }

    // Get daftar dokumen
    public function getDocuments($user_id, $page = 1, $limit = null, $search = '', $category = '', $file_type = '') {
        try {
            $limit = $limit ?: Config::ITEMS_PER_PAGE;
            $offset = ($page - 1) * $limit;

            // Base query
            $where_conditions = ['d.user_id = :user_id'];
            $params = [':user_id' => $user_id];

            // Search filter
            if (!empty($search)) {
                $where_conditions[] = '(d.original_name LIKE :search OR d.description LIKE :search OR d.tags LIKE :search)';
                $params[':search'] = '%' . $search . '%';
            }

            // Category filter
            if (!empty($category)) {
                $where_conditions[] = 'd.category = :category';
                $params[':category'] = $category;
            }

            // File type filter
            if (!empty($file_type)) {
                $where_conditions[] = 'd.file_type = :file_type';
                $params[':file_type'] = $file_type;
            }

            $where_clause = implode(' AND ', $where_conditions);

            // Count total records
            $count_query = "SELECT COUNT(*) as total FROM documents d WHERE {$where_clause}";
            $count_stmt = $this->conn->prepare($count_query);
            foreach ($params as $key => $value) {
                $count_stmt->bindValue($key, $value);
            }
            $count_stmt->execute();
            $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Get documents
            $query = "SELECT d.*, u.username 
                     FROM documents d 
                     LEFT JOIN users u ON d.user_id = u.id 
                     WHERE {$where_clause} 
                     ORDER BY d.created_at DESC 
                     LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format file size
            foreach ($documents as &$doc) {
                $doc['formatted_size'] = $this->formatBytes($doc['file_size']);
                $doc['file_url'] = Config::BASE_URL . Config::UPLOAD_PATH . $doc['stored_name'];
            }

            return [
                'status' => 'success',
                'data' => $documents,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total_records / $limit),
                    'total_records' => $total_records,
                    'per_page' => $limit
                ]
            ];

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Get dokumen by ID
    public function getDocumentById($document_id, $user_id = null) {
        try {
            $query = "SELECT d.*, u.username 
                     FROM documents d 
                     LEFT JOIN users u ON d.user_id = u.id 
                     WHERE d.id = :document_id";
            
            $params = [':document_id' => $document_id];
            
            // Filter by user if specified
            if ($user_id !== null) {
                $query .= " AND d.user_id = :user_id";
                $params[':user_id'] = $user_id;
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $document = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($document) {
                $document['formatted_size'] = $this->formatBytes($document['file_size']);
                $document['file_url'] = Config::BASE_URL . Config::UPLOAD_PATH . $document['stored_name'];
                
                return [
                    'status' => 'success',
                    'data' => $document
                ];
            } else {
                return ['status' => 'error', 'message' => 'Dokumen tidak ditemukan'];
            }

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Update dokumen
    public function updateDocument($document_id, $user_id, $description = null, $tags = null, $category = null) {
        try {
            $set_clauses = [];
            $params = [':document_id' => $document_id, ':user_id' => $user_id];

            if ($description !== null) {
                $set_clauses[] = 'description = :description';
                $params[':description'] = $description;
            }

            if ($tags !== null) {
                $set_clauses[] = 'tags = :tags';
                $params[':tags'] = $tags;
            }

            if ($category !== null) {
                $set_clauses[] = 'category = :category';
                $params[':category'] = $category;
            }

            if (empty($set_clauses)) {
                return ['status' => 'error', 'message' => 'Tidak ada data yang diupdate'];
            }

            $set_clauses[] = 'updated_at = NOW()';
            $set_clause = implode(', ', $set_clauses);

            $query = "UPDATE documents SET {$set_clause} WHERE id = :document_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            
            if ($stmt->execute($params)) {
                if ($stmt->rowCount() > 0) {
                    // Log aktivitas
                    if ($this->auth) {
                        $this->auth->logActivity($user_id, 'update', 'document', $document_id, 'Update info dokumen');
                    }

                    return ['status' => 'success', 'message' => 'Dokumen berhasil diupdate'];
                } else {
                    return ['status' => 'error', 'message' => 'Dokumen tidak ditemukan atau tidak ada perubahan'];
                }
            } else {
                return ['status' => 'error', 'message' => 'Gagal mengupdate dokumen'];
            }

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Delete dokumen
    public function deleteDocument($document_id, $user_id) {
        try {
            // Get document info first
            $doc_info = $this->getDocumentById($document_id, $user_id);
            if ($doc_info['status'] !== 'success') {
                return $doc_info;
            }

            $document = $doc_info['data'];

            // Delete from database
            $query = "DELETE FROM documents WHERE id = :document_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            
            if ($stmt->execute([':document_id' => $document_id, ':user_id' => $user_id])) {
                if ($stmt->rowCount() > 0) {
                    // Delete physical file
                    $file_path = Config::BASE_PATH . Config::UPLOAD_PATH . $document['stored_name'];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }

                    // Log aktivitas
                    if ($this->auth) {
                        $this->auth->logActivity($user_id, 'delete', 'document', $document_id, 'Hapus dokumen: ' . $document['original_name']);
                    }

                    return ['status' => 'success', 'message' => 'Dokumen berhasil dihapus'];
                } else {
                    return ['status' => 'error', 'message' => 'Dokumen tidak ditemukan'];
                }
            } else {
                return ['status' => 'error', 'message' => 'Gagal menghapus dokumen'];
            }

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Download dokumen
    public function downloadDocument($document_id, $user_id = null) {
        try {
            $doc_info = $this->getDocumentById($document_id, $user_id);
            if ($doc_info['status'] !== 'success') {
                return $doc_info;
            }

            $document = $doc_info['data'];
            $file_path = Config::BASE_PATH . Config::UPLOAD_PATH . $document['stored_name'];

            if (!file_exists($file_path)) {
                return ['status' => 'error', 'message' => 'File tidak ditemukan'];
            }

            // Log aktivitas download
            if ($this->auth && $user_id) {
                $this->auth->logActivity($user_id, 'download', 'document', $document_id, 'Download dokumen: ' . $document['original_name']);
            }

            // Update download count
            $this->updateDownloadCount($document_id);

            return [
                'status' => 'success',
                'file_path' => $file_path,
                'file_name' => $document['original_name'],
                'mime_type' => $document['mime_type']
            ];

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Update download count
    private function updateDownloadCount($document_id) {
        try {
            $query = "UPDATE documents SET download_count = download_count + 1 WHERE id = :document_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':document_id' => $document_id]);
        } catch (Exception $e) {
            // Silent fail for download count update
        }
    }

    // Get categories
    public function getCategories($user_id = null) {
        try {
            $query = "SELECT DISTINCT category FROM documents WHERE category != '' AND category IS NOT NULL";
            $params = [];
            
            if ($user_id !== null) {
                $query .= " AND user_id = :user_id";
                $params[':user_id'] = $user_id;
            }
            
            $query .= " ORDER BY category";

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

            return [
                'status' => 'success',
                'data' => $categories
            ];

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Get file types
    public function getFileTypes($user_id = null) {
        try {
            $query = "SELECT DISTINCT file_type FROM documents";
            $params = [];
            
            if ($user_id !== null) {
                $query .= " WHERE user_id = :user_id";
                $params[':user_id'] = $user_id;
            }
            
            $query .= " ORDER BY file_type";

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $file_types = $stmt->fetchAll(PDO::FETCH_COLUMN);

            return [
                'status' => 'success',
                'data' => $file_types
            ];

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Get statistics
    public function getStatistics($user_id = null) {
        try {
            $params = [];
            $where_clause = '';
            
            if ($user_id !== null) {
                $where_clause = 'WHERE user_id = :user_id';
                $params[':user_id'] = $user_id;
            }

            // Total documents
            $query = "SELECT COUNT(*) as total_documents FROM documents {$where_clause}";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $total_documents = $stmt->fetch(PDO::FETCH_ASSOC)['total_documents'];

            // Total size
            $query = "SELECT SUM(file_size) as total_size FROM documents {$where_clause}";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $total_size = $stmt->fetch(PDO::FETCH_ASSOC)['total_size'] ?: 0;

            // Total downloads
            $query = "SELECT SUM(download_count) as total_downloads FROM documents {$where_clause}";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $total_downloads = $stmt->fetch(PDO::FETCH_ASSOC)['total_downloads'] ?: 0;

            // Documents by type
            $query = "SELECT file_type, COUNT(*) as count FROM documents {$where_clause} GROUP BY file_type ORDER BY count DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $by_type = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Recent uploads
            $query = "SELECT original_name, created_at FROM documents {$where_clause} ORDER BY created_at DESC LIMIT 5";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $recent_uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'status' => 'success',
                'data' => [
                    'total_documents' => $total_documents,
                    'total_size' => $total_size,
                    'formatted_total_size' => $this->formatBytes($total_size),
                    'total_downloads' => $total_downloads,
                    'by_type' => $by_type,
                    'recent_uploads' => $recent_uploads
                ]
            ];

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Format bytes to human readable
    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    // Search documents with advanced filters
    public function searchDocuments($user_id, $filters = []) {
        try {
            $where_conditions = ['d.user_id = :user_id'];
            $params = [':user_id' => $user_id];

            // Search in name, description, tags
            if (!empty($filters['search'])) {
                $where_conditions[] = '(d.original_name LIKE :search OR d.description LIKE :search OR d.tags LIKE :search)';
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            // Date range
            if (!empty($filters['date_from'])) {
                $where_conditions[] = 'd.created_at >= :date_from';
                $params[':date_from'] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $where_conditions[] = 'd.created_at <= :date_to';
                $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
            }

            // File size range
            if (!empty($filters['size_min'])) {
                $where_conditions[] = 'd.file_size >= :size_min';
                $params[':size_min'] = $filters['size_min'];
            }

            if (!empty($filters['size_max'])) {
                $where_conditions[] = 'd.file_size <= :size_max';
                $params[':size_max'] = $filters['size_max'];
            }

            // Category and file type
            if (!empty($filters['category'])) {
                $where_conditions[] = 'd.category = :category';
                $params[':category'] = $filters['category'];
            }

            if (!empty($filters['file_type'])) {
                $where_conditions[] = 'd.file_type = :file_type';
                $params[':file_type'] = $filters['file_type'];
            }

            $where_clause = implode(' AND ', $where_conditions);

            // Order by
            $order_by = 'd.created_at DESC';
            if (!empty($filters['sort'])) {
                switch ($filters['sort']) {
                    case 'name_asc':
                        $order_by = 'd.original_name ASC';
                        break;
                    case 'name_desc':
                        $order_by = 'd.original_name DESC';
                        break;
                    case 'size_asc':
                        $order_by = 'd.file_size ASC';
                        break;
                    case 'size_desc':
                        $order_by = 'd.file_size DESC';
                        break;
                    case 'date_asc':
                        $order_by = 'd.created_at ASC';
                        break;
                }
            }

            $query = "SELECT d.*, u.username 
                     FROM documents d 
                     LEFT JOIN users u ON d.user_id = u.id 
                     WHERE {$where_clause} 
                     ORDER BY {$order_by}";

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format results
            foreach ($documents as &$doc) {
                $doc['formatted_size'] = $this->formatBytes($doc['file_size']);
                $doc['file_url'] = Config::BASE_URL . Config::UPLOAD_PATH . $doc['stored_name'];
            }

            return [
                'status' => 'success',
                'data' => $documents,
                'count' => count($documents)
            ];

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Bulk operations
    public function bulkDelete($document_ids, $user_id) {
        try {
            if (empty($document_ids) || !is_array($document_ids)) {
                return ['status' => 'error', 'message' => 'ID dokumen tidak valid'];
            }

            $deleted_count = 0;
            $errors = [];

            foreach ($document_ids as $document_id) {
                $result = $this->deleteDocument($document_id, $user_id);
                if ($result['status'] === 'success') {
                    $deleted_count++;
                } else {
                    $errors[] = "ID {$document_id}: " . $result['message'];
                }
            }

            if ($deleted_count > 0) {
                $message = "{$deleted_count} dokumen berhasil dihapus";
                if (!empty($errors)) {
                    $message .= ". Error: " . implode(', ', $errors);
                }
                return ['status' => 'success', 'message' => $message, 'deleted_count' => $deleted_count];
            } else {
                return ['status' => 'error', 'message' => 'Tidak ada dokumen yang dihapus. ' . implode(', ', $errors)];
            }

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
?>