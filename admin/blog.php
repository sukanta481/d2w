<?php
require_once 'includes/auth.php';
$auth->requireLogin();
require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

// Handle image upload
function handleImageUpload() {
    if (!isset($_FILES['image_upload']) || $_FILES['image_upload']['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $uploadDir = '../uploads/blog/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $file = $_FILES['image_upload'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (in_array($mimeType, $allowedTypes) && $file['size'] <= 5 * 1024 * 1024) {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('blog_', true) . '.' . strtolower($extension);
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            return 'uploads/blog/' . $filename;
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $uploadedImage = handleImageUpload();

        if (isset($_POST['add_post'])) {
            $featuredImage = $uploadedImage ?? ($_POST['featured_image'] ?? null);
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['title'])));
            $stmt = $db->prepare("INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, category, tags, author_id, status, published_at)
                                  VALUES (:title, :slug, :excerpt, :content, :featured_image, :category, :tags, :author_id, :status, :published_at)");
            $stmt->execute([':title' => $_POST['title'], ':slug' => $slug, ':excerpt' => $_POST['excerpt'] ?? null, ':content' => $_POST['content'],
                ':featured_image' => $featuredImage, ':category' => $_POST['category'], ':tags' => $_POST['tags'] ?? null,
                ':author_id' => $auth->getUserId(), ':status' => $_POST['status'], ':published_at' => $_POST['status'] === 'published' ? date('Y-m-d H:i:s') : null]);
            $successMessage = "Blog post added successfully!";
        }
        if (isset($_POST['update_post'])) {
            $featuredImage = $uploadedImage ?? ($_POST['featured_image'] ?? null);
            $stmt = $db->prepare("UPDATE blog_posts SET title = :title, excerpt = :excerpt, content = :content, featured_image = :featured_image,
                                  category = :category, tags = :tags, status = :status, published_at = :published_at WHERE id = :id");
            $publishedAt = $_POST['status'] === 'published' ? ($_POST['published_at'] ?? date('Y-m-d H:i:s')) : null;
            $stmt->execute([':title' => $_POST['title'], ':excerpt' => $_POST['excerpt'] ?? null, ':content' => $_POST['content'],
                ':featured_image' => $featuredImage, ':category' => $_POST['category'], ':tags' => $_POST['tags'] ?? null,
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

<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/c6dnzoialg8zo3sb0ymi2pq3fwr09mpe8pqy4vtef212k4gf/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<style>
.image-upload-wrapper {
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
    background: #f8f9fa;
    cursor: pointer;
    position: relative;
}
.image-upload-wrapper:hover, .image-upload-wrapper.dragover {
    border-color: var(--primary-color);
    background: #e8f4ff;
}
.image-preview {
    max-width: 100%;
    max-height: 200px;
    border-radius: 8px;
    margin-top: 10px;
    display: none;
}
.image-preview.show { display: block; }
.upload-icon {
    font-size: 3rem;
    color: #adb5bd;
    margin-bottom: 10px;
}
.image-upload-wrapper:hover .upload-icon { color: var(--primary-color); }
.remove-image-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    display: none;
    z-index: 10;
}
.image-upload-wrapper.has-image .remove-image-btn { display: flex; align-items: center; justify-content: center; }
.tox-tinymce { border-radius: 8px !important; }
.tox-notifications-container { display: none !important; }
</style>

<div class="admin-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div><h1 class="page-title">Blog Management</h1><p class="page-subtitle">Manage blog posts with rich text editor</p></div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPostModal"><i class="fas fa-plus me-2"></i>Add New Post</button>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo $successMessage; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?php echo $errorMessage; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
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
                                    <img src="../<?php echo htmlspecialchars($post['featured_image']); ?>" alt="" style="width: 80px; height: 50px; object-fit: cover; border-radius: 5px;">
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
                            <div class="modal-dialog modal-xl"><div class="modal-content"><form method="POST" enctype="multipart/form-data">
                                <div class="modal-header"><h5 class="modal-title">Edit Blog Post</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <input type="hidden" name="published_at" value="<?php echo $post['published_at']; ?>">
                                    <input type="hidden" name="featured_image" id="edit_featured_image_<?php echo $post['id']; ?>" value="<?php echo htmlspecialchars($post['featured_image'] ?? ''); ?>">
                                    <div class="row">
                                        <div class="col-md-8 mb-3"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($post['title']); ?>" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Category *</label><select name="category" class="form-select" required>
                                            <option value="Web Design" <?php echo $post['category'] === 'Web Design' ? 'selected' : ''; ?>>Web Design</option>
                                            <option value="Web Development" <?php echo $post['category'] === 'Web Development' ? 'selected' : ''; ?>>Web Development</option>
                                            <option value="AI Technology" <?php echo $post['category'] === 'AI Technology' ? 'selected' : ''; ?>>AI Technology</option>
                                            <option value="Digital Marketing" <?php echo $post['category'] === 'Digital Marketing' ? 'selected' : ''; ?>>Digital Marketing</option>
                                            <option value="SEO" <?php echo $post['category'] === 'SEO' ? 'selected' : ''; ?>>SEO</option>
                                            <option value="Tips & Tricks" <?php echo $post['category'] === 'Tips & Tricks' ? 'selected' : ''; ?>>Tips & Tricks</option>
                                            <option value="News" <?php echo $post['category'] === 'News' ? 'selected' : ''; ?>>News</option>
                                        </select></div>

                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Featured Image</label>
                                            <div class="image-upload-wrapper <?php echo $post['featured_image'] ? 'has-image' : ''; ?>" id="edit_wrapper_<?php echo $post['id']; ?>" onclick="document.getElementById('edit_file_<?php echo $post['id']; ?>').click()">
                                                <input type="file" name="image_upload" id="edit_file_<?php echo $post['id']; ?>" accept="image/*" style="display:none" onchange="previewImage(this, 'edit', <?php echo $post['id']; ?>)">
                                                <button type="button" class="remove-image-btn" onclick="removeImage(event, 'edit', <?php echo $post['id']; ?>)"><i class="fas fa-times"></i></button>
                                                <i class="fas fa-cloud-upload-alt upload-icon" id="edit_icon_<?php echo $post['id']; ?>" <?php echo $post['featured_image'] ? 'style="display:none"' : ''; ?>></i>
                                                <img src="<?php echo $post['featured_image'] ? '../' . htmlspecialchars($post['featured_image']) : ''; ?>" class="image-preview <?php echo $post['featured_image'] ? 'show' : ''; ?>" id="edit_preview_<?php echo $post['id']; ?>">
                                                <p class="mb-0 text-muted" id="edit_text_<?php echo $post['id']; ?>" <?php echo $post['featured_image'] ? 'style="display:none"' : ''; ?>>Click or drag image here (Max 5MB)</p>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mb-3"><label class="form-label">Excerpt</label><textarea name="excerpt" class="form-control" rows="2"><?php echo htmlspecialchars($post['excerpt'] ?? ''); ?></textarea></div>
                                        <div class="col-md-12 mb-3"><label class="form-label">Content *</label><textarea name="content" class="tinymce-editor" id="edit_content_<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['content']); ?></textarea></div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Tags (comma separated)</label><input type="text" name="tags" class="form-control" value="<?php echo htmlspecialchars($post['tags'] ?? ''); ?>"></div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Status *</label><select name="status" class="form-select" required>
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
<div class="modal fade" id="addPostModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><form method="POST" enctype="multipart/form-data">
    <div class="modal-header"><h5 class="modal-title">Add New Blog Post</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row">
        <div class="col-md-8 mb-3"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required></div>
        <div class="col-md-4 mb-3"><label class="form-label">Category *</label><select name="category" class="form-select" required>
            <option value="Web Design">Web Design</option>
            <option value="Web Development">Web Development</option>
            <option value="AI Technology">AI Technology</option>
            <option value="Digital Marketing">Digital Marketing</option>
            <option value="SEO">SEO</option>
            <option value="Tips & Tricks">Tips & Tricks</option>
            <option value="News">News</option>
        </select></div>

        <div class="col-md-12 mb-3">
            <label class="form-label">Featured Image</label>
            <div class="image-upload-wrapper" id="add_wrapper" onclick="document.getElementById('add_file').click()">
                <input type="file" name="image_upload" id="add_file" accept="image/*" style="display:none" onchange="previewImage(this, 'add')">
                <input type="hidden" name="featured_image" id="add_featured_image" value="">
                <button type="button" class="remove-image-btn" onclick="removeImage(event, 'add')"><i class="fas fa-times"></i></button>
                <i class="fas fa-cloud-upload-alt upload-icon" id="add_icon"></i>
                <img src="" class="image-preview" id="add_preview">
                <p class="mb-0 text-muted" id="add_text">Click or drag image here (Max 5MB)</p>
            </div>
        </div>

        <div class="col-md-12 mb-3"><label class="form-label">Excerpt</label><textarea name="excerpt" class="form-control" rows="2" placeholder="Short description for preview..."></textarea></div>
        <div class="col-md-12 mb-3"><label class="form-label">Content *</label><textarea name="content" class="tinymce-editor" id="add_content"></textarea></div>
        <div class="col-md-6 mb-3"><label class="form-label">Tags (comma separated)</label><input type="text" name="tags" class="form-control" placeholder="php, web, tutorial"></div>
        <div class="col-md-6 mb-3"><label class="form-label">Status *</label><select name="status" class="form-select" required><option value="draft">Draft</option><option value="published">Published</option></select></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add_post" class="btn btn-primary">Add Post</button></div>
</form></div></div></div>

<script>
// TinyMCE initialization
function initTinyMCE(selector) {
    if (tinymce.get(selector.replace('#', ''))) {
        tinymce.get(selector.replace('#', '')).remove();
    }
    tinymce.init({
        selector: selector,
        height: 400,
        menubar: true,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
        toolbar: 'undo redo | blocks | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | removeformat code fullscreen',
        toolbar_mode: 'sliding',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; font-size: 16px; line-height: 1.6; }',
        // Preserve formatting when pasting
        paste_data_images: true,
        paste_as_text: false,
        paste_enable_default_filters: false,
        paste_webkit_styles: 'all',
        paste_retain_style_properties: 'all',
        paste_merge_formats: true,
        valid_elements: '*[*]',
        extended_valid_elements: '*[*]',
        // Image upload
        images_upload_handler: function(blobInfo) {
            return new Promise((resolve, reject) => {
                const formData = new FormData();
                formData.append('image', blobInfo.blob(), blobInfo.filename());
                fetch('upload.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => data.success ? resolve('../' + data.url) : reject(data.message))
                    .catch(err => reject('Upload failed'));
            });
        },
        automatic_uploads: true,
        setup: function(editor) {
            editor.on('change', function() { tinymce.triggerSave(); });
        }
    });
}

// Initialize on modal show
document.getElementById('addPostModal').addEventListener('shown.bs.modal', function() {
    initTinyMCE('#add_content');
});

document.querySelectorAll('[id^="editPostModal"]').forEach(function(modal) {
    modal.addEventListener('shown.bs.modal', function() {
        const textarea = this.querySelector('.tinymce-editor');
        if (textarea) initTinyMCE('#' + textarea.id);
    });
});

// Image preview
function previewImage(input, type, id = '') {
    const file = input.files[0];
    if (!file) return;

    if (!['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(file.type)) {
        alert('Invalid file type. Use JPG, PNG, GIF, or WebP.');
        return;
    }
    if (file.size > 5 * 1024 * 1024) {
        alert('File too large. Maximum 5MB.');
        return;
    }

    const suffix = id ? '_' + id : '';
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById(type + '_preview' + suffix).src = e.target.result;
        document.getElementById(type + '_preview' + suffix).classList.add('show');
        document.getElementById(type + '_icon' + suffix).style.display = 'none';
        document.getElementById(type + '_text' + suffix).style.display = 'none';
        document.getElementById(type + '_wrapper' + suffix).classList.add('has-image');
    };
    reader.readAsDataURL(file);
}

function removeImage(event, type, id = '') {
    event.stopPropagation();
    const suffix = id ? '_' + id : '';
    document.getElementById(type + '_file' + suffix).value = '';
    document.getElementById(type + '_preview' + suffix).src = '';
    document.getElementById(type + '_preview' + suffix).classList.remove('show');
    document.getElementById(type + '_icon' + suffix).style.display = 'block';
    document.getElementById(type + '_text' + suffix).style.display = 'block';
    document.getElementById(type + '_wrapper' + suffix).classList.remove('has-image');
    document.getElementById(type + '_featured_image' + suffix).value = '';
}

// Drag and drop
document.querySelectorAll('.image-upload-wrapper').forEach(function(wrapper) {
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(e => wrapper.addEventListener(e, ev => { ev.preventDefault(); ev.stopPropagation(); }, false));
    ['dragenter', 'dragover'].forEach(e => wrapper.addEventListener(e, () => wrapper.classList.add('dragover'), false));
    ['dragleave', 'drop'].forEach(e => wrapper.addEventListener(e, () => wrapper.classList.remove('dragover'), false));
    wrapper.addEventListener('drop', function(e) {
        const input = this.querySelector('input[type="file"]');
        if (e.dataTransfer.files.length) {
            input.files = e.dataTransfer.files;
            input.dispatchEvent(new Event('change'));
        }
    }, false);
});
</script>

<?php include 'includes/footer.php'; ?>
