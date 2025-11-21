<?php
/**
 * Create Admin User
 * Run this script to create/reset the admin user
 */

require_once 'config/database.php';

$database = new Database();
$db = $database->connect();

// Admin user details
$username = 'admin';
$email = 'admin@biznexa.tech';
$password = 'admin123';
$fullName = 'Administrator';
$role = 'super_admin';

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Create Admin User</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .box { background: white; border-radius: 20px; padding: 40px; max-width: 600px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .header { text-align: center; margin-bottom: 30px; }
        .header i { font-size: 4rem; color: #0d6efd; margin-bottom: 20px; }
        .header h1 { font-size: 2rem; font-weight: 800; color: #2C3E50; }
    </style>
</head>
<body>
    <div class='box'>
        <div class='header'>
            <i class='fas fa-user-shield'></i>
            <h1>Create Admin User</h1>
        </div>";

try {
    // Check if user exists
    $checkQuery = "SELECT id FROM admin_users WHERE username = :username";
    $stmt = $db->prepare($checkQuery);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Update existing user
        $updateQuery = "UPDATE admin_users SET
                        email = :email,
                        password = :password,
                        full_name = :full_name,
                        role = :role,
                        status = 'active'
                        WHERE username = :username";

        $stmt = $db->prepare($updateQuery);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':full_name', $fullName);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        echo "<div class='alert alert-success'>
                <h4><i class='fas fa-check-circle me-2'></i>Admin User Updated!</h4>
                <p>The admin user has been updated successfully.</p>
              </div>";
    } else {
        // Insert new user
        $insertQuery = "INSERT INTO admin_users (username, email, password, full_name, role, status)
                        VALUES (:username, :email, :password, :full_name, :role, 'active')";

        $stmt = $db->prepare($insertQuery);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':full_name', $fullName);
        $stmt->bindParam(':role', $role);
        $stmt->execute();

        echo "<div class='alert alert-success'>
                <h4><i class='fas fa-check-circle me-2'></i>Admin User Created!</h4>
                <p>The admin user has been created successfully.</p>
              </div>";
    }

    echo "
        <div class='mt-4'>
            <h5>Login Credentials:</h5>
            <div class='card bg-light p-3'>
                <p class='mb-2'><strong>Username:</strong> <code>$username</code></p>
                <p class='mb-2'><strong>Password:</strong> <code>$password</code></p>
                <p class='mb-0'><strong>Email:</strong> <code>$email</code></p>
            </div>
        </div>

        <div class='alert alert-info mt-3'>
            <i class='fas fa-info-circle me-2'></i>
            <strong>Password Hash:</strong><br>
            <small style='word-break: break-all;'>$hashedPassword</small>
        </div>

        <div class='text-center mt-4'>
            <a href='login.php' class='btn btn-primary btn-lg'>
                <i class='fas fa-sign-in-alt me-2'></i>Go to Login
            </a>
        </div>

        <div class='alert alert-warning mt-3 mb-0'>
            <i class='fas fa-exclamation-triangle me-2'></i>
            <strong>Security:</strong> Delete this file (create-admin.php) after creating the admin user.
        </div>
    ";

} catch(PDOException $e) {
    echo "<div class='alert alert-danger'>
            <h4><i class='fas fa-times-circle me-2'></i>Error!</h4>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
            <hr>
            <p class='mb-0'><strong>Make sure:</strong></p>
            <ul>
                <li>The database 'd2w_cms' exists</li>
                <li>The 'admin_users' table has been created</li>
                <li>MySQL is running</li>
            </ul>
          </div>";
}

echo "
    </div>
</body>
</html>";
?>
