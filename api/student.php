<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

$action = $_POST['action'] ?? '';

if ($action == 'upload_assignment') {
    $homeworkId = $_POST['homework_id'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (empty($homeworkId)) {
        echo json_encode(['success' => false, 'message' => 'Please select a homework']);
        exit;
    }
    
    try {
        // Get student info
        $stmt = $conn->prepare("SELECT id FROM students WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$student) {
            echo json_encode(['success' => false, 'message' => 'Student not found']);
            exit;
        }
        
        $studentId = $student['id'];
        $submissionFile = null;
        
        // Handle file upload
        if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] == 0) {
            $uploadDir = '../uploads/assignments/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['assignment_file']['name']);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $targetFile)) {
                $submissionFile = $fileName;
            }
        }
        
        // Check if already submitted
        $stmt = $conn->prepare("SELECT id FROM homework_submissions WHERE homework_id = :homework_id AND student_id = :student_id");
        $stmt->execute(['homework_id' => $homeworkId, 'student_id' => $studentId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Update existing submission
            $stmt = $conn->prepare("
                UPDATE homework_submissions 
                SET submission_file = :file, submission_text = :notes, status = 'submitted', submitted_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                'file' => $submissionFile,
                'notes' => $notes,
                'id' => $existing['id']
            ]);
        } else {
            // Create new submission
            $stmt = $conn->prepare("
                INSERT INTO homework_submissions (homework_id, student_id, submission_file, submission_text, status, submitted_at)
                VALUES (:homework_id, :student_id, :file, :notes, 'submitted', NOW())
            ");
            $stmt->execute([
                'homework_id' => $homeworkId,
                'student_id' => $studentId,
                'file' => $submissionFile,
                'notes' => $notes
            ]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Assignment uploaded successfully']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()]);
    }
}
else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
