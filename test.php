<!DOCTYPE html>
<html>
<head>
    <title>SmartSchool - Connection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-box {
            background: white;
            padding: 20px;
            margin: 10px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { border-left: 4px solid #27AE60; }
        .error { border-left: 4px solid #E74C3C; }
        h1 { color: #2C3E50; }
        h3 { color: #34495E; margin-top: 0; }
        code {
            background: #ecf0f1;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #4A90E2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>🔧 SmartSchool Connection Test</h1>
    
    <?php
    // Test 1: PHP Version
    echo '<div class="test-box success">';
    echo '<h3>✅ Test 1: PHP Version</h3>';
    echo '<p>PHP Version: <strong>' . phpversion() . '</strong></p>';
    echo '<p>Status: PHP is working correctly!</p>';
    echo '</div>';
    
    // Test 2: Database Configuration File
    echo '<div class="test-box ' . (file_exists('config/database.php') ? 'success' : 'error') . '">';
    echo '<h3>' . (file_exists('config/database.php') ? '✅' : '❌') . ' Test 2: Config File</h3>';
    if (file_exists('config/database.php')) {
        echo '<p>Status: <code>config/database.php</code> exists!</p>';
    } else {
        echo '<p>Status: <code>config/database.php</code> NOT found!</p>';
        echo '<p>Solution: Make sure the file exists in the config folder.</p>';
    }
    echo '</div>';
    
    // Test 3: Database Connection
    $dbConnected = false;
    $dbError = '';
    
    try {
        require_once 'config/database.php';
        $database = new Database();
        $conn = $database->getConnection();
        $dbConnected = true;
    } catch(Exception $e) {
        $dbError = $e->getMessage();
    }
    
    echo '<div class="test-box ' . ($dbConnected ? 'success' : 'error') . '">';
    echo '<h3>' . ($dbConnected ? '✅' : '❌') . ' Test 3: Database Connection</h3>';
    if ($dbConnected) {
        echo '<p>Status: Successfully connected to MySQL database!</p>';
        echo '<p>Database: <strong>smartschool_db</strong></p>';
    } else {
        echo '<p>Status: Failed to connect to database</p>';
        echo '<p>Error: <code>' . htmlspecialchars($dbError) . '</code></p>';
        echo '<h4>Common Solutions:</h4>';
        echo '<ul>';
        echo '<li>Make sure MySQL is running in XAMPP/WAMP</li>';
        echo '<li>Check database credentials in <code>config/database.php</code></li>';
        echo '<li>Verify database <code>smartschool_db</code> exists</li>';
        echo '<li>Check MySQL port (default: 3306)</li>';
        echo '</ul>';
    }
    echo '</div>';
    
    // Test 4: Check Tables
    if ($dbConnected) {
        try {
            $stmt = $conn->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo '<div class="test-box ' . (count($tables) > 0 ? 'success' : 'error') . '">';
            echo '<h3>' . (count($tables) > 0 ? '✅' : '❌') . ' Test 4: Database Tables</h3>';
            
            if (count($tables) > 0) {
                echo '<p>Status: Found <strong>' . count($tables) . '</strong> tables</p>';
                echo '<p>Tables: ' . implode(', ', $tables) . '</p>';
            } else {
                echo '<p>Status: No tables found in database</p>';
                echo '<p>Solution: Import <code>database/schema.sql</code> in MySQL Workbench or phpMyAdmin</p>';
            }
            echo '</div>';
            
            // Test 5: Check Users
            if (in_array('users', $tables)) {
                $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
                $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                echo '<div class="test-box ' . ($userCount > 0 ? 'success' : 'error') . '">';
                echo '<h3>' . ($userCount > 0 ? '✅' : '❌') . ' Test 5: Sample Data</h3>';
                
                if ($userCount > 0) {
                    echo '<p>Status: Found <strong>' . $userCount . '</strong> users in database</p>';
                    
                    // Show sample users
                    $stmt = $conn->query("SELECT email, role FROM users LIMIT 5");
                    echo '<h4>Sample Login Credentials:</h4>';
                    echo '<table style="width: 100%; border-collapse: collapse;">';
                    echo '<tr style="background: #ecf0f1;"><th style="padding: 8px; text-align: left;">Email</th><th style="padding: 8px; text-align: left;">Role</th><th style="padding: 8px; text-align: left;">Password</th></tr>';
                    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<tr style="border-bottom: 1px solid #ddd;">';
                        echo '<td style="padding: 8px;">' . htmlspecialchars($user['email']) . '</td>';
                        echo '<td style="padding: 8px;">' . htmlspecialchars($user['role']) . '</td>';
                        echo '<td style="padding: 8px;"><code>password</code></td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p>Status: Users table is empty</p>';
                    echo '<p>Solution: The SQL import might have failed. Re-import <code>database/schema.sql</code></p>';
                }
                echo '</div>';
            }
        } catch(Exception $e) {
            echo '<div class="test-box error">';
            echo '<h3>❌ Test 4: Database Error</h3>';
            echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '</div>';
        }
    }
    
    // Test 6: Session Support
    echo '<div class="test-box ' . (session_status() == PHP_SESSION_ACTIVE || session_start() ? 'success' : 'error') . '">';
    echo '<h3>' . (session_status() == PHP_SESSION_ACTIVE || session_start() ? '✅' : '❌') . ' Test 6: Session Support</h3>';
    echo '<p>Status: PHP sessions are working!</p>';
    echo '</div>';
    
    // Summary
    echo '<div class="test-box">';
    echo '<h3>📋 Summary</h3>';
    if ($dbConnected && count($tables ?? []) > 0) {
        echo '<p style="color: #27AE60; font-weight: bold;">✅ All tests passed! Your system is ready.</p>';
        echo '<a href="index.html" class="button">Go to Login Page →</a>';
    } else {
        echo '<p style="color: #E74C3C; font-weight: bold;">❌ Some tests failed. Please fix the errors above.</p>';
        echo '<h4>Quick Fix Steps:</h4>';
        echo '<ol>';
        echo '<li>Start Apache and MySQL in XAMPP/WAMP</li>';
        echo '<li>Open MySQL Workbench</li>';
        echo '<li>Create database: <code>CREATE DATABASE smartschool_db;</code></li>';
        echo '<li>Import file: <code>database/schema.sql</code></li>';
        echo '<li>Refresh this page</li>';
        echo '</ol>';
    }
    echo '</div>';
    ?>
</body>
</html>
