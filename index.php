<?php 
$currentPage = 'home';
$pageTitle = 'Home';
include 'includes/header.php'; 
?>

<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-12" data-aos="fade-right">
                <div class="hero-content">
                    <h1 class="hero-title">Website Design and Development Company</h1>
                    <p class="hero-subtitle">Custom Web Design Services at Affordable Pricing</p>

                    <div class="hero-features">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="hero-feature-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Responsive Design</span>
                                </div>
                                <div class="hero-feature-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span>SEO-Optimized</span>
                                </div>
                                <div class="hero-feature-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Fast Loading Speed</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="hero-feature-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Content Management System</span>
                                </div>
                                <div class="hero-feature-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span>E-Commerce Integration</span>
                                </div>
                                <div class="hero-feature-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Secure & Scalable Solutions</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="contact.php" class="btn-hero">
                        Have a Query? <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-6 col-md-12" data-aos="fade-left">
                <div class="hero-illustration">
                    <img src="https://cdn.pixabay.com/photo/2019/10/09/07/28/development-4536630_1280.png" alt="Web Development Illustration" class="img-fluid">
                </div>
            </div>
        </div>
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
                <p>Dawn To Web is a premier web development and digital marketing company focused on small businesses. Utilizing the latest technology including agentic AI, our team of skilled professionals specializes in creating result-driven websites to increase leads and sales.</p>
                <p>From strategic business websites to e-commerce and custom portal development with AI integration, we have the expertise to design and develop all types of websites for small and medium companies.</p>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="row stats-row">
                    <div class="col-6 mb-3">
                        <div class="stat-box">
                            <i class="fas fa-briefcase stat-icon"></i>
                            <h3 class="stat-number" data-count="5">0</h3>
                            <p class="stat-label">Years of Experience</p>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-box">
                            <i class="fas fa-project-diagram stat-icon"></i>
                            <h3 class="stat-number" data-count="150">0</h3>
                            <p class="stat-label">Projects Done</p>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-box">
                            <i class="fas fa-smile stat-icon"></i>
                            <h3 class="stat-number" data-count="120">0</h3>
                            <p class="stat-label">Satisfied Clients</p>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-box">
                            <i class="fas fa-globe stat-icon"></i>
                            <h3 class="stat-number" data-count="15">0</h3>
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
        <div id="servicesCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <!-- Slide 1 - Desktop: 3 cards, Mobile: 2 cards -->
                <div class="carousel-item active">
                    <div class="row justify-content-center">
                        <div class="col-lg-4 col-md-6 col-6 mb-4">
                            <div class="service-card">
                                <div class="service-icon">
                                    <i class="fas fa-laptop-code"></i>
                                </div>
                                <h4>Website Design</h4>
                                <p>Faster loading secured website designing service.</p>
                                <a href="services.php" class="btn btn-outline-primary">Learn More</a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-6 mb-4">
                            <div class="service-card">
                                <div class="service-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <h4>Ecommerce Website</h4>
                                <p>Premium Quality E-Commerce Development Services.</p>
                                <a href="services.php" class="btn btn-outline-primary">Learn More</a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 d-none d-lg-block mb-4">
                            <div class="service-card">
                                <div class="service-icon">
                                    <i class="fas fa-code"></i>
                                </div>
                                <h4>Web Development</h4>
                                <p>Web portal development with the latest technology.</p>
                                <a href="services.php" class="btn btn-outline-primary">Contact Us</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Slide 2 - Desktop: 3 cards, Mobile: 2 cards -->
                <div class="carousel-item">
                    <div class="row justify-content-center">
                        <div class="col-lg-4 col-md-6 d-none d-lg-block mb-4">
                            <div class="service-card">
                                <div class="service-icon">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <h4>Agentic AI Solutions</h4>
                                <p>Intelligent AI agents to automate your business processes.</p>
                                <a href="services.php" class="btn btn-outline-primary">Learn More</a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 d-none d-lg-block mb-4">
                            <div class="service-card">
                                <div class="service-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h4>Digital Marketing</h4>
                                <p>Result-oriented digital marketing services.</p>
                                <a href="services.php" class="btn btn-outline-primary">Contact Us</a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 d-none d-lg-block mb-4">
                            <div class="service-card">
                                <div class="service-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <h4>SEO</h4>
                                <p>SEO services to rank higher in search engines.</p>
                                <a href="services.php" class="btn btn-outline-primary">Contact Us</a>
                            </div>
                        </div>
                        <!-- Mobile only cards -->
                        <div class="col-6 d-lg-none mb-4">
                            <div class="service-card">
                                <div class="service-icon">
                                    <i class="fas fa-code"></i>
                                </div>
                                <h4>Web Development</h4>
                                <p>Web portal development with the latest technology.</p>
                                <a href="services.php" class="btn btn-outline-primary">Contact Us</a>
                            </div>
                        </div>
                        <div class="col-6 d-lg-none mb-4">
                            <div class="service-card">
                                <div class="service-icon">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <h4>Agentic AI Solutions</h4>
                                <p>Intelligent AI agents to automate your business processes.</p>
                                <a href="services.php" class="btn btn-outline-primary">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Slide 3 - Mobile only -->
                <div class="carousel-item d-lg-none">
                    <div class="row justify-content-center">
                        <div class="col-6 mb-4">
                            <div class="service-card">
                                <div class="service-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h4>Digital Marketing</h4>
                                <p>Result-oriented digital marketing services.</p>
                                <a href="services.php" class="btn btn-outline-primary">Contact Us</a>
                            </div>
                        </div>
                        <div class="col-6 mb-4">
                            <div class="service-card">
                                <div class="service-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <h4>SEO</h4>
                                <p>SEO services to rank higher in search engines.</p>
                                <a href="services.php" class="btn btn-outline-primary">Contact Us</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#servicesCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#servicesCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#servicesCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#servicesCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#servicesCarousel" data-bs-slide-to="2" class="d-lg-none" aria-label="Slide 3"></button>
            </div>
        </div>
    </div>
