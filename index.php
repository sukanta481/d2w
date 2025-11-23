<?php
$currentPage = 'home';
$pageTitle = 'Home';

// Include database helper
include_once 'includes/db_config.php';

// Get data from database
$settings = getAllSettings();
$services = getServices();
$testimonials = getTestimonials();
$technologies = getTechnologies();

// Fallback values if database not available
$heroTitle = $settings['hero_title'] ?? 'Website Design and Development Company';
$heroSubtitle = $settings['hero_subtitle'] ?? 'Custom Web Design Services at Affordable Pricing';
$statYears = $settings['stat_years'] ?? '5';
$statProjects = $settings['stat_projects'] ?? '150';
$statClients = $settings['stat_clients'] ?? '120';
$statCountries = $settings['stat_countries'] ?? '15';

include 'includes/header.php';
?>

<!-- Hero Section -->
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

<!-- About Section with Light Animated Background -->
<section class="section-light-animated" style="padding: 100px 0;">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
    </div>

    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4" data-aos="fade-right">
                <div class="about-content-home">
                    <div class="section-badge d-inline-flex mb-3" style="background: rgba(13, 110, 253, 0.1); border-color: rgba(13, 110, 253, 0.2);">
                        <i class="fas fa-info-circle" style="color: #0d6efd;"></i>
                        <span style="color: #0d6efd;">About Us</span>
                    </div>
                    <h2 style="color: #1e293b; font-size: 2.5rem; font-weight: 800; margin-bottom: 20px;">Expert Web Development & <span style="color: #0d6efd;">Agentic AI</span> Services</h2>
                    <p style="color: #64748b; font-size: 1.05rem; line-height: 1.8; margin-bottom: 15px;">BizNexa is a premier web development and digital marketing company focused on small businesses. Utilizing the latest technology including agentic AI, our team of skilled professionals specializes in creating result-driven websites to increase leads and sales.</p>
                    <p style="color: #64748b; font-size: 1.05rem; line-height: 1.8; margin-bottom: 25px;">From strategic business websites to e-commerce and custom portal development with AI integration, we have the expertise to design and develop all types of websites for small and medium companies.</p>
                    <a href="about.php" class="btn-learn-more">
                        <span>Learn More About Us</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-6 mb-4" data-aos="fade-left">
                <div class="stats-grid-home">
                    <div class="stat-item-home" data-aos="zoom-in" data-aos-delay="100">
                        <div class="stat-icon-home">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h3 class="stat-number" data-count="<?php echo $statYears; ?>">0</h3>
                        <p>Years Experience</p>
                    </div>
                    <div class="stat-item-home" data-aos="zoom-in" data-aos-delay="200">
                        <div class="stat-icon-home">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <h3 class="stat-number" data-count="<?php echo $statProjects; ?>">0</h3>
                        <p>Projects Done</p>
                    </div>
                    <div class="stat-item-home" data-aos="zoom-in" data-aos-delay="300">
                        <div class="stat-icon-home">
                            <i class="fas fa-smile"></i>
                        </div>
                        <h3 class="stat-number" data-count="<?php echo $statClients; ?>">0</h3>
                        <p>Happy Clients</p>
                    </div>
                    <div class="stat-item-home" data-aos="zoom-in" data-aos-delay="400">
                        <div class="stat-icon-home">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h3 class="stat-number" data-count="<?php echo $statCountries; ?>">0</h3>
                        <p>Countries Served</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section with Dark Animated Background -->
