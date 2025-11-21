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

<section class="page-header">
    <div class="container">
        <h1 data-aos="fade-up">Our Portfolio</h1>
        <p data-aos="fade-up" data-aos-delay="100">Showcasing Our Best Work</p>
    </div>
</section>

<section class="portfolio-section py-5">
    <div class="container">
        <div class="section-title text-center mb-5" data-aos="fade-up">
            <h2>Our Recent Projects</h2>
            <p>We are creating websites for businesses, NGOs & portfolios. From E-commerce to Custom Portals, we've got you covered.</p>
        </div>

        <div class="portfolio-filters text-center mb-5" data-aos="fade-up">
            <button class="filter-btn active" data-filter="all">Show All</button>
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                <button class="filter-btn" data-filter="<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $category))); ?>"><?php echo htmlspecialchars(ucwords($category)); ?></button>
                <?php endforeach; ?>
            <?php else: ?>
                <button class="filter-btn" data-filter="website">Website</button>
                <button class="filter-btn" data-filter="ecommerce">E-Commerce</button>
                <button class="filter-btn" data-filter="webportal">Web Portal</button>
                <button class="filter-btn" data-filter="ai">AI Solutions</button>
            <?php endif; ?>
        </div>

        <div class="row portfolio-grid">
            <?php if (!empty($projects)): ?>
                <?php $delay = 0; foreach ($projects as $project): ?>
                <div class="col-lg-4 col-md-6 mb-4 portfolio-item" data-category="<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $project['category'] ?? 'website'))); ?>" data-aos="fade-up" <?php echo $delay > 0 ? 'data-aos-delay="' . $delay . '"' : ''; ?>>
                    <div class="portfolio-card">
                        <div class="portfolio-image">
                            <?php if (!empty($project['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($project['image_url']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>">
                            <?php else: ?>
                            <img src="https://cdn.pixabay.com/photo/2020/05/18/16/17/social-media-5187243_1280.png" alt="<?php echo htmlspecialchars($project['title']); ?>">
                            <?php endif; ?>
                            <div class="portfolio-overlay">
                                <h4><?php echo htmlspecialchars($project['title']); ?></h4>
                                <p><?php echo htmlspecialchars($project['category'] ?? 'Web Project'); ?></p>
                                <a href="project.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-light mt-2">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $delay = $delay >= 200 ? 0 : $delay + 100; endforeach; ?>
            <?php else: ?>
                <!-- Fallback static portfolio items -->
                <div class="col-lg-4 col-md-6 mb-4 portfolio-item" data-category="website" data-aos="fade-up">
                    <div class="portfolio-card">
                        <div class="portfolio-image">
                            <img src="https://cdn.pixabay.com/photo/2020/05/18/16/17/social-media-5187243_1280.png" alt="Restaurant Website">
                            <div class="portfolio-overlay">
                                <h4>Modern Restaurant Website</h4>
                                <p>Website Design</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4 portfolio-item" data-category="ecommerce" data-aos="fade-up" data-aos-delay="100">
                    <div class="portfolio-card">
                        <div class="portfolio-image">
                            <img src="https://cdn.pixabay.com/photo/2016/11/29/13/14/attractive-1869761_1280.jpg" alt="Fashion E-commerce">
                            <div class="portfolio-overlay">
                                <h4>Fashion E-Commerce Store</h4>
                                <p>E-Commerce Development</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4 portfolio-item" data-category="ai" data-aos="fade-up" data-aos-delay="200">
                    <div class="portfolio-card">
                        <div class="portfolio-image">
                            <img src="https://cdn.pixabay.com/photo/2023/01/26/22/12/ai-generated-7747180_1280.jpg" alt="AI Chatbot">
                            <div class="portfolio-overlay">
                                <h4>Customer Service AI Bot</h4>
                                <p>Agentic AI Solution</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="cta-section py-5">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="zoom-in">
                <h2 class="text-white mb-3">Want to See Your Project Here?</h2>
                <p class="text-white mb-4">Let's work together to create something amazing for your business.</p>
                <a href="contact.php" class="btn btn-light btn-lg">Start Your Project</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
