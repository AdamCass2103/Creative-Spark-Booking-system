-- Create database
CREATE DATABASE IF NOT EXISTS booking_system;
USE booking_system;

-- Table 1: Users (for signup/login)
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table 2: User preferences (tick boxes and training status)
CREATE TABLE IF NOT EXISTS user_preferences (
    pref_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    is_returning_member BOOLEAN DEFAULT FALSE,
    needs_training BOOLEAN DEFAULT FALSE,
    terms_accepted BOOLEAN DEFAULT FALSE,
    training_status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Insert admin user (password: admin123)
INSERT INTO users (name, email, password) 
VALUES ('Admin', 'admin@booking.com', '$2y$10$YourHashedPasswordHere');

-- Note: For demo, you can use this password hash generator or use:
-- password_hash('admin123', PASSWORD_DEFAULT)