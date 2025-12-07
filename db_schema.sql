-- Database schema for Attendance project
-- Run these statements in MySQL to create tables

CREATE TABLE IF NOT EXISTS `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fullname` VARCHAR(255) NOT NULL,
  `matricule` VARCHAR(100) NOT NULL UNIQUE,
  `group_id` VARCHAR(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `attendance_sessions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_id` VARCHAR(100) NOT NULL,
  `group_id` VARCHAR(100) NOT NULL,
  `date` DATE NOT NULL,
  `opened_by` INT DEFAULT NULL,
  `status` ENUM('open','closed') DEFAULT 'open',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
