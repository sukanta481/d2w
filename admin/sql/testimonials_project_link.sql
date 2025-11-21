-- Add project_id column to testimonials table for linking testimonials to projects
-- Run this SQL in phpMyAdmin to add the new column

ALTER TABLE `testimonials`
ADD COLUMN `project_id` int(11) DEFAULT NULL AFTER `client_photo`,
ADD KEY `idx_project_id` (`project_id`);

-- Note: We don't add a foreign key constraint to allow flexibility
-- (testimonials can exist without being linked to a project)

-- To link an existing testimonial to a project, update like this:
-- UPDATE testimonials SET project_id = 1 WHERE id = 1;
