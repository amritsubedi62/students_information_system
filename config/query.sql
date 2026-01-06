-- Create database
CREATE DATABASE IF NOT EXISTS student_information_system;
USE student_information_system;

-- ==========================
-- USERS TABLE (teacher + parents)
-- ==========================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('teacher', 'parent') NOT NULL DEFAULT 'parent'
);

CREATE TABLE attendance_monthly (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  month VARCHAR(7) NOT NULL,   -- YYYY-MM
  total_days INT NOT NULL,
  present_days INT NOT NULL,
  UNIQUE(student_id, month)
);


-- Insert default admin/teacher
INSERT INTO users (username, email, password, role)
VALUES ('admin', 'admin@gmail.com', 'admin123', 'teacher');


-- ==========================
-- STUDENTS TABLE
-- ==========================
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    class VARCHAR(50) NOT NULL,
    roll_no VARCHAR(20) NOT NULL,
    parent_id INT,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE
);


-- ==========================
-- RESULTS TABLE
-- ==========================
CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    marks INT NOT NULL,
    grade VARCHAR(5),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);


-- ==========================
-- ATTENDANCE TABLE
-- ==========================
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('Present', 'Absent') NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);


