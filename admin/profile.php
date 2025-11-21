<?php
require_once 'includes/auth.php';
$auth->requireLogin();
require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

// Get current user data
$stmt = $db->prepare("SELECT * FROM admin_users WHERE id = :id");
$stmt->execute([':id' => $auth->getUserId()]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_profile'])) {
            $stmt = $db->prepare("UPDATE admin_users SET full_name = :full_name, email = :email, avatar = :avatar WHERE id = :id");
            $stmt->execute([':full_name' => $_POST['full_name'], ':email' => $_POST['email'], ':avatar' => $_POST['avatar'] ?? null, ':id' => $auth->getUserId()]);

            $_SESSION['admin_full_name'] = $_POST['full_name'];
            $_SESSION['admin_email'] = $_POST['email'];
            $_SESSION['admin_avatar'] = $_POST['avatar'] ?? null;

            $auth->logActivity($auth->getUserId(), 'update', 'admin_users', $auth->getUserId(), 'Updated profile');
            $successMessage = "Profile updated successfully!";

            // Refresh user data
            $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = :id");
            $stmt->execute([':id' => $auth->getUserId()]);
            $user = $stmt->fetch();
        }

        if (isset($_POST['change_password'])) {
            // Verify current password
            if (password_verify($_POST['current_password'], $user['password'])) {
                if ($_POST['new_password'] === $_POST['confirm_password']) {
                    if (strlen($_POST['new_password']) >= 6) {
                        $hashedPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                        $stmt = $db->prepare("UPDATE admin_users SET password = :password WHERE id = :id");
                        $stmt->execute([':password' => $hashedPassword, ':id' => $auth->getUserId()]);

                        $auth->logActivity($auth->getUserId(), 'update', 'admin_users', $auth->getUserId(), 'Changed password');
                        $successMessage = "Password changed successfully!";
                    } else {
                        $errorMessage = "Password must be at least 6 characters long.";
                    }
                } else {
                    $errorMessage = "New passwords do not match.";
                }
            } else {
                $errorMessage = "Current password is incorrect.";
            }
        }
    } catch(PDOException $e) { $errorMessage = "Error: " . $e->getMessage(); }
}

$pageTitle = 'My Profile';
include 'includes/header.php';
?>

<div class="admin-content">
    <div class="page-header">
        <h1 class="page-title">My Profile</h1>
        <p class="page-subtitle">Manage your account settings</p>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo $successMessage; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?php echo $errorMessage; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row">
        <!-- Profile Information -->
        <div class="col-lg-8 mb-4">
            <div class="content-card">
                <h3 class="h5 mb-4"><i class="fas fa-user me-2 text-primary"></i>Profile Information</h3>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small class="text-muted">Username cannot be changed</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="<?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>" disabled>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Avatar URL</label>
                            <input type="url" name="avatar" class="form-control" value="<?php echo htmlspecialchars($user['avatar'] ?? ''); ?>" placeholder="https://example.com/avatar.jpg">
                        </div>
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Profile
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="content-card mt-4">
                <h3 class="h5 mb-4"><i class="fas fa-lock me-2 text-primary"></i>Change Password</h3>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Current Password *</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Password *</label>
                            <input type="password" name="new_password" class="form-control" minlength="6" required>
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm New Password *</label>
                            <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                        </div>
                    </div>

                    <button type="submit" name="change_password" class="btn btn-warning">
                        <i class="fas fa-key me-2"></i>Change Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Profile Summary -->
        <div class="col-lg-4 mb-4">
            <div class="content-card text-center">
                <div class="mb-4">
                    <?php if ($user['avatar']): ?>
                        <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <div style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, #0d6efd, #10B981); color: white; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: bold; margin: 0 auto;">
                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                <p class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></p>

                <span class="badge bg-primary mb-3"><?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?></span>

                <hr>

                <div class="text-start">
                    <p class="mb-2"><i class="fas fa-envelope me-2 text-muted"></i><?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="mb-2"><i class="fas fa-calendar me-2 text-muted"></i>Joined: <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                    <?php if ($user['last_login']): ?>
                        <p class="mb-0"><i class="fas fa-clock me-2 text-muted"></i>Last login: <?php echo date('M d, Y g:i A', strtotime($user['last_login'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
