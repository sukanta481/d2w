-- Migration: Add banner_url column to projects table
-- Date: 2026-01-03
-- Description: Adds a dedicated banner upload field for projects

USE d2w_cms;

-- Add banner_url column to projects table
ALTER TABLE projects 
ADD COLUMN banner_url VARCHAR(255) DEFAULT NULL 
AFTER image_url;

-- Add index for better query performance
CREATE INDEX idx_banner_url ON projects(banner_url);
