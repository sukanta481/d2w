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

// Handle form submissions
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

        // Add Bank Account
        if (isset($_POST['add_bank'])) {
            $stmt = $db->prepare("INSERT INTO payment_methods (type, name, bank_name, account_holder, account_number, ifsc_code, branch_name, is_default, created_by)
                VALUES ('bank', :name, :bank_name, :account_holder, :account_number, :ifsc_code, :branch_name, :is_default, :created_by)");
            $stmt->execute([
                ':name' => $_POST['name'],
                ':bank_name' => $_POST['bank_name'],
                ':account_holder' => $_POST['account_holder'],
                ':account_number' => $_POST['account_number'],
                ':ifsc_code' => $_POST['ifsc_code'],
                ':branch_name' => $_POST['branch_name'] ?? null,
                ':is_default' => isset($_POST['is_default']) ? 1 : 0,
                ':created_by' => $auth->getUserId()
            ]);
            
            // If set as default, remove default from others
            if (isset($_POST['is_default'])) {
                $newId = $db->lastInsertId();
                $db->prepare("UPDATE payment_methods SET is_default = 0 WHERE type = 'bank' AND id != :id")
                   ->execute([':id' => $newId]);
            }
            
            $auth->logActivity($auth->getUserId(), 'create', 'payment_methods', $db->lastInsertId(), 'Added bank account: ' . $_POST['name']);
            $successMessage = "Bank account added successfully!";
        }

        // Add UPI
        if (isset($_POST['add_upi'])) {
            $qrCodePath = null;
            
            // Handle QR code upload
            if (isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/payment-qr/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
                $fileType = mime_content_type($_FILES['qr_code']['tmp_name']);
                
                if (in_array($fileType, $allowedTypes)) {
                    $extension = pathinfo($_FILES['qr_code']['name'], PATHINFO_EXTENSION);
                    $fileName = 'qr_' . time() . '_' . uniqid() . '.' . $extension;
                    $targetPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['qr_code']['tmp_name'], $targetPath)) {
                        $qrCodePath = 'uploads/payment-qr/' . $fileName;
                    }
                }
            }
            
            $stmt = $db->prepare("INSERT INTO payment_methods (type, name, upi_id, qr_code_path, is_default, created_by)
                VALUES ('upi', :name, :upi_id, :qr_code_path, :is_default, :created_by)");
            $stmt->execute([
                ':name' => $_POST['name'],
                ':upi_id' => $_POST['upi_id'],
                ':qr_code_path' => $qrCodePath,
                ':is_default' => isset($_POST['is_default']) ? 1 : 0,
                ':created_by' => $auth->getUserId()
            ]);
            
            // If set as default, remove default from others
            if (isset($_POST['is_default'])) {
                $newId = $db->lastInsertId();
                $db->prepare("UPDATE payment_methods SET is_default = 0 WHERE type = 'upi' AND id != :id")
                   ->execute([':id' => $newId]);
            }
            
            $auth->logActivity($auth->getUserId(), 'create', 'payment_methods', $db->lastInsertId(), 'Added UPI: ' . $_POST['name']);
            $successMessage = "UPI payment method added successfully!";
        }

        // Update Payment Method
        if (isset($_POST['update_payment_method'])) {
            $methodId = $_POST['method_id'];
            $methodType = $_POST['method_type'];
            
            if ($methodType === 'bank') {
                $stmt = $db->prepare("UPDATE payment_methods SET name = :name, bank_name = :bank_name, 
                    account_holder = :account_holder, account_number = :account_number, 
                    ifsc_code = :ifsc_code, branch_name = :branch_name, is_active = :is_active
                    WHERE id = :id");
                $stmt->execute([
                    ':id' => $methodId,
                    ':name' => $_POST['name'],
                    ':bank_name' => $_POST['bank_name'],
                    ':account_holder' => $_POST['account_holder'],
                    ':account_number' => $_POST['account_number'],
                    ':ifsc_code' => $_POST['ifsc_code'],
                    ':branch_name' => $_POST['branch_name'] ?? null,
                    ':is_active' => isset($_POST['is_active']) ? 1 : 0
                ]);
            } else {
                // Get current QR code path
                $stmt = $db->prepare("SELECT qr_code_path FROM payment_methods WHERE id = :id");
                $stmt->execute([':id' => $methodId]);
                $current = $stmt->fetch();
                $qrCodePath = $current['qr_code_path'];
                
                // Handle new QR code upload
                if (isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../uploads/payment-qr/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
                    $fileType = mime_content_type($_FILES['qr_code']['tmp_name']);
                    
                    if (in_array($fileType, $allowedTypes)) {
                        // Delete old QR code
                        if ($qrCodePath && file_exists('../' . $qrCodePath)) {
                            unlink('../' . $qrCodePath);
                        }
                        
                        $extension = pathinfo($_FILES['qr_code']['name'], PATHINFO_EXTENSION);
                        $fileName = 'qr_' . time() . '_' . uniqid() . '.' . $extension;
                        $targetPath = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['qr_code']['tmp_name'], $targetPath)) {
                            $qrCodePath = 'uploads/payment-qr/' . $fileName;
                        }
                    }
                }
                
                $stmt = $db->prepare("UPDATE payment_methods SET name = :name, upi_id = :upi_id, 
                    qr_code_path = :qr_code_path, is_active = :is_active WHERE id = :id");
                $stmt->execute([
                    ':id' => $methodId,
                    ':name' => $_POST['name'],
                    ':upi_id' => $_POST['upi_id'],
                    ':qr_code_path' => $qrCodePath,
                    ':is_active' => isset($_POST['is_active']) ? 1 : 0
                ]);
            }
            
            $auth->logActivity($auth->getUserId(), 'update', 'payment_methods', $methodId, 'Updated payment method');
            $successMessage = "Payment method updated successfully!";
        }

        // Delete Payment Method
        if (isset($_POST['delete_payment_method'])) {
            $methodId = $_POST['method_id'];
            
            // Get QR code path to delete file
            $stmt = $db->prepare("SELECT qr_code_path FROM payment_methods WHERE id = :id");
            $stmt->execute([':id' => $methodId]);
            $method = $stmt->fetch();
            
            if ($method && $method['qr_code_path'] && file_exists('../' . $method['qr_code_path'])) {
                unlink('../' . $method['qr_code_path']);
            }
            
            $stmt = $db->prepare("DELETE FROM payment_methods WHERE id = :id");
            $stmt->execute([':id' => $methodId]);
            
            $auth->logActivity($auth->getUserId(), 'delete', 'payment_methods', $methodId, 'Deleted payment method');
            $successMessage = "Payment method deleted successfully!";
        }

        // Set as Default
        if (isset($_POST['set_default'])) {
            $methodId = $_POST['method_id'];
            $methodType = $_POST['method_type'];
            
            // Remove default from all of same type
            $db->prepare("UPDATE payment_methods SET is_default = 0 WHERE type = :type")
               ->execute([':type' => $methodType]);
            
            // Set this one as default
            $db->prepare("UPDATE payment_methods SET is_default = 1 WHERE id = :id")
               ->execute([':id' => $methodId]);
            
            $successMessage = "Default payment method updated!";
        }

    } catch(PDOException $e) { 
        $errorMessage = "Error: " . $e->getMessage(); 
    }
}

