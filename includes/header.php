<?php
// Include SEO helper
include_once __DIR__ . '/seo.php';

// Default pageMeta if not set by page
if (!isset($pageMeta)) {
    $pageMeta = [];
}
if (!isset($settings)) {
    include_once __DIR__ . '/db_config.php';
    $settings = getAllSettings();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo renderMetaTags($pageMeta); ?>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="apple-touch-icon" href="assets/images/favicon.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Swiper CSS for Mobile Sliders -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <?php echo renderJsonLd($pageMeta, $settings); ?>
</head>
<body>
    <div class="top-bar">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="top-info">
                        <a href="mailto:info@biznexa.tech" class="top-info-link"><i class="fas fa-envelope"></i> info@biznexa.tech</a>
                        <span class="ms-4"><a href="tel:+919433215443" class="top-info-link"><i class="fas fa-phone"></i> Call: +91 94332 15443</a></span>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <a href="contact.php" class="btn btn-sm btn-success">Get Started</a>
                </div>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/images/logo.png" alt="BizNexa - Web Development, AI & Digital Marketing" height="60">
            </a>
            <!-- Mobile menu toggle button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-label="Toggle navigation menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Desktop menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'home') ? 'active' : ''; ?>" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo ($currentPage == 'services') ? 'active' : ''; ?>" href="services.php" id="servicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Services
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="servicesDropdown">
                            <li><a class="dropdown-item" href="services.php#web-development"><i class="fas fa-code me-2"></i>Web Development</a></li>
                            <li><a class="dropdown-item" href="services.php#ai-automation"><i class="fas fa-robot me-2"></i>AI & Automation</a></li>
                            <li><a class="dropdown-item" href="services.php#digital-marketing"><i class="fas fa-chart-line me-2"></i>Digital Marketing</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'portfolio') ? 'active' : ''; ?>" href="portfolio.php">Portfolio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'about') ? 'active' : ''; ?>" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'blog') ? 'active' : ''; ?>" href="blog.php">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'contact') ? 'active' : ''; ?>" href="contact.php">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Mobile Off-canvas Menu -->
    <div class="offcanvas offcanvas-start mobile-menu" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
        <div class="offcanvas-header mobile-menu-header">
            <a href="index.php">
                <img src="assets/images/logo.png" alt="BizNexa" height="50">
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mobile-menu-body">
            <ul class="mobile-nav-list">
                <li class="mobile-nav-item">
                    <a href="index.php" class="mobile-nav-link <?php echo ($currentPage == 'home') ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="mobile-nav-item mobile-dropdown">
                    <a href="#" class="mobile-nav-link <?php echo ($currentPage == 'services') ? 'active' : ''; ?>" onclick="toggleMobileDropdown(event)">
                        <i class="fas fa-cogs"></i> Services
                        <i class="fas fa-chevron-down mobile-dropdown-arrow"></i>
                    </a>
                    <ul class="mobile-submenu">
                        <li><a href="services.php#web-development" class="mobile-submenu-link"><i class="fas fa-code me-2"></i>Web Development</a></li>
                        <li><a href="services.php#ai-automation" class="mobile-submenu-link"><i class="fas fa-robot me-2"></i>AI & Automation</a></li>
                        <li><a href="services.php#digital-marketing" class="mobile-submenu-link"><i class="fas fa-chart-line me-2"></i>Digital Marketing</a></li>
                    </ul>
                </li>
                <li class="mobile-nav-item">
                    <a href="portfolio.php" class="mobile-nav-link <?php echo ($currentPage == 'portfolio') ? 'active' : ''; ?>">
                        <i class="fas fa-briefcase"></i> Portfolio
                    </a>
                </li>
                <li class="mobile-nav-item">
                    <a href="about.php" class="mobile-nav-link <?php echo ($currentPage == 'about') ? 'active' : ''; ?>">
                        <i class="fas fa-building"></i> About
                    </a>
                </li>
                <li class="mobile-nav-item">
                    <a href="blog.php" class="mobile-nav-link <?php echo ($currentPage == 'blog') ? 'active' : ''; ?>">
                        <i class="fas fa-newspaper"></i> Blog
                    </a>
                </li>
                <li class="mobile-nav-item">
                    <a href="contact.php" class="mobile-nav-link <?php echo ($currentPage == 'contact') ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i> Contact
                    </a>
                </li>
            </ul>

            <!-- Get Started Button -->
            <div class="mobile-menu-cta">
                <a href="contact.php" class="mobile-cta-btn">
                    <i class="fas fa-rocket"></i>Get Started
                </a>
            </div>

            <!-- Contact Info -->
            <div class="mobile-menu-contact">
                <p><i class="fas fa-envelope"></i> info@biznexa.tech</p>
                <p><i class="fas fa-phone"></i> +91 94332 15443</p>
            </div>
        </div>
    </div>

    <script>
    function toggleMobileDropdown(event) {
        event.preventDefault();
        const parent = event.currentTarget.parentElement;
        const submenu = parent.querySelector('.mobile-submenu');
        const arrow = parent.querySelector('.mobile-dropdown-arrow');

        if (submenu.style.maxHeight === '0px' || submenu.style.maxHeight === '') {
            submenu.style.maxHeight = '200px';
            arrow.style.transform = 'rotate(180deg)';
        } else {
            submenu.style.maxHeight = '0px';
            arrow.style.transform = 'rotate(0deg)';
        }
    }
    </script>