<section class="section-dark-animated" style="padding: 100px 0;">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
        <div class="bg-shape bg-shape-3"></div>
    </div>

    <!-- Floating Code Elements -->
    <div class="floating-elements">
        <div class="floating-element">&lt;code/&gt;</div>
        <div class="floating-element">{...}</div>
        <div class="floating-element">&lt;div&gt;</div>
        <div class="floating-element">( )</div>
    </div>

    <div class="container">
        <!-- Section Title -->
        <div class="section-title-animated" data-aos="fade-up">
            <div class="section-badge">
                <i class="fas fa-rocket"></i>
                <span>What We Offer</span>
            </div>
            <h2>Our Featured <span class="text-gradient">Services</span></h2>
            <p>Elevate Your Online Presence with Our Expertise - You Dream It, We Build It</p>
        </div>

        <!-- Services Grid (Desktop) -->
        <div class="row services-grid-desktop">
            <?php if (!empty($services)): ?>
                <?php $delay = 0; $count = 0; foreach ($services as $service): if ($count >= 6) break; ?>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <div class="service-card-animated h-100">
                        <div class="service-icon-animated">
                            <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                        <p><?php echo htmlspecialchars($service['short_description']); ?></p>
                        <a href="services.php" class="btn-service">
                            Learn More <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php $delay = $delay >= 200 ? 0 : $delay + 100; $count++; endforeach; ?>
            <?php else: ?>
                <!-- Fallback static services -->
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up">
                    <div class="service-card-animated h-100">
                        <div class="service-icon-animated">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <h4>Website Design</h4>
                        <p>Create stunning, user-friendly websites that captivate your audience and drive conversions.</p>
                        <a href="services.php" class="btn-service">
                            Learn More <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-card-animated h-100">
                        <div class="service-icon-animated">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h4>E-Commerce Development</h4>
                        <p>Launch your online store with powerful e-commerce solutions that drive sales.</p>
                        <a href="services.php" class="btn-service">
                            Learn More <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-card-animated h-100">
                        <div class="service-icon-animated">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h4>Agentic AI Solutions</h4>
                        <p>Harness the power of AI agents to automate tasks and improve customer service.</p>
                        <a href="services.php" class="btn-service">
                            Learn More <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Services Slider (Mobile) -->
        <div class="swiper services-swiper-mobile" data-aos="fade-up">
            <div class="swiper-wrapper">
                <?php if (!empty($services)): ?>
                    <?php $count = 0; foreach ($services as $service): if ($count >= 6) break; ?>
                    <div class="swiper-slide">
                        <div class="service-card-animated h-100">
                            <div class="service-icon-animated">
                                <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                            </div>
                            <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                            <p><?php echo htmlspecialchars($service['short_description']); ?></p>
                            <a href="services.php" class="btn-service">
                                Learn More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <?php $count++; endforeach; ?>
                <?php else: ?>
                    <div class="swiper-slide">
                        <div class="service-card-animated h-100">
                            <div class="service-icon-animated">
                                <i class="fas fa-laptop-code"></i>
                            </div>
                            <h4>Website Design</h4>
                            <p>Create stunning, user-friendly websites that captivate your audience and drive conversions.</p>
                            <a href="services.php" class="btn-service">
                                Learn More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="service-card-animated h-100">
                            <div class="service-icon-animated">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h4>E-Commerce Development</h4>
                            <p>Launch your online store with powerful e-commerce solutions that drive sales.</p>
                            <a href="services.php" class="btn-service">
                                Learn More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="service-card-animated h-100">
                            <div class="service-icon-animated">
                                <i class="fas fa-robot"></i>
                            </div>
                            <h4>Agentic AI Solutions</h4>
                            <p>Harness the power of AI agents to automate tasks and improve customer service.</p>
                            <a href="services.php" class="btn-service">
                                Learn More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="swiper-pagination services-pagination"></div>
        </div>

        <!-- View All Button -->
        <div class="text-center mt-4" data-aos="fade-up">
            <a href="services.php" class="btn-hero-primary" style="padding: 14px 35px;">
                <span>View All Services</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Technology Section with Light Animated Background -->
