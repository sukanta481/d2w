<?php 
$currentPage = 'contact';
$pageTitle = 'Contact Us';
include 'includes/header.php'; 
?>

<section class="page-header">
    <div class="container">
        <h1 data-aos="fade-up">Contact Us</h1>
        <p data-aos="fade-up" data-aos-delay="100">Get in Touch - We're Here to Help</p>
    </div>
</section>

<section class="contact-section py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-4 mb-4" data-aos="fade-up">
                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h4>Our Office Address</h4>
                    <p>123 Business Avenue, Suite 100<br>New York, NY 10001<br>United States</p>
                </div>
            </div>
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h4>Call Us</h4>
                    <p>Phone: <a href="tel:+1234567890">+91 94332 15443</a><br>
                    Toll Free: <a href="tel:+1800123456">+91 89610 90050</a></p>
                </div>
            </div>
            <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h4>Email Us</h4>
                    <p>General: <a href="mailto:info@dawntoweb.com">info@dawntoweb.com</a><br>
                    Support: <a href="mailto:support@dawntoweb.com">info.dawntoweb@gmail.com</a></p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4" data-aos="fade-right">
                <div class="contact-form-wrapper">
                    <h3>Send Us a Message</h3>
                    <p class="mb-4">Feel free to ask for details, don't save any questions!</p>
                    <form id="contactForm" action="php/contact-form-handler.php" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Your Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Your Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="service" class="form-label">Enquiry For *</label>
                                <select class="form-select" id="service" name="service" required>
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
                        <div class="mb-3">
                            <label for="message" class="form-label">Your Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <div id="formMessage" class="alert" style="display: none;"></div>
                        <button type="submit" class="btn btn-primary btn-lg">Submit</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-4 mb-4" data-aos="fade-left">
                <div class="business-hours-card">
                    <h4>Business Hours</h4>
                    <ul class="business-hours-list">
                        <li>
                            <i class="far fa-clock"></i>
                            <span>Monday - Friday</span>
                            <strong>9am to 5pm</strong>
                        </li>
                        <li>
                            <i class="far fa-clock"></i>
                            <span>Saturday</span>
                            <strong>9am to 2pm</strong>
                        </li>
                        <li>
                            <i class="far fa-clock"></i>
                            <span>Sunday</span>
                            <strong>Closed</strong>
                        </li>
                    </ul>
                    <p class="mt-4"><small>We are working remotely. All communication will be done virtually by Phone call or Video Meeting.</small></p>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12" data-aos="fade-up">
                <div class="map-wrapper">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d193595.15830869428!2d-74.11976369936802!3d40.69766374865766!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2sin!4v1637148345678!5m2!1sen!2sin" width="100%" height="400" style="border:0; border-radius: 10px;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
