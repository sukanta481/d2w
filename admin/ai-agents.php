<?php
require_once 'includes/auth.php';
$auth->requireLogin();

require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_agent'])) {
            $stmt = $db->prepare("INSERT INTO ai_agents (name, description, category, features, pricing, icon, demo_url, documentation_url, status, created_by)
                                  VALUES (:name, :description, :category, :features, :pricing, :icon, :demo_url, :documentation_url, :status, :created_by)");

            $stmt->execute([
                ':name' => $_POST['name'],
                ':description' => $_POST['description'],
                ':category' => $_POST['category'],
                ':features' => $_POST['features'] ?? null,
                ':pricing' => $_POST['pricing'] ?? null,
                ':icon' => $_POST['icon'],
                ':demo_url' => $_POST['demo_url'] ?? null,
                ':documentation_url' => $_POST['documentation_url'] ?? null,
                ':status' => $_POST['status'],
                ':created_by' => $auth->getUserId()
            ]);

            $auth->logActivity($auth->getUserId(), 'create', 'ai_agents', $db->lastInsertId(), 'Created AI agent: ' . $_POST['name']);
            $successMessage = "AI Agent added successfully!";
        }

        if (isset($_POST['update_agent'])) {
            $stmt = $db->prepare("UPDATE ai_agents SET name = :name, description = :description, category = :category,
                                  features = :features, pricing = :pricing, icon = :icon, demo_url = :demo_url,
                                  documentation_url = :documentation_url, status = :status WHERE id = :id");

            $stmt->execute([
                ':name' => $_POST['name'],
                ':description' => $_POST['description'],
                ':category' => $_POST['category'],
                ':features' => $_POST['features'] ?? null,
                ':pricing' => $_POST['pricing'] ?? null,
                ':icon' => $_POST['icon'],
                ':demo_url' => $_POST['demo_url'] ?? null,
                ':documentation_url' => $_POST['documentation_url'] ?? null,
                ':status' => $_POST['status'],
                ':id' => $_POST['agent_id']
            ]);

            $auth->logActivity($auth->getUserId(), 'update', 'ai_agents', $_POST['agent_id'], 'Updated AI agent: ' . $_POST['name']);
            $successMessage = "AI Agent updated successfully!";
        }

        if (isset($_POST['delete_agent'])) {
            $stmt = $db->prepare("DELETE FROM ai_agents WHERE id = :id");
            $stmt->execute([':id' => $_POST['agent_id']]);

            $auth->logActivity($auth->getUserId(), 'delete', 'ai_agents', $_POST['agent_id'], 'Deleted AI agent');
            $successMessage = "AI Agent deleted successfully!";
        }
    } catch(PDOException $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

// Get all AI agents
try {
    $stmt = $db->query("SELECT * FROM ai_agents ORDER BY created_at DESC");
    $agents = $stmt->fetchAll();
} catch(PDOException $e) {
    $agents = [];
}

$pageTitle = 'AI Agents Management';
include 'includes/header.php';
?>

<div class="admin-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">AI Agents Management</h1>
            <p class="page-subtitle">Manage your agentic AI solutions</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAgentModal">
            <i class="fas fa-plus me-2"></i>Add New AI Agent
        </button>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php if (!empty($agents)): ?>
            <?php foreach ($agents as $agent): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="content-card h-100">
                        <div class="text-center mb-3">
                            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #0d6efd, #10B981); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                <i class="<?php echo htmlspecialchars($agent['icon']); ?> fa-2x text-white"></i>
                            </div>
                        </div>

                        <h3 class="h5 text-center mb-3"><?php echo htmlspecialchars($agent['name']); ?></h3>

                        <div class="mb-3">
                            <span class="badge bg-info"><?php echo htmlspecialchars($agent['category']); ?></span>
                            <span class="badge bg-<?php echo $agent['status'] === 'active' ? 'success' : ($agent['status'] === 'coming_soon' ? 'warning' : 'secondary'); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $agent['status'])); ?>
                            </span>
                        </div>

                        <p class="text-muted small"><?php echo htmlspecialchars(substr($agent['description'], 0, 100)); ?>...</p>

                        <?php if ($agent['pricing']): ?>
                            <p class="mb-3"><strong>Pricing:</strong> $<?php echo number_format($agent['pricing'], 2); ?></p>
                        <?php endif; ?>

                        <?php if ($agent['features']): ?>
                            <p class="small"><strong>Features:</strong> <?php echo htmlspecialchars(substr($agent['features'], 0, 50)); ?>...</p>
                        <?php endif; ?>

                        <div class="d-flex gap-2 mt-3">
                            <?php if ($agent['demo_url']): ?>
                                <a href="<?php echo htmlspecialchars($agent['demo_url']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-play"></i> Demo
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editAgentModal<?php echo $agent['id']; ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAgentModal<?php echo $agent['id']; ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editAgentModal<?php echo $agent['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit AI Agent</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">

                                        <div class="row">
                                            <div class="col-md-8 mb-3">
                                                <label class="form-label">Agent Name *</label>
                                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($agent['name']); ?>" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Category *</label>
                                                <select name="category" class="form-select" required>
                                                    <option value="automation" <?php echo $agent['category'] === 'automation' ? 'selected' : ''; ?>>Automation</option>
                                                    <option value="chatbot" <?php echo $agent['category'] === 'chatbot' ? 'selected' : ''; ?>>Chatbot</option>
                                                    <option value="analytics" <?php echo $agent['category'] === 'analytics' ? 'selected' : ''; ?>>Analytics</option>
                                                    <option value="content-generation" <?php echo $agent['category'] === 'content-generation' ? 'selected' : ''; ?>>Content Generation</option>
                                                    <option value="customer-service" <?php echo $agent['category'] === 'customer-service' ? 'selected' : ''; ?>>Customer Service</option>
                                                </select>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Description *</label>
                                                <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($agent['description']); ?></textarea>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Features (one per line)</label>
                                                <textarea name="features" class="form-control" rows="4"><?php echo htmlspecialchars($agent['features'] ?? ''); ?></textarea>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Icon Class (Font Awesome)</label>
                                                <input type="text" name="icon" class="form-control" value="<?php echo htmlspecialchars($agent['icon']); ?>" placeholder="fas fa-robot">
                                                <small class="text-muted">e.g., fas fa-robot, fas fa-brain</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Pricing ($)</label>
                                                <input type="number" name="pricing" step="0.01" class="form-control" value="<?php echo $agent['pricing']; ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Demo URL</label>
                                                <input type="url" name="demo_url" class="form-control" value="<?php echo htmlspecialchars($agent['demo_url'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Documentation URL</label>
                                                <input type="url" name="documentation_url" class="form-control" value="<?php echo htmlspecialchars($agent['documentation_url'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Status *</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="active" <?php echo $agent['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                    <option value="inactive" <?php echo $agent['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                    <option value="coming_soon" <?php echo $agent['status'] === 'coming_soon' ? 'selected' : ''; ?>>Coming Soon</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" name="update_agent" class="btn btn-primary">Update Agent</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteAgentModal<?php echo $agent['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Delete AI Agent</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                                        <p>Are you sure you want to delete this AI agent?</p>
                                        <p class="text-danger"><strong><?php echo htmlspecialchars($agent['name']); ?></strong></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" name="delete_agent" class="btn btn-danger">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="content-card text-center py-5">
                    <i class="fas fa-robot fa-4x text-muted mb-3"></i>
                    <h3>No AI Agents Yet</h3>
                    <p class="text-muted">Add your first AI agent to showcase your solutions</p>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addAgentModal">
                        <i class="fas fa-plus me-2"></i>Add AI Agent
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Agent Modal -->
<div class="modal fade" id="addAgentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New AI Agent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Agent Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Category *</label>
                            <select name="category" class="form-select" required>
                                <option value="automation">Automation</option>
                                <option value="chatbot">Chatbot</option>
                                <option value="analytics">Analytics</option>
                                <option value="content-generation">Content Generation</option>
                                <option value="customer-service">Customer Service</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description *</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Features (one per line)</label>
                            <textarea name="features" class="form-control" rows="4" placeholder="Feature 1&#10;Feature 2&#10;Feature 3"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Icon Class (Font Awesome)</label>
                            <input type="text" name="icon" class="form-control" value="fas fa-robot" placeholder="fas fa-robot">
                            <small class="text-muted">e.g., fas fa-robot, fas fa-brain, fas fa-cog</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pricing ($)</label>
                            <input type="number" name="pricing" step="0.01" class="form-control" placeholder="99.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Demo URL</label>
                            <input type="url" name="demo_url" class="form-control" placeholder="https://demo.example.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Documentation URL</label>
                            <input type="url" name="documentation_url" class="form-control" placeholder="https://docs.example.com">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="coming_soon">Coming Soon</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_agent" class="btn btn-primary">Add Agent</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
