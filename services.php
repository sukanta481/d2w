<?php
$currentPage = 'services';
$pageTitle = 'Our Services';

// Include database helper
include_once 'includes/db_config.php';
$services = getServices();
$technologies = getTechnologies();
$settings = getAllSettings();

include 'includes/header.php';
?>

<section class="page-header-new">
    <!-- Animated Background -->
    <div class="page-header-bg">
        <div class="header-shape header-shape-1"></div>
        <div class="header-shape header-shape-2"></div>
        <div class="header-shape header-shape-3"></div>
    </div>

    <div class="container">
        <div class="header-content">
            <div class="header-badge" data-aos="fade-up">
                <i class="fas fa-cogs"></i>
                <span>What We Offer</span>
            </div>
            <h1 data-aos="fade-up" data-aos-delay="100">Our <span class="text-gradient">Services</span></h1>
            <p class="header-subtitle" data-aos="fade-up" data-aos-delay="200">Comprehensive digital solutions tailored to transform your business and accelerate growth in the digital landscape.</p>
            <div class="header-breadcrumb" data-aos="fade-up" data-aos-delay="300">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <i class="fas fa-chevron-right"></i>
                <span>Services</span>
            </div>
        </div>
    </div>
</section>

<!-- Services Section with Dark Animated Background -->
<section class="section-dark-animated">
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
                <span>Our Expertise</span>
            </div>
            <h2>Featured <span class="text-gradient">Services</span></h2>
            <p>Elevate your online presence with our expertise. You dream it, we build it with cutting-edge technology.</p>
        </div>

        <!-- Services Grid -->
        <div class="row">
            <?php if (!empty($services)): ?>
                <?php $delay = 0; foreach ($services as $service): ?>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <div class="service-card-animated h-100">
                        <div class="service-icon-animated">
                            <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                        <p><?php echo htmlspecialchars($service['short_description']); ?></p>
                        <?php if (!empty($service['features'])): ?>
                        <ul class="service-features-list">
                            <?php
                            $features = explode("\n", $service['features']);
                            $count = 0;
                            foreach ($features as $feature):
                                if (trim($feature) && $count < 4):
                            ?>
                                <li><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars(trim($feature)); ?></li>
                            <?php
                                $count++;
                                endif;
                            endforeach;
                            ?>
                        </ul>
                        <?php endif; ?>
                        <a href="contact.php" class="btn-service">
                            Get Started <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php $delay = $delay >= 200 ? 0 : $delay + 100; endforeach; ?>
            <?php else: ?>
                <!-- Fallback static services -->
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up">
                    <div class="service-card-animated h-100">
                        <div class="service-icon-animated">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <h4>Website Design</h4>
                        <p>Create stunning, user-friendly websites that captivate your audience and drive conversions.</p>
                        <ul class="service-features-list">
                            <li><i class="fas fa-check-circle"></i> Custom Design</li>
                            <li><i class="fas fa-check-circle"></i> Mobile Responsive</li>
                            <li><i class="fas fa-check-circle"></i> Fast Loading</li>
                            <li><i class="fas fa-check-circle"></i> SEO Optimized</li>
                        </ul>
                        <a href="contact.php" class="btn-service">
                            Get Started <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-card-animated h-100">
                        <div class="service-icon-animated">
                            <i class="fas fa-code"></i>
                        </div>
                        <h4>Web Development</h4>
                        <p>Build powerful web applications and portals using the latest technologies.</p>
                        <ul class="service-features-list">
                            <li><i class="fas fa-check-circle"></i> Custom Solutions</li>
                            <li><i class="fas fa-check-circle"></i> Scalable Architecture</li>
                            <li><i class="fas fa-check-circle"></i> Secure Development</li>
                            <li><i class="fas fa-check-circle"></i> API Integration</li>
                        </ul>
                        <a href="contact.php" class="btn-service">
                            Get Started <i class="fas fa-arrow-right"></i>
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
                        <ul class="service-features-list">
                            <li><i class="fas fa-check-circle"></i> AI Chatbots</li>
                            <li><i class="fas fa-check-circle"></i> Process Automation</li>
                            <li><i class="fas fa-check-circle"></i> Smart Analytics</li>
                            <li><i class="fas fa-check-circle"></i> 24/7 Support</li>
                        </ul>
                        <a href="contact.php" class="btn-service">
                            Get Started <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up">
                    <div class="service-card-animated h-100">
                        <div class="service-icon-animated">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h4>E-Commerce Development</h4>
                        <p>Launch your online store with powerful e-commerce solutions that drive sales.</p>
                        <ul class="service-features-list">
                            <li><i class="fas fa-check-circle"></i> Custom Store Design</li>
                            <li><i class="fas fa-check-circle"></i> Payment Integration</li>
                            <li><i class="fas fa-check-circle"></i> Inventory Management</li>
                            <li><i class="fas fa-check-circle"></i> Order Tracking</li>
                        </ul>
                        <a href="contact.php" class="btn-service">
                            Get Started <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-card-animated h-100">
                        <div class="service-icon-animated">
                            <i class="fas fa-search"></i>
                        </div>
                        <h4>SEO Services</h4>
                        <p>Boost your online visibility and rank higher on search engines.</p>
                        <ul class="service-features-list">
                            <li><i class="fas fa-check-circle"></i> Keyword Research</li>
                            <li><i class="fas fa-check-circle"></i> On-Page SEO</li>
                            <li><i class="fas fa-check-circle"></i> Link Building</li>
                            <li><i class="fas fa-check-circle"></i> Analytics Reports</li>
                        </ul>
                        <a href="contact.php" class="btn-service">
                            Get Started <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-card-animated h-100">
                        <div class="service-icon-animated">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h4>Digital Marketing</h4>
                        <p>Reach your target audience with effective digital marketing strategies.</p>
                        <ul class="service-features-list">
                            <li><i class="fas fa-check-circle"></i> Social Media Marketing</li>
                            <li><i class="fas fa-check-circle"></i> PPC Campaigns</li>
                            <li><i class="fas fa-check-circle"></i> Email Marketing</li>
                            <li><i class="fas fa-check-circle"></i> Content Strategy</li>
                        </ul>
                        <a href="contact.php" class="btn-service">
                            Get Started <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Technology Section with Light Animated Background -->
