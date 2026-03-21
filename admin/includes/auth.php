<?php
/**
 * Authentication Helper
 * BizNexa CMS
 */

session_start();

require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    // Login user
    public function login($username, $password) {
        try {
            $query = "SELECT * FROM admin_users WHERE username = :username AND status = 'active' LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();

                if (password_verify($password, $user['password'])) {
                    // Regenerate session ID to prevent fixation
                    session_regenerate_id(true);

                    // Set session variables
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_email'] = $user['email'];
                    $_SESSION['admin_full_name'] = $user['full_name'];
                    $_SESSION['admin_role'] = $user['role'];
                    $_SESSION['admin_avatar'] = $user['avatar'];

                    // Store session security data
                    $_SESSION['last_activity'] = time();
                    $_SESSION['user_agent_hash'] = md5($_SERVER['HTTP_USER_AGENT'] ?? '');

                    // Update last login
                    $updateQuery = "UPDATE admin_users SET last_login = NOW() WHERE id = :id";
                    $updateStmt = $this->db->prepare($updateQuery);
                    $updateStmt->bindParam(':id', $user['id']);
                    $updateStmt->execute();

                    // Log activity
                    $this->logActivity($user['id'], 'login', null, null, 'User logged in');

                    return true;
                }
            }

            return false;
        } catch(PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            return false;
        }
    }

    // Logout user
    public function logout() {
        if (isset($_SESSION['admin_id'])) {
            $this->logActivity($_SESSION['admin_id'], 'logout', null, null, 'User logged out');
        }

        session_unset();
        session_destroy();
    }

    // Require login
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }

        // 30-minute idle timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 1800) {
            $this->logout();
            header('Location: login.php?timeout=1');
            exit();
        }
        $_SESSION['last_activity'] = time();

        // User-agent consistency check
        $currentUA = md5($_SERVER['HTTP_USER_AGENT'] ?? '');
        if (isset($_SESSION['user_agent_hash']) && $_SESSION['user_agent_hash'] !== $currentUA) {
            $this->logout();
            header('Location: login.php');
            exit();
        }
    }

    // Check if user has specific role
    public function hasRole($role) {
        if (!$this->isLoggedIn()) {
            return false;
        }

        if ($role === 'super_admin') {
            return $_SESSION['admin_role'] === 'super_admin';
        } elseif ($role === 'admin') {
            return in_array($_SESSION['admin_role'], ['super_admin', 'admin']);
        } else {
            return in_array($_SESSION['admin_role'], ['super_admin', 'admin', 'editor']);
        }
    }

    // Log activity
    public function logActivity($userId, $action, $tableName = null, $recordId = null, $description = null) {
        try {
            $query = "INSERT INTO activity_log (user_id, action, table_name, record_id, description, ip_address, user_agent)
                      VALUES (:user_id, :action, :table_name, :record_id, :description, :ip_address, :user_agent)";

            $stmt = $this->db->prepare($query);

            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':table_name', $tableName);
            $stmt->bindParam(':record_id', $recordId);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':ip_address', $ipAddress);
            $stmt->bindParam(':user_agent', $userAgent);

            $stmt->execute();
        } catch(PDOException $e) {
            error_log("Activity Log Error: " . $e->getMessage());
        }
    }

    // Get current user ID
    public function getUserId() {
        return $_SESSION['admin_id'] ?? null;
    }

    // Get current user data
    public function getUserData() {
        return [
            'id' => $_SESSION['admin_id'] ?? null,
            'username' => $_SESSION['admin_username'] ?? null,
            'email' => $_SESSION['admin_email'] ?? null,
            'full_name' => $_SESSION['admin_full_name'] ?? null,
            'role' => $_SESSION['admin_role'] ?? null,
            'avatar' => $_SESSION['admin_avatar'] ?? null
        ];
    }
}

// Create global auth instance
$auth = new Auth();
?>
