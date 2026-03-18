-- Inspection Module Tables
-- Run this migration to add inspection functionality

CREATE TABLE IF NOT EXISTS inspection_banks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bank_name VARCHAR(150) NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_bank_name (bank_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inspection_branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bank_id INT NOT NULL,
    branch_name VARCHAR(150) NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_bank_branch (bank_id, branch_name),
    FOREIGN KEY (bank_id) REFERENCES inspection_banks(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inspection_sources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_source_name (source_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inspection_payment_modes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mode_name VARCHAR(100) NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_mode_name (mode_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inspection_my_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_name VARCHAR(150) NOT NULL,
    bank_name VARCHAR(150) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    ifsc_code VARCHAR(20) DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inspection_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_number VARCHAR(20) NOT NULL,
    file_date DATE NOT NULL,
    file_type ENUM('office','self') NOT NULL,
    location ENUM('kolkata','out_of_kolkata') DEFAULT NULL,
    customer_name VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(20) DEFAULT NULL,
    property_address TEXT NOT NULL,
    property_value DECIMAL(15,2) NOT NULL,
    bank_id INT NOT NULL,
    branch_id INT NOT NULL,
    source_id INT NOT NULL,
    fees DECIMAL(10,2) DEFAULT NULL,
    report_status ENUM('draft','final_soft','final_hard') DEFAULT NULL,
    payment_mode_id INT DEFAULT NULL,
    payment_status ENUM('due','paid','partially') DEFAULT NULL,
    amount DECIMAL(10,2) DEFAULT NULL,
    paid_to_office ENUM('paid','due') DEFAULT NULL,
    office_amount DECIMAL(10,2) DEFAULT NULL,
    commission DECIMAL(10,2) NOT NULL,
    extra_amount DECIMAL(10,2) DEFAULT 0,
    gross_amount DECIMAL(10,2) NOT NULL,
    received_account_id INT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_file_number (file_number),
    INDEX idx_file_type (file_type),
    INDEX idx_payment_status (payment_status),
    INDEX idx_bank_id (bank_id),
    INDEX idx_source_id (source_id),
    INDEX idx_file_date (file_date),
    FOREIGN KEY (bank_id) REFERENCES inspection_banks(id) ON DELETE RESTRICT,
    FOREIGN KEY (branch_id) REFERENCES inspection_branches(id) ON DELETE RESTRICT,
    FOREIGN KEY (source_id) REFERENCES inspection_sources(id) ON DELETE RESTRICT,
    FOREIGN KEY (payment_mode_id) REFERENCES inspection_payment_modes(id) ON DELETE RESTRICT,
    FOREIGN KEY (received_account_id) REFERENCES inspection_my_accounts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default payment modes
INSERT IGNORE INTO inspection_payment_modes (mode_name) VALUES ('Cash'), ('UPI'), ('Bank Transfer'), ('Cheque');
