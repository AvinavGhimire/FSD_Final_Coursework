-- Updated database schema to support all workout plan features
-- Run these ALTER TABLE statements to add the missing columns

USE fitness_club_db;

-- Add new columns to workout_plans table
ALTER TABLE workout_plans 
ADD COLUMN plan_type VARCHAR(100) NULL AFTER plan_name,
ADD COLUMN plan_description TEXT NULL AFTER plan_type,
ADD COLUMN sessions_per_week INT NULL AFTER end_date,
ADD COLUMN session_duration INT NULL AFTER sessions_per_week,
ADD COLUMN difficulty_level VARCHAR(50) NULL AFTER session_duration,
ADD COLUMN goals TEXT NULL AFTER difficulty_level,
ADD COLUMN notes TEXT NULL AFTER goals,
ADD COLUMN exercises JSON NULL AFTER notes;

-- Add hourly_rate column to trainers table for cost calculation
ALTER TABLE trainers 
ADD COLUMN hourly_rate DECIMAL(10,2) NULL AFTER certification;

-- Update existing trainers with sample hourly rates
UPDATE trainers SET hourly_rate = 75.00 WHERE id = 1; -- Alex Martinez
UPDATE trainers SET hourly_rate = 65.00 WHERE id = 2; -- Emily Chen  
UPDATE trainers SET hourly_rate = 80.00 WHERE id = 3; -- Robert Taylor
UPDATE trainers SET hourly_rate = 70.00 WHERE id = 4; -- Lisa Anderson