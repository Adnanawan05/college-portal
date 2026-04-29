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
        $stmt = $conn->query("
            SELECT s.*, u.email
            FROM students s
            LEFT JOIN users u ON u.id = s.user_id
            ORDER BY s.id DESC
        ");
        $students = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'students' => $students
        ]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

elseif ($action == 'list_teachers') {
    try {
        $stmt = $conn->query("
            SELECT t.*, u.email
            FROM teachers t
            LEFT JOIN users u ON u.id = t.user_id
            ORDER BY t.id DESC
        ");
        $teachers = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'teachers' => $teachers
        ]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

elseif ($action == 'update_student') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $class = $_POST['class'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $conn->beginTransaction();
        
        // Get user_id
        $stmt = $conn->prepare("SELECT user_id FROM students WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $student = $stmt->fetch();
        
        if (!$student) {
            echo json_encode(['success' => false, 'message' => 'Student not found']);
            exit;
        }
        
        $userId = $student['user_id'];
        
        // Update users table
        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email, password = :password WHERE id = :user_id");
            $stmt->execute(['name' => $name, 'email' => $email, 'password' => $hashedPassword, 'user_id' => $userId]);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email WHERE id = :user_id");
            $stmt->execute(['name' => $name, 'email' => $email, 'user_id' => $userId]);
        }
        
        // Update students table
        $stmt = $conn->prepare("UPDATE students SET student_name = :name, `class` = :class WHERE id = :id");
        $stmt->execute(['name' => $name, 'class' => $class, 'id' => $id]);
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Student updated successfully']);
    } catch(PDOException $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

elseif ($action == 'delete_student') {
    $id = $_POST['id'] ?? '';
    try {
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("SELECT user_id FROM students WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $student = $stmt->fetch();
        
        if ($student) {
            $userId = $student['user_id'];
            // Delete user (will cascade to students table due to foreign key)
            $stmt = $conn->prepare("DELETE FROM users WHERE id = :user_id");
            $stmt->execute(['user_id' => $userId]);
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
    } catch(PDOException $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

elseif ($action == 'update_teacher') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("SELECT user_id FROM teachers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $teacher = $stmt->fetch();
        
        if (!$teacher) {
            echo json_encode(['success' => false, 'message' => 'Teacher not found']);
            exit;
        }
        
        $userId = $teacher['user_id'];
        
        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email, password = :password WHERE id = :user_id");
            $stmt->execute(['name' => $name, 'email' => $email, 'password' => $hashedPassword, 'user_id' => $userId]);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email WHERE id = :user_id");
            $stmt->execute(['name' => $name, 'email' => $email, 'user_id' => $userId]);
        }
        
        $stmt = $conn->prepare("UPDATE teachers SET teacher_name = :name, subject = :subject WHERE id = :id");
        $stmt->execute(['name' => $name, 'subject' => $subject, 'id' => $id]);
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Teacher updated successfully']);
    } catch(PDOException $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

elseif ($action == 'delete_teacher') {
    $id = $_POST['id'] ?? '';
    try {
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("SELECT user_id FROM teachers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $teacher = $stmt->fetch();
        
        if ($teacher) {
            $userId = $teacher['user_id'];
            $stmt = $conn->prepare("DELETE FROM users WHERE id = :user_id");
            $stmt->execute(['user_id' => $userId]);
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Teacher deleted successfully']);
    } catch(PDOException $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
