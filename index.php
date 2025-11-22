<?php
$currentPage = 'home';
$pageTitle = 'Home';

// Include database helper
include_once 'includes/db_config.php';

// Get data from database
$settings = getAllSettings();
$services = getServices();
$testimonials = getTestimonials();

// Fallback values if database not available
$heroTitle = $settings['hero_title'] ?? 'Website Design and Development Company';
$heroSubtitle = $settings['hero_subtitle'] ?? 'Custom Web Design Services at Affordable Pricing';
$statYears = $settings['stat_years'] ?? '5';
$statProjects = $settings['stat_projects'] ?? '150';
$statClients = $settings['stat_clients'] ?? '120';
$statCountries = $settings['stat_countries'] ?? '15';

include 'includes/header.php';
?>

<section class="hero-section-new">
    <!-- Animated Background Elements -->
    <div class="hero-bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
        <div class="floating-code floating-code-1">&lt;div&gt;</div>
        <div class="floating-code floating-code-2">&lt;/&gt;</div>
        <div class="floating-code floating-code-3">{...}</div>
    </div>

    <div class="container">
        <div class="row align-items-center min-vh-85">
            <div class="col-lg-6 col-md-12 order-lg-1 order-2">
                <div class="hero-content-new">
                    <!-- Badge -->
                    <div class="hero-badge" data-aos="fade-up" data-aos-delay="100">
                        <span class="badge-icon"><i class="fas fa-rocket"></i></span>
                        <span class="badge-text">Trusted by 120+ Businesses Worldwide</span>
                    </div>

                    <!-- Main Title -->
                    <h1 class="hero-title-new" data-aos="fade-up" data-aos-delay="200">
                        We Build <span class="text-gradient">Digital Experiences</span> That Drive Results
                    </h1>

                    <!-- Subtitle -->
                    <p class="hero-subtitle-new" data-aos="fade-up" data-aos-delay="300">
                        Transform your vision into stunning, high-performance websites with our expert team. We combine cutting-edge design with powerful technology to create solutions that convert visitors into customers.
                    </p>

                    <!-- Feature Pills -->
                    <div class="hero-pills" data-aos="fade-up" data-aos-delay="400">
                        <span class="hero-pill"><i class="fas fa-bolt"></i> Lightning Fast</span>
                        <span class="hero-pill"><i class="fas fa-mobile-alt"></i> Mobile First</span>
                        <span class="hero-pill"><i class="fas fa-search"></i> SEO Ready</span>
                        <span class="hero-pill"><i class="fas fa-shield-alt"></i> Secure</span>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="hero-cta-group" data-aos="fade-up" data-aos-delay="500">
                        <a href="contact.php" class="btn-hero-primary">
                            <span>Start Your Project</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="portfolio.php" class="btn-hero-secondary">
                            <span class="play-icon"><i class="fas fa-play"></i></span>
                            <span>View Our Work</span>
                        </a>
                    </div>

                    <!-- Trust Indicators -->
                    <div class="hero-trust" data-aos="fade-up" data-aos-delay="600">
                        <div class="trust-avatars">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Client">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Client">
                            <img src="https://randomuser.me/api/portraits/men/67.jpg" alt="Client">
                            <img src="https://randomuser.me/api/portraits/women/17.jpg" alt="Client">
                            <span class="avatar-count">+116</span>
                        </div>
                        <div class="trust-text">
                            <div class="trust-stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <span>5.0</span>
                            </div>
                            <p>From 120+ Happy Clients</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-12 order-lg-2 order-1">
                <div class="hero-visual" data-aos="zoom-in" data-aos-delay="300">
                    <!-- Main Visual Card -->
                    <div class="hero-visual-card">
                        <div class="visual-browser">
                            <div class="browser-dots">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                            <div class="browser-url">
                                <i class="fas fa-lock"></i>
                                <span>yourwebsite.com</span>
                            </div>
                        </div>
                        <div class="visual-content">
                            <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&h=400&fit=crop" alt="Website Preview">
                        </div>
                    </div>

                    <!-- Floating Stats Cards -->
                    <div class="floating-stat-card stat-card-1" data-aos="fade-left" data-aos-delay="600">
                        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="stat-info">
                            <span class="stat-value">+150%</span>
                            <span class="stat-label">Traffic Growth</span>
                        </div>
                    </div>

                    <div class="floating-stat-card stat-card-2" data-aos="fade-right" data-aos-delay="700">
                        <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-info">
                            <span class="stat-value"><?php echo $statProjects; ?>+</span>
                            <span class="stat-label">Projects Delivered</span>
                        </div>
                    </div>

                    <div class="floating-stat-card stat-card-3" data-aos="fade-up" data-aos-delay="800">
                        <div class="stat-icon warning"><i class="fas fa-star"></i></div>
                        <div class="stat-info">
                            <span class="stat-value">5.0</span>
                            <span class="stat-label">Client Rating</span>
                        </div>
                    </div>

                    <!-- Tech Stack Icons -->
                    <div class="hero-tech-stack">
                        <div class="tech-icon" title="React"><i class="fab fa-react"></i></div>
                        <div class="tech-icon" title="Node.js"><i class="fab fa-node-js"></i></div>
                        <div class="tech-icon" title="Python"><i class="fab fa-python"></i></div>
                        <div class="tech-icon" title="AWS"><i class="fab fa-aws"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="scroll-indicator">
        <div class="mouse">
            <div class="wheel"></div>
        </div>
        <span>Scroll to explore</span>
    </div>
