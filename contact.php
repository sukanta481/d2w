<?php
$currentPage = 'contact';
$pageTitle = 'Contact Us';

// Include database helper
include_once 'includes/db_config.php';
$settings = getAllSettings();
$contactEmail = $settings['site_email'] ?? 'info@biznexa.tech';
$contactPhone = $settings['site_phone'] ?? '+91 94332 15443';
$contactAddress = $settings['site_address'] ?? '123 Business Avenue, Suite 100, New York, NY 10001';

include 'includes/header.php';
?>

<!-- Contact Hero Section with Light Animated Background -->
<section class="section-light-animated" style="padding-top: 120px; padding-bottom: 80px;">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
    </div>

    <div class="container">
        <!-- Hero Title -->
        <div class="text-center mb-5" data-aos="fade-up">
            <h1 style="font-size: 3rem; color: #1e293b; font-weight: 800; margin-top: 15px;">Contact <span style="color: #0d6efd;">Us</span></h1>
            <p style="color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 15px auto;">Get in touch with our team. We're here to help you achieve your digital goals.</p>
            <div class="header-breadcrumb mt-3" style="justify-content: center;">
                <a href="index.php" style="color: #64748b;"><i class="fas fa-home"></i> Home</a>
                <i class="fas fa-chevron-right" style="color: #94a3b8;"></i>
                <span style="color: #1e293b; font-weight: 600;">Contact</span>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4" data-aos="fade-up">
                <div class="contact-card-animated">
                    <div class="contact-card-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h4>Our Office</h4>
                    <p><?php echo nl2br(htmlspecialchars($contactAddress)); ?></p>
                    <a href="https://maps.google.com/?q=<?php echo urlencode($contactAddress); ?>" target="_blank" class="contact-card-link">
                        <i class="fas fa-directions"></i> Get Directions
                    </a>
                </div>
            </div>
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="contact-card-animated">
                    <div class="contact-card-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <h4>Call Us</h4>
                    <p><?php echo htmlspecialchars($contactPhone); ?></p>
                    <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $contactPhone); ?>" class="contact-card-link">
                        <i class="fas fa-phone"></i> Call Now
                    </a>
                </div>
            </div>
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="contact-card-animated">
                    <div class="contact-card-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h4>Email Us</h4>
                    <p><?php echo htmlspecialchars($contactEmail); ?></p>
                    <a href="mailto:<?php echo htmlspecialchars($contactEmail); ?>" class="contact-card-link">
                        <i class="fas fa-paper-plane"></i> Send Email
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form Section with Dark Animated Background -->
<section class="section-dark-animated" style="padding: 100px 0;">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
        <div class="bg-shape bg-shape-3"></div>
    </div>

    <!-- Floating Code Elements -->
    <div class="floating-elements">
        <div class="floating-element">@</div>
        <div class="floating-element">&lt;/&gt;</div>
        <div class="floating-element">{...}</div>
        <div class="floating-element">( )</div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-7 mb-4" data-aos="fade-right">
                <div class="contact-form-animated">
                    <div class="section-badge d-inline-flex mb-3">
                        <i class="fas fa-paper-plane"></i>
                        <span>Send Message</span>
                    </div>
                    <h2 style="color: #fff; font-size: 2rem; font-weight: 800; margin-bottom: 10px;">Let's Start a <span class="text-gradient">Conversation</span></h2>
                    <p style="color: #94a3b8; margin-bottom: 30px;">Feel free to ask for details, don't save any questions!</p>

                    <form id="contactForm" action="php/contact-form-handler.php" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-group-animated">
                                    <label for="name"><i class="fas fa-user"></i> Your Name</label>
                                    <input type="text" class="form-control-animated" id="name" name="name" placeholder="John Doe" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="form-group-animated">
                                    <label for="email"><i class="fas fa-envelope"></i> Your Email</label>
                                    <input type="email" class="form-control-animated" id="email" name="email" placeholder="john@example.com" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-group-animated">
                                    <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                                    <input type="tel" class="form-control-animated" id="phone" name="phone" placeholder="+1 234 567 890" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="form-group-animated">
                                    <label for="service"><i class="fas fa-cogs"></i> Enquiry For</label>
                                    <select class="form-control-animated" id="service" name="service" required>
                                        <option value="">Select Service</option>
                                        <option value="Website Design">Website Design</option>
                                        <option value="Web Development">Web Development</option>
                                        <option value="E-Commerce">E-Commerce Website</option>
                                        <option value="Agentic AI">Agentic AI Solutions</option>
                                        <option value="Digital Marketing">Digital Marketing</option>
                                        <option value="SEO">SEO Services</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="form-group-animated">
                                <label for="message"><i class="fas fa-comment-alt"></i> Your Message</label>
                                <textarea class="form-control-animated" id="message" name="message" rows="5" placeholder="Tell us about your project..." required></textarea>
                            </div>
                        </div>
                        <div id="formMessage" class="alert" style="display: none;"></div>
                        <button type="submit" class="btn-submit-animated">
                            <span>Send Message</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-lg-5 mb-4" data-aos="fade-left">
                <div class="contact-info-sidebar">
                    <!-- Business Hours Card -->
                    <div class="info-card-animated mb-4">
                        <div class="info-card-header">
                            <i class="fas fa-clock"></i>
                            <h4>Business Hours</h4>
                        </div>
                        <ul class="hours-list">
                            <li>
                                <span class="day">Monday - Friday</span>
                                <span class="time">9:00 AM - 5:00 PM</span>
                            </li>
                            <li>
                                <span class="day">Saturday</span>
                                <span class="time">9:00 AM - 2:00 PM</span>
                            </li>
                            <li>
                                <span class="day">Sunday</span>
                                <span class="time closed">Closed</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Quick Connect Card -->
                    <div class="info-card-animated mb-4">
                        <div class="info-card-header">
                            <i class="fas fa-bolt"></i>
                            <h4>Quick Connect</h4>
                        </div>
                        <div class="quick-connect-buttons">
                            <a href="https://wa.me/919433215443" target="_blank" class="quick-btn whatsapp">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                            <a href="skype:biznexa?chat" class="quick-btn skype">
                                <i class="fab fa-skype"></i> Skype
                            </a>
                        </div>
                    </div>

                    <!-- Remote Work Notice -->
                    <div class="info-card-animated notice-card">
                        <div class="notice-icon">
                            <i class="fas fa-laptop-house"></i>
                        </div>
                        <p>We are working remotely. All communication will be done virtually by Phone call or Video Meeting.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="map-section" data-aos="fade-up">
    <?php
    // Generate Google Maps embed URL from address (no API key required)
    $mapAddress = urlencode($contactAddress);
    $mapUrl = "https://maps.google.com/maps?q=" . $mapAddress . "&t=&z=13&ie=UTF8&iwloc=&output=embed";
    ?>
    <iframe src="<?php echo $mapUrl; ?>" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
