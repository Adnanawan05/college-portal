# SmartSchool Portal - Functional Version

## Project Overview
SmartSchool is a comprehensive school management portal designed to streamline administrative tasks and improve communication between teachers, students, and parents. The system features a robust backend powered by PHP and a dynamic frontend built with modern web technologies.

## Core Features
- Role-Based Access Control: Specialized dashboards for Administrators, Teachers, Students, and Parents.
- Student Management: Administrators can add, update, and delete student records with full database persistence.
- Teacher Management: Management of teacher profiles including subjects and classes.
- Automated Attendance System: Newly registered students are automatically added to the daily attendance log upon account creation.
- Dynamic Attendance Management: Teachers and Administrators can view and modify daily attendance records (Present, Absent, Late).
- Dashboard Analytics: Real-time overview of school statistics and performance metrics.

## Installation Instructions

### 1. File Placement
Extract the project files into your XAMPP htdocs directory:
Path: C:\xampp\htdocs\smartschool-final

### 2. Service Configuration
Start the Apache and MySQL services using the XAMPP Control Panel.

### 3. Database Setup
1. Open phpMyAdmin or any MySQL client.
2. Create a new database named: smartschool1
3. Import the database schema from the following file: database/schema.sql

### 4. Configuration Check
Verify the database connection settings in: config/database.php
Default settings are:
- Host: localhost
- Port: 3306
- User: root
- Password: (As set in your XAMPP)
- Database: smartschool1

### 5. Verification
Visit http://localhost/smartschool-final/test.php to run the system connection tests.

## Access Credentials
The system includes sample accounts for testing:

- Administrator: admin@example.com / password
- Teacher: sarah.teacher@smartschool.com / password
- Student: mike.student@smartschool.com / password
- Parent: lisa.parent@smartschool.com / password

## Technology Stack
- Frontend: HTML5, CSS3, JavaScript (Vanilla)
- Backend: PHP
- Database: MySQL
- Server: Apache (via XAMPP)
