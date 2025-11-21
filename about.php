<?php
$currentPage = 'about';
$pageTitle = 'About Us';

// Include database helper
include_once 'includes/db_config.php';
$settings = getAllSettings();

// Get stats from settings
$statYears = $settings['stat_years'] ?? '5';
$statProjects = $settings['stat_projects'] ?? '150';
$statClients = $settings['stat_clients'] ?? '120';
$statCountries = $settings['stat_countries'] ?? '15';

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1 data-aos="fade-up">About Dawn To Web</h1>
        <p data-aos="fade-up" data-aos-delay="100">Your Trusted Partner in Digital Transformation</p>
    </div>
</section>

<section class="about-detail-section py-5">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6 mb-4" data-aos="fade-right">
                <h2>Dawn To Web - Your Partner in Digital Excellence Since 2020</h2>
                <p>Dawn To Web is a leading web development and digital marketing company dedicated to helping small businesses thrive in the digital age. We specialize in custom website design, agentic AI solutions, and comprehensive digital marketing services.</p>
                <p>Founded with a vision to make professional web services accessible to small businesses, we have successfully delivered innovative solutions to clients across multiple industries. Our commitment to quality, innovation, and customer satisfaction sets us apart.</p>
                <p>We believe in building long-term relationships with our clients, providing ongoing support and helping them adapt to the ever-changing digital landscape.</p>
            </div>
            <div class="col-lg-6 mb-4" data-aos="fade-left">
                <img src="https://cdn.pixabay.com/photo/2015/01/09/11/08/startup-594090_1280.jpg" alt="About Us" class="img-fluid rounded shadow">
            </div>
        </div>

        <div class="row stats-row mb-5">
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-box-large">
                    <i class="fas fa-smile"></i>
                    <h3 class="stat-number" data-count="<?php echo $statClients; ?>">0</h3>
                    <p>Happy Clients</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-box-large">
                    <i class="fas fa-calendar-alt"></i>
                    <h3 class="stat-number" data-count="<?php echo $statYears; ?>">0</h3>
                    <p>Years In Business</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-box-large">
                    <i class="fas fa-project-diagram"></i>
                    <h3 class="stat-number" data-count="<?php echo $statProjects; ?>">0</h3>
                    <p>Projects Done</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-box-large">
                    <i class="fas fa-globe"></i>
                    <h3 class="stat-number" data-count="<?php echo $statCountries; ?>">0</h3>
                    <p>Countries Served</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="who-we-are-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-4" data-aos="fade-right">
                <h2>Who We Are</h2>
                <p>We are experts in making your business grow in the digital world. Our services will enhance your business growth in the digital market. Our highly skilled professional team has the capability to fulfill our client's requirements.</p>
                <p>We offer affordable websites for small businesses along with Website Designing, Web Development, E-Commerce Website Development, Agentic AI Integration, SEO, and Digital Marketing services.</p>
                <p>Our approach is collaborative - we work closely with you to understand your business goals and create solutions that drive real results. From concept to launch and beyond, we're with you every step of the way.</p>
            </div>
            <div class="col-lg-6 mb-4" data-aos="fade-left">
                <h3 class="mb-4">Our Expertise</h3>
                <div class="skill-item mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Website Designing</span>
                        <span class="skill-percentage">95%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%" data-width="95"></div>
                    </div>
                </div>
                <div class="skill-item mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Web & Mobile Apps Development</span>
                        <span class="skill-percentage">90%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%" data-width="90"></div>
                    </div>
                </div>
                <div class="skill-item mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Agentic AI Solutions</span>
                        <span class="skill-percentage">85%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%" data-width="85"></div>
                    </div>
                </div>
                <div class="skill-item mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>SEO</span>
                        <span class="skill-percentage">88%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%" data-width="88"></div>
                    </div>
                </div>
                <div class="skill-item mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Digital Marketing</span>
                        <span class="skill-percentage">92%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%" data-width="92"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="values-section py-5">
    <div class="container">
        <div class="section-title text-center mb-5" data-aos="fade-up">
            <h2>Our Core Values</h2>
            <p>The principles that guide everything we do</p>
        </div>
        <div class="row">
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h4>Quality First</h4>
                    <p>We never compromise on quality. Every project receives our complete attention to detail and commitment to excellence.</p>
                </div>
            </div>
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h4>Innovation</h4>
                    <p>We stay ahead of technology trends to provide cutting-edge solutions that give you a competitive advantage.</p>
                </div>
            </div>
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h4>Integrity</h4>
                    <p>We believe in transparent communication, honest pricing, and building trust through consistent delivery.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-section py-5">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="zoom-in">
                <h2 class="text-white mb-3">Let's Build Something Great Together</h2>
                <p class="text-white mb-4">Partner with us for your next digital project and experience the Dawn To Web difference.</p>
                <a href="contact.php" class="btn btn-light btn-lg">Get In Touch</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