<section class="section-light-animated">
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
                    <div class="tech-item-animated text-center p-4" style="background: #fff; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); transition: all 0.4s ease; cursor: pointer;">
                        <i class="<?php echo htmlspecialchars($tech['icon']); ?>" style="font-size: 48px; color: <?php echo htmlspecialchars($tech['color']); ?>; transition: transform 0.4s ease;"></i>
                        <p style="margin-top: 15px; font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($tech['name']); ?></p>
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
                    <div class="tech-item-animated text-center p-4" style="background: #fff; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); transition: all 0.4s ease; cursor: pointer;">
                        <i class="<?php echo $tech['icon']; ?>" style="font-size: 48px; color: <?php echo $tech['color']; ?>; transition: transform 0.4s ease;"></i>
                        <p style="margin-top: 15px; font-weight: 600; color: #1e293b;"><?php echo $tech['name']; ?></p>
                    </div>
                </div>
                <?php $delay += 50; endforeach; ?>
            <?php endif; ?>
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
                    <i class="fas fa-handshake"></i>
                    <span>Let's Work Together</span>
                </div>
                <h2 style="font-size: 2.75rem; color: #fff; font-weight: 800; margin-bottom: 20px;">Need a <span class="text-gradient">Custom Solution</span>?</h2>
                <p style="color: #94a3b8; font-size: 1.2rem; margin-bottom: 35px; max-width: 600px; margin-left: auto; margin-right: auto;">Let's discuss your specific requirements and create something amazing together. Our team is ready to bring your vision to life.</p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="contact.php" class="btn-hero-primary" style="padding: 16px 35px; font-size: 1.1rem;">
                        <span>Start Your Project</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="portfolio.php" class="btn-hero-secondary" style="padding: 16px 35px; font-size: 1.1rem;">
                        <span>View Our Work</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.tech-item-animated:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.15) !important;
}
.tech-item-animated:hover i {
    transform: scale(1.2) rotate(10deg);
}
</style>

<?php include 'includes/footer.php'; ?>
