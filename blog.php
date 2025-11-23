<?php
$currentPage = 'blog';
$pageTitle = 'Blog';

// Include database helper
include_once 'includes/db_config.php';
$blogPosts = getBlogPosts();
$settings = getAllSettings();

include 'includes/header.php';
?>

<section class="page-header-new">
    <!-- Animated Background -->
    <div class="page-header-bg">
        <div class="header-shape header-shape-1"></div>
        <div class="header-shape header-shape-2"></div>
        <div class="header-shape header-shape-3"></div>
    </div>

    <div class="container">
        <div class="header-content">
            <div class="header-badge" data-aos="fade-up">
                <i class="fas fa-blog"></i>
                <span>Latest Articles</span>
            </div>
            <h1 data-aos="fade-up" data-aos-delay="100">Our <span class="text-gradient">Blog</span></h1>
            <p class="header-subtitle" data-aos="fade-up" data-aos-delay="200">Stay updated with the latest insights, tips, and trends in web development, AI, and digital marketing.</p>
            <div class="header-breadcrumb" data-aos="fade-up" data-aos-delay="300">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <i class="fas fa-chevron-right"></i>
                <span>Blog</span>
            </div>
        </div>
    </div>
</section>

<!-- Blog Section with Dark Animated Background -->
<section class="section-dark-animated" style="padding: 100px 0;">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
        <div class="bg-shape bg-shape-3"></div>
    </div>

    <!-- Floating Code Elements -->
    <div class="floating-elements">
        <div class="floating-element">&lt;/&gt;</div>
        <div class="floating-element">{...}</div>
        <div class="floating-element">&lt;p&gt;</div>
        <div class="floating-element">AI</div>
    </div>

    <div class="container">
        <!-- Section Title -->
        <div class="section-title-animated" data-aos="fade-up">
            <div class="section-badge">
                <i class="fas fa-newspaper"></i>
                <span>Latest Posts</span>
            </div>
            <h2>Insights & <span class="text-gradient">Articles</span></h2>
            <p>Discover the latest trends, tips, and best practices in web development and digital marketing.</p>
        </div>

        <div class="row">
            <?php if (!empty($blogPosts)): ?>
                <?php $delay = 0; foreach ($blogPosts as $post): ?>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" <?php echo $delay > 0 ? 'data-aos-delay="' . $delay . '"' : ''; ?>>
                    <article class="blog-card-animated">
                        <div class="blog-image-animated">
                            <?php
                            // Handle image path - check for external URLs, local paths, or use default
                            $imgSrc = '';
                            $defaultImg = 'https://cdn.pixabay.com/photo/2018/05/18/15/30/web-design-3411373_1280.jpg';

                            if (!empty($post['featured_image'])) {
                                $featuredImg = $post['featured_image'];
                                // External URL (http/https or protocol-relative)
                                if (strpos($featuredImg, 'http') === 0 || strpos($featuredImg, '//') === 0) {
                                    $imgSrc = $featuredImg;
                                } else {
                                    // Local path - check if file exists
                                    $localPath = $featuredImg;
                                    // Remove leading slash if present
                                    if (strpos($localPath, '/') === 0) {
                                        $localPath = substr($localPath, 1);
                                    }
                                    // Check if file exists on server
                                    if (file_exists(__DIR__ . '/' . $localPath)) {
                                        $imgSrc = $localPath;
                                    } else {
                                        // File doesn't exist locally, use default
                                        $imgSrc = $defaultImg;
                                    }
                                }
                            } else {
                                $imgSrc = $defaultImg;
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            <div class="blog-date-badge">
                                <span class="day"><?php echo date('d', strtotime($post['published_at'] ?? $post['created_at'])); ?></span>
                                <span class="month"><?php echo date('M', strtotime($post['published_at'] ?? $post['created_at'])); ?></span>
                            </div>
                            <?php if (!empty($post['category'])): ?>
                            <div class="blog-category-badge"><?php echo htmlspecialchars($post['category']); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="blog-content-animated">
                            <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr(strip_tags($post['excerpt'] ?? $post['content']), 0, 120)); ?>...</p>
                            <a href="blog-post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="blog-read-more-animated">
                                <span>Read More</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                </div>
                <?php $delay = $delay >= 200 ? 0 : $delay + 100; endforeach; ?>
            <?php else: ?>
                <!-- Fallback static blog posts -->
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up">
                    <article class="blog-card-animated">
                        <div class="blog-image-animated">
                            <img src="https://cdn.pixabay.com/photo/2018/05/18/15/30/web-design-3411373_1280.jpg" alt="Web Design Trends">
                            <div class="blog-date-badge">
                                <span class="day">18</span>
                                <span class="month">Nov</span>
                            </div>
                            <div class="blog-category-badge">Web Design</div>
                        </div>
                        <div class="blog-content-animated">
                            <h3>Top Web Design Trends for 2025</h3>
                            <p>Discover the latest web design trends that will shape the digital landscape in 2025...</p>
                            <a href="#" class="blog-read-more-animated">
                                <span>Read More</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                </div>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <article class="blog-card-animated">
                        <div class="blog-image-animated">
                            <img src="https://cdn.pixabay.com/photo/2023/05/04/17/00/ai-generated-7970187_1280.png" alt="Agentic AI">
                            <div class="blog-date-badge">
                                <span class="day">15</span>
                                <span class="month">Nov</span>
                            </div>
                            <div class="blog-category-badge">Artificial Intelligence</div>
                        </div>
                        <div class="blog-content-animated">
                            <h3>How Agentic AI is Transforming Business</h3>
                            <p>Learn how agentic AI is revolutionizing business operations and customer service...</p>
                            <a href="#" class="blog-read-more-animated">
                                <span>Read More</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                </div>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <article class="blog-card-animated">
                        <div class="blog-image-animated">
                            <img src="https://cdn.pixabay.com/photo/2017/06/26/19/03/social-media-2444991_1280.jpg" alt="SEO Tips">
                            <div class="blog-date-badge">
                                <span class="day">12</span>
                                <span class="month">Nov</span>
                            </div>
                            <div class="blog-category-badge">SEO</div>
                        </div>
                        <div class="blog-content-animated">
                            <h3>Essential SEO Strategies for Small Businesses</h3>
                            <p>Boost your online visibility with these proven SEO strategies for small businesses...</p>
                            <a href="#" class="blog-read-more-animated">
                                <span>Read More</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($blogPosts) && count($blogPosts) > 6): ?>
        <nav aria-label="Blog pagination" class="mt-5" data-aos="fade-up">
            <ul class="pagination-animated justify-content-center">
                <li class="page-item-animated disabled">
                    <a class="page-link-animated" href="#" tabindex="-1">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                </li>
                <li class="page-item-animated active"><a class="page-link-animated" href="#">1</a></li>
                <li class="page-item-animated"><a class="page-link-animated" href="#">2</a></li>
                <li class="page-item-animated"><a class="page-link-animated" href="#">3</a></li>
                <li class="page-item-animated">
                    <a class="page-link-animated" href="#">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</section>

