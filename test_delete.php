<?php
require_once 'config/database.php';
$database = new Database();
$conn = $database->getConnection();

$id = 11; // Delete Test student id

try {
    $conn->beginTransaction();
    
    $stmt = $conn->prepare("SELECT user_id FROM students WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $student = $stmt->fetch();
    
    if ($student) {
        $userId = $student['user_id'];
        echo "Found student. User ID: $userId\n";
        
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        echo "Delete command executed.\n";
    } else {
        echo "Student not found.\n";
    }
    
    $conn->commit();
    echo "Transaction committed.\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if ($conn->inTransaction()) $conn->rollBack();
}

// Verify
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM students WHERE id = :id");
$stmt->execute(['id' => $id]);
echo "Student count: " . $stmt->fetch()['count'] . "\n";
?>
