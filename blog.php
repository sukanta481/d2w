<?php
$currentPage = 'blog';
$pageTitle = 'Blog';

// Include database helper
include_once 'includes/db_config.php';
$blogPosts = getBlogPosts();
$settings = getAllSettings();

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1 data-aos="fade-up">Our Blog</h1>
        <p data-aos="fade-up" data-aos-delay="100">Insights, Tips, and Industry News</p>
    </div>
</section>

<section class="blog-section py-5">
    <div class="container">
        <div class="row">
            <?php if (!empty($blogPosts)): ?>
                <?php $delay = 0; foreach ($blogPosts as $post): ?>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" <?php echo $delay > 0 ? 'data-aos-delay="' . $delay . '"' : ''; ?>>
                    <article class="blog-card">
                        <div class="blog-image">
                            <?php if (!empty($post['featured_image'])): ?>
                            <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            <?php else: ?>
                            <img src="https://cdn.pixabay.com/photo/2018/05/18/15/30/web-design-3411373_1280.jpg" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            <?php endif; ?>
                            <div class="blog-date"><?php echo date('M d, Y', strtotime($post['published_at'] ?? $post['created_at'])); ?></div>
                        </div>
                        <div class="blog-content">
                            <?php if (!empty($post['category'])): ?>
                            <div class="blog-category"><?php echo htmlspecialchars($post['category']); ?></div>
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr(strip_tags($post['excerpt'] ?? $post['content']), 0, 150)); ?>...</p>
                            <a href="blog-post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="blog-read-more">Read More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </article>
                </div>
                <?php $delay = $delay >= 200 ? 0 : $delay + 100; endforeach; ?>
            <?php else: ?>
                <!-- Fallback static blog posts -->
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up">
                    <article class="blog-card">
                        <div class="blog-image">
                            <img src="https://cdn.pixabay.com/photo/2018/05/18/15/30/web-design-3411373_1280.jpg" alt="Web Design Trends">
                            <div class="blog-date">Nov 18, 2025</div>
                        </div>
                        <div class="blog-content">
                            <div class="blog-category">Web Design</div>
                            <h3>Top Web Design Trends for 2025</h3>
                            <p>Discover the latest web design trends that will shape the digital landscape in 2025...</p>
                            <a href="#" class="blog-read-more">Read More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </article>
                </div>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <article class="blog-card">
                        <div class="blog-image">
                            <img src="https://cdn.pixabay.com/photo/2023/05/04/17/00/ai-generated-7970187_1280.png" alt="Agentic AI">
                            <div class="blog-date">Nov 15, 2025</div>
                        </div>
                        <div class="blog-content">
                            <div class="blog-category">Artificial Intelligence</div>
                            <h3>How Agentic AI is Transforming Business</h3>
                            <p>Learn how agentic AI is revolutionizing business operations...</p>
                            <a href="#" class="blog-read-more">Read More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </article>
                </div>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <article class="blog-card">
                        <div class="blog-image">
                            <img src="https://cdn.pixabay.com/photo/2017/06/26/19/03/social-media-2444991_1280.jpg" alt="SEO Tips">
                            <div class="blog-date">Nov 12, 2025</div>
                        </div>
                        <div class="blog-content">
                            <div class="blog-category">SEO</div>
                            <h3>Essential SEO Strategies for Small Businesses</h3>
                            <p>Boost your online visibility with these proven SEO strategies...</p>
                            <a href="#" class="blog-read-more">Read More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </article>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($blogPosts) && count($blogPosts) > 6): ?>
        <nav aria-label="Blog pagination" class="mt-5">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
