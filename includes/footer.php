<?php
require_once __DIR__ . '/csrf.php';
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
                    <h5 class="footer-title">About BizNexa</h5>
                    <p class="footer-text">We specialize in web development, AI &amp; automation solutions, and digital marketing services designed for small businesses. Let us help you grow your digital presence.</p>
                    <div class="social-links mt-3">
                        <a href="<?php echo htmlspecialchars($facebook); ?>" target="_blank" rel="noopener noreferrer" aria-label="Follow us on Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?php echo htmlspecialchars($twitter); ?>" target="_blank" rel="noopener noreferrer" aria-label="Follow us on Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="<?php echo htmlspecialchars($linkedin); ?>" target="_blank" rel="noopener noreferrer" aria-label="Connect on LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="<?php echo htmlspecialchars($instagram); ?>" target="_blank" rel="noopener noreferrer" aria-label="Follow us on Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                    <!-- MSME Registration Badge -->
                    <div class="msme-badge mt-4">
                        <img src="assets/images/msme-logo.png" alt="MSME Registered - Government of India" class="msme-logo" width="45" height="45">
                        <span class="msme-text">MSME Registered</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-title">Our Services</h5>
                    <ul class="footer-links">
                        <li><a href="services.php#web-development">Web Development</a></li>
                        <li><a href="services.php#web-development">Custom Website Design</a></li>
                        <li><a href="services.php#web-development">E-Commerce Solutions</a></li>
                        <li><a href="services.php#ai-automation">AI & Automation</a></li>
                        <li><a href="services.php#digital-marketing">Digital Marketing</a></li>
                        <li><a href="services.php#digital-marketing">SEO Services</a></li>
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
                        <?php echo csrfField(); ?>
                        <div class="input-group">
                            <label for="newsletter-email" class="visually-hidden">Email address</label>
                            <input type="email" class="form-control" id="newsletter-email" placeholder="Your Email" required>
                            <button class="btn btn-primary" type="submit">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
            <hr class="footer-divider">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="copyright">&copy; <?php echo date('Y'); ?> BizNexa. All Rights Reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <span class="footer-link-inline">Privacy Policy</span> |
                    <span class="footer-link-inline">Terms & Conditions</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating Side Contact Box -->
    <div class="side-contact-box" id="sideContactBox">
        <a href="mailto:<?php echo htmlspecialchars($settings['site_email'] ?? 'info@biznexa.tech'); ?>" class="side-contact-item" aria-label="Email us">
            <i class="fas fa-envelope"></i>
        </a>
        <a href="tel:<?php echo htmlspecialchars(preg_replace('/\s+/', '', $settings['site_phone'] ?? '+919433215443')); ?>" class="side-contact-item" aria-label="Call us">
            <i class="fas fa-phone"></i>
        </a>
        <a href="skype:live:biznexa?chat" class="side-contact-item" aria-label="Chat on Skype">
            <i class="fab fa-skype"></i>
        </a>
        <a href="contact.php" class="side-contact-item" aria-label="Get a quote">
            <i class="fas fa-quote-right"></i>
        </a>
    </div>

    <!-- Floating WhatsApp Button -->
    <?php $waPhone = preg_replace('/[^0-9]/', '', $settings['site_phone'] ?? '919433215443'); ?>
    <a href="https://wa.me/<?php echo $waPhone; ?>?text=Hi%20BizNexa!%20I%20need%20assistance." target="_blank" class="whatsapp-float" id="whatsappFloat" aria-label="Chat on WhatsApp">
        <div class="whatsapp-pulse"></div>
        <div class="whatsapp-icon">
            <i class="fab fa-whatsapp"></i>
        </div>
        <span class="whatsapp-tooltip">Need Help? Chat with us!</span>
    </a>

    <!-- N8N Chat Widget -->
    <link href="https://cdn.jsdelivr.net/npm/@n8n/chat/dist/style.css" rel="stylesheet" />

    <style>
    /* N8N Chat CSS Variables */
    :root {
        --chat--color--primary: #0d6efd;
        --chat--color--primary-shade-50: #0b5ed7;
        --chat--color--primary--shade-100: #084298;
        --chat--color--secondary: #6610f2;
        --chat--color-secondary-shade-50: #5a0fd1;
        --chat--color-white: #ffffff;
        --chat--color-light: #f8f9fa;
        --chat--color-light-shade-50: #e9ecef;
        --chat--color-light-shade-100: #c2c5cc;
        --chat--color-medium: #d2d4d9;
        --chat--color-dark: #1e293b;
        --chat--color-disabled: #777980;
        --chat--color-typing: #404040;
        --chat--spacing: 1rem;
        --chat--border-radius: 0.75rem;
        --chat--transition-duration: 0.3s;
        --chat--window--width: 400px;
        --chat--window--height: 600px;
        --chat--header--background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
        --chat--header--color: var(--chat--color-white);
        --chat--header--padding: 1rem;
        --chat--toggle--background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
        --chat--toggle--hover--background: linear-gradient(135deg, #0b5ed7 0%, #5a0fd1 100%);
        --chat--toggle--active--background: linear-gradient(135deg, #084298 0%, #4a0bc0 100%);
        --chat--toggle--color: var(--chat--color-white);
        --chat--toggle--size: 60px;
        --chat--message--user--background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
        --chat--message--user--color: var(--chat--color-white);
        --chat--message--bot--background: var(--chat--color-white);
        --chat--message--bot--color: var(--chat--color-dark);
    }
    </style>

    <script type="module">
        import { createChat } from 'https://cdn.jsdelivr.net/npm/@n8n/chat/dist/chat.bundle.es.js';

        createChat({
            webhookUrl: 'https://n8n.influentra.media/webhook/c95fffc5-4cf7-4ff9-acc7-45bca9373550/chat',
            initialMessages: [
                'Hi there! 👋',
                'I\'m the BizNexa AI assistant. How can I help you today?'
            ],
            i18n: {
                en: {
                    title: 'Hi there! 👋',
                    subtitle: 'BizNexa AI Assistant - We\'re here to help!',
                    inputPlaceholder: 'Type your message...',
                    getStarted: 'New Conversation',
                    footer: ''
                }
            },
            mode: 'window',
            showWelcomeScreen: false
        });

        // Customize the chat button after widget loads
        function customizeChatWidget() {
            const chatContainer = document.getElementById('n8n-chat');
            if (!chatContainer) return false;

            const shadowRoot = chatContainer.shadowRoot;
            if (!shadowRoot) return false;

            const existingStyle = shadowRoot.querySelector('#biznexa-custom-styles');
            if (!existingStyle) {
                const styleSheet = document.createElement('style');
                styleSheet.id = 'biznexa-custom-styles';
                styleSheet.textContent = `
                    .chat-toggle-button {
                        bottom: 180px !important;
                        right: 25px !important;
                        width: 60px !important;
                        height: 60px !important;
                        box-shadow: 0 4px 20px rgba(13, 110, 253, 0.5) !important;
                        transition: transform 0.2s ease-out !important;
                    }
                    .chat-toggle-button:hover {
                        transform: scale(1.1) !important;
                        box-shadow: 0 8px 30px rgba(13, 110, 253, 0.7) !important;
                    }
                    .chat-toggle-button svg {
                        display: none !important;
                    }
                    .chat-toggle-button .bot-icon {
                        font-size: 24px;
                        line-height: 1;
                        color: #fff;
                    }
                    .chat-container {
                        bottom: 180px !important;
                        right: 25px !important;
                    }
                    .chat-window-wrapper {
                        bottom: 250px !important;
                    }
                `;
                shadowRoot.appendChild(styleSheet);
            }

            // Add Font Awesome chat icon instead of emoji
            const toggleButton = shadowRoot.querySelector('.chat-toggle-button');
            if (toggleButton && !toggleButton.querySelector('.bot-icon')) {
                const botIcon = document.createElement('span');
                botIcon.className = 'bot-icon';
                botIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="white"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.54.36 2.98.97 4.29L1 23l6.71-1.97C9.02 21.64 10.46 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm-1 14h-2v-2h2v2zm2.07-4.78c-.38.47-.86.8-.86 1.78h-2c0-1.61.86-2.39 1.24-2.86.34-.42.6-.72.6-1.14 0-.88-.72-1.6-1.6-1.6-.74 0-1.37.51-1.55 1.2l-1.9-.58C7.36 7.34 8.56 6.2 10.05 6.2c1.76 0 3.2 1.44 3.2 3.2 0 1.06-.68 1.73-1.18 2.32z"/></svg>';
                toggleButton.appendChild(botIcon);
                return true;
            }

            return !!toggleButton;
        }

        let attempts = 0;
        const maxAttempts = 30;
        const customizeInterval = setInterval(() => {
            attempts++;
            if (customizeChatWidget() || attempts >= maxAttempts) {
                clearInterval(customizeInterval);
            }
        }, 200);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Swiper JS for Mobile Sliders -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
