<?php
require_once 'includes/auth.php';
$auth->requireLogin();

require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

// Get statistics
$stats = [
    'total_leads' => 0,
    'new_leads' => 0,
    'total_projects' => 0,
    'total_ai_agents' => 0,
    'total_blog_posts' => 0
];

try {
    // Total leads
    $stmt = $db->query("SELECT COUNT(*) as count FROM leads");
    $stats['total_leads'] = $stmt->fetch()['count'];

    // New leads (last 7 days)
    $stmt = $db->query("SELECT COUNT(*) as count FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['new_leads'] = $stmt->fetch()['count'];

    // Total projects
    $stmt = $db->query("SELECT COUNT(*) as count FROM projects WHERE status = 'active'");
    $stats['total_projects'] = $stmt->fetch()['count'];

    // Total AI agents
    $stmt = $db->query("SELECT COUNT(*) as count FROM ai_agents WHERE status = 'active'");
    $stats['total_ai_agents'] = $stmt->fetch()['count'];

    // Total blog posts
    $stmt = $db->query("SELECT COUNT(*) as count FROM blog_posts WHERE status = 'published'");
    $stats['total_blog_posts'] = $stmt->fetch()['count'];

    // Recent leads
    $stmt = $db->query("SELECT * FROM leads ORDER BY created_at DESC LIMIT 5");
    $recent_leads = $stmt->fetchAll();

    // Recent activity
    $stmt = $db->prepare("
        SELECT al.*, au.full_name
        FROM activity_log al
        LEFT JOIN admin_users au ON al.user_id = au.id
        ORDER BY al.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recent_activity = $stmt->fetchAll();

} catch(PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
}

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<div class="admin-content">
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_full_name']); ?>!</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-value"><?php echo number_format($stats['total_leads']); ?></div>
                    <div class="stat-label">Total Leads</div>
                </div>
                <div class="stat-icon primary">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span><?php echo $stats['new_leads']; ?> new this week</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-value"><?php echo number_format($stats['total_projects']); ?></div>
                    <div class="stat-label">Active Projects</div>
                </div>
                <div class="stat-icon success">
                    <i class="fas fa-briefcase"></i>
                </div>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-check-circle"></i>
                <span>All on track</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-value"><?php echo number_format($stats['total_ai_agents']); ?></div>
                    <div class="stat-label">AI Agents</div>
                </div>
                <div class="stat-icon warning">
                    <i class="fas fa-robot"></i>
                </div>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-star"></i>
                <span>Featured services</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-value"><?php echo number_format($stats['total_blog_posts']); ?></div>
                    <div class="stat-label">Blog Posts</div>
                </div>
                <div class="stat-icon danger">
                    <i class="fas fa-blog"></i>
                </div>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-pen"></i>
                <span>Published</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <!-- Recent Leads -->
            <div class="content-card">
                <div class="card-header-flex">
                    <h2 class="card-title">Recent Leads</h2>
                    <a href="leads.php" class="btn btn-sm btn-primary">View All</a>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_leads)): ?>
                                <?php foreach ($recent_leads as $lead): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($lead['name']); ?></strong>
                                            <?php if ($lead['company']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($lead['company']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($lead['email']); ?></td>
                                        <td><?php echo htmlspecialchars($lead['service_type'] ?? 'General'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php
                                                echo $lead['status'] === 'new' ? 'primary' :
                                                     ($lead['status'] === 'won' ? 'success' : 'secondary');
                                            ?>">
                                                <?php echo ucfirst($lead['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($lead['created_at'])); ?></td>
                                        <td>
                                            <a href="leads.php?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-icon">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No leads yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <!-- Recent Activity -->
            <div class="content-card">
                <div class="card-header-flex">
                    <h2 class="card-title">Recent Activity</h2>
                </div>

                <div class="activity-list">
                    <?php if (!empty($recent_activity)): ?>
                        <?php foreach ($recent_activity as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-<?php
                                        echo $activity['action'] === 'login' ? 'sign-in-alt' :
                                             ($activity['action'] === 'create' ? 'plus' :
                                             ($activity['action'] === 'update' ? 'edit' : 'trash'));
                                    ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <p class="activity-text">
                                        <strong><?php echo htmlspecialchars($activity['full_name'] ?? 'System'); ?></strong>
                                        <?php echo htmlspecialchars($activity['description'] ?? $activity['action']); ?>
                                    </p>
                                    <span class="activity-time">
                                        <?php echo date('M d, Y g:i A', strtotime($activity['created_at'])); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">No activity yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.activity-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.activity-item {
    display: flex;
    gap: 15px;
    padding: 12px;
    border-radius: 10px;
    background: var(--light);
    transition: all 0.3s ease;
}

.activity-item:hover {
    background: #e9ecef;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-text {
    margin: 0 0 5px 0;
    font-size: 0.9rem;
    color: var(--dark);
}

.activity-time {
    font-size: 0.75rem;
    color: var(--secondary);
}
</style>

<?php include 'includes/footer.php'; ?>
