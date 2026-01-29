-- Pausen Tracker Veritabanı Yapısı

CREATE DATABASE IF NOT EXISTS pausen_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE pausen_tracker;

-- Kullanıcılar tablosu
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Saat kayıtları tablosu
CREATE TABLE IF NOT EXISTS time_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type ENUM('Unterricht', 'Pause') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    duration INT NOT NULL COMMENT 'Dakika cinsinden',
    record_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, record_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Kullanıcı ayarları tablosu (zamanlama ayarları)
CREATE TABLE IF NOT EXISTS user_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    setting_name VARCHAR(100) NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_setting (user_id, setting_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Örnek kullanıcı ekleme (şifre: admin123)
INSERT INTO users (username, password, email) VALUES 
('admin', '$2y$10$YourHashedPasswordHere', 'admin@example.com');
