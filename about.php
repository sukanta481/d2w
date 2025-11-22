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
                <i class="fas fa-building"></i>
                <span>Who We Are</span>
            </div>
            <h1 data-aos="fade-up" data-aos-delay="100">About <span class="text-gradient">BizNexa</span></h1>
            <p class="header-subtitle" data-aos="fade-up" data-aos-delay="200">Your trusted partner in digital transformation. We craft innovative solutions that drive business growth and success.</p>
            <div class="header-breadcrumb" data-aos="fade-up" data-aos-delay="300">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <i class="fas fa-chevron-right"></i>
                <span>About Us</span>
            </div>
        </div>
    </div>
</section>

<!-- About Story Section with Light Animated Background -->
<section class="section-light-animated" style="padding: 100px 0;">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
    </div>

    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4" data-aos="fade-right">
                <div class="about-image-wrapper">
                    <img src="https://cdn.pixabay.com/photo/2015/01/09/11/08/startup-594090_1280.jpg" alt="About Us" class="about-main-image">
                    <div class="about-experience-badge">
                        <span class="exp-number"><?php echo $statYears; ?>+</span>
                        <span class="exp-text">Years of Excellence</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4" data-aos="fade-left">
                <div class="about-content-animated">
                    <div class="section-badge d-inline-flex mb-3" style="background: rgba(13, 110, 253, 0.1); border-color: rgba(13, 110, 253, 0.2);">
                        <i class="fas fa-rocket" style="color: #0d6efd;"></i>
                        <span style="color: #0d6efd;">Our Story</span>
                    </div>
                    <h2 style="color: #1e293b; font-size: 2.5rem; font-weight: 800; margin-bottom: 20px;">Your Partner in <span style="color: #0d6efd;">Digital Excellence</span> Since 2020</h2>
                    <p style="color: #64748b; font-size: 1.05rem; line-height: 1.8; margin-bottom: 15px;">BizNexa is a leading web development and digital marketing company dedicated to helping small businesses thrive in the digital age. We specialize in custom website design, agentic AI solutions, and comprehensive digital marketing services.</p>
                    <p style="color: #64748b; font-size: 1.05rem; line-height: 1.8; margin-bottom: 25px;">Founded with a vision to make professional web services accessible to small businesses, we have successfully delivered innovative solutions to clients across multiple industries.</p>

                    <div class="about-features">
                        <div class="about-feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Quality-Driven Approach</span>
                        </div>
                        <div class="about-feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Cutting-Edge Technology</span>
                        </div>
                        <div class="about-feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>24/7 Support Available</span>
                        </div>
                        <div class="about-feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Affordable Solutions</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section with Dark Animated Background -->
<section class="section-dark-animated" style="padding: 80px 0;">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
        <div class="bg-shape bg-shape-3"></div>
    </div>

    <!-- Floating Code Elements -->
    <div class="floating-elements">
        <div class="floating-element">&lt;/&gt;</div>
        <div class="floating-element">{...}</div>
        <div class="floating-element">01</div>
        <div class="floating-element">++</div>
    </div>

    <div class="container">
        <div class="row text-center">
            <div class="col-lg-3 col-md-6 mb-4" data-aos="zoom-in">
                <div class="stat-box-animated">
                    <div class="stat-icon-box">
                        <i class="fas fa-smile"></i>
                    </div>
                    <h3 class="stat-number" data-count="<?php echo $statClients; ?>">0</h3>
                    <p>Happy Clients</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="100">
                <div class="stat-box-animated">
                    <div class="stat-icon-box">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="stat-number" data-count="<?php echo $statYears; ?>">0</h3>
                    <p>Years In Business</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="200">
                <div class="stat-box-animated">
                    <div class="stat-icon-box">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <h3 class="stat-number" data-count="<?php echo $statProjects; ?>">0</h3>
                    <p>Projects Done</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="300">
                <div class="stat-box-animated">
                    <div class="stat-icon-box">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h3 class="stat-number" data-count="<?php echo $statCountries; ?>">0</h3>
                    <p>Countries Served</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Who We Are & Skills Section with Light Animated Background -->
