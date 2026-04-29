<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Get action from POST
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action == 'login') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : '';
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit;
    }
    
    if (empty($role)) {
        echo json_encode(['success' => false, 'message' => 'Role is required']);
        exit;
    }
    
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email AND role = :role");
        $stmt->execute(['email' => $email, 'role' => $role]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            echo json_encode([
                'success' => true, 
                'message' => 'Login successful',
                'role' => $user['role'],
                'name' => $user['name']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

elseif ($action == 'signup') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $role = isset($_POST['role']) ? $_POST['role'] : '';
    $studentClass = isset($_POST['class']) ? trim($_POST['class']) : '';
    $teacherSubject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    
    if (empty($email) || empty($password) || empty($name) || empty($role)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if ($role == 'admin') {
        echo json_encode(['success' => false, 'message' => 'Cannot register as admin']);
        exit;
    }

    if (!in_array($role, ['teacher', 'student', 'parent'], true)) {
        echo json_encode(['success' => false, 'message' => 'Invalid role for registration']);
        exit;
    }
    
    $conn = null;
    try {
        $database = new Database();
        $conn = $database->getConnection();
        $conn->beginTransaction();
        
        // Check if email already exists. If a previous partial signup left a user
        // without student/teacher row, complete that record instead of blocking forever.
        $stmt = $conn->prepare("SELECT id, role FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $existingUser = $stmt->fetch();
        if ($existingUser) {
            if ($existingUser['role'] !== $role) {
                $conn->rollBack();
                echo json_encode(['success' => false, 'message' => 'Email already registered']);
                exit;
            }

            $existingUserId = (int)$existingUser['id'];

            if ($role === 'student') {
                $checkStudent = $conn->prepare("SELECT id FROM students WHERE user_id = :user_id LIMIT 1");
                $checkStudent->execute(['user_id' => $existingUserId]);
                if (!$checkStudent->fetch()) {
                    $createStudent = $conn->prepare("INSERT INTO students (user_id, student_name, `class`, attendance_percentage, average_grade) VALUES (:user_id, :name, :class, 0, 'N/A')");
                    $createStudent->execute([
                        'user_id' => $existingUserId,
                        'name' => $name,
                        'class' => $studentClass !== '' ? $studentClass : 'Grade 10A'
                    ]);
                    $studentId = $conn->lastInsertId();
                    
                    // Auto-add to today's attendance
                    $stmt = $conn->prepare("INSERT INTO attendance (student_id, date, status) VALUES (:student_id, CURDATE(), 'present')");
                    $stmt->execute(['student_id' => $studentId]);

                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Registration successful']);
                    exit;
                }
            } elseif ($role === 'teacher') {
                $checkTeacher = $conn->prepare("SELECT id FROM teachers WHERE user_id = :user_id LIMIT 1");
                $checkTeacher->execute(['user_id' => $existingUserId]);
                if (!$checkTeacher->fetch()) {
                    $createTeacher = $conn->prepare("INSERT INTO teachers (user_id, teacher_name, subject, total_classes, total_students) VALUES (:user_id, :name, :subject, 0, 0)");
                    $createTeacher->execute([
                        'user_id' => $existingUserId,
                        'name' => $name,
                        'subject' => $teacherSubject !== '' ? $teacherSubject : 'Not Assigned'
                    ]);
                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Registration successful']);
                    exit;
                }
            }

            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => 'Email already registered']);
            exit;
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Keep signup compatible with older DB schemas that may not have users.phone.
        $hasPhoneColumn = false;
        $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'phone'");
        if ($columnCheck && $columnCheck->fetch()) {
            $hasPhoneColumn = true;
        }

        if ($hasPhoneColumn) {
            $stmt = $conn->prepare("INSERT INTO users (email, password, role, name, phone) VALUES (:email, :password, :role, :name, :phone)");
            $stmt->execute([
                'email' => $email,
                'password' => $hashedPassword,
                'role' => $role,
                'name' => $name,
                'phone' => $phone !== '' ? $phone : null
            ]);
        } else {
            $stmt = $conn->prepare("INSERT INTO users (email, password, role, name) VALUES (:email, :password, :role, :name)");
            $stmt->execute([
                'email' => $email,
                'password' => $hashedPassword,
                'role' => $role,
                'name' => $name
            ]);
        }
        
        $userId = $conn->lastInsertId();
        
        // Create student or teacher record
        if ($role == 'student') {
            $stmt = $conn->prepare("INSERT INTO students (user_id, student_name, `class`, attendance_percentage, average_grade) VALUES (:user_id, :name, :class, 0, 'N/A')");
            $stmt->execute([
                'user_id' => $userId,
                'name' => $name,
                'class' => $studentClass !== '' ? $studentClass : 'Grade 10A'
            ]);
            $studentId = $conn->lastInsertId();
            
            // Auto-add to today's attendance
            $stmt = $conn->prepare("INSERT INTO attendance (student_id, date, status) VALUES (:student_id, CURDATE(), 'present')");
            $stmt->execute(['student_id' => $studentId]);
            
        } elseif ($role == 'teacher') {
            $stmt = $conn->prepare("INSERT INTO teachers (user_id, teacher_name, subject, total_classes, total_students) VALUES (:user_id, :name, :subject, 0, 0)");
            $stmt->execute([
                'user_id' => $userId,
                'name' => $name,
                'subject' => $teacherSubject !== '' ? $teacherSubject : 'Not Assigned'
            ]);
        }

        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Registration successful']);
    } catch(PDOException $e) {
        if ($conn && $conn->inTransaction()) {
            $conn->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
    }
}

elseif ($action == 'logout') {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}

elseif ($action == 'check_session') {
    if (isset($_SESSION['user_id'])) {
        $response = [
            'success' => true,
            'logged_in' => true,
            'role' => $_SESSION['role'],
            'name' => $_SESSION['name']
        ];
        
        if ($_SESSION['role'] === 'parent' || $_SESSION['role'] === 'student') {
            $database = new Database();
            $conn = $database->getConnection();
        }
        
        if ($_SESSION['role'] === 'parent') {
            try {
                $stmt = $conn->prepare("SELECT student_name, `class` FROM students WHERE parent_id = :parent_id");
                $stmt->execute(['parent_id' => $_SESSION['user_id']]);
                $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response['children'] = $children;
            } catch(PDOException $e) {
                // Ignore errors for this specific optional data
            }
        } elseif ($_SESSION['role'] === 'student') {
            try {
                $stmt = $conn->prepare("SELECT student_name, `class` FROM students WHERE user_id = :user_id");
                $stmt->execute(['user_id' => $_SESSION['user_id']]);
                $student = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($student) {
                    $response['student_name'] = $student['student_name'];
                    $response['class'] = $student['class'];
                }
            } catch(PDOException $e) {}
        }
        
        echo json_encode($response);
    } else {
        echo json_encode(['success' => true, 'logged_in' => false]);
    }
}

else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