<section class="section-light-animated" style="padding: 80px 0;">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
    </div>

    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <div class="section-badge d-inline-flex" style="background: rgba(13, 110, 253, 0.1); border-color: rgba(13, 110, 253, 0.2);">
                <i class="fas fa-layer-group" style="color: #0d6efd;"></i>
                <span style="color: #0d6efd;">Tech Stack</span>
            </div>
            <h2 style="color: #1e293b; font-size: 2.5rem; font-weight: 800; margin-top: 15px;">Technologies We <span style="color: #0d6efd;">Master</span></h2>
            <p style="color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 15px auto 0;">We use cutting-edge technologies to build secure, scalable, and high-performance solutions.</p>
        </div>

        <div class="row justify-content-center">
            <?php if (!empty($technologies)): ?>
                <?php $delay = 0; foreach ($technologies as $tech): ?>
                <div class="col-6 col-md-3 col-lg-2 mb-4" data-aos="zoom-in" data-aos-delay="<?php echo $delay; ?>">
                    <div class="tech-item-home">
                        <i class="<?php echo htmlspecialchars($tech['icon']); ?>" style="color: <?php echo htmlspecialchars($tech['color']); ?>;"></i>
                        <p><?php echo htmlspecialchars($tech['name']); ?></p>
                    </div>
                </div>
                <?php $delay = $delay >= 300 ? 0 : $delay + 50; endforeach; ?>
            <?php else: ?>
                <!-- Fallback static technologies -->
                <?php
                $fallbackTechs = [
                    ['icon' => 'fab fa-html5', 'color' => '#E34F26', 'name' => 'HTML5'],
                    ['icon' => 'fab fa-css3-alt', 'color' => '#1572B6', 'name' => 'CSS3'],
                    ['icon' => 'fab fa-js-square', 'color' => '#F7DF1E', 'name' => 'JavaScript'],
                    ['icon' => 'fab fa-react', 'color' => '#61DAFB', 'name' => 'React'],
                    ['icon' => 'fab fa-php', 'color' => '#777BB4', 'name' => 'PHP'],
                    ['icon' => 'fab fa-python', 'color' => '#3776AB', 'name' => 'Python'],
                ];
                $delay = 0;
                foreach ($fallbackTechs as $tech):
                ?>
                <div class="col-6 col-md-3 col-lg-2 mb-4" data-aos="zoom-in" data-aos-delay="<?php echo $delay; ?>">
                    <div class="tech-item-home">
                        <i class="<?php echo $tech['icon']; ?>" style="color: <?php echo $tech['color']; ?>;"></i>
                        <p><?php echo $tech['name']; ?></p>
                    </div>
                </div>
                <?php $delay += 50; endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Testimonials Section with Dark Animated Background -->