<!-- Newsletter Section with Light Animated Background -->
<section class="section-light-animated" style="padding: 100px 0;">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
    </div>

    <div class="container">
        <div class="newsletter-box-animated" data-aos="zoom-in">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="newsletter-content">
                        <div class="newsletter-icon">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <h2>Subscribe to Our <span style="color: #0d6efd;">Newsletter</span></h2>
                        <p>Get the latest articles, tips, and insights delivered directly to your inbox. No spam, we promise!</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <form class="newsletter-form-animated">
                        <div class="input-group-animated">
                            <input type="email" placeholder="Enter your email address" required>
                            <button type="submit">
                                <span>Subscribe</span>
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Blog Card Animated */
.blog-card-animated {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.4s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.blog-card-animated:hover {
    transform: translateY(-10px);
    border-color: rgba(13, 110, 253, 0.3);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
}

.blog-image-animated {
    position: relative;
    height: 220px;
    overflow: hidden;
}

.blog-image-animated img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.blog-card-animated:hover .blog-image-animated img {
    transform: scale(1.1);
}

.blog-date-badge {
    position: absolute;
    top: 20px;
    left: 20px;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    padding: 12px 15px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(13, 110, 253, 0.3);
}

.blog-date-badge .day {
    display: block;
    font-size: 1.5rem;
    font-weight: 800;
    color: #fff;
    line-height: 1;
}

.blog-date-badge .month {
    display: block;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.9);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.blog-category-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
}

.blog-content-animated {
    padding: 30px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.blog-content-animated h3 {
    color: #fff;
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 15px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.blog-content-animated p {
    color: #94a3b8;
    font-size: 15px;
    line-height: 1.7;
    margin-bottom: 20px;
    flex: 1;
}

.blog-read-more-animated {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: #0d6efd;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.blog-read-more-animated:hover {
    color: #6610f2;
    gap: 15px;
}

.blog-read-more-animated i {
    transition: transform 0.3s ease;
}

.blog-read-more-animated:hover i {
    transform: translateX(5px);
}

/* Pagination Animated */
.pagination-animated {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 10px;
}

.page-link-animated {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #94a3b8;
    padding: 12px 20px;
    border-radius: 12px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
}

.page-link-animated:hover,
.page-item-animated.active .page-link-animated {
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    border-color: transparent;
    color: #fff;
}

.page-item-animated.disabled .page-link-animated {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

/* Newsletter Box Animated */
.newsletter-box-animated {
    background: #fff;
    border-radius: 25px;
    padding: 60px 50px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.1);
}

.newsletter-content {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.newsletter-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.newsletter-icon i {
    font-size: 28px;
    color: #fff;
}

.newsletter-content h2 {
    color: #1e293b;
    font-size: 2rem;
    font-weight: 800;
    margin: 0;
}

.newsletter-content p {
    color: #64748b;
    font-size: 1rem;
    margin: 0;
    line-height: 1.6;
}

.input-group-animated {
    display: flex;
    gap: 15px;
    background: #f1f5f9;
    padding: 10px;
    border-radius: 50px;
}

.input-group-animated input {
    flex: 1;
    background: transparent;
    border: none;
    padding: 15px 25px;
    font-size: 15px;
    color: #1e293b;
    outline: none;
}

.input-group-animated input::placeholder {
    color: #94a3b8;
}

.input-group-animated button {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    color: #fff;
    padding: 15px 30px;
    border: none;
    border-radius: 50px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.input-group-animated button:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 30px rgba(13, 110, 253, 0.3);
}

@media (max-width: 991px) {
    .newsletter-box-animated {
        padding: 40px 30px;
    }

    .newsletter-content {
        text-align: center;
        align-items: center;
    }

    .newsletter-content h2 {
        font-size: 1.6rem;
    }
}

@media (max-width: 576px) {
    .input-group-animated {
        flex-direction: column;
        border-radius: 20px;
    }

    .input-group-animated button {
        justify-content: center;
    }

    .blog-image-animated {
        height: 180px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
