<?php
/**
 * Database Configuration
 * BizNexa CMS
 */

require_once __DIR__ . '/../../config.env.php';

$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'])
           || strpos($_SERVER['SERVER_NAME'] ?? '', '.local') !== false
           || (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1')
           || php_sapi_name() === 'cli';

if (!defined('DB_HOST')) {
    if ($isLocal) {
        define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
        define('DB_USER', getenv('DB_USER') ?: 'root');
        define('DB_PASS', getenv('DB_PASS') ?: '');
        define('DB_NAME', getenv('DB_NAME') ?: 'd2w_cms');
    } else {
        // Production — use .env if available, fallback to hardcoded
        define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
        define('DB_USER', getenv('DB_USER') ?: 'u286257250_d2w');
        define('DB_PASS', getenv('DB_PASS') ?: 'Sukanta@0050');
        define('DB_NAME', getenv('DB_NAME') ?: 'u286257250_d2w_cms');
    }
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $charset = DB_CHARSET;

    public $conn;

    public function connect() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
        } catch(PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }

        return $this->conn;
    }
}
?>
