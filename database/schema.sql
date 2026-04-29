-- SmartSchool Database Schema

CREATE DATABASE IF NOT EXISTS smartschool1;
USE smartschool1;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student', 'parent') NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    student_name VARCHAR(100) NOT NULL,
    class VARCHAR(50),
    parent_id INT,
    attendance_percentage DECIMAL(5,2) DEFAULT 0,
    average_grade VARCHAR(5) DEFAULT 'N/A',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Teachers table
CREATE TABLE IF NOT EXISTS teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    teacher_name VARCHAR(100) NOT NULL,
    subject VARCHAR(100),
    total_classes INT DEFAULT 0,
    total_students INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Classes table
CREATE TABLE IF NOT EXISTS classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_name VARCHAR(50) NOT NULL,
    teacher_id INT,
    subject VARCHAR(100),
    schedule_time VARCHAR(100),
    room VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
);

-- Subjects table
CREATE TABLE IF NOT EXISTS subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Grades table
CREATE TABLE IF NOT EXISTS grades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    subject VARCHAR(100),
    grade VARCHAR(5),
    exam_name VARCHAR(100),
    score DECIMAL(5,2),
    total_marks DECIMAL(5,2),
    date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') NOT NULL,
    class_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
);

-- Homework table
CREATE TABLE IF NOT EXISTS homework (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT,
    teacher_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    due_date DATE,
    subject VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
);

-- Homework submissions table
CREATE TABLE IF NOT EXISTS homework_submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    homework_id INT,
    student_id INT,
    submission_file VARCHAR(255),
    submission_text TEXT,
    status ENUM('pending', 'submitted', 'late') DEFAULT 'pending',
    submitted_at TIMESTAMP NULL,
    grade VARCHAR(5),
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (homework_id) REFERENCES homework(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Exams table
CREATE TABLE IF NOT EXISTS exams (
    id INT PRIMARY KEY AUTO_INCREMENT,
    exam_name VARCHAR(100) NOT NULL,
    subject VARCHAR(100),
    class VARCHAR(50),
    date DATE,
    duration VARCHAR(50),
    total_marks DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events table
CREATE TABLE IF NOT EXISTS events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_name VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE,
    event_type VARCHAR(50),
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Fees table
CREATE TABLE IF NOT EXISTS fees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
    due_date DATE,
    paid_date DATE NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Messages table
CREATE TABLE IF NOT EXISTS messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT,
    receiver_id INT,
    subject VARCHAR(200),
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin
INSERT INTO users (email, password, role, name, phone) VALUES 
('admin@smartschool.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'John Admin', '+1234567890');
-- Default password is 'password'

-- Insert sample data for testing

-- Sample Teachers
INSERT INTO users (email, password, role, name, phone) VALUES 
('sarah.teacher@smartschool.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Sarah Teacher', '+1234567891'),
('james.wilson@smartschool.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'James Wilson', '+1234567892');

INSERT INTO teachers (user_id, teacher_name, subject, total_classes, total_students) VALUES
(2, 'Sarah Teacher', 'Mathematics', 8, 240),
(3, 'James Wilson', 'Physics', 6, 180);

-- Sample Students
INSERT INTO users (email, password, role, name, phone) VALUES 
('mike.student@smartschool.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Mike Student', '+1234567893'),
('john.doe@smartschool.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'John Doe', '+1234567894');

-- Sample Parent
INSERT INTO users (email, password, role, name, phone) VALUES 
('lisa.parent@smartschool.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', 'Lisa Parent', '+1234567895');

INSERT INTO students (user_id, student_name, class, parent_id, attendance_percentage, average_grade) VALUES
(4, 'Mike Student', 'Grade 10A', 6, 92, 'A-'),
(5, 'John Doe', 'Grade 10A', 6, 95, 'B+');

-- Sample Classes
INSERT INTO classes (class_name, teacher_id, subject, schedule_time, room) VALUES
('Grade 10A', 1, 'Mathematics', '8:00 AM - 10:00 AM', 'Room 101'),
('Grade 11B', 1, 'Physics', '10:00 AM - 12:00 PM', 'Room 205'),
('Grade 12A', 1, 'Chemistry', '2:00 PM - 3:30 PM', 'Lab 1');

-- Sample Subjects
INSERT INTO subjects (subject_name, description) VALUES
('Mathematics', 'Algebra, Geometry, Calculus'),
('English', 'Literature, Grammar, Writing'),
('Science', 'Physics, Chemistry, Biology'),
('Physics', 'Mechanics, Thermodynamics, Optics'),
('Chemistry', 'Organic, Inorganic, Physical');

-- Sample Grades
INSERT INTO grades (student_id, subject, grade, exam_name, score, total_marks, date) VALUES
(1, 'Mathematics', 'A', 'Mathematics Quiz', 92, 100, '2026-04-15'),
(1, 'English', 'B+', 'English Essay', 85, 100, '2026-04-16'),
(1, 'Science', 'A', 'Science Lab', 95, 100, '2026-04-17');

-- Sample Homework
INSERT INTO homework (class_id, teacher_id, title, description, due_date, subject) VALUES
(1, 1, 'Math Assignment #5', 'Complete Chapter 5 exercises', '2026-04-30', 'Mathematics'),
(1, 1, 'Physics Lab Report', 'Write report on Chemical Reactions', '2026-04-28', 'Physics'),
(1, 1, 'Science Lab Report', 'Complete lab analysis', '2026-04-29', 'Science');

-- Sample Homework Submissions
INSERT INTO homework_submissions (homework_id, student_id, status, submitted_at) VALUES
(1, 1, 'submitted', '2026-04-20 10:30:00'),
(2, 1, 'pending', NULL);

-- Sample Exams
INSERT INTO exams (exam_name, subject, class, date, duration, total_marks) VALUES
('Mid-term Mathematics', 'Mathematics', 'Grade 10A', '2026-05-10', '2 hours', 100),
('Physics Final', 'Physics', 'Grade 10A', '2026-05-15', '3 hours', 100);

-- Sample Events
INSERT INTO events (event_name, description, event_date, event_type, status) VALUES
('Parent-Teacher Meeting', 'Monthly parent-teacher conference', '2026-04-25', 'Meeting', 'Scheduled'),
('Science Fair', 'Annual science exhibition', '2026-05-05', 'Fair', 'Next Week'),
('Fee Payment Due', 'Monthly tuition payment', '2026-04-30', 'Payment', '5 Days');

-- Sample Fees
INSERT INTO fees (student_id, amount, status, due_date, description) VALUES
(1, 250, 'pending', '2026-04-30', 'Monthly Tuition Fee'),
(2, 250, 'pending', '2026-04-30', 'Monthly Tuition Fee');

-- Sample Attendance (last 30 days)
INSERT INTO attendance (student_id, date, status, class_id) VALUES
(1, '2026-04-01', 'present', 1),
(1, '2026-04-02', 'present', 1),
(1, '2026-04-03', 'present', 1),
(1, '2026-04-04', 'absent', 1),
(1, '2026-04-05', 'present', 1);

-- Sample Messages
INSERT INTO messages (sender_id, receiver_id, subject, message, is_read) VALUES
(2, 4, 'Assignment Reminder', 'Please submit your math assignment by Friday.', FALSE),
(1, 6, 'Parent Meeting', 'We have scheduled a parent-teacher meeting for next week.', FALSE);
