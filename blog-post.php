<?php
$currentPage = 'blog';

// Include database helper
include_once 'includes/db_config.php';

// Get post slug from URL
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

// Get blog post
$post = getBlogPost($slug);

// If post not found, redirect to blog
if (!$post) {
    header('Location: blog.php');
    exit;
}

$pageTitle = htmlspecialchars($post['title']);
$settings = getAllSettings();

// Get related posts
$relatedPosts = getRelatedBlogPosts($post['id'], $post['category'], 3);

// Handle image path
$featuredImage = $post['featured_image'] ?? '';
if ($featuredImage && strpos($featuredImage, 'http') !== 0 && strpos($featuredImage, '//') !== 0) {
    $featuredImage = $featuredImage; // Local path
}
if (empty($featuredImage)) {
    $featuredImage = 'https://cdn.pixabay.com/photo/2018/05/18/15/30/web-design-3411373_1280.jpg';
}

// Parse tags
$tags = [];
if (!empty($post['tags'])) {
    $tags = array_map('trim', explode(',', $post['tags']));
}

include 'includes/header.php';
?>

<section class="page-header blog-post-header">
    <div class="container">
        <span class="blog-category-badge" data-aos="fade-up"><?php echo htmlspecialchars($post['category']); ?></span>
        <h1 data-aos="fade-up" data-aos-delay="100"><?php echo htmlspecialchars($post['title']); ?></h1>
        <div class="post-meta" data-aos="fade-up" data-aos-delay="150">
            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($post['author_name'] ?? 'Admin'); ?></span>
            <span><i class="fas fa-calendar"></i> <?php echo date('F d, Y', strtotime($post['published_at'] ?? $post['created_at'])); ?></span>
            <span><i class="fas fa-eye"></i> <?php echo number_format($post['views'] + 1); ?> views</span>
        </div>
    </div>
</section>

<article class="blog-post-content py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Featured Image -->
                <div class="post-featured-image mb-4" data-aos="fade-up">
                    <img src="<?php echo htmlspecialchars($featuredImage); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="img-fluid rounded shadow">
                </div>

                <!-- Post Content -->
                <div class="post-body" data-aos="fade-up" data-aos-delay="100">
                    <?php
                    // Output content - allow HTML from TinyMCE
                    echo $post['content'];
                    ?>
                </div>

                <!-- Tags -->
                <?php if (!empty($tags)): ?>
                <div class="post-tags mt-4" data-aos="fade-up">
                    <strong><i class="fas fa-tags me-2"></i>Tags:</strong>
                    <?php foreach ($tags as $tag): ?>
                    <span class="tag-badge"><?php echo htmlspecialchars($tag); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Share Buttons -->
                <div class="post-share mt-4 pt-4 border-top" data-aos="fade-up">
                    <strong><i class="fas fa-share-alt me-2"></i>Share this post:</strong>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="share-btn facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($post['title']); ?>" target="_blank" class="share-btn twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&title=<?php echo urlencode($post['title']); ?>" target="_blank" class="share-btn linkedin"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://wa.me/?text=<?php echo urlencode($post['title'] . ' - ' . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="share-btn whatsapp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</article>

<!-- Related Posts -->
<?php if (!empty($relatedPosts)): ?>
<section class="related-posts-section py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4" data-aos="fade-up">Related Posts</h2>
        <div class="row">
            <?php foreach ($relatedPosts as $index => $related): ?>
            <?php
            $relatedImg = $related['featured_image'] ?? '';
            if ($relatedImg && strpos($relatedImg, 'http') !== 0 && strpos($relatedImg, '//') !== 0) {
                $relatedImg = $relatedImg;
            }
            if (empty($relatedImg)) {
                $relatedImg = 'https://cdn.pixabay.com/photo/2018/05/18/15/30/web-design-3411373_1280.jpg';
            }
            ?>
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                <article class="blog-card">
                    <div class="blog-image">
                        <img src="<?php echo htmlspecialchars($relatedImg); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>">
                        <div class="blog-date"><?php echo date('M d, Y', strtotime($related['published_at'] ?? $related['created_at'])); ?></div>
                    </div>
                    <div class="blog-content">
                        <div class="blog-category"><?php echo htmlspecialchars($related['category']); ?></div>
                        <h3><?php echo htmlspecialchars($related['title']); ?></h3>
                        <p><?php echo htmlspecialchars(substr(strip_tags($related['excerpt'] ?? $related['content']), 0, 100)); ?>...</p>
                        <a href="blog-post.php?slug=<?php echo htmlspecialchars($related['slug']); ?>" class="blog-read-more">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="cta-section py-5">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="zoom-in">
                <h2 class="text-white mb-3">Want More Insights?</h2>
                <p class="text-white mb-4">Stay updated with our latest articles on web development, AI, and digital marketing.</p>
                <a href="blog.php" class="btn btn-light btn-lg me-2">View All Posts</a>
                <a href="contact.php" class="btn btn-outline-light btn-lg">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<style>
/* Blog Post Page Styles */
.blog-post-header {
    padding: 80px 0 60px;
}

.blog-category-badge {
    display: inline-block;
    background: rgba(255,255,255,0.2);
    color: #fff;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    margin-bottom: 15px;
}

.post-meta {
    color: rgba(255,255,255,0.8);
    font-size: 0.95rem;
}

.post-meta span {
    margin: 0 15px;
}

.post-meta i {
    margin-right: 6px;
}

.post-featured-image img {
    width: 100%;
    max-height: 500px;
    object-fit: cover;
    border-radius: 12px;
}

.post-body {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #444;
}

.post-body h2, .post-body h3, .post-body h4 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: var(--dark-color);
}

.post-body p {
    margin-bottom: 1.5rem;
}

.post-body img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1.5rem 0;
}

.post-body ul, .post-body ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.post-body blockquote {
    background: #f8f9fa;
    border-left: 4px solid var(--primary-color);
    padding: 1.5rem;
    margin: 1.5rem 0;
    font-style: italic;
    border-radius: 0 8px 8px 0;
}

.post-body pre {
    background: #2d3748;
    color: #e2e8f0;
    padding: 1.5rem;
    border-radius: 8px;
    overflow-x: auto;
    margin: 1.5rem 0;
}

.post-body code {
    background: #e2e8f0;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.9em;
}

.post-body pre code {
    background: transparent;
    padding: 0;
}

.post-tags .tag-badge {
    display: inline-block;
    background: #e9ecef;
    color: #495057;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.85rem;
    margin: 5px 5px 5px 0;
    transition: all 0.3s ease;
}

.post-tags .tag-badge:hover {
    background: var(--primary-color);
    color: #fff;
}

.share-buttons {
    display: inline-flex;
    gap: 10px;
    margin-left: 15px;
}

.share-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: #fff;
    text-decoration: none;
    transition: transform 0.3s ease;
}

.share-btn:hover {
    transform: translateY(-3px);
    color: #fff;
}

.share-btn.facebook { background: #1877f2; }
.share-btn.twitter { background: #1da1f2; }
.share-btn.linkedin { background: #0077b5; }
.share-btn.whatsapp { background: #25d366; }

.related-posts-section h2 {
    color: var(--dark-color);
    font-family: var(--font-heading);
}

@media (max-width: 767px) {
    .post-meta span {
        display: block;
        margin: 5px 0;
    }

    .share-buttons {
        display: flex;
        margin-left: 0;
        margin-top: 10px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
