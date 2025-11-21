<?php
require_once 'includes/auth.php';
$auth->requireLogin();
require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_service'])) {
            $stmt = $db->prepare("INSERT INTO services (title, short_description, full_description, icon, features, pricing_info, display_order, status, created_by)
                                  VALUES (:title, :short_description, :full_description, :icon, :features, :pricing_info, :display_order, :status, :created_by)");
            $stmt->execute([':title' => $_POST['title'], ':short_description' => $_POST['short_description'], ':full_description' => $_POST['full_description'] ?? null,
                ':icon' => $_POST['icon'], ':features' => $_POST['features'] ?? null, ':pricing_info' => $_POST['pricing_info'] ?? null,
                ':display_order' => $_POST['display_order'] ?? 0, ':status' => $_POST['status'], ':created_by' => $auth->getUserId()]);
            $successMessage = "Service added successfully!";
        }
        if (isset($_POST['update_service'])) {
            $stmt = $db->prepare("UPDATE services SET title = :title, short_description = :short_description, full_description = :full_description,
                                  icon = :icon, features = :features, pricing_info = :pricing_info, display_order = :display_order, status = :status WHERE id = :id");
            $stmt->execute([':title' => $_POST['title'], ':short_description' => $_POST['short_description'], ':full_description' => $_POST['full_description'] ?? null,
                ':icon' => $_POST['icon'], ':features' => $_POST['features'] ?? null, ':pricing_info' => $_POST['pricing_info'] ?? null,
                ':display_order' => $_POST['display_order'] ?? 0, ':status' => $_POST['status'], ':id' => $_POST['service_id']]);
            $successMessage = "Service updated successfully!";
        }
        if (isset($_POST['delete_service'])) {
            $stmt = $db->prepare("DELETE FROM services WHERE id = :id");
            $stmt->execute([':id' => $_POST['service_id']]);
            $successMessage = "Service deleted successfully!";
        }

        // Technology/Tool operations
        if (isset($_POST['add_technology'])) {
            $stmt = $db->prepare("INSERT INTO technologies (name, icon, color, display_order, status) VALUES (:name, :icon, :color, :display_order, :status)");
            $stmt->execute([':name' => $_POST['tech_name'], ':icon' => $_POST['tech_icon'], ':color' => $_POST['tech_color'] ?? '#333333',
                ':display_order' => $_POST['tech_display_order'] ?? 0, ':status' => $_POST['tech_status']]);
            $successMessage = "Technology added successfully!";
        }
        if (isset($_POST['update_technology'])) {
            $stmt = $db->prepare("UPDATE technologies SET name = :name, icon = :icon, color = :color, display_order = :display_order, status = :status WHERE id = :id");
            $stmt->execute([':name' => $_POST['tech_name'], ':icon' => $_POST['tech_icon'], ':color' => $_POST['tech_color'] ?? '#333333',
                ':display_order' => $_POST['tech_display_order'] ?? 0, ':status' => $_POST['tech_status'], ':id' => $_POST['tech_id']]);
            $successMessage = "Technology updated successfully!";
        }
        if (isset($_POST['delete_technology'])) {
            $stmt = $db->prepare("DELETE FROM technologies WHERE id = :id");
            $stmt->execute([':id' => $_POST['tech_id']]);
            $successMessage = "Technology deleted successfully!";
        }
    } catch(PDOException $e) { $errorMessage = "Error: " . $e->getMessage(); }
}

$stmt = $db->query("SELECT * FROM services ORDER BY display_order ASC, id ASC");
$services = $stmt->fetchAll();

// Get technologies
$technologies = [];
try {
    $stmt = $db->query("SELECT * FROM technologies ORDER BY display_order ASC, id ASC");
    $technologies = $stmt->fetchAll();
} catch(PDOException $e) {
    // Table might not exist yet
}

$pageTitle = 'Services & Tools';
include 'includes/header.php';
?>

