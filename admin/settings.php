<?php
require_once 'includes/auth.php';
$auth->requireLogin();
require_once __DIR__ . '/../includes/csrf.php';
require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid form submission. Please refresh and try again.';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    if (isset($_POST['update_settings'])) {
        try {
            $settingMeta = [
                'site_name' => ['type' => 'text', 'description' => 'Website name'],
                'site_email' => ['type' => 'email', 'description' => 'Primary contact email'],
                'site_phone' => ['type' => 'text', 'description' => 'Primary contact phone'],
                'site_address' => ['type' => 'text', 'description' => 'Business address'],
                'facebook_url' => ['type' => 'url', 'description' => 'Facebook page URL'],
                'twitter_url' => ['type' => 'url', 'description' => 'Twitter profile URL'],
                'linkedin_url' => ['type' => 'url', 'description' => 'LinkedIn profile URL'],
                'instagram_url' => ['type' => 'url', 'description' => 'Instagram profile URL'],
                'stat_years' => ['type' => 'number', 'description' => 'Years of experience shown on homepage'],
                'stat_projects' => ['type' => 'number', 'description' => 'Projects completed shown on homepage'],
                'stat_clients' => ['type' => 'number', 'description' => 'Happy clients shown on homepage'],
                'stat_countries' => ['type' => 'number', 'description' => 'Countries served shown on homepage'],
                'hero_title' => ['type' => 'text', 'description' => 'Homepage hero title'],
                'hero_subtitle' => ['type' => 'text', 'description' => 'Homepage hero subtitle'],
                'hero_image' => ['type' => 'url', 'description' => 'Homepage hero image URL']
            ];

            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value, setting_type, description, updated_by)
                                  VALUES (:key, :value, :type, :description, :user)
                                  ON DUPLICATE KEY UPDATE
                                    setting_value = VALUES(setting_value),
                                    setting_type = VALUES(setting_type),
                                    description = VALUES(description),
                                    updated_by = VALUES(updated_by)");

            foreach ($_POST['settings'] as $key => $value) {
                $meta = $settingMeta[$key] ?? ['type' => 'text', 'description' => 'Site setting'];
                $stmt->execute([
                    ':key' => $key,
                    ':value' => $value,
                    ':type' => $meta['type'],
                    ':description' => $meta['description'],
                    ':user' => $auth->getUserId()
                ]);
            }

            // Save additional phone numbers as JSON
            $phones = [];
            if (!empty($_POST['phone_number'])) {
                foreach ($_POST['phone_number'] as $i => $number) {
                    $number = trim($number);
                    if ($number !== '') {
                        $phones[] = [
                            'number' => $number,
                            'label' => trim($_POST['phone_label'][$i] ?? 'General'),
                        ];
                    }
                }
            }
            $stmt->execute([
                ':key' => 'contact_phones',
                ':value' => json_encode($phones),
                ':type' => 'json',
                ':description' => 'Additional contact phone numbers',
                ':user' => $auth->getUserId()
            ]);

            // Save additional emails as JSON
            $emails = [];
            if (!empty($_POST['email_address'])) {
                foreach ($_POST['email_address'] as $i => $email) {
                    $email = trim($email);
                    if ($email !== '') {
                        $emails[] = [
                            'email' => $email,
                            'department' => trim($_POST['email_department'][$i] ?? 'General'),
                        ];
                    }
                }
            }
            $stmt->execute([
                ':key' => 'contact_emails',
                ':value' => json_encode($emails),
                ':type' => 'json',
                ':description' => 'Department-wise contact emails',
                ':user' => $auth->getUserId()
            ]);

            $auth->logActivity($auth->getUserId(), 'update', 'settings', null, 'Updated site settings');
            $successMessage = "Settings updated successfully!";
        } catch(PDOException $e) { $errorMessage = "Error: " . $e->getMessage(); }
    }
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

    <form method="POST"><?php echo csrfField(); ?>
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
                        <label class="form-label">Primary Email</label>
                        <input type="email" name="settings[site_email]" class="form-control" value="<?php echo htmlspecialchars($settings['site_email'] ?? ''); ?>">
                        <small class="text-muted">Shown in header bar and used as default contact</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Primary Phone</label>
                        <input type="text" name="settings[site_phone]" class="form-control" value="<?php echo htmlspecialchars($settings['site_phone'] ?? ''); ?>">
                        <small class="text-muted">Shown in header bar, WhatsApp, and side contact</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Business Address</label>
                        <textarea name="settings[site_address]" class="form-control" rows="3"><?php echo htmlspecialchars($settings['site_address'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Additional Phone Numbers -->
            <div class="col-lg-6 mb-4">
                <div class="content-card">
                    <h3 class="h5 mb-4"><i class="fas fa-phone-alt me-2 text-primary"></i>Phone Numbers</h3>
                    <p class="text-muted small mb-3">Add multiple contact numbers for different purposes (Sales, Support, WhatsApp, etc.)</p>

                    <?php
                    $contactPhones = json_decode($settings['contact_phones'] ?? '[]', true) ?: [];
                    if (empty($contactPhones)) {
                        $contactPhones = [['number' => '', 'label' => '']];
                    }
                    ?>

                    <div id="phoneList">
                        <?php foreach ($contactPhones as $i => $phone): ?>
                        <div class="phone-row d-flex gap-2 mb-2 align-items-center">
                            <select name="phone_label[]" class="form-select" style="width: 140px;">
                                <option value="Sales" <?php echo ($phone['label'] ?? '') === 'Sales' ? 'selected' : ''; ?>>Sales</option>
                                <option value="Support" <?php echo ($phone['label'] ?? '') === 'Support' ? 'selected' : ''; ?>>Support</option>
                                <option value="WhatsApp" <?php echo ($phone['label'] ?? '') === 'WhatsApp' ? 'selected' : ''; ?>>WhatsApp</option>
                                <option value="Office" <?php echo ($phone['label'] ?? '') === 'Office' ? 'selected' : ''; ?>>Office</option>
                                <option value="HR" <?php echo ($phone['label'] ?? '') === 'HR' ? 'selected' : ''; ?>>HR</option>
                                <option value="General" <?php echo ($phone['label'] ?? '') === 'General' ? 'selected' : ''; ?>>General</option>
                            </select>
                            <input type="text" name="phone_number[]" class="form-control" placeholder="+91 XXXXX XXXXX" value="<?php echo htmlspecialchars($phone['number'] ?? ''); ?>">
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.phone-row').remove()"><i class="fas fa-times"></i></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addPhone()">
                        <i class="fas fa-plus me-1"></i>Add Phone
                    </button>
                </div>
            </div>

            <!-- Department Emails -->
            <div class="col-lg-6 mb-4">
                <div class="content-card">
                    <h3 class="h5 mb-4"><i class="fas fa-envelope me-2 text-primary"></i>Department Emails</h3>
                    <p class="text-muted small mb-3">Add emails for specific departments shown on the contact page.</p>

                    <?php
                    $contactEmails = json_decode($settings['contact_emails'] ?? '[]', true) ?: [];
                    if (empty($contactEmails)) {
                        $contactEmails = [['email' => '', 'department' => '']];
                    }
                    ?>

                    <div id="emailList">
                        <?php foreach ($contactEmails as $i => $em): ?>
                        <div class="email-row d-flex gap-2 mb-2 align-items-center">
                            <select name="email_department[]" class="form-select" style="width: 140px;">
                                <option value="Sales" <?php echo ($em['department'] ?? '') === 'Sales' ? 'selected' : ''; ?>>Sales</option>
                                <option value="Support" <?php echo ($em['department'] ?? '') === 'Support' ? 'selected' : ''; ?>>Support</option>
                                <option value="Billing" <?php echo ($em['department'] ?? '') === 'Billing' ? 'selected' : ''; ?>>Billing</option>
                                <option value="HR" <?php echo ($em['department'] ?? '') === 'HR' ? 'selected' : ''; ?>>HR</option>
                                <option value="Careers" <?php echo ($em['department'] ?? '') === 'Careers' ? 'selected' : ''; ?>>Careers</option>
                                <option value="General" <?php echo ($em['department'] ?? '') === 'General' ? 'selected' : ''; ?>>General</option>
                            </select>
                            <input type="email" name="email_address[]" class="form-control" placeholder="dept@biznexa.tech" value="<?php echo htmlspecialchars($em['email'] ?? ''); ?>">
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.email-row').remove()"><i class="fas fa-times"></i></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addEmail()">
                        <i class="fas fa-plus me-1"></i>Add Email
                    </button>
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

<script>
function addPhone() {
    const html = `<div class="phone-row d-flex gap-2 mb-2 align-items-center">
        <select name="phone_label[]" class="form-select" style="width: 140px;">
            <option value="Sales">Sales</option>
            <option value="Support">Support</option>
            <option value="WhatsApp">WhatsApp</option>
            <option value="Office">Office</option>
            <option value="HR">HR</option>
            <option value="General">General</option>
        </select>
        <input type="text" name="phone_number[]" class="form-control" placeholder="+91 XXXXX XXXXX">
        <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.phone-row').remove()"><i class="fas fa-times"></i></button>
    </div>`;
    document.getElementById('phoneList').insertAdjacentHTML('beforeend', html);
}

function addEmail() {
    const html = `<div class="email-row d-flex gap-2 mb-2 align-items-center">
        <select name="email_department[]" class="form-select" style="width: 140px;">
            <option value="Sales">Sales</option>
            <option value="Support">Support</option>
            <option value="Billing">Billing</option>
            <option value="HR">HR</option>
            <option value="Careers">Careers</option>
            <option value="General">General</option>
        </select>
        <input type="email" name="email_address[]" class="form-control" placeholder="dept@biznexa.tech">
        <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.email-row').remove()"><i class="fas fa-times"></i></button>
    </div>`;
    document.getElementById('emailList').insertAdjacentHTML('beforeend', html);
}
</script>

<?php include 'includes/footer.php'; ?>
