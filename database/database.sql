-- Fitness Club Membership Management System Database Schema
-- Create database and table structure

CREATE DATABASE IF NOT EXISTS fitness_club_db;
USE fitness_club_db;

-- Members table
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    membership_type VARCHAR(50) NOT NULL,
    membership_start_date DATE NOT NULL,
    membership_expiry_date DATE NOT NULL,
    status ENUM('Active', 'Expired', 'Suspended') DEFAULT 'Active',
    address TEXT NULL,
    date_of_birth DATE NULL,
    emergency_contact_name VARCHAR(100) NULL,
    emergency_contact_phone VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_membership_type (membership_type),
    INDEX idx_expiry_date (membership_expiry_date),
    INDEX idx_status (status),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trainers table
CREATE TABLE IF NOT EXISTS trainers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    specialization VARCHAR(255) NULL,
    experience_years INT NULL,
    certification VARCHAR(255) NULL,
    hire_date DATE NOT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Workout Plans table
CREATE TABLE IF NOT EXISTS workout_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    trainer_id INT NULL,
    plan_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    duration_weeks INT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    status ENUM('Active', 'Completed', 'Cancelled') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE SET NULL,
    INDEX idx_member_id (member_id),
    INDEX idx_trainer_id (trainer_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Insert sample data for members
INSERT INTO members (first_name, last_name, email, phone, membership_type, membership_start_date, membership_expiry_date, status, date_of_birth) VALUES
('John', 'Doe', 'john.doe@email.com', '555-0101', 'Premium', '2025-01-01', '2025-12-31', 'Active', '1990-05-15'),
('Jane', 'Smith', 'jane.smith@email.com', '555-0102', 'Standard', '2025-01-15', '2025-07-15', 'Active', '1988-08-22'),
('Mike', 'Johnson', 'mike.johnson@email.com', '555-0103', 'Basic', '2024-12-01', '2025-03-01', 'Active', '1992-11-10'),
('Sarah', 'Williams', 'sarah.williams@email.com', '555-0104', 'Premium', '2024-11-01', '2025-11-01', 'Active', '1985-03-20'),
('David', 'Brown', 'david.brown@email.com', '555-0105', 'Standard', '2024-10-15', '2025-04-15', 'Expired', '1995-07-05');

-- Insert sample data for trainers
INSERT INTO trainers (first_name, last_name, email, phone, specialization, experience_years, certification, hire_date, status) VALUES
('Alex', 'Martinez', 'alex.martinez@fitness.com', '555-0201', 'Strength Training, Bodybuilding', 8, 'NASM Certified Personal Trainer', '2020-01-15', 'Active'),
('Emily', 'Chen', 'emily.chen@fitness.com', '555-0202', 'Yoga, Pilates, Flexibility', 5, 'RYT 200 Yoga Instructor', '2021-03-01', 'Active'),
('Robert', 'Taylor', 'robert.taylor@fitness.com', '555-0203', 'Cardio, Weight Loss, HIIT', 10, 'ACE Certified Personal Trainer', '2019-06-10', 'Active'),
('Lisa', 'Anderson', 'lisa.anderson@fitness.com', '555-0204', 'CrossFit, Functional Training', 6, 'CrossFit Level 2 Trainer', '2022-01-20', 'Active');

-- Insert sample data for workout plans
INSERT INTO workout_plans (member_id, trainer_id, plan_name, description, duration_weeks, start_date, end_date, status) VALUES
(1, 1, 'Strength Building Program', '12-week strength training program focusing on compound movements', 12, '2025-01-15', '2025-04-15', 'Active'),
(2, 3, 'Weight Loss Journey', '16-week cardio and nutrition program for weight loss', 16, '2025-01-20', '2025-05-20', 'Active'),
(3, 2, 'Flexibility & Mobility', '8-week yoga and stretching program', 8, '2025-01-10', '2025-03-10', 'Active'),
(4, 1, 'Muscle Gain Program', '20-week hypertrophy-focused training plan', 20, '2024-11-15', '2025-04-15', 'Active');
