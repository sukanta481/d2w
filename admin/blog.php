<?php
require_once 'includes/auth.php';
$auth->requireLogin();
require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_post'])) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['title'])));
            $stmt = $db->prepare("INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, category, tags, author_id, status, published_at)
                                  VALUES (:title, :slug, :excerpt, :content, :featured_image, :category, :tags, :author_id, :status, :published_at)");
            $stmt->execute([':title' => $_POST['title'], ':slug' => $slug, ':excerpt' => $_POST['excerpt'] ?? null, ':content' => $_POST['content'],
                ':featured_image' => $_POST['featured_image'] ?? null, ':category' => $_POST['category'], ':tags' => $_POST['tags'] ?? null,
                ':author_id' => $auth->getUserId(), ':status' => $_POST['status'], ':published_at' => $_POST['status'] === 'published' ? date('Y-m-d H:i:s') : null]);
            $successMessage = "Blog post added successfully!";
        }
        if (isset($_POST['update_post'])) {
            $stmt = $db->prepare("UPDATE blog_posts SET title = :title, excerpt = :excerpt, content = :content, featured_image = :featured_image,
                                  category = :category, tags = :tags, status = :status, published_at = :published_at WHERE id = :id");
            $publishedAt = $_POST['status'] === 'published' ? ($_POST['published_at'] ?? date('Y-m-d H:i:s')) : null;
            $stmt->execute([':title' => $_POST['title'], ':excerpt' => $_POST['excerpt'] ?? null, ':content' => $_POST['content'],
                ':featured_image' => $_POST['featured_image'] ?? null, ':category' => $_POST['category'], ':tags' => $_POST['tags'] ?? null,
                ':status' => $_POST['status'], ':published_at' => $publishedAt, ':id' => $_POST['post_id']]);
            $successMessage = "Blog post updated successfully!";
        }
        if (isset($_POST['delete_post'])) {
            $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = :id");
            $stmt->execute([':id' => $_POST['post_id']]);
            $successMessage = "Blog post deleted successfully!";
        }
    } catch(PDOException $e) { $errorMessage = "Error: " . $e->getMessage(); }
}

$stmt = $db->query("SELECT bp.*, au.full_name as author_name FROM blog_posts bp LEFT JOIN admin_users au ON bp.author_id = au.id ORDER BY bp.created_at DESC");
$posts = $stmt->fetchAll();
$pageTitle = 'Blog Management';
include 'includes/header.php';
?>

