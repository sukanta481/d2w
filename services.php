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

<section class="page-header">
    <div class="container">
        <h1 data-aos="fade-up">Our Services</h1>
        <p data-aos="fade-up" data-aos-delay="100">Comprehensive Digital Solutions for Your Business</p>
    </div>
</section>

<section class="services-detail-section py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-12" data-aos="fade-up">
                <h2 class="section-heading">What We Offer</h2>
                <p class="lead">Our team is made up of talented designers, developers, and digital marketing experts who work closely together to deliver a seamless user experience. We use the latest technologies and trends to create websites that are visually appealing, easy to navigate, and optimized for search engines.</p>
                <p>Our capabilities include everything from concept development and design to content creation and SEO optimization. We work closely with our clients to understand their business, goals, and target audience, so we can create a website that truly represents them.</p>
            </div>
        </div>

        <div class="section-title text-center mb-5">
            <h2>Our Featured Services</h2>
            <p>Elevate Your Online Presence with Our Expertise – You Dream It, We Build It</p>
        </div>

        <div class="row">
            <?php if (!empty($services)): ?>
                <?php $delay = 100; foreach ($services as $service): ?>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <div class="service-card-detailed">
                        <div class="service-icon-large">
                            <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                        <p><?php echo htmlspecialchars($service['short_description']); ?></p>
                        <?php if (!empty($service['features'])): ?>
                        <ul class="service-features">
                            <?php foreach (explode("\n", $service['features']) as $feature): ?>
                                <?php if (trim($feature)): ?>
                                <li><i class="fas fa-check"></i> <?php echo htmlspecialchars(trim($feature)); ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
                <?php $delay = $delay >= 300 ? 100 : $delay + 100; endforeach; ?>
            <?php else: ?>
                <!-- Fallback static services -->
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-card-detailed">
                        <div class="service-icon-large"><i class="fas fa-laptop-code"></i></div>
                        <h3>Website Design</h3>
                        <p>Create stunning, user-friendly websites that captivate your audience and drive conversions.</p>
                        <ul class="service-features">
                            <li><i class="fas fa-check"></i> Custom Design</li>
                            <li><i class="fas fa-check"></i> Mobile Responsive</li>
                            <li><i class="fas fa-check"></i> Fast Loading</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-card-detailed">
                        <div class="service-icon-large"><i class="fas fa-code"></i></div>
                        <h3>Web Development</h3>
                        <p>Build powerful web applications and portals using the latest technologies.</p>
                        <ul class="service-features">
                            <li><i class="fas fa-check"></i> Custom Solutions</li>
                            <li><i class="fas fa-check"></i> Scalable Architecture</li>
                            <li><i class="fas fa-check"></i> Secure Development</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="service-card-detailed">
                        <div class="service-icon-large"><i class="fas fa-robot"></i></div>
                        <h3>Agentic AI Solutions</h3>
                        <p>Harness the power of AI agents to automate tasks and improve customer service.</p>
                        <ul class="service-features">
                            <li><i class="fas fa-check"></i> AI Chatbots</li>
                            <li><i class="fas fa-check"></i> Process Automation</li>
                            <li><i class="fas fa-check"></i> Smart Analytics</li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="technology-section py-5 bg-light">
    <div class="container">
        <div class="section-title text-center mb-5">
            <h2>Technologies We Use</h2>
            <p>We use the latest technologies & tools to build secure & updated websites & applications</p>
        </div>
        <div class="row text-center">
            <?php if (!empty($technologies)): ?>
                <?php
                $delay = 100;
                foreach ($technologies as $tech):
                ?>
                <div class="col-6 col-md-3 mb-4" data-aos="zoom-in" data-aos-delay="<?php echo $delay; ?>">
                    <div class="tech-item">
                        <i class="<?php echo htmlspecialchars($tech['icon']); ?>" style="color: <?php echo htmlspecialchars($tech['color']); ?>;"></i>
                        <p><?php echo htmlspecialchars($tech['name']); ?></p>
                    </div>
                </div>
                <?php
                $delay = $delay >= 400 ? 100 : $delay + 100;
                endforeach;
                ?>
            <?php else: ?>
                <!-- Fallback static technologies -->
                <div class="col-6 col-md-3 mb-4" data-aos="zoom-in" data-aos-delay="100">
                    <div class="tech-item">
                        <i class="fab fa-html5" style="color: #E34F26;"></i>
                        <p>HTML5</p>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="tech-item">
                        <i class="fab fa-css3-alt" style="color: #1572B6;"></i>
                        <p>CSS3</p>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-4" data-aos="zoom-in" data-aos-delay="300">
                    <div class="tech-item">
                        <i class="fab fa-js-square" style="color: #F7DF1E;"></i>
                        <p>JavaScript</p>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-4" data-aos="zoom-in" data-aos-delay="400">
                    <div class="tech-item">
                        <i class="fab fa-php" style="color: #777BB4;"></i>
                        <p>PHP</p>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-4" data-aos="zoom-in" data-aos-delay="100">
                    <div class="tech-item">
                        <i class="fab fa-wordpress" style="color: #21759B;"></i>
                        <p>WordPress</p>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="tech-item">
                        <i class="fab fa-bootstrap" style="color: #7952B3;"></i>
                        <p>Bootstrap</p>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-4" data-aos="zoom-in" data-aos-delay="300">
                    <div class="tech-item">
                        <i class="fab fa-shopify" style="color: #96bf48;"></i>
                        <p>Shopify</p>
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
                <h2 class="text-white mb-3">Need a Custom Solution?</h2>
                <p class="text-white mb-4">Contact us to discuss your specific requirements and get a personalized quote.</p>
                <a href="contact.php" class="btn btn-light btn-lg">Contact Us Today</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