<div class="admin-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div><h1 class="page-title">Services Management</h1><p class="page-subtitle">Manage your service offerings</p></div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal"><i class="fas fa-plus me-2"></i>Add New Service</button>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo $successMessage; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="content-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Order</th><th>Icon</th><th>Title</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?php echo $service['display_order']; ?></td>
                            <td><i class="<?php echo htmlspecialchars($service['icon']); ?> fa-2x text-primary"></i></td>
                            <td><strong><?php echo htmlspecialchars($service['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars(substr($service['short_description'], 0, 60)); ?>...</td>
                            <td><span class="badge bg-<?php echo $service['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($service['status']); ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#editServiceModal<?php echo $service['id']; ?>"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-icon text-danger" data-bs-toggle="modal" data-bs-target="#deleteServiceModal<?php echo $service['id']; ?>"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editServiceModal<?php echo $service['id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg"><div class="modal-content"><form method="POST">
                                <div class="modal-header"><h5 class="modal-title">Edit Service</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                    <div class="row">
                                        <div class="col-md-8 mb-3"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($service['title']); ?>" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Display Order</label><input type="number" name="display_order" class="form-control" value="<?php echo $service['display_order']; ?>"></div>
                                        <div class="col-md-12 mb-3"><label class="form-label">Short Description *</label><textarea name="short_description" class="form-control" rows="2" required><?php echo htmlspecialchars($service['short_description']); ?></textarea></div>
                                        <div class="col-md-12 mb-3"><label class="form-label">Full Description</label><textarea name="full_description" class="form-control" rows="4"><?php echo htmlspecialchars($service['full_description'] ?? ''); ?></textarea></div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Icon Class *</label><input type="text" name="icon" class="form-control" value="<?php echo htmlspecialchars($service['icon']); ?>" required></div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Status *</label><select name="status" class="form-select" required><option value="active" <?php echo $service['status'] === 'active' ? 'selected' : ''; ?>>Active</option><option value="inactive" <?php echo $service['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option></select></div>
                                        <div class="col-md-12 mb-3"><label class="form-label">Features (one per line)</label><textarea name="features" class="form-control" rows="3"><?php echo htmlspecialchars($service['features'] ?? ''); ?></textarea></div>
                                        <div class="col-md-12 mb-3"><label class="form-label">Pricing Info</label><textarea name="pricing_info" class="form-control" rows="2"><?php echo htmlspecialchars($service['pricing_info'] ?? ''); ?></textarea></div>
                                    </div>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="update_service" class="btn btn-primary">Update Service</button></div>
                            </form></div></div>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteServiceModal<?php echo $service['id']; ?>" tabindex="-1">
                            <div class="modal-dialog"><div class="modal-content"><form method="POST">
                                <div class="modal-header"><h5 class="modal-title">Delete Service</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                    <p>Delete <strong><?php echo htmlspecialchars($service['title']); ?></strong>?</p>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="delete_service" class="btn btn-danger">Delete</button></div>
                            </form></div></div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="POST">
    <div class="modal-header"><h5 class="modal-title">Add New Service</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row">
        <div class="col-md-8 mb-3"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required></div>
        <div class="col-md-4 mb-3"><label class="form-label">Display Order</label><input type="number" name="display_order" class="form-control" value="0"></div>
        <div class="col-md-12 mb-3"><label class="form-label">Short Description *</label><textarea name="short_description" class="form-control" rows="2" required></textarea></div>
        <div class="col-md-12 mb-3"><label class="form-label">Full Description</label><textarea name="full_description" class="form-control" rows="4"></textarea></div>
        <div class="col-md-6 mb-3"><label class="form-label">Icon Class *</label><input type="text" name="icon" class="form-control" value="fas fa-cog" required></div>
        <div class="col-md-6 mb-3"><label class="form-label">Status *</label><select name="status" class="form-select" required><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        <div class="col-md-12 mb-3"><label class="form-label">Features (one per line)</label><textarea name="features" class="form-control" rows="3"></textarea></div>
        <div class="col-md-12 mb-3"><label class="form-label">Pricing Info</label><textarea name="pricing_info" class="form-control" rows="2"></textarea></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add_service" class="btn btn-primary">Add Service</button></div>
</form></div></div></div>

<!-- Technologies/Tools Section -->
<div class="admin-content mt-4">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div><h1 class="page-title">Technologies & Tools</h1><p class="page-subtitle">Manage technologies displayed on homepage</p></div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTechModal"><i class="fas fa-plus me-2"></i>Add Technology</button>
    </div>

    <div class="content-card">
        <?php if (empty($technologies)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No technologies found. Please run the SQL file located at <code>admin/sql/technologies_table.sql</code> in phpMyAdmin to create the table.
            </div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($technologies as $tech): ?>
            <div class="col-lg-3 col-md-4 col-6 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="<?php echo htmlspecialchars($tech['icon']); ?> fa-3x mb-3" style="color: <?php echo htmlspecialchars($tech['color']); ?>;"></i>
                        <h5 class="card-title"><?php echo htmlspecialchars($tech['name']); ?></h5>
                        <p class="mb-1"><small class="text-muted">Order: <?php echo $tech['display_order']; ?></small></p>
                        <span class="badge bg-<?php echo $tech['status'] === 'active' ? 'success' : 'secondary'; ?> mb-2"><?php echo ucfirst($tech['status']); ?></span>
                        <div class="mt-2">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTechModal<?php echo $tech['id']; ?>"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteTechModal<?php echo $tech['id']; ?>"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Tech Modal -->
            <div class="modal fade" id="editTechModal<?php echo $tech['id']; ?>" tabindex="-1">
                <div class="modal-dialog"><div class="modal-content"><form method="POST">
                    <div class="modal-header"><h5 class="modal-title">Edit Technology</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="tech_id" value="<?php echo $tech['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Name *</label>
                            <input type="text" name="tech_name" class="form-control" value="<?php echo htmlspecialchars($tech['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon Class * <small class="text-muted">(FontAwesome)</small></label>
                            <input type="text" name="tech_icon" class="form-control" value="<?php echo htmlspecialchars($tech['icon']); ?>" required>
                            <small class="text-muted">e.g., fab fa-html5, fab fa-react, fas fa-database</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Icon Color</label>
                                <input type="color" name="tech_color" class="form-control form-control-color w-100" value="<?php echo htmlspecialchars($tech['color']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" name="tech_display_order" class="form-control" value="<?php echo $tech['display_order']; ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status *</label>
                            <select name="tech_status" class="form-select" required>
                                <option value="active" <?php echo $tech['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $tech['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Preview</label>
                            <div class="text-center p-3 bg-light rounded">
                                <i class="<?php echo htmlspecialchars($tech['icon']); ?> fa-3x" style="color: <?php echo htmlspecialchars($tech['color']); ?>;"></i>
                                <p class="mt-2 mb-0"><?php echo htmlspecialchars($tech['name']); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_technology" class="btn btn-primary">Update</button>
                    </div>
                </form></div></div>
            </div>

            <!-- Delete Tech Modal -->
            <div class="modal fade" id="deleteTechModal<?php echo $tech['id']; ?>" tabindex="-1">
                <div class="modal-dialog"><div class="modal-content"><form method="POST">
                    <div class="modal-header"><h5 class="modal-title">Delete Technology</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="tech_id" value="<?php echo $tech['id']; ?>">
                        <p>Delete <strong><?php echo htmlspecialchars($tech['name']); ?></strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_technology" class="btn btn-danger">Delete</button>
                    </div>
                </form></div></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Technology Modal -->
<div class="modal fade" id="addTechModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content"><form method="POST">
        <div class="modal-header"><h5 class="modal-title">Add New Technology</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Name *</label>
                <input type="text" name="tech_name" class="form-control" placeholder="e.g., React, Node.js, MySQL" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Icon Class * <small class="text-muted">(FontAwesome)</small></label>
                <input type="text" name="tech_icon" class="form-control" value="fab fa-" required>
                <small class="text-muted">Find icons at <a href="https://fontawesome.com/icons" target="_blank">fontawesome.com</a></small>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Icon Color</label>
                    <input type="color" name="tech_color" class="form-control form-control-color w-100" value="#333333">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="tech_display_order" class="form-control" value="0">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Status *</label>
                <select name="tech_status" class="form-select" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="add_technology" class="btn btn-primary">Add Technology</button>
        </div>
    </form></div></div>
</div>

<?php include 'includes/footer.php'; ?>
