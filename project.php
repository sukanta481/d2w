<?php
$currentPage = 'portfolio';

// Include database helper
include_once 'includes/db_config.php';

// Get project ID from URL
$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get project details
$project = getProject($projectId);

// If project not found, redirect to portfolio
if (!$project) {
    header('Location: portfolio.php');
    exit;
}

$pageTitle = htmlspecialchars($project['title']);
$settings = getAllSettings();

// Get client testimonial if available
$testimonial = null;
if (!empty($project['client_name'])) {
    $testimonial = getTestimonialByClient($project['client_name']);
}

// Get related projects
$relatedProjects = getRelatedProjects($project['id'], $project['category'], 3);

// Parse technologies
$technologies = [];
if (!empty($project['technologies'])) {
    $technologies = array_map('trim', explode(',', $project['technologies']));
}

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb" data-aos="fade-up">
            <ol class="breadcrumb justify-content-center">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="portfolio.php">Portfolio</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($project['title']); ?></li>
            </ol>
        </nav>
        <h1 data-aos="fade-up" data-aos-delay="100"><?php echo htmlspecialchars($project['title']); ?></h1>
        <p data-aos="fade-up" data-aos-delay="200"><?php echo htmlspecialchars($project['category']); ?></p>
    </div>
</section>

<section class="project-detail-section py-5">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8 mb-4">
                <!-- Project Image -->
                <div class="project-main-image mb-4" data-aos="fade-up">
                    <?php if (!empty($project['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($project['image_url']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" class="img-fluid rounded shadow">
                    <?php else: ?>
                    <img src="https://cdn.pixabay.com/photo/2020/05/18/16/17/social-media-5187243_1280.png" alt="<?php echo htmlspecialchars($project['title']); ?>" class="img-fluid rounded shadow">
                    <?php endif; ?>
                </div>

                <!-- Project Description -->
                <div class="project-description" data-aos="fade-up" data-aos-delay="100">
                    <h2>Project Overview</h2>
                    <div class="description-content">
                        <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                    </div>
                </div>

                <!-- Technologies Used -->
                <?php if (!empty($technologies)): ?>
                <div class="project-technologies mt-4" data-aos="fade-up" data-aos-delay="200">
                    <h3>Technologies Used</h3>
                    <div class="tech-tags">
                        <?php foreach ($technologies as $tech): ?>
                        <span class="tech-tag"><?php echo htmlspecialchars($tech); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Project Info Card -->
                <div class="project-info-card mb-4" data-aos="fade-left">
                    <h3>Project Details</h3>
                    <ul class="project-info-list">
                        <?php if (!empty($project['client_name'])): ?>
                        <li>
                            <i class="fas fa-user"></i>
                            <div>
                                <span class="info-label">Client</span>
                                <span class="info-value"><?php echo htmlspecialchars($project['client_name']); ?></span>
                            </div>
                        </li>
                        <?php endif; ?>
                        <li>
                            <i class="fas fa-folder"></i>
                            <div>
                                <span class="info-label">Category</span>
                                <span class="info-value"><?php echo htmlspecialchars($project['category']); ?></span>
                            </div>
                        </li>
                        <?php if (!empty($project['completion_date'])): ?>
                        <li>
                            <i class="fas fa-calendar-check"></i>
                            <div>
                                <span class="info-label">Completed</span>
                                <span class="info-value"><?php echo date('F Y', strtotime($project['completion_date'])); ?></span>
                            </div>
                        </li>
                        <?php endif; ?>
                    </ul>

                    <?php if (!empty($project['project_url'])): ?>
                    <a href="<?php echo htmlspecialchars($project['project_url']); ?>" target="_blank" class="btn btn-primary btn-lg w-100 mt-3">
                        <i class="fas fa-external-link-alt me-2"></i>Visit Live Site
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Client Testimonial -->
                <?php if ($testimonial): ?>
                <div class="client-testimonial-card" data-aos="fade-left" data-aos-delay="100">
                    <h3>Client Feedback</h3>
                    <div class="testimonial-content">
                        <div class="quote-icon">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        <p class="testimonial-text"><?php echo htmlspecialchars($testimonial['testimonial']); ?></p>
                        <div class="testimonial-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star<?php echo $i <= $testimonial['rating'] ? '' : '-empty'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="testimonial-author">
                            <?php if (!empty($testimonial['client_photo'])): ?>
                            <img src="<?php echo htmlspecialchars($testimonial['client_photo']); ?>" alt="<?php echo htmlspecialchars($testimonial['client_name']); ?>" class="author-photo">
                            <?php else: ?>
                            <div class="author-avatar">
                                <?php echo strtoupper(substr($testimonial['client_name'], 0, 1)); ?>
                            </div>
                            <?php endif; ?>
                            <div class="author-info">
                                <strong><?php echo htmlspecialchars($testimonial['client_name']); ?></strong>
                                <?php if (!empty($testimonial['client_position'])): ?>
                                <span><?php echo htmlspecialchars($testimonial['client_position']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($testimonial['client_company'])): ?>
                                <span><?php echo htmlspecialchars($testimonial['client_company']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- CTA Card -->
                <div class="project-cta-card mt-4" data-aos="fade-left" data-aos-delay="200">
                    <h4>Like What You See?</h4>
                    <p>Let's create something amazing for your business too!</p>
                    <a href="contact.php" class="btn btn-outline-primary w-100">Start Your Project</a>
                </div>
            </div>
        </div>

        <!-- Related Projects -->
        <?php if (!empty($relatedProjects)): ?>
        <div class="related-projects-section mt-5 pt-5 border-top">
            <h2 class="text-center mb-4" data-aos="fade-up">Related Projects</h2>
            <div class="row">
                <?php foreach ($relatedProjects as $index => $related): ?>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                    <div class="portfolio-card">
                        <div class="portfolio-image">
                            <?php if (!empty($related['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($related['image_url']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>">
                            <?php else: ?>
                            <img src="https://cdn.pixabay.com/photo/2020/05/18/16/17/social-media-5187243_1280.png" alt="<?php echo htmlspecialchars($related['title']); ?>">
                            <?php endif; ?>
                            <div class="portfolio-overlay">
                                <h4><?php echo htmlspecialchars($related['title']); ?></h4>
                                <p><?php echo htmlspecialchars($related['category']); ?></p>
                                <a href="project.php?id=<?php echo $related['id']; ?>" class="btn btn-sm btn-light mt-2">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="cta-section py-5">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="zoom-in">
                <h2 class="text-white mb-3">Ready to Start Your Project?</h2>
                <p class="text-white mb-4">Let's work together to bring your vision to life.</p>
                <a href="contact.php" class="btn btn-light btn-lg">Get In Touch</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
