<?php
// Get settings for footer if not already loaded
if (!isset($settings)) {
    include_once __DIR__ . '/db_config.php';
    $settings = getAllSettings();
}
$facebook = $settings['facebook_url'] ?? '#';
$twitter = $settings['twitter_url'] ?? '#';
$linkedin = $settings['linkedin_url'] ?? '#';
$instagram = $settings['instagram_url'] ?? '#';
?>
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-title">About Dawn To Web</h5>
                    <p class="footer-text">We specialize in web development, agentic AI solutions, and comprehensive digital marketing services designed specifically for small businesses. Let us help you grow your digital presence.</p>
                    <div class="social-links mt-3">
                        <a href="<?php echo htmlspecialchars($facebook); ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?php echo htmlspecialchars($twitter); ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-twitter"></i></a>
                        <a href="<?php echo htmlspecialchars($linkedin); ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-linkedin-in"></i></a>
                        <a href="<?php echo htmlspecialchars($instagram); ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-title">Our Services</h5>
                    <ul class="footer-links">
                        <li><a href="services.php">Website Design</a></li>
                        <li><a href="services.php">Web Development</a></li>
                        <li><a href="services.php">Agentic AI Solutions</a></li>
                        <li><a href="services.php">E-Commerce Development</a></li>
                        <li><a href="services.php">Digital Marketing</a></li>
                        <li><a href="services.php">SEO Services</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="portfolio.php">Portfolio</a></li>
                        <li><a href="blog.php">Blog</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-12 mb-4">
                    <h5 class="footer-title">Newsletter</h5>
                    <p class="footer-text">Subscribe to get the latest updates and news.</p>
                    <form class="newsletter-form" id="newsletterForm">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your Email" required>
                            <button class="btn btn-primary" type="submit">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
            <hr class="footer-divider">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="copyright">&copy; <?php echo date('Y'); ?> Dawn To Web. All Rights Reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="footer-link-inline">Privacy Policy</a> | 
                    <a href="#" class="footer-link-inline">Terms & Conditions</a>
                </div>
            </div>
        </div>
    </footer>

    <button class="scroll-to-top" id="scrollToTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/919876543210?text=Hi%20Dawn%20To%20Web!%20I%20need%20assistance." target="_blank" class="whatsapp-float" id="whatsappFloat" aria-label="Chat on WhatsApp">
        <div class="whatsapp-pulse"></div>
        <div class="whatsapp-icon">
            <i class="fab fa-whatsapp"></i>
        </div>
        <span class="whatsapp-tooltip">Need Help? Chat with us!</span>
    </a>

    <style>
    /* Floating WhatsApp Button Styles */
    .whatsapp-float {
        position: fixed;
        bottom: 100px;
        right: 30px;
        z-index: 9999;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .whatsapp-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(37, 211, 102, 0.5);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        z-index: 2;
    }

    .whatsapp-icon i {
        font-size: 32px;
        color: #fff;
        transition: transform 0.3s ease;
    }

    .whatsapp-float:hover .whatsapp-icon {
        transform: scale(1.15) rotate(10deg);
        box-shadow: 0 8px 30px rgba(37, 211, 102, 0.7);
    }

    .whatsapp-float:hover .whatsapp-icon i {
        transform: scale(1.1);
    }

    /* Pulse Animation Ring */
    .whatsapp-pulse {
        position: absolute;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: rgba(37, 211, 102, 0.4);
        animation: whatsappPulse 2s ease-out infinite;
        z-index: 1;
    }

    @keyframes whatsappPulse {
        0% {
            transform: scale(1);
            opacity: 0.8;
        }
        100% {
            transform: scale(1.8);
            opacity: 0;
        }
    }

    /* Tooltip */
    .whatsapp-tooltip {
        position: absolute;
        right: 75px;
        background: #2c3e50;
        color: #fff;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transform: translateX(20px);
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .whatsapp-tooltip::after {
        content: '';
        position: absolute;
        right: -8px;
        top: 50%;
        transform: translateY(-50%);
        border: 8px solid transparent;
        border-left-color: #2c3e50;
        border-right: none;
    }

    .whatsapp-float:hover .whatsapp-tooltip {
        opacity: 1;
        visibility: visible;
        transform: translateX(0);
    }

    /* Entrance Animation */
    .whatsapp-float {
        animation: whatsappBounceIn 1s ease-out 1s backwards;
    }

    @keyframes whatsappBounceIn {
        0% {
            opacity: 0;
            transform: scale(0) translateY(50px);
        }
        50% {
            transform: scale(1.2) translateY(-10px);
        }
        70% {
            transform: scale(0.9) translateY(5px);
        }
        100% {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    /* Floating Animation */
    .whatsapp-icon {
        animation: whatsappFloat 3s ease-in-out infinite;
    }

    @keyframes whatsappFloat {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-8px);
        }
    }

    .whatsapp-float:hover .whatsapp-icon {
        animation: none;
        transform: scale(1.15) rotate(10deg);
    }

    /* Mobile Adjustments */
    @media (max-width: 768px) {
        .whatsapp-float {
            bottom: 90px;
            right: 20px;
        }

        .whatsapp-icon {
            width: 55px;
            height: 55px;
        }

        .whatsapp-icon i {
            font-size: 28px;
        }

        .whatsapp-pulse {
            width: 55px;
            height: 55px;
        }

        .whatsapp-tooltip {
            display: none;
        }
    }

    @media (max-width: 576px) {
        .whatsapp-float {
            bottom: 85px;
            right: 15px;
        }

        .whatsapp-icon {
            width: 50px;
            height: 50px;
        }

        .whatsapp-icon i {
            font-size: 26px;
        }

        .whatsapp-pulse {
            width: 50px;
            height: 50px;
        }
    }

    /* Adjust scroll-to-top button position to avoid overlap */
    .scroll-to-top {
        bottom: 30px;
        right: 30px;
    }

    @media (max-width: 768px) {
        .scroll-to-top {
            bottom: 25px;
            right: 20px;
            width: 45px;
            height: 45px;
        }
    }

    @media (max-width: 576px) {
        .scroll-to-top {
            bottom: 20px;
            right: 15px;
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }
    }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
