<?php
// Get settings for footer if not already loaded
if (!isset($settings)) {
    include_once __DIR__ . '/db_config.php';
    $settings = getAllSettings();
}
$facebook = $settings['social_facebook'] ?? '#';
$twitter = $settings['social_twitter'] ?? '#';
$linkedin = $settings['social_linkedin'] ?? '#';
$instagram = $settings['social_instagram'] ?? '#';
?>
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-title">About Dawn To Web</h5>
                    <p class="footer-text">We specialize in web development, agentic AI solutions, and comprehensive digital marketing services designed specifically for small businesses. Let us help you grow your digital presence.</p>
                    <div class="social-links mt-3">
                        <?php if ($facebook && $facebook !== '#'): ?><a href="<?php echo htmlspecialchars($facebook); ?>" target="_blank"><i class="fab fa-facebook"></i></a><?php endif; ?>
                        <?php if ($twitter && $twitter !== '#'): ?><a href="<?php echo htmlspecialchars($twitter); ?>" target="_blank"><i class="fab fa-twitter"></i></a><?php endif; ?>
                        <?php if ($linkedin && $linkedin !== '#'): ?><a href="<?php echo htmlspecialchars($linkedin); ?>" target="_blank"><i class="fab fa-linkedin"></i></a><?php endif; ?>
                        <?php if ($instagram && $instagram !== '#'): ?><a href="<?php echo htmlspecialchars($instagram); ?>" target="_blank"><i class="fab fa-instagram"></i></a><?php endif; ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
