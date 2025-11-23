<?php
$currentPage = 'portfolio';
$pageTitle = 'Portfolio';

// Include database helper
include_once 'includes/db_config.php';
$projects = getProjects();
$settings = getAllSettings();

// Get unique categories from projects for filter buttons
$categories = [];
if (!empty($projects)) {
    foreach ($projects as $project) {
        if (!empty($project['category']) && !in_array($project['category'], $categories)) {
            $categories[] = $project['category'];
        }
    }
}

include 'includes/header.php';
?>

<!-- Portfolio Hero Section with Dark Animated Background -->
<section class="section-dark-animated" style="padding-top: 120px; padding-bottom: 100px;">
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
        <div class="floating-element">[ ]</div>
        <div class="floating-element">&lt;img&gt;</div>
    </div>

    <div class="container">
        <!-- Hero Title -->
        <div class="section-title-animated" data-aos="fade-up">
            <div class="section-badge">
                <i class="fas fa-briefcase"></i>
                <span>Our Work</span>
            </div>
            <h1 style="font-size: 3rem; color: #fff;">Our <span class="text-gradient">Portfolio</span></h1>
            <p style="color: #94a3b8;">Explore our collection of successful projects that showcase our expertise in web development, design, and AI solutions.</p>
            <div class="header-breadcrumb mt-3" data-aos="fade-up" data-aos-delay="100" style="justify-content: center;">
                <a href="index.php" style="color: #94a3b8;"><i class="fas fa-home"></i> Home</a>
                <i class="fas fa-chevron-right" style="color: #64748b;"></i>
                <span style="color: #fff;">Portfolio</span>
            </div>
        </div>

        <!-- Filter Buttons -->
        <div class="portfolio-filters-animated text-center mb-5" data-aos="fade-up">
            <button class="filter-btn-animated active" data-filter="all">
                <i class="fas fa-th-large"></i> Show All
            </button>
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                <button class="filter-btn-animated" data-filter="<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $category))); ?>">
                    <i class="fas fa-folder"></i> <?php echo htmlspecialchars(ucwords($category)); ?>
                </button>
                <?php endforeach; ?>
            <?php else: ?>
                <button class="filter-btn-animated" data-filter="website">
                    <i class="fas fa-globe"></i> Website
                </button>
                <button class="filter-btn-animated" data-filter="ecommerce">
                    <i class="fas fa-shopping-cart"></i> E-Commerce
                </button>
                <button class="filter-btn-animated" data-filter="webportal">
                    <i class="fas fa-server"></i> Web Portal
                </button>
                <button class="filter-btn-animated" data-filter="ai">
                    <i class="fas fa-robot"></i> AI Solutions
                </button>
            <?php endif; ?>
        </div>

        <!-- Portfolio Grid -->
        <div class="row portfolio-grid">
            <?php if (!empty($projects)): ?>
                <?php $delay = 0; foreach ($projects as $project): ?>
                <div class="col-lg-4 col-md-6 mb-4 portfolio-item" data-category="<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $project['category'] ?? 'website'))); ?>" data-aos="fade-up" <?php echo $delay > 0 ? 'data-aos-delay="' . $delay . '"' : ''; ?>>
                    <div class="portfolio-card-animated">
                        <div class="portfolio-image-animated">
                            <?php if (!empty($project['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($project['image_url']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>">
                            <?php else: ?>
                            <img src="https://cdn.pixabay.com/photo/2020/05/18/16/17/social-media-5187243_1280.png" alt="<?php echo htmlspecialchars($project['title']); ?>">
                            <?php endif; ?>
                            <div class="portfolio-overlay-animated">
                                <div class="overlay-content">
                                    <span class="project-category"><?php echo htmlspecialchars($project['category'] ?? 'Web Project'); ?></span>
                                    <h4><?php echo htmlspecialchars($project['title']); ?></h4>
                                    <div class="overlay-buttons">
                                        <a href="project.php?id=<?php echo $project['id']; ?>" class="btn-portfolio">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                        <?php if (!empty($project['live_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($project['live_url']); ?>" target="_blank" class="btn-portfolio-outline">
                                            <i class="fas fa-external-link-alt"></i> Live
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="portfolio-info">
                            <h5><?php echo htmlspecialchars($project['title']); ?></h5>
                            <p><?php echo htmlspecialchars($project['category'] ?? 'Web Project'); ?></p>
                        </div>
                    </div>
                </div>
                <?php $delay = $delay >= 200 ? 0 : $delay + 100; endforeach; ?>
            <?php else: ?>
                <!-- Fallback static portfolio items -->
                <div class="col-lg-4 col-md-6 mb-4 portfolio-item" data-category="website" data-aos="fade-up">
                    <div class="portfolio-card-animated">
                        <div class="portfolio-image-animated">
                            <img src="https://cdn.pixabay.com/photo/2020/05/18/16/17/social-media-5187243_1280.png" alt="Restaurant Website">
                            <div class="portfolio-overlay-animated">
                                <div class="overlay-content">
                                    <span class="project-category">Website Design</span>
                                    <h4>Modern Restaurant Website</h4>
                                    <div class="overlay-buttons">
                                        <a href="#" class="btn-portfolio">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="portfolio-info">
                            <h5>Modern Restaurant Website</h5>
                            <p>Website Design</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4 portfolio-item" data-category="ecommerce" data-aos="fade-up" data-aos-delay="100">
                    <div class="portfolio-card-animated">
                        <div class="portfolio-image-animated">
                            <img src="https://cdn.pixabay.com/photo/2016/11/29/13/14/attractive-1869761_1280.jpg" alt="Fashion E-commerce">
                            <div class="portfolio-overlay-animated">
                                <div class="overlay-content">
                                    <span class="project-category">E-Commerce</span>
                                    <h4>Fashion E-Commerce Store</h4>
                                    <div class="overlay-buttons">
                                        <a href="#" class="btn-portfolio">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="portfolio-info">
                            <h5>Fashion E-Commerce Store</h5>
                            <p>E-Commerce Development</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4 portfolio-item" data-category="ai" data-aos="fade-up" data-aos-delay="200">
                    <div class="portfolio-card-animated">
                        <div class="portfolio-image-animated">
                            <img src="https://cdn.pixabay.com/photo/2023/01/26/22/12/ai-generated-7747180_1280.jpg" alt="AI Chatbot">
                            <div class="portfolio-overlay-animated">
                                <div class="overlay-content">
                                    <span class="project-category">AI Solution</span>
                                    <h4>Customer Service AI Bot</h4>
                                    <div class="overlay-buttons">
                                        <a href="#" class="btn-portfolio">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="portfolio-info">
                            <h5>Customer Service AI Bot</h5>
                            <p>Agentic AI Solution</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Stats Section with Light Animated Background -->
<section class="section-light-animated">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
    </div>

    <div class="container">
        <div class="row text-center">
            <div class="col-lg-3 col-md-6 mb-4" data-aos="zoom-in">
                <div class="stat-card-animated">
                    <div class="stat-icon-animated">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <h3 class="stat-number-animated" data-count="150">0</h3>
                    <p>Projects Completed</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="100">
                <div class="stat-card-animated">
                    <div class="stat-icon-animated">
                        <i class="fas fa-smile"></i>
                    </div>
                    <h3 class="stat-number-animated" data-count="120">0</h3>
                    <p>Happy Clients</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="200">
                <div class="stat-card-animated">
                    <div class="stat-icon-animated">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h3 class="stat-number-animated" data-count="15">0</h3>
                    <p>Countries Served</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="300">
                <div class="stat-card-animated">
                    <div class="stat-icon-animated">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3 class="stat-number-animated" data-count="5">0</h3>
                    <p>Years Experience</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section with Dark Animated Background -->
<section class="section-dark-animated" style="padding: 100px 0;">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
    </div>

    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center" data-aos="zoom-in">
                <div class="section-badge d-inline-flex mb-4">
                    <i class="fas fa-rocket"></i>
                    <span>Start Your Project</span>
                </div>
                <h2 style="font-size: 2.75rem; color: #fff; font-weight: 800; margin-bottom: 20px;">Want to See Your <span class="text-gradient">Project Here</span>?</h2>
                <p style="color: #94a3b8; font-size: 1.2rem; margin-bottom: 35px; max-width: 600px; margin-left: auto; margin-right: auto;">Let's work together to create something amazing for your business. Our team is ready to bring your vision to life.</p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="contact.php" class="btn-hero-primary" style="padding: 16px 35px; font-size: 1.1rem;">
                        <span>Start Your Project</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="services.php" class="btn-hero-secondary" style="padding: 16px 35px; font-size: 1.1rem;">
                        <span>Our Services</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Portfolio Filter Buttons */
.portfolio-filters-animated {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 12px;
    margin-bottom: 40px;
}

.filter-btn-animated {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #94a3b8;
    padding: 12px 24px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.filter-btn-animated:hover,
.filter-btn-animated.active {
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    border-color: transparent;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(13, 110, 253, 0.3);
}

/* Portfolio Card Animated */
.portfolio-card-animated {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.08);
    transition: all 0.4s ease;
}

.portfolio-card-animated:hover {
    transform: translateY(-10px);
    border-color: rgba(13, 110, 253, 0.3);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
}

.portfolio-image-animated {
    position: relative;
    overflow: hidden;
    height: 250px;
}

.portfolio-image-animated img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.portfolio-card-animated:hover .portfolio-image-animated img {
    transform: scale(1.1);
}

.portfolio-overlay-animated {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.95) 0%, rgba(102, 16, 242, 0.95) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.4s ease;
}

.portfolio-card-animated:hover .portfolio-overlay-animated {
    opacity: 1;
}

.overlay-content {
    text-align: center;
    padding: 20px;
    transform: translateY(20px);
    transition: transform 0.4s ease;
}

.portfolio-card-animated:hover .overlay-content {
    transform: translateY(0);
}

.project-category {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 12px;
    color: #fff;
    margin-bottom: 10px;
}

.overlay-content h4 {
    color: #fff;
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 15px;
}

.overlay-buttons {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.btn-portfolio {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #fff;
    color: #0d6efd;
    padding: 10px 20px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-portfolio:hover {
    background: #0f172a;
    color: #fff;
    transform: scale(1.05);
}

.btn-portfolio-outline {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: transparent;
    color: #fff;
    padding: 10px 20px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    border: 2px solid #fff;
    transition: all 0.3s ease;
}

.btn-portfolio-outline:hover {
    background: #fff;
    color: #0d6efd;
}

.portfolio-info {
    padding: 20px;
    text-align: center;
}

.portfolio-info h5 {
    color: #fff;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.portfolio-info p {
    color: #64748b;
    font-size: 14px;
    margin: 0;
}

/* Stat Cards Animated */
.stat-card-animated {
    background: #fff;
    border-radius: 20px;
    padding: 40px 30px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
    transition: all 0.4s ease;
}

.stat-card-animated:hover {
    transform: translateY(-10px);
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
}

.stat-icon-animated {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.stat-icon-animated i {
    font-size: 28px;
    color: #fff;
}

.stat-number-animated {
    font-size: 3rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 5px;
}

.stat-card-animated p {
    color: #64748b;
    font-size: 1rem;
    font-weight: 500;
    margin: 0;
}

@media (max-width: 768px) {
    .portfolio-filters-animated {
        gap: 8px;
    }

    .filter-btn-animated {
        padding: 10px 16px;
        font-size: 13px;
    }

    .portfolio-image-animated {
        height: 200px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