<div class="admin-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div><h1 class="page-title">Blog Management</h1><p class="page-subtitle">Manage blog posts</p></div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPostModal"><i class="fas fa-plus me-2"></i>Add New Post</button>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo $successMessage; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="content-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Image</th><th>Title</th><th>Category</th><th>Author</th><th>Status</th><th>Views</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td>
                                <?php if ($post['featured_image']): ?>
                                    <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="" style="width: 80px; height: 50px; object-fit: cover; border-radius: 5px;">
                                <?php else: ?>
                                    <div style="width: 80px; height: 50px; background: #e9ecef; border-radius: 5px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-image text-muted"></i></div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($post['title']); ?></strong><br><small class="text-muted">/blog/<?php echo htmlspecialchars($post['slug']); ?></small></td>
                            <td><span class="badge bg-info"><?php echo htmlspecialchars($post['category']); ?></span></td>
                            <td><?php echo htmlspecialchars($post['author_name'] ?? 'Unknown'); ?></td>
                            <td><span class="badge bg-<?php echo $post['status'] === 'published' ? 'success' : ($post['status'] === 'draft' ? 'warning' : 'secondary'); ?>"><?php echo ucfirst($post['status']); ?></span></td>
                            <td><?php echo number_format($post['views']); ?></td>
                            <td><?php echo $post['published_at'] ? date('M d, Y', strtotime($post['published_at'])) : '-'; ?></td>
                            <td>
                                <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#editPostModal<?php echo $post['id']; ?>"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-icon text-danger" data-bs-toggle="modal" data-bs-target="#deletePostModal<?php echo $post['id']; ?>"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editPostModal<?php echo $post['id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-xl"><div class="modal-content"><form method="POST">
                                <div class="modal-header"><h5 class="modal-title">Edit Blog Post</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <input type="hidden" name="published_at" value="<?php echo $post['published_at']; ?>">
                                    <div class="row">
                                        <div class="col-md-8 mb-3"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($post['title']); ?>" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Category *</label><select name="category" class="form-select" required>
                                            <option value="web-development" <?php echo $post['category'] === 'web-development' ? 'selected' : ''; ?>>Web Development</option>
                                            <option value="ai-technology" <?php echo $post['category'] === 'ai-technology' ? 'selected' : ''; ?>>AI Technology</option>
                                            <option value="digital-marketing" <?php echo $post['category'] === 'digital-marketing' ? 'selected' : ''; ?>>Digital Marketing</option>
                                            <option value="tips-tricks" <?php echo $post['category'] === 'tips-tricks' ? 'selected' : ''; ?>>Tips & Tricks</option>
                                            <option value="news" <?php echo $post['category'] === 'news' ? 'selected' : ''; ?>>News</option>
                                        </select></div>
                                        <div class="col-md-12 mb-3"><label class="form-label">Excerpt</label><textarea name="excerpt" class="form-control" rows="2"><?php echo htmlspecialchars($post['excerpt'] ?? ''); ?></textarea></div>
                                        <div class="col-md-12 mb-3"><label class="form-label">Content *</label><textarea name="content" class="form-control" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea></div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Featured Image URL</label><input type="url" name="featured_image" class="form-control" value="<?php echo htmlspecialchars($post['featured_image'] ?? ''); ?>"></div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Tags (comma separated)</label><input type="text" name="tags" class="form-control" value="<?php echo htmlspecialchars($post['tags'] ?? ''); ?>"></div>
                                        <div class="col-md-12 mb-3"><label class="form-label">Status *</label><select name="status" class="form-select" required>
                                            <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                            <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                            <option value="archived" <?php echo $post['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                                        </select></div>
                                    </div>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="update_post" class="btn btn-primary">Update Post</button></div>
                            </form></div></div>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deletePostModal<?php echo $post['id']; ?>" tabindex="-1">
                            <div class="modal-dialog"><div class="modal-content"><form method="POST">
                                <div class="modal-header"><h5 class="modal-title">Delete Post</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body"><input type="hidden" name="post_id" value="<?php echo $post['id']; ?>"><p>Delete <strong><?php echo htmlspecialchars($post['title']); ?></strong>?</p></div>
                                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="delete_post" class="btn btn-danger">Delete</button></div>
                            </form></div></div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Post Modal -->
<div class="modal fade" id="addPostModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><form method="POST">
    <div class="modal-header"><h5 class="modal-title">Add New Blog Post</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row">
        <div class="col-md-8 mb-3"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required></div>
        <div class="col-md-4 mb-3"><label class="form-label">Category *</label><select name="category" class="form-select" required>
            <option value="web-development">Web Development</option><option value="ai-technology">AI Technology</option>
            <option value="digital-marketing">Digital Marketing</option><option value="tips-tricks">Tips & Tricks</option><option value="news">News</option>
        </select></div>
        <div class="col-md-12 mb-3"><label class="form-label">Excerpt</label><textarea name="excerpt" class="form-control" rows="2" placeholder="Short description for preview..."></textarea></div>
        <div class="col-md-12 mb-3"><label class="form-label">Content *</label><textarea name="content" class="form-control" rows="10" required></textarea></div>
        <div class="col-md-6 mb-3"><label class="form-label">Featured Image URL</label><input type="url" name="featured_image" class="form-control"></div>
        <div class="col-md-6 mb-3"><label class="form-label">Tags (comma separated)</label><input type="text" name="tags" class="form-control" placeholder="php, web, tutorial"></div>
        <div class="col-md-12 mb-3"><label class="form-label">Status *</label><select name="status" class="form-select" required><option value="draft">Draft</option><option value="published">Published</option></select></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add_post" class="btn btn-primary">Add Post</button></div>
</form></div></div></div>

<?php include 'includes/footer.php'; ?>