</section>

<section class="technology-section py-5 bg-light">
    <div class="container">
        <div class="section-title text-center mb-5" data-aos="fade-up">
            <h2>Technology We Use to Build Secure Website & Application</h2>
            <p>We use latest technologies & tools to build secure & updated website & Application</p>
        </div>
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="tech-logo">
                    <i class="fab fa-html5 fa-4x" style="color: #E34F26;"></i>
                    <p class="tech-name mt-2">HTML5</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="150">
                <div class="tech-logo">
                    <i class="fab fa-css3-alt fa-4x" style="color: #1572B6;"></i>
                    <p class="tech-name mt-2">CSS3</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="tech-logo">
                    <i class="fab fa-js-square fa-4x" style="color: #F7DF1E;"></i>
                    <p class="tech-name mt-2">JavaScript</p>
                </div>
            </div>
            <!-- <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="250">
                <div class="tech-logo">
                    <i class="fab fa-react fa-4x" style="color: #61DAFB;"></i>
                    <p class="tech-name mt-2">React</p>
                </div>
            </div> -->
            <!-- <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="tech-logo">
                    <i class="fab fa-node-js fa-4x" style="color: #339933;"></i>
                    <p class="tech-name mt-2">Node.js</p>
                </div>
            </div> -->
            <!-- <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="350">
                <div class="tech-logo">
                    <i class="fab fa-python fa-4x" style="color: #3776AB;"></i>
                    <p class="tech-name mt-2">Python</p>
                </div>
            </div> -->
            <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="tech-logo">
                    <i class="fab fa-php fa-4x" style="color: #777BB4;"></i>
                    <p class="tech-name mt-2">PHP</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="150">
                <div class="tech-logo">
                    <i class="fab fa-wordpress fa-4x" style="color: #21759B;"></i>
                    <p class="tech-name mt-2">WordPress</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="tech-logo">
                    <i class="fab fa-bootstrap fa-4x" style="color: #7952B3;"></i>
                    <p class="tech-name mt-2">Bootstrap</p>
                </div>
            </div>
            <!-- <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="250">
                <div class="tech-logo">
                    <i class="fab fa-aws fa-4x" style="color: #FF9900;"></i>
                    <p class="tech-name mt-2">AWS</p>
                </div>
            </div> -->
            <!-- <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="tech-logo">
                    <i class="fab fa-docker fa-4x" style="color: #2496ED;"></i>
                    <p class="tech-name mt-2">Docker</p>
                </div>
            </div> -->
            <!-- <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="350">
                <div class="tech-logo">
                    <i class="fab fa-git-alt fa-4x" style="color: #F05032;"></i>
                    <p class="tech-name mt-2">Git</p>
                </div>
            </div> -->
                        <div class="col-lg-2 col-md-3 col-4 mb-4" data-aos="fade-up" data-aos-delay="350">
                <div class="tech-logo">
                    <i class="fab fa-shopify fa-4x" style="color: #96bf48;"></i>
                    <p class="tech-name mt-2">Shopify</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="testimonials-section py-5 bg-light">
    <div class="container">
        <div class="section-title text-center mb-5" data-aos="fade-up">
            <h2>What Our Clients Say</h2>
            <p>Trusted by businesses worldwide</p>
        </div>
        <div class="row">
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="testimonial-card">
                    <div class="testimonial-text">
                        <p>"Dawn To Web transformed our online presence completely. Their expertise in web development and AI integration is outstanding. Highly recommended!"</p>
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
                        <p>"Professional, dedicated, and responsive. Dawn To Web created a beautiful website for our business that truly represents our brand."</p>
                    </div>
                    <div class="testimonial-author">
                        <h5>Michael Chen</h5>
                        <span>Owner, Local Bistro</span>
                    </div>
                </div>
            </div>
        </div>
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
