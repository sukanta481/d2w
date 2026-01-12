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
                    <h5 class="footer-title">About BizNexa</h5>
                    <p class="footer-text">We specialize in web development, agentic AI solutions, and comprehensive digital marketing services designed specifically for small businesses. Let us help you grow your digital presence.</p>
                    <div class="social-links mt-3">
                        <a href="<?php echo htmlspecialchars($facebook); ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?php echo htmlspecialchars($twitter); ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-twitter"></i></a>
                        <a href="<?php echo htmlspecialchars($linkedin); ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-linkedin-in"></i></a>
                        <a href="<?php echo htmlspecialchars($instagram); ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                    </div>
                    <!-- MSME Registration Badge -->
                    <div class="msme-badge mt-4">
                        <img src="assets/images/msme-logo.png" alt="MSME Registered" class="msme-logo">
                        <span class="msme-text">MSME Registered</span>
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
                    <p class="copyright">&copy; <?php echo date('Y'); ?> BizNexa. All Rights Reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="footer-link-inline">Privacy Policy</a> | 
                    <a href="#" class="footer-link-inline">Terms & Conditions</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating Side Contact Box -->
    <div class="side-contact-box" id="sideContactBox">
        <a href="mailto:info@biznexa.tech" class="side-contact-item" title="Email Us">
            <i class="fas fa-envelope"></i>
        </a>
        <a href="tel:+919433215443" class="side-contact-item" title="Call Us">
            <i class="fas fa-phone"></i>
        </a>
        <a href="skype:live:biznexa?chat" class="side-contact-item" title="Skype">
            <i class="fab fa-skype"></i>
        </a>
        <a href="contact.php" class="side-contact-item" title="Get a Quote">
            <i class="fas fa-quote-right"></i>
        </a>
    </div>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/919433215443?text=Hi%20BizNexa!%20I%20need%20assistance." target="_blank" class="whatsapp-float" id="whatsappFloat" aria-label="Chat on WhatsApp">
        <div class="whatsapp-pulse"></div>
        <div class="whatsapp-icon">
            <i class="fab fa-whatsapp"></i>
        </div>
        <span class="whatsapp-tooltip">Need Help? Chat with us!</span>
    </a>

    <style>
    /* MSME Badge Styles */
    .msme-badge {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        background: rgba(255, 255, 255, 0.1);
        padding: 12px 18px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        transition: all 0.3s ease;
    }

    .msme-badge:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
    }

    .msme-logo {
        width: 45px;
        height: auto;
        filter: brightness(1.1);
    }

    .msme-text {
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.3;
    }

    @media (max-width: 576px) {
        .msme-badge {
            padding: 10px 14px;
            gap: 10px;
        }

        .msme-logo {
            width: 38px;
        }

        .msme-text {
            font-size: 12px;
        }
    }

    /* Floating Side Contact Box Styles */
    .side-contact-box {
        position: fixed;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        z-index: 9998;
        display: flex;
        flex-direction: column;
        border-radius: 8px 0 0 8px;
        overflow: hidden;
        box-shadow: -3px 0 15px rgba(0, 0, 0, 0.15);
    }

    .side-contact-item {
        width: 50px;
        height: 50px;
        background: #0d6efd;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s ease;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .side-contact-item:last-child {
        border-bottom: none;
    }

    .side-contact-item i {
        font-size: 18px;
        transition: transform 0.3s ease;
    }

    .side-contact-item:hover {
        background: #0b5ed7;
        color: #fff;
        width: 60px;
    }

    .side-contact-item:hover i {
        transform: scale(1.2);
    }

    /* Individual item colors on hover */
    .side-contact-item[href^="mailto"]:hover {
        background: #ea4335;
    }

    .side-contact-item[href^="tel"]:hover {
        background: #34a853;
    }

    .side-contact-item[href^="skype"]:hover {
        background: #00aff0;
    }

    .side-contact-item[href="contact.php"]:hover {
        background: #f59e0b;
    }

    /* Hide on mobile to avoid overlap with WhatsApp button */
    @media (max-width: 768px) {
        .side-contact-box {
            display: none;
        }
    }

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
    /* N8N Chatbot Custom Styles */
    :root {
        --chat--color-primary: #0d6efd;
        --chat--color-primary-shade-50: #0b5ed7;
        --chat--color-primary-shade-100: #084298;
        --chat--color-secondary: #6610f2;
        --chat--color-secondary-shade-50: #5a0fd1;
        --chat--color-white: #ffffff;
        --chat--color-light: #f8f9fa;
        --chat--color-light-shade-50: #e9ecef;
        --chat--color-medium: #64748b;
        --chat--color-dark: #1e293b;
        --chat--color-disabled: #94a3b8;
        --chat--color-typing: #94a3b8;
        --chat--spacing: 1rem;
        --chat--border-radius: 1rem;
        --chat--transition-duration: 0.3s;
        --chat--window--width: 400px;
        --chat--window--height: 600px;
        --chat--textarea--height: 50px;
        --chat--header--padding: var(--chat--spacing);
        --chat--header--background: linear-gradient(135deg, var(--chat--color-primary) 0%, var(--chat--color-secondary) 100%);
        --chat--header--color: var(--chat--color-white);
        --chat--header--border-top: none;
        --chat--header--border-bottom: none;
        --chat--header--border-bottom-color: transparent;
        --chat--header--font-size: 1.1rem;
        --chat--message--font-size: 0.95rem;
        --chat--message--padding: var(--chat--spacing);
        --chat--message--border-radius: var(--chat--border-radius);
        --chat--message-line-height: 1.6;
        --chat--message--bot--background: var(--chat--color-light);
        --chat--message--bot--color: var(--chat--color-dark);
        --chat--message--user--background: linear-gradient(135deg, var(--chat--color-primary) 0%, var(--chat--color-secondary) 100%);
        --chat--message--user--color: var(--chat--color-white);
        --chat--message--pre--background: var(--chat--color-light-shade-50);
        --chat--toggle--background: linear-gradient(135deg, var(--chat--color-primary) 0%, var(--chat--color-secondary) 100%);
        --chat--toggle--hover--background: linear-gradient(135deg, var(--chat--color-primary-shade-50) 0%, var(--chat--color-secondary-shade-50) 100%);
        --chat--toggle--active--background: linear-gradient(135deg, var(--chat--color-primary-shade-100) 0%, var(--chat--color-secondary-shade-50) 100%);
        --chat--toggle--color: var(--chat--color-white);
        --chat--toggle--size: 60px;
        --chat--button--background: var(--chat--color-primary);
        --chat--button--hover--background: var(--chat--color-primary-shade-50);
        --chat--button--active--background: var(--chat--color-primary-shade-100);
        --chat--button--color: var(--chat--color-white);
        --chat--input--background: var(--chat--color-white);
        --chat--input--border-color: var(--chat--color-light-shade-50);
        --chat--input--focus--border-color: var(--chat--color-primary);
        --chat--input--placeholder-color: var(--chat--color-medium);
    }

    /* Override n8n chat toggle button styles */
    .n8n-chat .chat-toggle-button {
        width: 60px !important;
        height: 60px !important;
        border-radius: 50% !important;
        background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%) !important;
        box-shadow: 0 4px 20px rgba(13, 110, 253, 0.5) !important;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        border: none !important;
        position: fixed !important;
        bottom: 250px !important;
        right: 30px !important;
        z-index: 9997 !important;
    }

    .n8n-chat .chat-toggle-button:hover {
        transform: scale(1.15) rotate(10deg) !important;
        box-shadow: 0 8px 30px rgba(13, 110, 253, 0.7) !important;
    }

    /* Hide default SVG and show custom bot icon */
    .n8n-chat .chat-toggle-button svg {
        display: none !important;
    }

    .n8n-chat .chat-toggle-button::before {
        content: '🤖';
        font-size: 28px;
        line-height: 1;
    }

    /* Chat window styling */
    .n8n-chat .chat-window {
        border-radius: 20px !important;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2) !important;
        border: 1px solid rgba(13, 110, 253, 0.1) !important;
        overflow: hidden !important;
        bottom: 250px !important;
        right: 30px !important;
    }

    .n8n-chat .chat-header {
        background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%) !important;
        border-bottom: none !important;
        padding: 18px 20px !important;
    }

    .n8n-chat .chat-header h4, 
    .n8n-chat .chat-header .chat-title {
        font-family: 'Montserrat', sans-serif !important;
        font-weight: 700 !important;
        font-size: 1.1rem !important;
    }

    .n8n-chat .chat-messages {
        background: #f8f9fa !important;
    }

    .n8n-chat .chat-message.user .message-content {
        background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%) !important;
        color: #fff !important;
        border-radius: 16px 16px 4px 16px !important;
    }

    .n8n-chat .chat-message.bot .message-content {
        background: #fff !important;
        color: #1e293b !important;
        border-radius: 16px 16px 16px 4px !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08) !important;
    }

    .n8n-chat .chat-input-container {
        border-top: 1px solid #e9ecef !important;
        background: #fff !important;
        padding: 16px !important;
    }

    .n8n-chat .chat-input {
        border-radius: 25px !important;
        border: 2px solid #e9ecef !important;
        padding: 12px 20px !important;
        transition: border-color 0.3s ease !important;
    }

    .n8n-chat .chat-input:focus {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1) !important;
        outline: none !important;
    }

    .n8n-chat .send-button {
        background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%) !important;
        border-radius: 50% !important;
        width: 44px !important;
        height: 44px !important;
        border: none !important;
        transition: all 0.3s ease !important;
    }

    .n8n-chat .send-button:hover {
        transform: scale(1.1) !important;
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.4) !important;
    }

    /* Responsive adjustments for chatbot */
    @media (max-width: 768px) {
        .n8n-chat .chat-toggle-button {
            bottom: 240px !important;
            right: 20px !important;
            width: 55px !important;
            height: 55px !important;
        }

        .n8n-chat .chat-toggle-button::before {
            font-size: 24px !important;
        }

        .n8n-chat .chat-window {
            width: calc(100vw - 40px) !important;
            height: 60vh !important;
            bottom: 240px !important;
            right: 20px !important;
        }
    }

    @media (max-width: 576px) {
        .n8n-chat .chat-toggle-button {
            bottom: 230px !important;
            right: 15px !important;
            width: 50px !important;
            height: 50px !important;
        }

        .n8n-chat .chat-toggle-button::before {
            font-size: 22px !important;
        }

        .n8n-chat .chat-window {
            width: calc(100vw - 30px) !important;
            bottom: 230px !important;
            right: 15px !important;
        }
    }
    </style>

    <!-- N8N Chat Widget -->
    <link href="https://cdn.jsdelivr.net/npm/@n8n/chat/dist/style.css" rel="stylesheet" />
    
    <!-- Custom styles for n8n chat - using correct CSS variable names from documentation -->
    <style>
        /* Apply at :root level so CSS variables cascade into Shadow DOM */
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
        
        /* Position the chat widget higher to avoid overlap */
        #n8n-chat {
            --chat--toggle--size: 60px;
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
            
            // Inject custom styles into shadow DOM
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
                        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
                    }
                    .chat-toggle-button:hover {
                        transform: scale(1.1) !important;
                        box-shadow: 0 8px 30px rgba(13, 110, 253, 0.7) !important;
                    }
                    .chat-toggle-button svg {
                        display: none !important;
                    }
                    .chat-toggle-button .bot-icon {
                        font-size: 28px;
                        line-height: 1;
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
            
            // Add bot emoji icon
            const toggleButton = shadowRoot.querySelector('.chat-toggle-button');
            if (toggleButton && !toggleButton.querySelector('.bot-icon')) {
                const botIcon = document.createElement('span');
                botIcon.className = 'bot-icon';
                botIcon.textContent = '🤖';
                toggleButton.appendChild(botIcon);
                return true;
            }
            
            return !!toggleButton;
        }

        // Try to customize with retries (widget loads async)
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
