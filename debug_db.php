<?php
require_once 'config/database.php';
$database = new Database();
$conn = $database->getConnection();

echo "--- Students Table ---\n";
$stmt = $conn->query("SELECT * FROM students");
print_r($stmt->fetchAll());

echo "\n--- Users Table ---\n";
$stmt = $conn->query("SELECT id, email, role, name FROM users");
print_r($stmt->fetchAll());

echo "\n--- Relationships ---\n";
$stmt = $conn->query("SELECT s.id as student_id, s.user_id, u.id as user_actual_id FROM students s LEFT JOIN users u ON s.user_id = u.id");
print_r($stmt->fetchAll());
?>
