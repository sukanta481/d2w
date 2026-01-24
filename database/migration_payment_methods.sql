-- Payment Methods Migration
-- Created: 2026-01-24
-- Description: Creates table for managing multiple payment methods (bank accounts, UPI)

USE d2w_cms;

-- Payment Methods table
CREATE TABLE IF NOT EXISTS payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('bank', 'upi') NOT NULL,
    name VARCHAR(100) NOT NULL COMMENT 'Display name like "HDFC Savings" or "GooglePay UPI"',
    -- Bank account fields
    bank_name VARCHAR(100) DEFAULT NULL,
    account_holder VARCHAR(100) DEFAULT NULL,
    account_number VARCHAR(50) DEFAULT NULL,
    ifsc_code VARCHAR(20) DEFAULT NULL,
    branch_name VARCHAR(100) DEFAULT NULL,
    -- UPI fields  
    upi_id VARCHAR(100) DEFAULT NULL,
    qr_code_path VARCHAR(255) DEFAULT NULL COMMENT 'Path to uploaded QR code image',
    -- Common fields
    is_default BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL,
    INDEX idx_type (type),
    INDEX idx_active (is_active),
    INDEX idx_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add payment method columns to bills table
ALTER TABLE bills 
ADD COLUMN bank_payment_method_id INT DEFAULT NULL AFTER terms,
ADD COLUMN upi_payment_method_id INT DEFAULT NULL AFTER bank_payment_method_id,
ADD CONSTRAINT fk_bills_bank_payment FOREIGN KEY (bank_payment_method_id) REFERENCES payment_methods(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_bills_upi_payment FOREIGN KEY (upi_payment_method_id) REFERENCES payment_methods(id) ON DELETE SET NULL;