// Get payment methods
try {
    $bankAccounts = $db->query("SELECT * FROM payment_methods WHERE type = 'bank' ORDER BY is_default DESC, display_order, name")->fetchAll();
    $upiMethods = $db->query("SELECT * FROM payment_methods WHERE type = 'upi' ORDER BY is_default DESC, display_order, name")->fetchAll();
} catch(PDOException $e) {
    $bankAccounts = [];
    $upiMethods = [];
}

$pageTitle = 'My Profile';
include 'includes/header.php';
?>

<style>
.payment-method-card {
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    background: var(--card-bg);
    transition: all 0.3s;
    position: relative;
}
.payment-method-card:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.payment-method-card.default {
    border-color: #28a745;
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.05), transparent);
}
.payment-method-card .badge-default {
    position: absolute;
    top: 10px;
    right: 10px;
}
.payment-method-card .method-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-right: 1rem;
}
.payment-method-card .method-icon.bank {
    background: linear-gradient(135deg, #0d6efd, #0a58ca);
    color: #fff;
}
.payment-method-card .method-icon.upi {
    background: linear-gradient(135deg, #6f42c1, #5a32a3);
    color: #fff;
}
.payment-method-card .method-details h5 {
    margin: 0 0 0.25rem 0;
    font-size: 1rem;
}
.payment-method-card .method-details p {
    margin: 0;
    font-size: 0.85rem;
    color: #6c757d;
}
.payment-method-card .method-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}
.qr-preview {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid var(--border-color);
}
.nav-tabs-billing {
    border-bottom: 2px solid var(--border-color);
    margin-bottom: 1.5rem;
}
.nav-tabs-billing .nav-link {
    border: none;
    color: #6c757d;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    position: relative;
}
.nav-tabs-billing .nav-link.active {
    color: var(--primary-color);
    background: transparent;
}
.nav-tabs-billing .nav-link.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--primary-color);
}
.tab-pane { padding-top: 1rem; }
</style>

