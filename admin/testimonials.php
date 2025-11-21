<?php
require_once 'includes/auth.php';
$auth->requireLogin();
require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

// Check if project_id column exists in testimonials table
$hasProjectColumn = false;
try {
    $checkCol = $db->query("SHOW COLUMNS FROM testimonials LIKE 'project_id'");
    $hasProjectColumn = $checkCol->rowCount() > 0;
} catch(PDOException $e) {
    // Column check failed
}

// Get all projects for linking dropdown
$projects = [];
try {
    $stmt = $db->query("SELECT id, title FROM projects WHERE status = 'active' ORDER BY title ASC");
    $projects = $stmt->fetchAll();
} catch(PDOException $e) {
    // Table might not exist
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_testimonial'])) {
            if ($hasProjectColumn) {
                $stmt = $db->prepare("INSERT INTO testimonials (client_name, client_position, client_company, testimonial, rating, client_photo, project_id, display_order, status)
                                      VALUES (:client_name, :client_position, :client_company, :testimonial, :rating, :client_photo, :project_id, :display_order, :status)");
                $stmt->execute([':client_name' => $_POST['client_name'], ':client_position' => $_POST['client_position'] ?? null, ':client_company' => $_POST['client_company'] ?? null,
                    ':testimonial' => $_POST['testimonial'], ':rating' => $_POST['rating'], ':client_photo' => $_POST['client_photo'] ?? null,
                    ':project_id' => !empty($_POST['project_id']) ? $_POST['project_id'] : null,
                    ':display_order' => $_POST['display_order'] ?? 0, ':status' => $_POST['status']]);
            } else {
                $stmt = $db->prepare("INSERT INTO testimonials (client_name, client_position, client_company, testimonial, rating, client_photo, display_order, status)
                                      VALUES (:client_name, :client_position, :client_company, :testimonial, :rating, :client_photo, :display_order, :status)");
                $stmt->execute([':client_name' => $_POST['client_name'], ':client_position' => $_POST['client_position'] ?? null, ':client_company' => $_POST['client_company'] ?? null,
                    ':testimonial' => $_POST['testimonial'], ':rating' => $_POST['rating'], ':client_photo' => $_POST['client_photo'] ?? null,
                    ':display_order' => $_POST['display_order'] ?? 0, ':status' => $_POST['status']]);
            }
            $successMessage = "Testimonial added successfully!";
        }
        if (isset($_POST['update_testimonial'])) {
            if ($hasProjectColumn) {
                $stmt = $db->prepare("UPDATE testimonials SET client_name = :client_name, client_position = :client_position, client_company = :client_company,
                                      testimonial = :testimonial, rating = :rating, client_photo = :client_photo, project_id = :project_id, display_order = :display_order, status = :status WHERE id = :id");
                $stmt->execute([':client_name' => $_POST['client_name'], ':client_position' => $_POST['client_position'] ?? null, ':client_company' => $_POST['client_company'] ?? null,
                    ':testimonial' => $_POST['testimonial'], ':rating' => $_POST['rating'], ':client_photo' => $_POST['client_photo'] ?? null,
                    ':project_id' => !empty($_POST['project_id']) ? $_POST['project_id'] : null,
                    ':display_order' => $_POST['display_order'] ?? 0, ':status' => $_POST['status'], ':id' => $_POST['testimonial_id']]);
            } else {
                $stmt = $db->prepare("UPDATE testimonials SET client_name = :client_name, client_position = :client_position, client_company = :client_company,
                                      testimonial = :testimonial, rating = :rating, client_photo = :client_photo, display_order = :display_order, status = :status WHERE id = :id");
                $stmt->execute([':client_name' => $_POST['client_name'], ':client_position' => $_POST['client_position'] ?? null, ':client_company' => $_POST['client_company'] ?? null,
                    ':testimonial' => $_POST['testimonial'], ':rating' => $_POST['rating'], ':client_photo' => $_POST['client_photo'] ?? null,
                    ':display_order' => $_POST['display_order'] ?? 0, ':status' => $_POST['status'], ':id' => $_POST['testimonial_id']]);
            }
            $successMessage = "Testimonial updated successfully!";
        }
        if (isset($_POST['delete_testimonial'])) {
            $stmt = $db->prepare("DELETE FROM testimonials WHERE id = :id");
            $stmt->execute([':id' => $_POST['testimonial_id']]);
            $successMessage = "Testimonial deleted successfully!";
        }
    } catch(PDOException $e) { $errorMessage = "Error: " . $e->getMessage(); }
}

// Fetch testimonials with or without project join based on column existence
if ($hasProjectColumn) {
    $stmt = $db->query("SELECT t.*, p.title as project_title FROM testimonials t LEFT JOIN projects p ON t.project_id = p.id ORDER BY t.display_order ASC, t.id DESC");
} else {
    $stmt = $db->query("SELECT * FROM testimonials ORDER BY display_order ASC, id DESC");
}
$testimonials = $stmt->fetchAll();
$pageTitle = 'Testimonials Management';
include 'includes/header.php';
?>

<div class="admin-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div><h1 class="page-title">Testimonials Management</h1><p class="page-subtitle">Manage client testimonials</p></div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTestimonialModal"><i class="fas fa-plus me-2"></i>Add Testimonial</button>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo $successMessage; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?php echo $errorMessage; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (!$hasProjectColumn): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Project Linking Feature:</strong> To link testimonials to projects, run this SQL in phpMyAdmin:
            <code class="d-block mt-2">ALTER TABLE testimonials ADD COLUMN project_id int(11) DEFAULT NULL AFTER client_photo;</code>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php foreach ($testimonials as $testimonial): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="content-card h-100">
                    <div class="d-flex align-items-center mb-3">
                        <?php if ($testimonial['client_photo']): ?>
                            <img src="<?php echo htmlspecialchars($testimonial['client_photo']); ?>" alt="Client" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 60px; height: 60px; border-radius: 50%; background: #0d6efd; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold;">
                                <?php echo strtoupper(substr($testimonial['client_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <div class="ms-3">
                            <h5 class="mb-0"><?php echo htmlspecialchars($testimonial['client_name']); ?></h5>
                            <small class="text-muted"><?php echo htmlspecialchars($testimonial['client_position'] ?? ''); ?> <?php echo $testimonial['client_company'] ? '@ ' . htmlspecialchars($testimonial['client_company']) : ''; ?></small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                        <?php endfor; ?>
                    </div>

                    <p class="text-muted"><?php echo htmlspecialchars($testimonial['testimonial']); ?></p>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="badge bg-<?php echo $testimonial['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($testimonial['status']); ?></span>
                        <span class="badge bg-info">Order: <?php echo $testimonial['display_order']; ?></span>
                        <?php if (!empty($testimonial['project_title'])): ?>
                        <span class="badge bg-primary"><i class="fas fa-link me-1"></i><?php echo htmlspecialchars($testimonial['project_title']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editTestimonialModal<?php echo $testimonial['id']; ?>">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteTestimonialModal<?php echo $testimonial['id']; ?>">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div class="modal fade" id="editTestimonialModal<?php echo $testimonial['id']; ?>" tabindex="-1">
                    <div class="modal-dialog"><div class="modal-content"><form method="POST">
                        <div class="modal-header"><h5 class="modal-title">Edit Testimonial</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                            <div class="mb-3"><label class="form-label">Client Name *</label><input type="text" name="client_name" class="form-control" value="<?php echo htmlspecialchars($testimonial['client_name']); ?>" required></div>
                            <div class="mb-3"><label class="form-label">Position</label><input type="text" name="client_position" class="form-control" value="<?php echo htmlspecialchars($testimonial['client_position'] ?? ''); ?>"></div>
                            <div class="mb-3"><label class="form-label">Company</label><input type="text" name="client_company" class="form-control" value="<?php echo htmlspecialchars($testimonial['client_company'] ?? ''); ?>"></div>
                            <div class="mb-3"><label class="form-label">Testimonial *</label><textarea name="testimonial" class="form-control" rows="4" required><?php echo htmlspecialchars($testimonial['testimonial']); ?></textarea></div>
                            <div class="mb-3"><label class="form-label">Rating *</label><select name="rating" class="form-select" required><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?php echo $i; ?>" <?php echo $testimonial['rating'] == $i ? 'selected' : ''; ?>><?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?></option><?php endfor; ?></select></div>
                            <div class="mb-3"><label class="form-label">Client Photo URL</label><input type="url" name="client_photo" class="form-control" value="<?php echo htmlspecialchars($testimonial['client_photo'] ?? ''); ?>"></div>
                            <?php if ($hasProjectColumn): ?>
                            <div class="mb-3">
                                <label class="form-label">Link to Project</label>
                                <select name="project_id" class="form-select">
                                    <option value="">-- No Project Link --</option>
                                    <?php foreach ($projects as $proj): ?>
                                    <option value="<?php echo $proj['id']; ?>" <?php echo ($testimonial['project_id'] ?? '') == $proj['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($proj['title']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Link this testimonial to a specific project</small>
                            </div>
                            <?php endif; ?>
                            <div class="mb-3"><label class="form-label">Display Order</label><input type="number" name="display_order" class="form-control" value="<?php echo $testimonial['display_order']; ?>"></div>
                            <div class="mb-3"><label class="form-label">Status *</label><select name="status" class="form-select" required><option value="active" <?php echo $testimonial['status'] === 'active' ? 'selected' : ''; ?>>Active</option><option value="inactive" <?php echo $testimonial['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option></select></div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="update_testimonial" class="btn btn-primary">Update</button></div>
                    </form></div></div>
                </div>

                <!-- Delete Modal -->
                <div class="modal fade" id="deleteTestimonialModal<?php echo $testimonial['id']; ?>" tabindex="-1">
                    <div class="modal-dialog"><div class="modal-content"><form method="POST">
                        <div class="modal-header"><h5 class="modal-title">Delete Testimonial</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body"><input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>"><p>Delete testimonial from <strong><?php echo htmlspecialchars($testimonial['client_name']); ?></strong>?</p></div>
                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="delete_testimonial" class="btn btn-danger">Delete</button></div>
                    </form></div></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Testimonial Modal -->
<div class="modal fade" id="addTestimonialModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST">
    <div class="modal-header"><h5 class="modal-title">Add New Testimonial</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Client Name *</label><input type="text" name="client_name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Position</label><input type="text" name="client_position" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Company</label><input type="text" name="client_company" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Testimonial *</label><textarea name="testimonial" class="form-control" rows="4" required></textarea></div>
        <div class="mb-3"><label class="form-label">Rating *</label><select name="rating" class="form-select" required><option value="5">5 Stars</option><option value="4">4 Stars</option><option value="3">3 Stars</option><option value="2">2 Stars</option><option value="1">1 Star</option></select></div>
        <div class="mb-3"><label class="form-label">Client Photo URL</label><input type="url" name="client_photo" class="form-control"></div>
        <?php if ($hasProjectColumn): ?>
        <div class="mb-3">
            <label class="form-label">Link to Project</label>
            <select name="project_id" class="form-select">
                <option value="">-- No Project Link --</option>
                <?php foreach ($projects as $proj): ?>
                <option value="<?php echo $proj['id']; ?>"><?php echo htmlspecialchars($proj['title']); ?></option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">Link this testimonial to a specific project (will show on project detail page)</small>
        </div>
        <?php endif; ?>
        <div class="mb-3"><label class="form-label">Display Order</label><input type="number" name="display_order" class="form-control" value="0"></div>
        <div class="mb-3"><label class="form-label">Status *</label><select name="status" class="form-select" required><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add_testimonial" class="btn btn-primary">Add Testimonial</button></div>
</form></div></div></div>

<?php include 'includes/footer.php'; ?>
