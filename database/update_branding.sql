-- Update site URL
USE d2w_cms;

UPDATE settings SET setting_value = 'biznexa.tech' WHERE setting_key = 'site_url';

-- Show all settings
SELECT * FROM settings;