</section>

<section class="about-section py-5">
    <div class="container">
        <div class="section-title text-center mb-5" data-aos="fade-up">
            <h2>Expert Web Development and Agentic AI Services</h2>
            <p>Custom, Responsive, and SEO-optimized websites that drive results for your business.</p>
        </div>
        <div class="row">
            <div class="col-lg-6 mb-4" data-aos="fade-right">
                <p>BizNexa is a premier web development and digital marketing company focused on small businesses. Utilizing the latest technology including agentic AI, our team of skilled professionals specializes in creating result-driven websites to increase leads and sales.</p>
                <p>From strategic business websites to e-commerce and custom portal development with AI integration, we have the expertise to design and develop all types of websites for small and medium companies.</p>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="row stats-row">
                    <div class="col-6 mb-3">
                        <div class="stat-box">
                            <i class="fas fa-briefcase stat-icon"></i>
                            <h3 class="stat-number" data-count="<?php echo $statYears; ?>">0</h3>
                            <p class="stat-label">Years of Experience</p>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-box">
                            <i class="fas fa-project-diagram stat-icon"></i>
                            <h3 class="stat-number" data-count="<?php echo $statProjects; ?>">0</h3>
                            <p class="stat-label">Projects Done</p>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-box">
                            <i class="fas fa-smile stat-icon"></i>
                            <h3 class="stat-number" data-count="<?php echo $statClients; ?>">0</h3>
                            <p class="stat-label">Satisfied Clients</p>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-box">
                            <i class="fas fa-globe stat-icon"></i>
                            <h3 class="stat-number" data-count="<?php echo $statCountries; ?>">0</h3>
                            <p class="stat-label">Countries Served</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="services-section py-5">
    <div class="container">
        <div class="section-title text-center mb-5" data-aos="fade-up">
            <h2>Our Featured Services</h2>
            <p>Elevate Your Online Presence with Our Expertise – You Dream It, We Build It</p>
        </div>
        <?php if (!empty($services)): ?>
        <?php
        // Calculate slides needed for different screen sizes
        $totalServices = count($services);
        ?>
        <!-- Desktop View: 3 services per slide -->
        <div id="servicesCarouselDesktop" class="carousel slide d-none d-md-block" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                $desktopPerSlide = 3;
                $desktopSlides = ceil($totalServices / $desktopPerSlide);
                for ($slide = 0; $slide < $desktopSlides; $slide++):
                ?>
                <div class="carousel-item <?php echo $slide === 0 ? 'active' : ''; ?>">
                    <div class="row justify-content-center">
                        <?php for ($i = 0; $i < $desktopPerSlide; $i++):
                            $index = $slide * $desktopPerSlide + $i;
                            if ($index >= $totalServices) break;
                            $service = $services[$index];
                        ?>
                        <div class="col-lg-4 col-md-4 mb-4">
                            <div class="service-card">
                                <div class="service-icon">
                                    <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                                </div>
                                <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                                <p><?php echo htmlspecialchars($service['short_description']); ?></p>
                                <a href="services.php" class="btn btn-outline-primary">Learn More</a>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
            <?php if ($desktopSlides > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#servicesCarouselDesktop" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#servicesCarouselDesktop" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
            <div class="carousel-indicators">
                <?php for ($s = 0; $s < $desktopSlides; $s++): ?>
                <button type="button" data-bs-target="#servicesCarouselDesktop" data-bs-slide-to="<?php echo $s; ?>" <?php echo $s === 0 ? 'class="active" aria-current="true"' : ''; ?> aria-label="Slide <?php echo $s + 1; ?>"></button>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Mobile View: 1 service per slide -->
        <div id="servicesCarouselMobile" class="carousel slide d-md-none" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($services as $idx => $service): ?>
                <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                    <div class="row justify-content-center">
                        <div class="col-10 col-sm-8 mb-4">
                            <div class="service-card">
                                <div class="service-icon">
                                    <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                                </div>
                                <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                                <p><?php echo htmlspecialchars($service['short_description']); ?></p>
                                <a href="services.php" class="btn btn-outline-primary">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if ($totalServices > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#servicesCarouselMobile" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#servicesCarouselMobile" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
            <div class="carousel-indicators">
                <?php for ($s = 0; $s < $totalServices; $s++): ?>
                <button type="button" data-bs-target="#servicesCarouselMobile" data-bs-slide-to="<?php echo $s; ?>" <?php echo $s === 0 ? 'class="active" aria-current="true"' : ''; ?> aria-label="Slide <?php echo $s + 1; ?>"></button>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <!-- Fallback static services if database unavailable -->
        <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 col-6 mb-4">
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-laptop-code"></i></div>
                    <h4>Website Design</h4>
                    <p>Faster loading secured website designing service.</p>
                    <a href="services.php" class="btn btn-outline-primary">Learn More</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-6 mb-4">
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-shopping-cart"></i></div>
                    <h4>Ecommerce Website</h4>
                    <p>Premium Quality E-Commerce Development Services.</p>
                    <a href="services.php" class="btn btn-outline-primary">Learn More</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 d-none d-lg-block mb-4">
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-code"></i></div>
                    <h4>Web Development</h4>
                    <p>Web portal development with the latest technology.</p>
                    <a href="services.php" class="btn btn-outline-primary">Contact Us</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php $technologies = getTechnologies(); ?>
<section class="technology-section py-5 bg-light">
    <div class="container">
        <div class="section-title text-center mb-5" data-aos="fade-up">
            <h2>Technology We Use to Build Secure Website & Application</h2>
            <p>We use latest technologies & tools to build secure & updated website & Application</p>
        </div>
        <div class="row justify-content-center align-items-center">
            <?php if (!empty($technologies)): ?>
                <?php $delay = 100; foreach ($technologies as $tech): ?>
                <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <div class="tech-logo">
                        <i class="<?php echo htmlspecialchars($tech['icon']); ?>" style="color: <?php echo htmlspecialchars($tech['color']); ?>;"></i>
                        <p class="tech-name mt-2"><?php echo htmlspecialchars($tech['name']); ?></p>
                    </div>
                </div>
                <?php $delay = $delay >= 350 ? 100 : $delay + 50; endforeach; ?>
            <?php else: ?>
                <!-- Fallback static technologies -->
                <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="tech-logo">
                        <i class="fab fa-html5" style="color: #E34F26;"></i>
                        <p class="tech-name mt-2">HTML5</p>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="150">
                    <div class="tech-logo">
                        <i class="fab fa-css3-alt" style="color: #1572B6;"></i>
                        <p class="tech-name mt-2">CSS3</p>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="tech-logo">
                        <i class="fab fa-js-square" style="color: #F7DF1E;"></i>
                        <p class="tech-name mt-2">JavaScript</p>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="250">
                    <div class="tech-logo">
                        <i class="fab fa-php" style="color: #777BB4;"></i>
                        <p class="tech-name mt-2">PHP</p>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="tech-logo">
                        <i class="fab fa-wordpress" style="color: #21759B;"></i>
                        <p class="tech-name mt-2">WordPress</p>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="350">
                    <div class="tech-logo">
                        <i class="fab fa-bootstrap" style="color: #7952B3;"></i>
                        <p class="tech-name mt-2">Bootstrap</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="testimonials-section py-5 bg-light">
    <div class="container">
        <div class="section-title text-center mb-5" data-aos="fade-up">
            <h2>What Our Clients Say</h2>
            <p>Trusted by businesses worldwide</p>
        </div>

        <?php if (!empty($testimonials)): ?>
        <!-- Desktop View: Static 3 columns -->
        <div class="row d-none d-lg-flex">
            <?php $delay = 100; foreach (array_slice($testimonials, 0, 3) as $testimonial): ?>
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                <div class="testimonial-card">
                    <div class="testimonial-rating mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="testimonial-text">
                        <p>"<?php echo htmlspecialchars($testimonial['testimonial']); ?>"</p>
                    </div>
                    <div class="testimonial-author">
                        <h5><?php echo htmlspecialchars($testimonial['client_name']); ?></h5>
                        <span><?php echo htmlspecialchars($testimonial['client_position'] ?? ''); ?><?php echo $testimonial['client_company'] ? ', ' . htmlspecialchars($testimonial['client_company']) : ''; ?></span>
                    </div>
                </div>
            </div>
            <?php $delay += 100; endforeach; ?>
        </div>

        <!-- Mobile/Tablet View: Carousel Slider -->
        <div id="testimonialsCarousel" class="carousel slide d-lg-none" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php $first = true; foreach ($testimonials as $testimonial): ?>
                <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
                    <div class="testimonial-card">
                        <div class="testimonial-rating mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="testimonial-text">
                            <p>"<?php echo htmlspecialchars($testimonial['testimonial']); ?>"</p>
                        </div>
                        <div class="testimonial-author">
                            <h5><?php echo htmlspecialchars($testimonial['client_name']); ?></h5>
                            <span><?php echo htmlspecialchars($testimonial['client_position'] ?? ''); ?><?php echo $testimonial['client_company'] ? ', ' . htmlspecialchars($testimonial['client_company']) : ''; ?></span>
                        </div>
                    </div>
                </div>
                <?php $first = false; endforeach; ?>
            </div>
            <?php if (count($testimonials) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
            <div class="carousel-indicators">
                <?php for ($t = 0; $t < count($testimonials); $t++): ?>
                <button type="button" data-bs-target="#testimonialsCarousel" data-bs-slide-to="<?php echo $t; ?>" <?php echo $t === 0 ? 'class="active" aria-current="true"' : ''; ?> aria-label="Slide <?php echo $t + 1; ?>"></button>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <!-- Fallback static testimonials if database unavailable -->
        <div class="row d-none d-lg-flex">
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        <p>"BizNexa transformed our online presence completely. Their expertise in web development and AI integration is outstanding. Highly recommended!"</p>
                    </div>
                    <div class="testimonial-author">
                        <h5>John Smith</h5>
                        <span>CEO, TechStart Inc.</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        <p>"Excellent service! The team delivered our e-commerce website on time with amazing features. Our sales have increased by 40% since launch."</p>
                    </div>
                    <div class="testimonial-author">
                        <h5>Sarah Johnson</h5>
                        <span>Founder, Fashion Hub</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        <p>"Professional, dedicated, and responsive. BizNexa created a beautiful website for our business that truly represents our brand."</p>
                    </div>
                    <div class="testimonial-author">
                        <h5>Michael Chen</h5>
                        <span>Owner, Local Bistro</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="cta-section py-5">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="zoom-in">
                <h2 class="text-white mb-3">Ready to Transform Your Digital Presence?</h2>
                <p class="text-white mb-4">Contact us today for a free consultation and let's discuss how we can help your business grow.</p>
                <a href="contact.php" class="btn btn-light btn-lg">Get Started Now</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
