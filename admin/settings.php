<?php
require_once 'includes/auth.php';
$auth->requireLogin();
require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    try {
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $db->prepare("UPDATE settings SET setting_value = :value, updated_by = :user WHERE setting_key = :key");
            $stmt->execute([':value' => $value, ':user' => $auth->getUserId(), ':key' => $key]);
        }
        $auth->logActivity($auth->getUserId(), 'update', 'settings', null, 'Updated site settings');
        $successMessage = "Settings updated successfully!";
    } catch(PDOException $e) { $errorMessage = "Error: " . $e->getMessage(); }
}

$stmt = $db->query("SELECT * FROM settings ORDER BY id ASC");
$settings = [];
while ($row = $stmt->fetch()) { $settings[$row['setting_key']] = $row['setting_value']; }

$pageTitle = 'Site Settings';
include 'includes/header.php';
?>

<div class="admin-content">
    <div class="page-header">
        <h1 class="page-title">Site Settings</h1>
        <p class="page-subtitle">Configure your website settings</p>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo $successMessage; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <form method="POST">
        <div class="row">
            <!-- General Settings -->
            <div class="col-lg-6 mb-4">
                <div class="content-card">
                    <h3 class="h5 mb-4"><i class="fas fa-cog me-2 text-primary"></i>General Settings</h3>

                    <div class="mb-3">
                        <label class="form-label">Site Name</label>
                        <input type="text" name="settings[site_name]" class="form-control" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'BizNexa'); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contact Email</label>
                        <input type="email" name="settings[site_email]" class="form-control" value="<?php echo htmlspecialchars($settings['site_email'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" name="settings[site_phone]" class="form-control" value="<?php echo htmlspecialchars($settings['site_phone'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Business Address</label>
                        <textarea name="settings[site_address]" class="form-control" rows="3"><?php echo htmlspecialchars($settings['site_address'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Social Media Settings -->
            <div class="col-lg-6 mb-4">
                <div class="content-card">
                    <h3 class="h5 mb-4"><i class="fas fa-share-alt me-2 text-primary"></i>Social Media</h3>

                    <div class="mb-3">
                        <label class="form-label"><i class="fab fa-facebook text-primary me-2"></i>Facebook URL</label>
                        <input type="url" name="settings[facebook_url]" class="form-control" value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="fab fa-twitter text-info me-2"></i>Twitter URL</label>
                        <input type="url" name="settings[twitter_url]" class="form-control" value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="fab fa-linkedin text-primary me-2"></i>LinkedIn URL</label>
                        <input type="url" name="settings[linkedin_url]" class="form-control" value="<?php echo htmlspecialchars($settings['linkedin_url'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="fab fa-instagram text-danger me-2"></i>Instagram URL</label>
                        <input type="url" name="settings[instagram_url]" class="form-control" value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- Homepage Stats -->
            <div class="col-lg-6 mb-4">
                <div class="content-card">
                    <h3 class="h5 mb-4"><i class="fas fa-chart-bar me-2 text-primary"></i>Homepage Statistics</h3>

                    <div class="mb-3">
                        <label class="form-label">Years of Experience</label>
                        <input type="number" name="settings[stat_years]" class="form-control" value="<?php echo htmlspecialchars($settings['stat_years'] ?? '5'); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Projects Completed</label>
                        <input type="number" name="settings[stat_projects]" class="form-control" value="<?php echo htmlspecialchars($settings['stat_projects'] ?? '150'); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Happy Clients</label>
                        <input type="number" name="settings[stat_clients]" class="form-control" value="<?php echo htmlspecialchars($settings['stat_clients'] ?? '120'); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Countries Served</label>
                        <input type="number" name="settings[stat_countries]" class="form-control" value="<?php echo htmlspecialchars($settings['stat_countries'] ?? '15'); ?>">
                    </div>
                </div>
            </div>

            <!-- Hero Section -->
            <div class="col-lg-6 mb-4">
                <div class="content-card">
                    <h3 class="h5 mb-4"><i class="fas fa-home me-2 text-primary"></i>Hero Section</h3>

                    <div class="mb-3">
                        <label class="form-label">Hero Title</label>
                        <input type="text" name="settings[hero_title]" class="form-control" value="<?php echo htmlspecialchars($settings['hero_title'] ?? 'Website Design and Development Company'); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hero Subtitle</label>
                        <input type="text" name="settings[hero_subtitle]" class="form-control" value="<?php echo htmlspecialchars($settings['hero_subtitle'] ?? 'Custom Web Design Services at Affordable Pricing'); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hero Image URL</label>
                        <input type="url" name="settings[hero_image]" class="form-control" value="<?php echo htmlspecialchars($settings['hero_image'] ?? ''); ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" name="update_settings" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-2"></i>Save Settings
            </button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
