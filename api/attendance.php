<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action == 'list') {
    try {
        $role = $_SESSION['role'];
        $userId = $_SESSION['user_id'];
        
        $query = "
            SELECT a.*, s.student_name, s.class, s.attendance_percentage
            FROM attendance a
            JOIN students s ON s.id = a.student_id
        ";
        
        $params = [];
        if ($role == 'student') {
            $query .= " WHERE s.user_id = :user_id";
            $params['user_id'] = $userId;
        } elseif ($role == 'parent') {
            $query .= " WHERE s.parent_id = (SELECT id FROM parents WHERE user_id = :user_id)";
            $params['user_id'] = $userId;
        }
        
        $query .= " ORDER BY a.date DESC, a.id DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $records = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'attendance' => $records
        ]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

elseif ($action == 'update') {
    if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'teacher') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? '';
    
    if (empty($id) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Missing data']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("UPDATE attendance SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $status, 'id' => $id]);
        echo json_encode(['success' => true, 'message' => 'Attendance updated']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

elseif ($action == 'mark_bulk') {
    if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'teacher') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $date = $_POST['date'] ?? date('Y-m-d');
    $records = $_POST['records'] ?? []; // Array of {student_id, status}
    
    try {
        $conn->beginTransaction();
        foreach ($records as $r) {
            $stmt = $conn->prepare("
                INSERT INTO attendance (student_id, date, status) 
                VALUES (:sid, :date, :status)
                ON DUPLICATE KEY UPDATE status = :status
            ");
            $stmt->execute([
                'sid' => $r['student_id'],
                'date' => $date,
                'status' => $r['status']
            ]);
        }
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Bulk attendance marked']);
    } catch(PDOException $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
