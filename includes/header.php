<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Dawn To Web | Web Development & Agentic AI Solutions</title>
    <meta name="description" content="Dawn To Web provides professional web development, agentic AI solutions, and digital marketing services for small businesses. Transform your digital presence today.">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="top-bar">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="top-info">
                        <i class="fas fa-envelope"></i> info@dawntoweb.com
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
                <h3 class="mb-0"><span class="brand-dawn">Dawn</span><span class="brand-to">To</span><span class="brand-web">Web</span></h3>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
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
