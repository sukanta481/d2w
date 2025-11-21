<?php
/**
 * Database Installation Script
 * BizNexa CMS
 *
 * Run this file once to set up the database
 */

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'd2w_cms';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>BizNexa CMS - Database Installer</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .install-box { background: white; border-radius: 20px; padding: 40px; max-width: 600px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .install-header { text-align: center; margin-bottom: 30px; }
        .install-header i { font-size: 4rem; color: #0d6efd; margin-bottom: 20px; }
        .install-header h1 { font-size: 2rem; font-weight: 800; color: #2C3E50; }
        .step { padding: 15px; border-left: 4px solid #0d6efd; background: #f8f9fa; margin-bottom: 10px; border-radius: 5px; }
        .step.success { border-left-color: #10B981; background: #d1fae5; }
        .step.error { border-left-color: #dc3545; background: #fee; }
        .btn-install { padding: 15px 40px; font-size: 1.1rem; font-weight: 600; }
    </style>
</head>
<body>
    <div class='install-box'>
        <div class='install-header'>
            <i class='fas fa-database'></i>
            <h1>BizNexa CMS</h1>
            <p class='text-muted'>Database Installation</p>
        </div>";

try {
    // Step 1: Connect to MySQL
    echo "<div class='step'><i class='fas fa-circle-notch fa-spin me-2'></i> Connecting to MySQL...</div>";
    flush();

    $conn = new PDO("mysql:host=$host", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<script>document.querySelector('.step:last-child').className = 'step success'; document.querySelector('.step:last-child i').className = 'fas fa-check-circle me-2';</script>";
    flush();

    // Step 2: Create database
    echo "<div class='step'><i class='fas fa-circle-notch fa-spin me-2'></i> Creating database '$dbname'...</div>";
    flush();

    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->exec("USE $dbname");

    echo "<script>document.querySelector('.step:last-child').className = 'step success'; document.querySelector('.step:last-child i').className = 'fas fa-check-circle me-2';</script>";
    flush();

    // Step 3: Read SQL file
    echo "<div class='step'><i class='fas fa-circle-notch fa-spin me-2'></i> Reading SQL file...</div>";
    flush();

    $sqlFile = __DIR__ . '/../database/d2w_cms.sql';

    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found at: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);

    echo "<script>document.querySelector('.step:last-child').className = 'step success'; document.querySelector('.step:last-child i').className = 'fas fa-check-circle me-2';</script>";
    flush();

    // Step 4: Execute SQL
    echo "<div class='step'><i class='fas fa-circle-notch fa-spin me-2'></i> Creating tables and inserting data...</div>";
    flush();

    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (!empty($statement) && stripos($statement, 'CREATE DATABASE') === false && stripos($statement, 'USE ') === false) {
            $conn->exec($statement);
        }
    }

    echo "<script>document.querySelector('.step:last-child').className = 'step success'; document.querySelector('.step:last-child i').className = 'fas fa-check-circle me-2';</script>";
    flush();

    // Success message
    echo "
        <div class='alert alert-success mt-4' role='alert'>
            <h4 class='alert-heading'><i class='fas fa-check-circle me-2'></i>Installation Complete!</h4>
            <p>The database has been successfully installed.</p>
            <hr>
            <div class='mb-2'><strong>Database:</strong> $dbname</div>
            <div class='mb-2'><strong>Default Login:</strong></div>
            <ul>
                <li>Username: <strong>admin</strong></li>
                <li>Password: <strong>admin123</strong></li>
            </ul>
            <div class='mt-3'>
                <a href='login.php' class='btn btn-primary btn-install'>
                    <i class='fas fa-sign-in-alt me-2'></i>Go to Login Page
                </a>
            </div>
        </div>

        <div class='alert alert-warning mt-3'>
            <i class='fas fa-exclamation-triangle me-2'></i>
            <strong>Important:</strong> For security, please delete this install.php file after installation.
        </div>
    ";

} catch(PDOException $e) {
    echo "<script>document.querySelector('.step:last-child').className = 'step error'; document.querySelector('.step:last-child i').className = 'fas fa-times-circle me-2';</script>";

    echo "
        <div class='alert alert-danger mt-4' role='alert'>
            <h4 class='alert-heading'><i class='fas fa-times-circle me-2'></i>Installation Failed</h4>
            <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            <hr>
            <p class='mb-0'><strong>Please check:</strong></p>
            <ul>
                <li>MySQL is running (start it from XAMPP Control Panel)</li>
                <li>Database credentials are correct</li>
                <li>SQL file exists at: ../database/d2w_cms.sql</li>
            </ul>
        </div>
    ";
} catch(Exception $e) {
    echo "
        <div class='alert alert-danger mt-4' role='alert'>
            <h4 class='alert-heading'><i class='fas fa-times-circle me-2'></i>Error</h4>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
        </div>
    ";
}

echo "
    </div>
</body>
</html>";
?>
