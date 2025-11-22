<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>BizNexa | Web Development & Agentic AI Solutions</title>
    <meta name="description" content="BizNexa provides professional web development, agentic AI solutions, and digital marketing services for small businesses. Transform your digital presence today.">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="apple-touch-icon" href="assets/images/favicon.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="top-bar">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="top-info">
                        <i class="fas fa-envelope"></i> info@biznexa.tech
                        <span class="ms-4"><i class="fas fa-phone"></i> Call: +91 94332 15443</span>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <a href="#" class="btn btn-sm btn-success">Get Started</a>
                </div>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/images/logo.png" alt="BizNexa" height="60">
            </a>
            <!-- Mobile menu toggle button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Desktop menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'home') ? 'active' : ''; ?>" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'services') ? 'active' : ''; ?>" href="services.php">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'portfolio') ? 'active' : ''; ?>" href="portfolio.php">Portfolio</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo (in_array($currentPage, ['about', 'blog'])) ? 'active' : ''; ?>" href="#" id="companyDropdown" role="button" data-bs-toggle="dropdown">
                            Company
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="about.php">About Us</a></li>
                            <li><a class="dropdown-item" href="blog.php">Blog</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'contact') ? 'active' : ''; ?>" href="contact.php">Contact Us</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Mobile Off-canvas Menu (slides from left) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel" style="width: 300px;">
        <div class="offcanvas-header" style="padding: 20px 25px; border-bottom: 1px solid #eee; background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);">
            <a href="index.php">
                <img src="assets/images/logo.png" alt="BizNexa" height="50">
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body" style="padding: 0; display: flex; flex-direction: column;">
            <ul style="list-style: none; padding: 15px 0; margin: 0; flex: 1;">
                <li style="border-bottom: 1px solid #f0f0f0;">
                    <a href="index.php" style="display: flex; align-items: center; padding: 15px 25px; color: <?php echo ($currentPage == 'home') ? '#0d6efd' : '#2d3748'; ?>; text-decoration: none; font-size: 15px; font-weight: 500; background: <?php echo ($currentPage == 'home') ? 'linear-gradient(135deg, #e8f4ff 0%, #dbeafe 100%)' : 'transparent'; ?>; border-left: <?php echo ($currentPage == 'home') ? '4px solid #0d6efd' : 'none'; ?>;">
                        <i class="fas fa-home" style="width: 24px; margin-right: 12px; font-size: 16px; color: #0d6efd;"></i> Home
                    </a>
                </li>
                <li style="border-bottom: 1px solid #f0f0f0;">
                    <a href="services.php" style="display: flex; align-items: center; padding: 15px 25px; color: <?php echo ($currentPage == 'services') ? '#0d6efd' : '#2d3748'; ?>; text-decoration: none; font-size: 15px; font-weight: 500; background: <?php echo ($currentPage == 'services') ? 'linear-gradient(135deg, #e8f4ff 0%, #dbeafe 100%)' : 'transparent'; ?>; border-left: <?php echo ($currentPage == 'services') ? '4px solid #0d6efd' : 'none'; ?>;">
                        <i class="fas fa-cogs" style="width: 24px; margin-right: 12px; font-size: 16px; color: #0d6efd;"></i> Services
                    </a>
                </li>
                <li style="border-bottom: 1px solid #f0f0f0;">
                    <a href="portfolio.php" style="display: flex; align-items: center; padding: 15px 25px; color: <?php echo ($currentPage == 'portfolio') ? '#0d6efd' : '#2d3748'; ?>; text-decoration: none; font-size: 15px; font-weight: 500; background: <?php echo ($currentPage == 'portfolio') ? 'linear-gradient(135deg, #e8f4ff 0%, #dbeafe 100%)' : 'transparent'; ?>; border-left: <?php echo ($currentPage == 'portfolio') ? '4px solid #0d6efd' : 'none'; ?>;">
                        <i class="fas fa-briefcase" style="width: 24px; margin-right: 12px; font-size: 16px; color: #0d6efd;"></i> Portfolio
                    </a>
                </li>
                <li class="mobile-dropdown" style="border-bottom: 1px solid #f0f0f0;">
                    <a href="#" onclick="toggleMobileDropdown(event)" style="display: flex; align-items: center; padding: 15px 25px; color: <?php echo (in_array($currentPage, ['about', 'blog'])) ? '#0d6efd' : '#2d3748'; ?>; text-decoration: none; font-size: 15px; font-weight: 500;">
                        <i class="fas fa-building" style="width: 24px; margin-right: 12px; font-size: 16px; color: #0d6efd;"></i> Company
                        <i class="fas fa-chevron-down dropdown-arrow" style="margin-left: auto; font-size: 12px; transition: transform 0.3s ease;"></i>
                    </a>
                    <ul class="mobile-submenu" style="list-style: none; padding: 0; margin: 0; background: #f8f9fa; max-height: 0; overflow: hidden; transition: max-height 0.3s ease;">
                        <li><a href="about.php" style="display: block; padding: 12px 25px 12px 60px; color: #4a5568; text-decoration: none; font-size: 14px; border-bottom: 1px solid #eee;">About Us</a></li>
                        <li><a href="blog.php" style="display: block; padding: 12px 25px 12px 60px; color: #4a5568; text-decoration: none; font-size: 14px;">Blog</a></li>
                    </ul>
                </li>
                <li style="border-bottom: 1px solid #f0f0f0;">
                    <a href="contact.php" style="display: flex; align-items: center; padding: 15px 25px; color: <?php echo ($currentPage == 'contact') ? '#0d6efd' : '#2d3748'; ?>; text-decoration: none; font-size: 15px; font-weight: 500; background: <?php echo ($currentPage == 'contact') ? 'linear-gradient(135deg, #e8f4ff 0%, #dbeafe 100%)' : 'transparent'; ?>; border-left: <?php echo ($currentPage == 'contact') ? '4px solid #0d6efd' : 'none'; ?>;">
                        <i class="fas fa-envelope" style="width: 24px; margin-right: 12px; font-size: 16px; color: #0d6efd;"></i> Contact Us
                    </a>
                </li>
            </ul>

            <!-- Get Started Button in Mobile Menu -->
            <div style="padding: 20px 25px; border-top: 1px solid #eee;">
                <a href="contact.php" style="display: flex; align-items: center; justify-content: center; width: 100%; padding: 14px 24px; background: linear-gradient(135deg, #10B981 0%, #059669 100%); border: none; border-radius: 50px; color: #fff; font-size: 15px; font-weight: 600; text-decoration: none; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
                    <i class="fas fa-rocket" style="margin-right: 10px;"></i>Get Started
                </a>
            </div>

            <!-- Contact Info in Mobile Menu -->
            <div style="padding: 20px 25px; background: #f8f9fa; border-top: 1px solid #eee;">
                <p style="margin: 0 0 10px 0; font-size: 13px; color: #4a5568; display: flex; align-items: center;">
                    <i class="fas fa-envelope" style="width: 20px; margin-right: 10px; color: #10B981;"></i> info@biznexa.tech
                </p>
                <p style="margin: 0; font-size: 13px; color: #4a5568; display: flex; align-items: center;">
                    <i class="fas fa-phone" style="width: 20px; margin-right: 10px; color: #10B981;"></i> +91 94332 15443
                </p>
            </div>
        </div>
    </div>

    <script>
    function toggleMobileDropdown(event) {
        event.preventDefault();
        const parent = event.currentTarget.parentElement;
        const submenu = parent.querySelector('.mobile-submenu');
        const arrow = parent.querySelector('.dropdown-arrow');

        if (submenu.style.maxHeight === '0px' || submenu.style.maxHeight === '') {
            submenu.style.maxHeight = '200px';
            arrow.style.transform = 'rotate(180deg)';
        } else {
            submenu.style.maxHeight = '0px';
            arrow.style.transform = 'rotate(0deg)';
        }
    }
    </script>
