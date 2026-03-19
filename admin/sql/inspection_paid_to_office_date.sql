-- Add paid_to_office_date column to inspection_files
-- Run this on both local and production databases

ALTER TABLE inspection_files
    ADD COLUMN paid_to_office_date DATE DEFAULT NULL AFTER paid_to_office;
