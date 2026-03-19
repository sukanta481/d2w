-- Run this AFTER inspection_tables.sql on production
-- Makes all fields optional for minimal file creation

ALTER TABLE inspection_files
    MODIFY file_date DATE DEFAULT NULL,
    MODIFY file_type ENUM('office','self') DEFAULT NULL,
    MODIFY customer_name VARCHAR(150) DEFAULT NULL,
    MODIFY property_address TEXT DEFAULT NULL,
    MODIFY property_value DECIMAL(15,2) DEFAULT NULL,
    MODIFY bank_id INT DEFAULT NULL,
    MODIFY branch_id INT DEFAULT NULL,
    MODIFY source_id INT DEFAULT NULL,
    ADD COLUMN report_status_date DATE DEFAULT NULL AFTER report_status,
    ADD COLUMN payment_status_date DATE DEFAULT NULL AFTER payment_status,
    ADD COLUMN commission_pending ENUM('yes','no') DEFAULT NULL AFTER paid_to_office,
    MODIFY commission DECIMAL(10,2) DEFAULT 0,
    MODIFY gross_amount DECIMAL(10,2) DEFAULT 0,
    MODIFY updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
