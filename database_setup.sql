-- Smart Horizon Hackathon Database Setup
-- Run this SQL script in your MySQL database

CREATE DATABASE IF NOT EXISTS smart_horizon_hackathon;
USE smart_horizon_hackathon;

-- Table to store problem statements
CREATE TABLE problem_statements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    org_name VARCHAR(255) NOT NULL,
    spoc_name VARCHAR(255) NOT NULL,
    spoc_contact VARCHAR(20) NOT NULL,
    contact_email VARCHAR(255) NOT NULL,
    ps_title VARCHAR(500) NOT NULL,
    ps_description TEXT NOT NULL,
    domain VARCHAR(100),
    dataset_link VARCHAR(500),
    logo_filename VARCHAR(255) NOT NULL,
    logo_original_name VARCHAR(255) NOT NULL,
    logo_file_size INT,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table to store supporting documents
CREATE TABLE supporting_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ps_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_size INT,
    file_type VARCHAR(100),
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ps_id) REFERENCES problem_statements(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_ps_status ON problem_statements(status);
CREATE INDEX idx_ps_submission_date ON problem_statements(submission_date);
CREATE INDEX idx_ps_org_name ON problem_statements(org_name);
CREATE INDEX idx_docs_ps_id ON supporting_documents(ps_id);

-- Insert sample data (optional)
INSERT INTO problem_statements (
    org_name, spoc_name, spoc_contact, contact_email, ps_title, ps_description, 
    domain, dataset_link, logo_filename, logo_original_name, logo_file_size
) VALUES (
    'Sample Organization', 
    'John Doe', 
    '+91 9876543210', 
    'john.doe@sample.org',
    'AI-Powered Traffic Management System',
    'Develop an intelligent traffic management system using AI and IoT sensors to optimize traffic flow in urban areas.',
    'ai-ml',
    'https://example.com/traffic-data',
    'sample_logo_123456.png',
    'company_logo.png',
    245760
);