<section class="section-dark-animated" style="padding: 100px 0;">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
    </div>

    <div class="container">
        <!-- Section Title -->
        <div class="section-title-animated" data-aos="fade-up">
            <div class="section-badge">
                <i class="fas fa-quote-left"></i>
                <span>Testimonials</span>
            </div>
            <h2>What Our <span class="text-gradient">Clients Say</span></h2>
            <p>Trusted by businesses worldwide - Here's what they have to say about us</p>
        </div>

        <!-- Testimonials Grid (Desktop) -->
        <div class="row testimonials-grid-desktop">
            <?php if (!empty($testimonials)): ?>
                <?php $delay = 0; $count = 0; foreach ($testimonials as $testimonial): if ($count >= 3) break; ?>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <div class="testimonial-card-animated">
                        <div class="testimonial-quote">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        <div class="testimonial-rating-animated">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'active' : ''; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="testimonial-text-animated">"<?php echo htmlspecialchars($testimonial['testimonial']); ?>"</p>
                        <div class="testimonial-author-animated">
                            <div class="author-avatar">
                                <?php echo strtoupper(substr($testimonial['client_name'], 0, 1)); ?>
                            </div>
                            <div class="author-info">
                                <h5><?php echo htmlspecialchars($testimonial['client_name']); ?></h5>
                                <span><?php echo htmlspecialchars($testimonial['client_position'] ?? ''); ?><?php echo $testimonial['client_company'] ? ', ' . htmlspecialchars($testimonial['client_company']) : ''; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $delay += 100; $count++; endforeach; ?>
            <?php else: ?>
                <!-- Fallback static testimonials -->
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up">
                    <div class="testimonial-card-animated">
                        <div class="testimonial-quote">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        <div class="testimonial-rating-animated">
                            <i class="fas fa-star active"></i>
                            <i class="fas fa-star active"></i>
                            <i class="fas fa-star active"></i>
                            <i class="fas fa-star active"></i>
                            <i class="fas fa-star active"></i>
                        </div>
                        <p class="testimonial-text-animated">"BizNexa transformed our online presence completely. Their expertise in web development and AI integration is outstanding. Highly recommended!"</p>
                        <div class="testimonial-author-animated">
                            <div class="author-avatar">J</div>
                            <div class="author-info">
                                <h5>John Smith</h5>
                                <span>CEO, TechStart Inc.</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-card-animated">
                        <div class="testimonial-quote">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        <div class="testimonial-rating-animated">
                            <i class="fas fa-star active"></i>
                            <i class="fas fa-star active"></i>
                            <i class="fas fa-star active"></i>
                            <i class="fas fa-star active"></i>
                            <i class="fas fa-star active"></i>
                        </div>
                        <p class="testimonial-text-animated">"Excellent service! The team delivered our e-commerce website on time with amazing features. Our sales have increased by 40% since launch."</p>
                        <div class="testimonial-author-animated">
                            <div class="author-avatar">S</div>
                            <div class="author-info">
                                <h5>Sarah Johnson</h5>
                                <span>Founder, Fashion Hub</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-card-animated">
                        <div class="testimonial-quote">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        <div class="testimonial-rating-animated">
                            <i class="fas fa-star active"></i>
                            <i class="fas fa-star active"></i>
                            <i class="fas fa-star active"></i>
                            <i class="fas fa-star active"></i>
                            <i class="fas fa-star active"></i>
                        </div>
                        <p class="testimonial-text-animated">"Professional, dedicated, and responsive. BizNexa created a beautiful website for our business that truly represents our brand."</p>
                        <div class="testimonial-author-animated">
                            <div class="author-avatar">M</div>
                            <div class="author-info">
                                <h5>Michael Chen</h5>
                                <span>Owner, Local Bistro</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Testimonials Slider (Mobile) -->
        <div class="swiper testimonials-swiper-mobile" data-aos="fade-up">
            <div class="swiper-wrapper">
                <?php if (!empty($testimonials)): ?>
                    <?php $count = 0; foreach ($testimonials as $testimonial): if ($count >= 3) break; ?>
                    <div class="swiper-slide">
                        <div class="testimonial-card-animated">
                            <div class="testimonial-quote">
                                <i class="fas fa-quote-left"></i>
                            </div>
                            <div class="testimonial-rating-animated">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'active' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="testimonial-text-animated">"<?php echo htmlspecialchars($testimonial['testimonial']); ?>"</p>
                            <div class="testimonial-author-animated">
                                <div class="author-avatar">
                                    <?php echo strtoupper(substr($testimonial['client_name'], 0, 1)); ?>
                                </div>
                                <div class="author-info">
                                    <h5><?php echo htmlspecialchars($testimonial['client_name']); ?></h5>
                                    <span><?php echo htmlspecialchars($testimonial['client_position'] ?? ''); ?><?php echo $testimonial['client_company'] ? ', ' . htmlspecialchars($testimonial['client_company']) : ''; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $count++; endforeach; ?>
                <?php else: ?>
                    <div class="swiper-slide">
                        <div class="testimonial-card-animated">
                            <div class="testimonial-quote">
                                <i class="fas fa-quote-left"></i>
                            </div>
                            <div class="testimonial-rating-animated">
                                <i class="fas fa-star active"></i>
                                <i class="fas fa-star active"></i>
                                <i class="fas fa-star active"></i>
                                <i class="fas fa-star active"></i>
                                <i class="fas fa-star active"></i>
                            </div>
                            <p class="testimonial-text-animated">"BizNexa transformed our online presence completely. Their expertise in web development and AI integration is outstanding. Highly recommended!"</p>
                            <div class="testimonial-author-animated">
                                <div class="author-avatar">J</div>
                                <div class="author-info">
                                    <h5>John Smith</h5>
                                    <span>CEO, TechStart Inc.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="testimonial-card-animated">
                            <div class="testimonial-quote">
                                <i class="fas fa-quote-left"></i>
                            </div>
                            <div class="testimonial-rating-animated">
                                <i class="fas fa-star active"></i>
                                <i class="fas fa-star active"></i>
                                <i class="fas fa-star active"></i>
                                <i class="fas fa-star active"></i>
                                <i class="fas fa-star active"></i>
                            </div>
                            <p class="testimonial-text-animated">"Excellent service! The team delivered our e-commerce website on time with amazing features. Our sales have increased by 40% since launch."</p>
                            <div class="testimonial-author-animated">
                                <div class="author-avatar">S</div>
                                <div class="author-info">
                                    <h5>Sarah Johnson</h5>
                                    <span>Founder, Fashion Hub</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="testimonial-card-animated">
                            <div class="testimonial-quote">
                                <i class="fas fa-quote-left"></i>
                            </div>
                            <div class="testimonial-rating-animated">
                                <i class="fas fa-star active"></i>
                                <i class="fas fa-star active"></i>
                                <i class="fas fa-star active"></i>
                                <i class="fas fa-star active"></i>
                                <i class="fas fa-star active"></i>
                            </div>
                            <p class="testimonial-text-animated">"Professional, dedicated, and responsive. BizNexa created a beautiful website for our business that truly represents our brand."</p>
                            <div class="testimonial-author-animated">
                                <div class="author-avatar">M</div>
                                <div class="author-info">
                                    <h5>Michael Chen</h5>
                                    <span>Owner, Local Bistro</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="swiper-pagination testimonials-pagination"></div>
        </div>
    </div>
