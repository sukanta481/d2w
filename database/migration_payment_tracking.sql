-- Add payment tracking columns to bills table
-- Run this on your live server
-- USE u286257250_d2w_cms; -- Uncomment for live server

-- For local development
USE d2w_cms;

-- Add payment tracking columns (run one at a time if errors occur)
ALTER TABLE bills ADD COLUMN IF NOT EXISTS payment_date DATE NULL;
ALTER TABLE bills ADD COLUMN IF NOT EXISTS payment_method ENUM('cash', 'bank', 'upi', 'cheque', 'other') NULL;
ALTER TABLE bills ADD COLUMN IF NOT EXISTS payment_bank_id INT NULL;
ALTER TABLE bills ADD COLUMN IF NOT EXISTS payment_upi_id INT NULL;
ALTER TABLE bills ADD COLUMN IF NOT EXISTS payment_reference VARCHAR(100) NULL;
ALTER TABLE bills ADD COLUMN IF NOT EXISTS payment_notes TEXT NULL;

-- If the above doesn't work (MySQL doesn't support IF NOT EXISTS for columns), 
-- you can use these statements instead. Run them one by one and ignore errors for existing columns:
-- ALTER TABLE bills ADD COLUMN payment_date DATE NULL;
-- ALTER TABLE bills ADD COLUMN payment_method ENUM('cash', 'bank', 'upi', 'cheque', 'other') NULL;
-- ALTER TABLE bills ADD COLUMN payment_bank_id INT NULL;
-- ALTER TABLE bills ADD COLUMN payment_upi_id INT NULL;
-- ALTER TABLE bills ADD COLUMN payment_reference VARCHAR(100) NULL;
-- ALTER TABLE bills ADD COLUMN payment_notes TEXT NULL;
