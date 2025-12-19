-- Participant Registration Database Setup for Smart Horizon Hackathon 2026
-- Run this in phpMyAdmin or MySQL command line

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS hackathon_participants;
USE hackathon_participants;

-- Participants table
CREATE TABLE IF NOT EXISTS participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL,
    team_leader_name VARCHAR(100) NOT NULL,
    team_leader_email VARCHAR(100) NOT NULL UNIQUE,
    team_leader_phone VARCHAR(20) NOT NULL,
    college_name VARCHAR(200) NOT NULL,
    college_address TEXT,
    team_size INT NOT NULL CHECK (team_size BETWEEN 2 AND 4),
    domain_preference VARCHAR(100),
    experience_level ENUM('Beginner', 'Intermediate', 'Advanced') DEFAULT 'Beginner',
    previous_hackathons INT DEFAULT 0,
    special_requirements TEXT,
    dietary_preferences TEXT,
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected', 'waitlist') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'waived') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Team members table
CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    participant_id INT NOT NULL,
    member_name VARCHAR(100) NOT NULL,
    member_email VARCHAR(100) NOT NULL,
    member_phone VARCHAR(20),
    member_role VARCHAR(50),
    skills TEXT,
    github_profile VARCHAR(200),
    linkedin_profile VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE
);

-- Skills table for better organization
CREATE TABLE IF NOT EXISTS participant_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    participant_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    proficiency_level ENUM('Beginner', 'Intermediate', 'Advanced', 'Expert') DEFAULT 'Beginner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE
);

-- Documents table for participant uploads
CREATE TABLE IF NOT EXISTS participant_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    participant_id INT NOT NULL,
    document_type ENUM('id_proof', 'college_id', 'resume', 'portfolio', 'other') NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    stored_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    mime_type VARCHAR(100),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_participant_email ON participants(team_leader_email);
CREATE INDEX idx_participant_status ON participants(status);
CREATE INDEX idx_participant_registration_date ON participants(registration_date);
CREATE INDEX idx_team_member_email ON team_members(member_email);

-- Insert some sample data for testing
INSERT INTO participants (
    team_name, team_leader_name, team_leader_email, team_leader_phone, 
    college_name, team_size, domain_preference, experience_level
) VALUES 
('Code Warriors', 'John Doe', 'john.doe@example.com', '+91-9876543210', 
 'New Horizon College of Engineering', 4, 'Web Development', 'Intermediate'),
('Tech Innovators', 'Jane Smith', 'jane.smith@example.com', '+91-9876543211', 
 'RV College of Engineering', 3, 'AI/ML', 'Advanced'),
('Digital Pioneers', 'Mike Johnson', 'mike.johnson@example.com', '+91-9876543212', 
 'PES University', 2, 'Mobile App Development', 'Beginner');

-- Insert sample team members
INSERT INTO team_members (participant_id, member_name, member_email, member_phone, member_role, skills) VALUES
(1, 'Alice Brown', 'alice.brown@example.com', '+91-9876543213', 'Frontend Developer', 'React, JavaScript, CSS'),
(1, 'Bob Wilson', 'bob.wilson@example.com', '+91-9876543214', 'Backend Developer', 'Node.js, Python, MongoDB'),
(1, 'Charlie Davis', 'charlie.davis@example.com', '+91-9876543215', 'UI/UX Designer', 'Figma, Adobe XD, Photoshop'),
(2, 'Diana Miller', 'diana.miller@example.com', '+91-9876543216', 'Data Scientist', 'Python, TensorFlow, Pandas'),
(2, 'Eve Garcia', 'eve.garcia@example.com', '+91-9876543217', 'ML Engineer', 'PyTorch, Scikit-learn, OpenCV');

SHOW TABLES;