</section>

<!-- CTA Section with Gradient Background -->
<section class="section-light-animated" style="padding: 100px 0;">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
    </div>

    <div class="container">
        <div class="cta-box-home" data-aos="zoom-in">
            <div class="cta-bg-shapes">
                <div class="cta-shape cta-shape-1"></div>
                <div class="cta-shape cta-shape-2"></div>
            </div>
            <div class="row align-items-center">
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <h2>Ready to Transform Your <span>Digital Presence</span>?</h2>
                    <p>Contact us today for a free consultation and let's discuss how we can help your business grow.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="contact.php" class="btn-cta-home">
                        <span>Get Started Now</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* About Content Home */
.btn-learn-more {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    color: #fff;
    padding: 14px 30px;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 10px 30px rgba(13, 110, 253, 0.3);
}

.btn-learn-more:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(13, 110, 253, 0.4);
    color: #fff;
}

/* Stats Grid Home */
.stats-grid-home {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
}

.stat-item-home {
    background: #fff;
    border-radius: 20px;
    padding: 35px 25px;
    text-align: center;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
    transition: all 0.4s ease;
}

.stat-item-home:hover {
    transform: translateY(-10px);
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
}

.stat-icon-home {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.stat-icon-home i {
    font-size: 28px;
    color: #fff;
}

.stat-item-home h3 {
    font-size: 2.5rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 5px;
}

.stat-item-home p {
    color: #64748b;
    font-size: 14px;
    font-weight: 500;
    margin: 0;
}

/* Tech Item Home */
.tech-item-home {
    background: #fff;
    border-radius: 16px;
    padding: 30px 20px;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    transition: all 0.4s ease;
}

.tech-item-home:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
}

.tech-item-home i {
    font-size: 48px;
    transition: transform 0.4s ease;
}

.tech-item-home:hover i {
    transform: scale(1.2) rotate(10deg);
}

.tech-item-home p {
    margin-top: 15px;
    font-weight: 600;
    color: #1e293b;
    font-size: 14px;
}

/* Testimonial Card Animated */
.testimonial-card-animated {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 40px 30px;
    position: relative;
    transition: all 0.4s ease;
    height: 100%;
}

.testimonial-card-animated:hover {
    transform: translateY(-10px);
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(13, 110, 253, 0.3);
}

.testimonial-quote {
    position: absolute;
    top: 20px;
    right: 25px;
    font-size: 3rem;
    color: rgba(13, 110, 253, 0.2);
}

.testimonial-rating-animated {
    display: flex;
    gap: 5px;
    margin-bottom: 20px;
}

.testimonial-rating-animated i {
    color: #64748b;
    font-size: 14px;
}

.testimonial-rating-animated i.active {
    color: #fbbf24;
}

.testimonial-text-animated {
    color: #94a3b8;
    font-size: 1rem;
    line-height: 1.8;
    margin-bottom: 25px;
    font-style: italic;
}

.testimonial-author-animated {
    display: flex;
    align-items: center;
    gap: 15px;
}

