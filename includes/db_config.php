<?php
/**
 * Frontend Database Configuration
 * BizNexa Website
 *
 * Automatically detects environment (local vs production)
 */

// Check if we're on local or production server
$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'])
           || strpos($_SERVER['SERVER_NAME'] ?? '', '.local') !== false
           || (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1');

if ($isLocal) {
    // Local Development (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'd2w_cms');
} else {
    // Production Server (Hostinger)
    define('DB_HOST', 'localhost');
    define('DB_USER', 'u286257250_d2w');
    define('DB_PASS', 'Sukanta@0050');
    define('DB_NAME', 'u286257250_d2w_cms');
}

function getDBConnection() {
    static $conn = null;

    if ($conn === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $conn = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch(PDOException $e) {
            // If database doesn't exist, return null (use static data)
            return null;
        }
    }

    return $conn;
}

// Get settings from database
function getSetting($key, $default = '') {
    $db = getDBConnection();
    if (!$db) return $default;

    try {
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = :key");
        $stmt->execute([':key' => $key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch(PDOException $e) {
        return $default;
    }
}

// Get all settings
function getAllSettings() {
    $db = getDBConnection();
    if (!$db) return [];

    try {
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch(PDOException $e) {
        return [];
    }
}

// Get active services
function getServices() {
    $db = getDBConnection();
    if (!$db) return [];

    try {
        $stmt = $db->query("SELECT * FROM services WHERE status = 'active' ORDER BY display_order ASC, id ASC");
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Get active testimonials
function getTestimonials() {
    $db = getDBConnection();
    if (!$db) return [];

    try {
        $stmt = $db->query("SELECT * FROM testimonials WHERE status = 'active' ORDER BY display_order ASC, id DESC LIMIT 6");
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Get active projects
function getProjects($limit = null, $featured = false) {
    $db = getDBConnection();
    if (!$db) return [];

    try {
        $query = "SELECT * FROM projects WHERE status = 'active'";
        if ($featured) $query .= " AND featured = 1";
        $query .= " ORDER BY featured DESC, created_at DESC";
        if ($limit) $query .= " LIMIT " . (int)$limit;

        $stmt = $db->query($query);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Get active AI agents
function getAIAgents() {
    $db = getDBConnection();
    if (!$db) return [];

    try {
        $stmt = $db->query("SELECT * FROM ai_agents WHERE status = 'active' ORDER BY display_order ASC, id ASC");
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Get active technologies/tools
function getTechnologies() {
    $db = getDBConnection();
    if (!$db) return [];

    try {
        $stmt = $db->query("SELECT * FROM technologies WHERE status = 'active' ORDER BY display_order ASC, id ASC");
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Get published blog posts
function getBlogPosts($limit = null) {
    $db = getDBConnection();
    if (!$db) return [];

    try {
        $query = "SELECT * FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC";
        if ($limit) $query .= " LIMIT " . (int)$limit;

        $stmt = $db->query($query);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Get single blog post by slug
function getBlogPost($slug) {
    $db = getDBConnection();
    if (!$db) return null;

    try {
        $stmt = $db->prepare("SELECT bp.*, au.full_name as author_name
                              FROM blog_posts bp
                              LEFT JOIN admin_users au ON bp.author_id = au.id
                              WHERE bp.slug = :slug AND bp.status = 'published'");
        $stmt->execute([':slug' => $slug]);
        $post = $stmt->fetch();

        // Increment views
        if ($post) {
            $updateStmt = $db->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = :id");
            $updateStmt->execute([':id' => $post['id']]);
        }

        return $post;
    } catch(PDOException $e) {
        return null;
    }
}

// Get related blog posts (same category, exclude current)
function getRelatedBlogPosts($postId, $category, $limit = 3) {
    $db = getDBConnection();
    if (!$db) return [];

    try {
        $stmt = $db->prepare("SELECT * FROM blog_posts
                              WHERE status = 'published'
                              AND category = :category
                              AND id != :id
                              ORDER BY published_at DESC
                              LIMIT " . (int)$limit);
        $stmt->execute([':category' => $category, ':id' => $postId]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Get single project by ID
function getProject($id) {
    $db = getDBConnection();
    if (!$db) return null;

    try {
        $stmt = $db->prepare("SELECT * FROM projects WHERE id = :id AND status = 'active'");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return null;
    }
}

// Get testimonial by client company name (to link project client with testimonial)
function getTestimonialByClient($clientName) {
    $db = getDBConnection();
    if (!$db) return null;

    try {
        // Try to find testimonial where client_company or client_name matches
        $stmt = $db->prepare("SELECT * FROM testimonials
                              WHERE status = 'active'
                              AND (client_company LIKE :name OR client_name LIKE :name2)
                              LIMIT 1");
        $stmt->execute([':name' => '%' . $clientName . '%', ':name2' => '%' . $clientName . '%']);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return null;
    }
}

// Get testimonial linked to a specific project by project_id
function getTestimonialByProject($projectId) {
    $db = getDBConnection();
    if (!$db) return null;

    try {
        $stmt = $db->prepare("SELECT * FROM testimonials
                              WHERE status = 'active'
                              AND project_id = :project_id
                              LIMIT 1");
        $stmt->execute([':project_id' => $projectId]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return null;
    }
}

// Get related projects (same category, exclude current)
function getRelatedProjects($projectId, $category, $limit = 3) {
    $db = getDBConnection();
    if (!$db) return [];

    try {
        $stmt = $db->prepare("SELECT * FROM projects
                              WHERE status = 'active'
                              AND category = :category
                              AND id != :id
                              ORDER BY featured DESC, created_at DESC
                              LIMIT " . (int)$limit);
        $stmt->execute([':category' => $category, ':id' => $projectId]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Save lead/contact form
function saveLead($data) {
    $db = getDBConnection();
    if (!$db) return false;

    try {
        $stmt = $db->prepare("INSERT INTO leads (name, email, phone, company, service_type, message, budget_range, source, status, priority)
                              VALUES (:name, :email, :phone, :company, :service_type, :message, :budget_range, :source, 'new', 'medium')");

        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?? null,
            ':company' => $data['company'] ?? null,
            ':service_type' => $data['service_type'] ?? null,
            ':message' => $data['message'],
            ':budget_range' => $data['budget_range'] ?? null,
            ':source' => $data['source'] ?? 'website'
        ]);

        return true;
    } catch(PDOException $e) {
        return false;
    }
}
?>
