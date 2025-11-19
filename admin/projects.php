<?php
require_once 'includes/auth.php';
$auth->requireLogin();

require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_project'])) {
            $stmt = $db->prepare("INSERT INTO projects (title, description, category, client_name, project_url, image_url, technologies, completion_date, featured, status, created_by)
                                  VALUES (:title, :description, :category, :client_name, :project_url, :image_url, :technologies, :completion_date, :featured, :status, :created_by)");

            $stmt->execute([
                ':title' => $_POST['title'],
                ':description' => $_POST['description'],
                ':category' => $_POST['category'],
                ':client_name' => $_POST['client_name'] ?? null,
                ':project_url' => $_POST['project_url'] ?? null,
                ':image_url' => $_POST['image_url'] ?? null,
                ':technologies' => $_POST['technologies'] ?? null,
                ':completion_date' => $_POST['completion_date'] ?? null,
                ':featured' => isset($_POST['featured']) ? 1 : 0,
                ':status' => $_POST['status'],
                ':created_by' => $auth->getUserId()
            ]);

            $auth->logActivity($auth->getUserId(), 'create', 'projects', $db->lastInsertId(), 'Created project: ' . $_POST['title']);
            $successMessage = "Project added successfully!";
        }

        if (isset($_POST['update_project'])) {
            $stmt = $db->prepare("UPDATE projects SET title = :title, description = :description, category = :category,
                                  client_name = :client_name, project_url = :project_url, image_url = :image_url,
                                  technologies = :technologies, completion_date = :completion_date, featured = :featured, status = :status
                                  WHERE id = :id");

            $stmt->execute([
                ':title' => $_POST['title'],
                ':description' => $_POST['description'],
                ':category' => $_POST['category'],
                ':client_name' => $_POST['client_name'] ?? null,
                ':project_url' => $_POST['project_url'] ?? null,
                ':image_url' => $_POST['image_url'] ?? null,
                ':technologies' => $_POST['technologies'] ?? null,
                ':completion_date' => $_POST['completion_date'] ?? null,
                ':featured' => isset($_POST['featured']) ? 1 : 0,
                ':status' => $_POST['status'],
                ':id' => $_POST['project_id']
            ]);

            $auth->logActivity($auth->getUserId(), 'update', 'projects', $_POST['project_id'], 'Updated project: ' . $_POST['title']);
            $successMessage = "Project updated successfully!";
        }

        if (isset($_POST['delete_project'])) {
            $stmt = $db->prepare("DELETE FROM projects WHERE id = :id");
            $stmt->execute([':id' => $_POST['project_id']]);

            $auth->logActivity($auth->getUserId(), 'delete', 'projects', $_POST['project_id'], 'Deleted project');
            $successMessage = "Project deleted successfully!";
        }
    } catch(PDOException $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

// Get all projects
try {
    $stmt = $db->query("SELECT * FROM projects ORDER BY featured DESC, created_at DESC");
    $projects = $stmt->fetchAll();
} catch(PDOException $e) {
    $projects = [];
}

$pageTitle = 'Projects Management';
include 'includes/header.php';
?>

<div class="admin-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Projects Management</h1>
            <p class="page-subtitle">Manage your portfolio projects</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">
            <i class="fas fa-plus me-2"></i>Add New Project
        </button>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="content-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Client</th>
                        <th>Featured</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($projects)): ?>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td>
                                    <?php if ($project['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($project['image_url']); ?>"
                                             alt="Project" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 60px; background: #e9ecef; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($project['title']); ?></strong>
                                    <?php if ($project['project_url']): ?>
                                        <br><a href="<?php echo htmlspecialchars($project['project_url']); ?>" target="_blank" class="small">
                                            <i class="fas fa-external-link-alt"></i> View
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-info"><?php echo htmlspecialchars($project['category']); ?></span></td>
                                <td><?php echo htmlspecialchars($project['client_name'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($project['featured']): ?>
                                        <span class="badge bg-warning"><i class="fas fa-star"></i> Featured</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Regular</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $project['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($project['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $project['completion_date'] ? date('M Y', strtotime($project['completion_date'])) : '-'; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-icon" data-bs-toggle="modal"
                                            data-bs-target="#editProjectModal<?php echo $project['id']; ?>" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon text-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteProjectModal<?php echo $project['id']; ?>" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editProjectModal<?php echo $project['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Project</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">

                                                <div class="row">
                                                    <div class="col-md-8 mb-3">
                                                        <label class="form-label">Project Title *</label>
                                                        <input type="text" name="title" class="form-control"
                                                               value="<?php echo htmlspecialchars($project['title']); ?>" required>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Category *</label>
                                                        <select name="category" class="form-select" required>
                                                            <option value="web-design" <?php echo $project['category'] === 'web-design' ? 'selected' : ''; ?>>Web Design</option>
                                                            <option value="e-commerce" <?php echo $project['category'] === 'e-commerce' ? 'selected' : ''; ?>>E-Commerce</option>
                                                            <option value="web-development" <?php echo $project['category'] === 'web-development' ? 'selected' : ''; ?>>Web Development</option>
                                                            <option value="ai-solutions" <?php echo $project['category'] === 'ai-solutions' ? 'selected' : ''; ?>>AI Solutions</option>
                                                            <option value="mobile-app" <?php echo $project['category'] === 'mobile-app' ? 'selected' : ''; ?>>Mobile App</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-12 mb-3">
                                                        <label class="form-label">Description *</label>
                                                        <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($project['description']); ?></textarea>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Client Name</label>
                                                        <input type="text" name="client_name" class="form-control"
                                                               value="<?php echo htmlspecialchars($project['client_name'] ?? ''); ?>">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Project URL</label>
                                                        <input type="url" name="project_url" class="form-control"
                                                               value="<?php echo htmlspecialchars($project['project_url'] ?? ''); ?>">
                                                    </div>
                                                    <div class="col-md-12 mb-3">
                                                        <label class="form-label">Image URL</label>
                                                        <input type="url" name="image_url" class="form-control"
                                                               value="<?php echo htmlspecialchars($project['image_url'] ?? ''); ?>">
                                                        <small class="text-muted">Enter image URL or upload to your server</small>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Technologies</label>
                                                        <input type="text" name="technologies" class="form-control"
                                                               value="<?php echo htmlspecialchars($project['technologies'] ?? ''); ?>"
                                                               placeholder="e.g., PHP, MySQL, Bootstrap">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Completion Date</label>
                                                        <input type="date" name="completion_date" class="form-control"
                                                               value="<?php echo $project['completion_date']; ?>">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Status *</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="active" <?php echo $project['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                            <option value="inactive" <?php echo $project['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-check mt-4">
                                                            <input type="checkbox" name="featured" class="form-check-input" id="featured<?php echo $project['id']; ?>"
                                                                   <?php echo $project['featured'] ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="featured<?php echo $project['id']; ?>">
                                                                <i class="fas fa-star text-warning"></i> Featured Project
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="update_project" class="btn btn-primary">Update Project</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteProjectModal<?php echo $project['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Project</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                                <p>Are you sure you want to delete this project?</p>
                                                <p class="text-danger"><strong><?php echo htmlspecialchars($project['title']); ?></strong></p>
                                                <p class="text-muted">This action cannot be undone.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="delete_project" class="btn btn-danger">Delete Project</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-briefcase fa-3x mb-3 d-block"></i>
                                No projects yet. Add your first project!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Project Modal -->
<div class="modal fade" id="addProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Project Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Category *</label>
                            <select name="category" class="form-select" required>
                                <option value="web-design">Web Design</option>
                                <option value="e-commerce">E-Commerce</option>
                                <option value="web-development">Web Development</option>
                                <option value="ai-solutions">AI Solutions</option>
                                <option value="mobile-app">Mobile App</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description *</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client Name</label>
                            <input type="text" name="client_name" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Project URL</label>
                            <input type="url" name="project_url" class="form-control" placeholder="https://example.com">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" name="image_url" class="form-control" placeholder="https://example.com/image.jpg">
                            <small class="text-muted">Enter image URL or upload to your server</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Technologies</label>
                            <input type="text" name="technologies" class="form-control" placeholder="e.g., PHP, MySQL, Bootstrap">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Completion Date</label>
                            <input type="date" name="completion_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="featured" class="form-check-input" id="featuredNew">
                                <label class="form-check-label" for="featuredNew">
                                    <i class="fas fa-star text-warning"></i> Featured Project
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_project" class="btn btn-primary">Add Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
