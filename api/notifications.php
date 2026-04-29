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
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

$action = $_GET['action'] ?? '';

if ($action == 'get_notifications') {
    try {
        // Get unread messages count
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM messages WHERE receiver_id = :user_id AND is_read = 0");
        $stmt->execute(['user_id' => $userId]);
        $unreadCount = $stmt->fetch()['count'];
        
        // Get recent notifications
        $notifications = [];
        
        if ($role == 'admin') {
            // Admin notifications
            $stmt = $conn->query("
                SELECT 'New Student Enrollment' as title, 
                       CONCAT(student_name, ' joined the school') as message,
                       created_at as time
                FROM students 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            $notifications = $stmt->fetchAll();
        } 
        elseif ($role == 'teacher') {
            // Teacher notifications
            $stmt = $conn->prepare("
                SELECT 'New Homework Submission' as title,
                       CONCAT(s.student_name, ' submitted assignment') as message,
                       hs.created_at as time
                FROM homework_submissions hs
                JOIN students s ON hs.student_id = s.id
                ORDER BY hs.created_at DESC
                LIMIT 5
            ");
            $stmt->execute();
            $notifications = $stmt->fetchAll();
        }
        elseif ($role == 'student') {
            // Student notifications
            $stmt = $conn->query("
                SELECT 'New Assignment' as title,
                       title as message,
                       created_at as time
                FROM homework
                WHERE due_date >= CURDATE()
                ORDER BY created_at DESC
                LIMIT 5
            ");
            $notifications = $stmt->fetchAll();
        }
        elseif ($role == 'parent') {
            // Parent notifications
            $notifications = [
                ['title' => 'Fee Reminder', 'message' => 'Monthly fee payment due in 5 days', 'time' => date('Y-m-d H:i:s')],
                ['title' => 'Parent Meeting', 'message' => 'Upcoming parent-teacher meeting', 'time' => date('Y-m-d H:i:s')]
            ];
        }
        
        echo json_encode([
            'success' => true,
            'count' => $unreadCount,
            'notifications' => $notifications
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

elseif ($action == 'mark_read') {
    try {
        $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