</section>

<style>
/* Contact Card Animated */
.contact-card-animated {
    background: #fff;
    border-radius: 20px;
    padding: 40px 30px;
    text-align: center;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
    transition: all 0.4s ease;
    height: 100%;
}

.contact-card-animated:hover {
    transform: translateY(-10px);
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
}

.contact-card-icon {
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

.contact-card-animated:hover .contact-card-icon {
    transform: scale(1.1) rotate(10deg);
}

.contact-card-icon i {
    font-size: 32px;
    color: #fff;
}

.contact-card-animated h4 {
    color: #1e293b;
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 15px;
}

.contact-card-animated p {
    color: #64748b;
    font-size: 1rem;
    margin-bottom: 20px;
    line-height: 1.6;
}

.contact-card-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #0d6efd;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.contact-card-link:hover {
    color: #6610f2;
    gap: 12px;
}

/* Contact Form Animated */
.contact-form-animated {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 25px;
    padding: 50px;
}

.form-group-animated label {
    display: block;
    color: #94a3b8;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 10px;
}

.form-group-animated label i {
    margin-right: 8px;
    color: #0d6efd;
}

.form-control-animated {
    width: 100%;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 15px 20px;
    color: #fff;
    font-size: 15px;
    transition: all 0.3s ease;
}

.form-control-animated:focus {
    outline: none;
    border-color: #0d6efd;
    background: rgba(255, 255, 255, 0.08);
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15);
}

.form-control-animated::placeholder {
    color: #64748b;
}

select.form-control-animated {
    cursor: pointer;
}

select.form-control-animated option {
    background: #1e293b;
    color: #fff;
}

textarea.form-control-animated {
    resize: none;
    min-height: 120px;
}

.btn-submit-animated {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    color: #fff;
    padding: 16px 40px;
    border: none;
    border-radius: 50px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 10px 30px rgba(13, 110, 253, 0.3);
}

.btn-submit-animated:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(13, 110, 253, 0.4);
}

.btn-submit-animated i {
    transition: transform 0.3s ease;
}

.btn-submit-animated:hover i {
    transform: translateX(5px);
}

/* Contact Info Sidebar */
.info-card-animated {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 30px;
}

.info-card-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.info-card-header i {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #fff;
}

.info-card-header h4 {
    color: #fff;
    font-size: 1.2rem;
    font-weight: 700;
    margin: 0;
}

.hours-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.hours-list li {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.hours-list li:last-child {
    border-bottom: none;
}

.hours-list .day {
    color: #94a3b8;
    font-weight: 500;
}

.hours-list .time {
    color: #fff;
    font-weight: 600;
}

.hours-list .time.closed {
    color: #ef4444;
}

.quick-connect-buttons {
    display: flex;
    gap: 15px;
}

.quick-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 20px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.quick-btn.whatsapp {
    background: #25D366;
    color: #fff;
}

.quick-btn.whatsapp:hover {
    background: #1da851;
    transform: translateY(-3px);
}

.quick-btn.skype {
    background: #00AFF0;
    color: #fff;
}

.quick-btn.skype:hover {
    background: #0095d5;
    transform: translateY(-3px);
}

.notice-card {
    display: flex;
    align-items: center;
    gap: 20px;
    background: rgba(16, 185, 129, 0.1);
    border-color: rgba(16, 185, 129, 0.2);
}

.notice-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.notice-icon i {
    font-size: 24px;
    color: #fff;
}

.notice-card p {
    color: #94a3b8;
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
}

/* Map Section */
.map-section {
    line-height: 0;
}

.map-section iframe {
    filter: grayscale(20%);
    transition: filter 0.3s ease;
}

.map-section:hover iframe {
    filter: grayscale(0%);
}

@media (max-width: 991px) {
    .contact-form-animated {
        padding: 30px;
    }

    .contact-info-sidebar {
        margin-top: 30px;
    }

    .quick-connect-buttons {
        flex-direction: column;
    }
}

@media (max-width: 576px) {
    .contact-form-animated {
        padding: 25px 20px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
