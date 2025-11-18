# Dawn To Web - Digital Marketing Website

## Overview
Professional digital marketing agency website built with Bootstrap 5, HTML, CSS, JavaScript, and PHP backend. Features web development and agentic AI solutions for small businesses.

## Project Structure
```
.
├── index.php              # Homepage with hero, stats, services, testimonials
├── services.php           # Detailed services page
├── portfolio.php          # Portfolio with filterable project gallery
├── about.php              # About us with company info and stats
├── blog.php               # Blog listing page
├── contact.php            # Contact form with Google Maps
├── includes/
│   ├── header.php        # Navigation and head section
│   └── footer.php        # Footer with newsletter and links
├── assets/
│   ├── css/
│   │   └── style.css     # Custom styling and animations
│   └── js/
│       └── script.js     # Interactive features and form handling
├── php/
│   └── contact-form-handler.php  # Email form backend
└── contacts.txt          # Form submission log
```

## Technologies Used
- **Frontend**: Bootstrap 5.3, HTML5, CSS3, JavaScript
- **Backend**: PHP 8.2
- **Animations**: AOS (Animate On Scroll)
- **Icons**: Font Awesome 6.4
- **Fonts**: Google Fonts (Poppins)

## Features
1. **Responsive Design**: Mobile-first approach with Bootstrap 5
2. **Hero Section**: Clean, professional layout with two-column design and floating illustration
3. **Services Carousel**: Sliding showcase of featured services with navigation controls
4. **Technology Stack Section**: Display of 12+ technologies with interactive hover effects
5. **Animated Counters**: Stats that count up on scroll
6. **Portfolio Filter**: JavaScript-based project filtering
7. **Contact Form**: PHP email handler with validation
8. **Smooth Scrolling**: Enhanced UX with animations
9. **Skill Bars**: Animated progress bars on About page
10. **Newsletter**: Footer subscription form

## Current Deployment
- **Server**: PHP Built-in Development Server
- **Port**: 5000
- **Command**: `php -S 0.0.0.0:5000`

## Email Configuration
The contact form uses PHP's `mail()` function with proper email headers.

### Important: Email Header Configuration
- **From**: Uses `noreply@dawntoweb.com` to avoid SPF/DMARC issues
- **Reply-To**: Contains visitor's email for easy responses
- This prevents email spoofing and improves deliverability

### Production Deployment Options

#### Option 1: Configure Sendmail/SMTP
1. Install and configure sendmail or SMTP on your server
2. Update PHP.ini with SMTP settings
3. Ensure `noreply@dawntoweb.com` is configured on your mail server
4. Test mail delivery

#### Option 2: Use Third-Party Email Service (Recommended)
Update `php/contact-form-handler.php` to use:
- **PHPMailer with SMTP** (Most reliable)
- **SendGrid API**
- **Mailgun API**
- **AWS SES**

Example with PHPMailer:
```bash
composer require phpmailer/phpmailer
```

Then update the handler to use PHPMailer instead of mail().

### Email Logging
All form submissions are logged to `contacts.txt` with:
- Timestamp and status (SENT/FAILED TO SEND)
- Complete contact information
- Full message content

This ensures no submission is lost even if email delivery fails.

### Fallback Behavior
If mail delivery fails, the form:
- Returns error message to user
- Logs complete submission to `contacts.txt` with "FAILED TO SEND" marker
- Provides direct contact email: info@dawntoweb.com
- Team can manually follow up using logged data

## Customization

### Update Company Information
1. **Contact Details**: Edit `includes/header.php` and `contact.php`
2. **Social Links**: Update `includes/footer.php`
3. **Services**: Modify `services.php` content
4. **Portfolio Items**: Update `portfolio.php` with your projects

### Branding Colors
Edit CSS variables in `assets/css/style.css`:
```css
:root {
    --primary-color: #0d6efd;
    --success-color: #10B981;
    --dark-color: #2C3E50;
}
```

### Logo
Replace the text logo in `includes/header.php` with an image:
```html
<img src="assets/images/logo.png" alt="Dawn To Web">
```

## Form Submissions
Contact form submissions are logged to `contacts.txt` with format:
```
YYYY-MM-DD HH:MM:SS - [SENT/FAILED] - Name, Email, Phone, Service
```

## Security Considerations
1. **Input Validation**: All form fields are validated
2. **Email Sanitization**: Using `filter_var()` for email validation
3. **XSS Prevention**: Data is properly escaped in PHP
4. **CSRF**: Consider adding CSRF tokens for production

## Browser Support
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## Performance
- Optimized images (using CDN)
- Minified CSS/JS in production
- Lazy loading for images
- AOS animations for smooth UX

## Recent Updates (November 2025)
1. **Hero Section Redesign**: Updated with light background, Kolkata location, and professional illustration
2. **Services Carousel**: Converted static grid to interactive Bootstrap carousel with 2 slides
3. **Technology Stack Section**: Added dedicated section showcasing 12 technologies (HTML5, CSS3, JavaScript, React, Node.js, Python, PHP, WordPress, Bootstrap, AWS, Docker, Git)

## Future Enhancements
- Blog CMS with database
- Portfolio detail pages
- Admin dashboard
- Email newsletter integration
- Live chat support
- SEO optimization
- Analytics integration

## Notes
- LSP warnings in `includes/header.php` are expected (variables defined in each page file)
- PHP development server is for development only
- For production, use Apache/Nginx with proper PHP-FPM configuration
- Enable HTTPS in production
- Set up database for dynamic content management