<section class="section-light-animated" style="padding: 100px 0;">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
    </div>

    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4" data-aos="fade-right">
                <div class="who-we-are-content">
                    <div class="section-badge d-inline-flex mb-3" style="background: rgba(13, 110, 253, 0.1); border-color: rgba(13, 110, 253, 0.2);">
                        <i class="fas fa-users" style="color: #0d6efd;"></i>
                        <span style="color: #0d6efd;">Who We Are</span>
                    </div>
                    <h2 style="color: #1e293b; font-size: 2.2rem; font-weight: 800; margin-bottom: 20px;">Experts in Digital <span style="color: #0d6efd;">Growth</span></h2>
                    <p style="color: #64748b; font-size: 1.05rem; line-height: 1.8; margin-bottom: 15px;">We are experts in making your business grow in the digital world. Our services will enhance your business growth in the digital market. Our highly skilled professional team has the capability to fulfill our client's requirements.</p>
                    <p style="color: #64748b; font-size: 1.05rem; line-height: 1.8; margin-bottom: 15px;">We offer affordable websites for small businesses along with Website Designing, Web Development, E-Commerce Website Development, Agentic AI Integration, SEO, and Digital Marketing services.</p>
                    <p style="color: #64748b; font-size: 1.05rem; line-height: 1.8;">Our approach is collaborative - we work closely with you to understand your business goals and create solutions that drive real results.</p>
                </div>
            </div>
            <div class="col-lg-6 mb-4" data-aos="fade-left">
                <div class="skills-wrapper">
                    <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 700; margin-bottom: 30px;"><i class="fas fa-chart-line" style="color: #0d6efd; margin-right: 10px;"></i>Our Expertise</h3>

                    <div class="skill-item-animated">
                        <div class="skill-header">
                            <span class="skill-name">Website Designing</span>
                            <span class="skill-percentage">95%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" data-width="95"></div>
                        </div>
                    </div>

                    <div class="skill-item-animated">
                        <div class="skill-header">
                            <span class="skill-name">Web & Mobile Apps</span>
                            <span class="skill-percentage">90%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" data-width="90"></div>
                        </div>
                    </div>

                    <div class="skill-item-animated">
                        <div class="skill-header">
                            <span class="skill-name">Agentic AI Solutions</span>
                            <span class="skill-percentage">85%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" data-width="85"></div>
                        </div>
                    </div>

                    <div class="skill-item-animated">
                        <div class="skill-header">
                            <span class="skill-name">SEO Optimization</span>
                            <span class="skill-percentage">88%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" data-width="88"></div>
                        </div>
                    </div>

                    <div class="skill-item-animated">
                        <div class="skill-header">
                            <span class="skill-name">Digital Marketing</span>
                            <span class="skill-percentage">92%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" data-width="92"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Core Values Section with Dark Animated Background -->
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
                <i class="fas fa-heart"></i>
                <span>What We Believe</span>
            </div>
            <h2>Our Core <span class="text-gradient">Values</span></h2>
            <p>The principles that guide everything we do</p>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="value-card-animated">
                    <div class="value-icon-animated">
                        <i class="fas fa-star"></i>
                    </div>
                    <h4>Quality First</h4>
                    <p>We never compromise on quality. Every project receives our complete attention to detail and commitment to excellence.</p>
                    <div class="value-number">01</div>
                </div>
            </div>
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="value-card-animated">
                    <div class="value-icon-animated">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h4>Innovation</h4>
                    <p>We stay ahead of technology trends to provide cutting-edge solutions that give you a competitive advantage.</p>
                    <div class="value-number">02</div>
                </div>
            </div>
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="value-card-animated">
                    <div class="value-icon-animated">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h4>Integrity</h4>
                    <p>We believe in transparent communication, honest pricing, and building trust through consistent delivery.</p>
                    <div class="value-number">03</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section with Light Background -->
<section class="section-light-animated" style="padding: 100px 0;">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
    </div>

    <div class="container">
        <div class="cta-box-animated" data-aos="zoom-in">
            <div class="row align-items-center">
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <h2>Let's Build Something <span style="color: #0d6efd;">Great Together</span></h2>
                    <p>Partner with us for your next digital project and experience the BizNexa difference.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="contact.php" class="btn-cta-animated">
                        <span>Get In Touch</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* About Image Wrapper */
.about-image-wrapper {
    position: relative;
    display: inline-block;
}

.about-main-image {
    width: 100%;
    border-radius: 20px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
}