<div class="admin-content">
    <div class="page-header">
        <h1 class="page-title">My Profile</h1>
        <p class="page-subtitle">Manage your account settings and billing information</p>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo $successMessage; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?php echo $errorMessage; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Profile Information -->
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

            <!-- Payment Methods Section -->
            <div class="content-card mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="h5 mb-0"><i class="fas fa-credit-card me-2 text-primary"></i>Billing & Payment Methods</h3>
                </div>
                
                <p class="text-muted mb-4">Add your bank accounts and UPI IDs to display on invoices. These will be available when creating bills.</p>

                <!-- Tabs -->
                <ul class="nav nav-tabs nav-tabs-billing" id="paymentTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank-accounts" type="button">
                            <i class="fas fa-university me-2"></i>Bank Accounts (<?php echo count($bankAccounts); ?>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="upi-tab" data-bs-toggle="tab" data-bs-target="#upi-methods" type="button">
                            <i class="fas fa-qrcode me-2"></i>UPI Payments (<?php echo count($upiMethods); ?>)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="paymentTabsContent">
                    <!-- Bank Accounts Tab -->
                    <div class="tab-pane fade show active" id="bank-accounts" role="tabpanel">
                        <button class="btn btn-outline-primary mb-3" data-bs-toggle="modal" data-bs-target="#addBankModal">
                            <i class="fas fa-plus me-2"></i>Add Bank Account
                        </button>

                        <?php if (empty($bankAccounts)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-university fa-3x mb-3 opacity-50"></i>
                                <p>No bank accounts added yet</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($bankAccounts as $bank): ?>
                            <div class="payment-method-card <?php echo $bank['is_default'] ? 'default' : ''; ?>">
                                <?php if ($bank['is_default']): ?>
                                    <span class="badge bg-success badge-default"><i class="fas fa-check me-1"></i>Default</span>
                                <?php endif; ?>
                                <div class="d-flex align-items-start">
                                    <div class="method-icon bank">
                                        <i class="fas fa-university"></i>
                                    </div>
                                    <div class="method-details flex-grow-1">
                                        <h5><?php echo htmlspecialchars($bank['name']); ?></h5>
                                        <p><strong><?php echo htmlspecialchars($bank['bank_name']); ?></strong></p>
                                        <p>A/C: <?php echo htmlspecialchars($bank['account_number']); ?> | IFSC: <?php echo htmlspecialchars($bank['ifsc_code']); ?></p>
                                        <p>Holder: <?php echo htmlspecialchars($bank['account_holder']); ?></p>
                                        <?php if (!$bank['is_active']): ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="method-actions">
                                    <?php if (!$bank['is_default']): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="set_default" value="1">
                                        <input type="hidden" name="method_id" value="<?php echo $bank['id']; ?>">
                                        <input type="hidden" name="method_type" value="bank">
                                        <button type="submit" class="btn btn-sm btn-outline-success">Set Default</button>
                                    </form>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editBankModal<?php echo $bank['id']; ?>">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteBankModal<?php echo $bank['id']; ?>">Delete</button>
                                </div>
                            </div>

                            <!-- Edit Bank Modal -->
                            <div class="modal fade" id="editBankModal<?php echo $bank['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <input type="hidden" name="update_payment_method" value="1">
                                            <input type="hidden" name="method_id" value="<?php echo $bank['id']; ?>">
                                            <input type="hidden" name="method_type" value="bank">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Bank Account</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Display Name *</label>
                                                    <input type="text" class="form-control" name="name" required value="<?php echo htmlspecialchars($bank['name']); ?>" placeholder="e.g., HDFC Savings">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Bank Name *</label>
                                                    <input type="text" class="form-control" name="bank_name" required value="<?php echo htmlspecialchars($bank['bank_name']); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Account Holder Name *</label>
                                                    <input type="text" class="form-control" name="account_holder" required value="<?php echo htmlspecialchars($bank['account_holder']); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Account Number *</label>
                                                    <input type="text" class="form-control" name="account_number" required value="<?php echo htmlspecialchars($bank['account_number']); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">IFSC Code *</label>
                                                    <input type="text" class="form-control" name="ifsc_code" required value="<?php echo htmlspecialchars($bank['ifsc_code']); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Branch Name</label>
                                                    <input type="text" class="form-control" name="branch_name" value="<?php echo htmlspecialchars($bank['branch_name'] ?? ''); ?>">
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="is_active" id="bankActive<?php echo $bank['id']; ?>" <?php echo $bank['is_active'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="bankActive<?php echo $bank['id']; ?>">Active</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Bank Modal -->
                            <div class="modal fade" id="deleteBankModal<?php echo $bank['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <input type="hidden" name="delete_payment_method" value="1">
                                            <input type="hidden" name="method_id" value="<?php echo $bank['id']; ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Bank Account</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete <strong><?php echo htmlspecialchars($bank['name']); ?></strong>?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- UPI Methods Tab -->
                    <div class="tab-pane fade" id="upi-methods" role="tabpanel">
                        <button class="btn btn-outline-primary mb-3" data-bs-toggle="modal" data-bs-target="#addUpiModal">
                            <i class="fas fa-plus me-2"></i>Add UPI Payment
                        </button>

                        <?php if (empty($upiMethods)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-qrcode fa-3x mb-3 opacity-50"></i>
                                <p>No UPI payment methods added yet</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($upiMethods as $upi): ?>
                            <div class="payment-method-card <?php echo $upi['is_default'] ? 'default' : ''; ?>">
                                <?php if ($upi['is_default']): ?>
                                    <span class="badge bg-success badge-default"><i class="fas fa-check me-1"></i>Default</span>
                                <?php endif; ?>
                                <div class="d-flex align-items-start">
                                    <div class="method-icon upi">
                                        <i class="fas fa-qrcode"></i>
                                    </div>
                                    <div class="method-details flex-grow-1">
                                        <h5><?php echo htmlspecialchars($upi['name']); ?></h5>
                                        <p><strong>UPI ID:</strong> <?php echo htmlspecialchars($upi['upi_id']); ?></p>
                                        <?php if (!$upi['is_active']): ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($upi['qr_code_path']): ?>
                                        <img src="../<?php echo htmlspecialchars($upi['qr_code_path']); ?>" alt="QR Code" class="qr-preview">
                                    <?php endif; ?>
                                </div>
                                <div class="method-actions">
                                    <?php if (!$upi['is_default']): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="set_default" value="1">
                                        <input type="hidden" name="method_id" value="<?php echo $upi['id']; ?>">
                                        <input type="hidden" name="method_type" value="upi">
                                        <button type="submit" class="btn btn-sm btn-outline-success">Set Default</button>
                                    </form>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUpiModal<?php echo $upi['id']; ?>">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteUpiModal<?php echo $upi['id']; ?>">Delete</button>
                                </div>
                            </div>

                            <!-- Edit UPI Modal -->
                            <div class="modal fade" id="editUpiModal<?php echo $upi['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="update_payment_method" value="1">
                                            <input type="hidden" name="method_id" value="<?php echo $upi['id']; ?>">
                                            <input type="hidden" name="method_type" value="upi">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit UPI Payment</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Display Name *</label>
                                                    <input type="text" class="form-control" name="name" required value="<?php echo htmlspecialchars($upi['name']); ?>" placeholder="e.g., GooglePay">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">UPI ID *</label>
                                                    <input type="text" class="form-control" name="upi_id" required value="<?php echo htmlspecialchars($upi['upi_id']); ?>" placeholder="yourname@upi">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">QR Code Image</label>
                                                    <?php if ($upi['qr_code_path']): ?>
                                                        <div class="mb-2">
                                                            <img src="../<?php echo htmlspecialchars($upi['qr_code_path']); ?>" alt="Current QR" style="max-width: 100px;">
                                                        </div>
                                                    <?php endif; ?>
                                                    <input type="file" class="form-control" name="qr_code" accept="image/*">
                                                    <small class="text-muted">Upload new QR code to replace existing</small>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="is_active" id="upiActive<?php echo $upi['id']; ?>" <?php echo $upi['is_active'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="upiActive<?php echo $upi['id']; ?>">Active</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete UPI Modal -->
                            <div class="modal fade" id="deleteUpiModal<?php echo $upi['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <input type="hidden" name="delete_payment_method" value="1">
                                            <input type="hidden" name="method_id" value="<?php echo $upi['id']; ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete UPI Payment</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete <strong><?php echo htmlspecialchars($upi['name']); ?></strong>?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
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

            <!-- Quick Stats -->
            <div class="content-card mt-4">
                <h5 class="mb-3"><i class="fas fa-chart-bar me-2 text-primary"></i>Payment Methods</h5>
                <div class="d-flex justify-content-around text-center">
                    <div>
                        <div class="h3 mb-0 text-primary"><?php echo count($bankAccounts); ?></div>
                        <small class="text-muted">Bank Accounts</small>
                    </div>
                    <div>
                        <div class="h3 mb-0 text-purple" style="color: #6f42c1;"><?php echo count($upiMethods); ?></div>
                        <small class="text-muted">UPI Methods</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Bank Account Modal -->
<div class="modal fade" id="addBankModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="add_bank" value="1">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-university me-2"></i>Add Bank Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Display Name *</label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g., HDFC Savings">
                        <small class="text-muted">A friendly name to identify this account</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bank Name *</label>
                        <input type="text" class="form-control" name="bank_name" required placeholder="e.g., HDFC Bank">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Holder Name *</label>
                        <input type="text" class="form-control" name="account_holder" required placeholder="Name as per bank records">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Number *</label>
                        <input type="text" class="form-control" name="account_number" required placeholder="Your bank account number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">IFSC Code *</label>
                        <input type="text" class="form-control" name="ifsc_code" required placeholder="e.g., HDFC0001234">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Branch Name</label>
                        <input type="text" class="form-control" name="branch_name" placeholder="e.g., Kolkata Main Branch">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_default" id="newBankDefault">
                        <label class="form-check-label" for="newBankDefault">Set as default bank account</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Bank Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add UPI Modal -->
<div class="modal fade" id="addUpiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_upi" value="1">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-qrcode me-2"></i>Add UPI Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Display Name *</label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g., GooglePay Business">
                        <small class="text-muted">A friendly name to identify this UPI</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">UPI ID *</label>
                        <input type="text" class="form-control" name="upi_id" required placeholder="yourname@upi">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">QR Code Image</label>
                        <input type="file" class="form-control" name="qr_code" accept="image/*">
                        <small class="text-muted">Upload your UPI QR code image (PNG, JPG)</small>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_default" id="newUpiDefault">
                        <label class="form-check-label" for="newUpiDefault">Set as default UPI</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add UPI Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