.author-avatar {
    width: 55px;
    height: 55px;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    font-weight: 700;
    color: #fff;
}

.author-info h5 {
    color: #fff;
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 3px;
}

.author-info span {
    color: #64748b;
    font-size: 13px;
}

/* CTA Box Home */
.cta-box-home {
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    border-radius: 25px;
    padding: 70px 60px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(13, 110, 253, 0.3);
}

.cta-bg-shapes {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}

.cta-shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
}

.cta-shape-1 {
    width: 300px;
    height: 300px;
    top: -150px;
    right: -100px;
}

.cta-shape-2 {
    width: 200px;
    height: 200px;
    bottom: -100px;
    left: -50px;
}

.cta-box-home h2 {
    color: #fff;
    font-size: 2.2rem;
    font-weight: 800;
    margin-bottom: 15px;
    position: relative;
}

.cta-box-home h2 span {
    text-decoration: underline;
    text-decoration-thickness: 3px;
    text-underline-offset: 5px;
}

.cta-box-home p {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.1rem;
    margin: 0;
    position: relative;
}

.btn-cta-home {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: #fff;
    color: #0d6efd;
    padding: 18px 40px;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    position: relative;
}

.btn-cta-home:hover {
    background: #0f172a;
    color: #fff;
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
}

@media (max-width: 991px) {
    .stats-grid-home {
        gap: 15px;
    }

    .stat-item-home {
        padding: 25px 15px;
    }

    .stat-item-home h3 {
        font-size: 2rem;
    }

    .cta-box-home {
        padding: 50px 35px;
        text-align: center;
    }

    .cta-box-home h2 {
        font-size: 1.8rem;
    }
}

@media (max-width: 576px) {
    .stat-icon-home {
        width: 55px;
        height: 55px;
    }

    .stat-icon-home i {
        font-size: 22px;
    }
}

/* Mobile Slider Styles */
.services-swiper-mobile,
.testimonials-swiper-mobile {
    display: none;
    padding-bottom: 50px;
}

.services-swiper-mobile .swiper-slide,
.testimonials-swiper-mobile .swiper-slide {
    height: auto;
    padding: 10px 5px;
}

.services-swiper-mobile .service-card-animated,
.testimonials-swiper-mobile .testimonial-card-animated {
    height: 100%;
}

/* Swiper Pagination Styling */
.services-pagination,
.testimonials-pagination {
    position: relative;
    margin-top: 20px;
}

.services-pagination .swiper-pagination-bullet,
.testimonials-pagination .swiper-pagination-bullet {
    width: 10px;
    height: 10px;
    background: rgba(255, 255, 255, 0.3);
    opacity: 1;
    transition: all 0.3s ease;
}

.services-pagination .swiper-pagination-bullet-active,
.testimonials-pagination .swiper-pagination-bullet-active {
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    width: 30px;
    border-radius: 5px;
}

/* Show slider on mobile, hide grid */
@media (max-width: 767px) {
    .services-grid-desktop,
    .testimonials-grid-desktop {
        display: none !important;
    }

    .services-swiper-mobile,
    .testimonials-swiper-mobile {
        display: block;
    }
}
</style>

<!-- Mobile Sliders Initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Services Swiper for Mobile
    if (window.innerWidth <= 767) {
        new Swiper('.services-swiper-mobile', {
            slidesPerView: 1.2,
            spaceBetween: 15,
            centeredSlides: true,
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.services-pagination',
                clickable: true,
            },
            breakpoints: {
                480: {
                    slidesPerView: 1.3,
                    spaceBetween: 20,
                }
            }
        });

        // Initialize Testimonials Swiper for Mobile
        new Swiper('.testimonials-swiper-mobile', {
            slidesPerView: 1.1,
            spaceBetween: 15,
            centeredSlides: true,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.testimonials-pagination',
                clickable: true,
            },
            breakpoints: {
                480: {
                    slidesPerView: 1.2,
                    spaceBetween: 20,
                }
            }
        });
    }

    // Reinitialize on resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            location.reload();
        }, 250);
    });
});
</script>

<?php include 'includes/footer.php'; ?>