.about-experience-badge {
    position: absolute;
    bottom: -30px;
    right: -30px;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    padding: 25px 35px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 15px 40px rgba(13, 110, 253, 0.4);
}

.about-experience-badge .exp-number {
    display: block;
    font-size: 2.5rem;
    font-weight: 800;
    color: #fff;
    line-height: 1;
}

.about-experience-badge .exp-text {
    display: block;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    margin-top: 5px;
}

/* About Features */
.about-features {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.about-feature-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #1e293b;
    font-weight: 500;
}

.about-feature-item i {
    color: #10B981;
    font-size: 18px;
}

/* Stat Box Animated */
.stat-box-animated {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 40px 30px;
    text-align: center;
    transition: all 0.4s ease;
}

.stat-box-animated:hover {
    transform: translateY(-10px);
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(13, 110, 253, 0.3);
}

.stat-icon-box {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.stat-icon-box i {
    font-size: 28px;
    color: #fff;
}

.stat-box-animated h3 {
    font-size: 3rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 5px;
}

.stat-box-animated p {
    color: #94a3b8;
    font-size: 1rem;
    margin: 0;
}

/* Skills Animated */
.skills-wrapper {
    background: #fff;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
}

.skill-item-animated {
    margin-bottom: 25px;
}

.skill-item-animated:last-child {
    margin-bottom: 0;
}

.skill-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.skill-name {
    font-weight: 600;
    color: #1e293b;
}

.skill-percentage {
    font-weight: 700;
    color: #0d6efd;
}

.skill-bar {
    height: 10px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}

.skill-progress {
    height: 100%;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    border-radius: 10px;
    width: 0;
    transition: width 1.5s ease;
}

/* Value Card Animated */
.value-card-animated {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 40px 30px;
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: all 0.4s ease;
    height: 100%;
}

.value-card-animated:hover {
    transform: translateY(-10px);
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(13, 110, 253, 0.3);
}

.value-icon-animated {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    transition: transform 0.4s ease;
}

.value-card-animated:hover .value-icon-animated {
    transform: scale(1.1) rotate(10deg);
}

.value-icon-animated i {
    font-size: 32px;
    color: #fff;
}

.value-card-animated h4 {
    color: #fff;
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 15px;
}

.value-card-animated p {
    color: #94a3b8;
    font-size: 1rem;
    line-height: 1.7;
    margin: 0;
}

.value-number {
    position: absolute;
    top: 20px;
    right: 25px;
    font-size: 4rem;
    font-weight: 900;
    color: rgba(255, 255, 255, 0.03);
    line-height: 1;
}

/* CTA Box Animated */
.cta-box-animated {
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    border-radius: 25px;
    padding: 60px 50px;
    box-shadow: 0 25px 60px rgba(13, 110, 253, 0.3);
}

.cta-box-animated h2 {
    color: #fff;
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 10px;
}

.cta-box-animated h2 span {
    color: #fff !important;
    text-decoration: underline;
    text-decoration-thickness: 3px;
    text-underline-offset: 5px;
}

.cta-box-animated p {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.1rem;
    margin: 0;
}

.btn-cta-animated {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #fff;
    color: #0d6efd;
    padding: 16px 35px;
    border-radius: 50px;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.btn-cta-animated:hover {
    background: #0f172a;
    color: #fff;
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
}

@media (max-width: 991px) {
    .about-experience-badge {
        right: 10px;
        bottom: -20px;
        padding: 20px 25px;
    }

    .about-experience-badge .exp-number {
        font-size: 2rem;
    }

    .about-features {
        grid-template-columns: 1fr;
    }

    .skills-wrapper {
        margin-top: 30px;
    }

    .cta-box-animated {
        padding: 40px 30px;
        text-align: center;
    }

    .cta-box-animated h2 {
        font-size: 1.6rem;
    }
}
</style>

<script>
// Animate skill bars when in viewport
document.addEventListener('DOMContentLoaded', function() {
    const skillBars = document.querySelectorAll('.skill-progress');

    const observerOptions = {
        threshold: 0.5
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const width = entry.target.getAttribute('data-width');
                entry.target.style.width = width + '%';
            }
        });
    }, observerOptions);

    skillBars.forEach(bar => observer.observe(bar));
});
</script>

<?php include 'includes/footer.php'; ?>
