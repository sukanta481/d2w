-- General Section Migration
-- Adds 'general' to category ENUM and 'type' column for income/expense tracking
-- Created: 2026-03-22

-- Add 'general' to the category ENUM
ALTER TABLE expenses
MODIFY COLUMN category ENUM('biznexa', 'inspection', 'general') NOT NULL;

-- Add type column (income or expense), default to 'expense' so existing records are unaffected
ALTER TABLE expenses
ADD COLUMN type ENUM('income', 'expense') NOT NULL DEFAULT 'expense' AFTER category;
