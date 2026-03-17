<?php
$currentPage = '404';
include_once 'includes/db_config.php';
$settings = getAllSettings();

$pageMeta = [
    'title' => 'Page Not Found',
    'description' => 'The page you are looking for does not exist. Return to BizNexa homepage.',
    'canonical' => '/404.php',
    'schema' => 'WebPage',
];

include 'includes/header.php';
?>

<section class="section-dark-animated" style="padding: 150px 0 100px;">
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
    </div>
    <div class="container text-center">
        <h1 style="font-size: 8rem; font-weight: 900; color: rgba(255,255,255,0.1); margin-bottom: 0;">404</h1>
        <h2 style="color: #fff; font-size: 2rem; font-weight: 700; margin-bottom: 20px;">Page Not Found</h2>
        <p style="color: #94a3b8; font-size: 1.1rem; max-width: 500px; margin: 0 auto 30px;">Sorry, the page you're looking for doesn't exist or has been moved.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="index.php" class="btn-hero-primary">
                <span>Back to Home</span>
                <i class="fas fa-home"></i>
            </a>
            <a href="contact.php" class="btn-hero-secondary">
                <span>Contact Us</span>
            </a>
        </div>
        <div class="mt-5">
            <h5 style="color: #fff; margin-bottom: 15px;">Popular Pages</h5>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="services.php" style="color: #0d6efd; text-decoration: none; font-weight: 500;">Services</a>
                <a href="portfolio.php" style="color: #0d6efd; text-decoration: none; font-weight: 500;">Portfolio</a>
                <a href="blog.php" style="color: #0d6efd; text-decoration: none; font-weight: 500;">Blog</a>
                <a href="about.php" style="color: #0d6efd; text-decoration: none; font-weight: 500;">About</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
