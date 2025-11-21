-- Technologies/Tools Table for Dawn To Web CMS
-- Run this SQL in phpMyAdmin to create the technologies table

CREATE TABLE IF NOT EXISTS `technologies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `icon` varchar(100) NOT NULL COMMENT 'FontAwesome icon class',
  `color` varchar(20) DEFAULT '#333333' COMMENT 'Icon color in hex',
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default technologies
INSERT INTO `technologies` (`name`, `icon`, `color`, `display_order`, `status`) VALUES
('HTML5', 'fab fa-html5', '#E34F26', 1, 'active'),
('CSS3', 'fab fa-css3-alt', '#1572B6', 2, 'active'),
('JavaScript', 'fab fa-js-square', '#F7DF1E', 3, 'active'),
('PHP', 'fab fa-php', '#777BB4', 4, 'active'),
('WordPress', 'fab fa-wordpress', '#21759B', 5, 'active'),
('Bootstrap', 'fab fa-bootstrap', '#7952B3', 6, 'active'),
('Shopify', 'fab fa-shopify', '#96bf48', 7, 'active');
