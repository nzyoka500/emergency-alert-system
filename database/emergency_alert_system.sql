-- ==========================================
-- DATABASE
-- ==========================================
CREATE DATABASE IF NOT EXISTS emergency_alert_system;
USE emergency_alert_system;

-- ==========================================
-- 1. ROLES
-- ==========================================
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO roles (name) VALUES 
('Admin'),
('Responder');

-- ==========================================
-- 2. USERS (Admin & Responders - Web)
-- ==========================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (role_id) REFERENCES roles(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);

-- ==========================================
-- 3. COMMUNITY USERS (Mobile App)
-- ==========================================
CREATE TABLE community_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) UNIQUE,
    device_token TEXT, -- FCM Token
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- 4. ALERT TYPES
-- ==========================================
CREATE TABLE alert_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO alert_types (name, description) VALUES
('Fire', 'Fire outbreak'),
('Flood', 'Flood disaster'),
('Accident', 'Road accident'),
('Security Threat', 'Man-made disaster');

-- ==========================================
-- 5. ALERTS
-- ==========================================
CREATE TABLE alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_type_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    status ENUM('pending','verified','broadcasted','resolved') DEFAULT 'pending',
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (alert_type_id) REFERENCES alert_types(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
    
    FOREIGN KEY (created_by) REFERENCES users(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
);

-- ==========================================
-- 6. ALERT RESPONSES (Responder Actions)
-- ==========================================
CREATE TABLE alert_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_id INT NOT NULL,
    responder_id INT NOT NULL,
    note TEXT,
    status ENUM('accepted','in_progress','completed') DEFAULT 'accepted',
    responded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (alert_id) REFERENCES alerts(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
    
    FOREIGN KEY (responder_id) REFERENCES users(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

-- ==========================================
-- 7. ALERT BROADCASTS (Tracks FCM sends)
-- ==========================================
CREATE TABLE alert_broadcasts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_id INT NOT NULL,
    sent_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (alert_id) REFERENCES alerts(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

-- ==========================================
-- 8. API TOKENS (Simple Token Auth)
-- ==========================================
CREATE TABLE api_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

-- ==========================================
-- 9. SYSTEM LOGS
-- ==========================================
CREATE TABLE system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
);
