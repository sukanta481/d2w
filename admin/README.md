# Dawn To Web - Admin CMS Documentation

## 🚀 Professional Content Management System

A complete, responsive admin panel to manage your website content, leads, projects, AI agents, and more.

---

## 📋 Table of Contents

1. [Installation](#installation)
2. [Features](#features)
3. [Default Login](#default-login)
4. [File Structure](#file-structure)
5. [Usage Guide](#usage-guide)
6. [Security](#security)

---

## 🛠️ Installation

### Step 1: Database Setup

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database or import the SQL file:
   - Navigate to `database/d2w_cms.sql`
   - Import this file into your MySQL database

### Step 2: Database Configuration

1. Open `admin/config/database.php`
2. Update the database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'd2w_cms');
   ```

### Step 3: Access Admin Panel

1. Navigate to: `http://localhost/d2w/admin/login.php`
2. Use default credentials (see below)

---

## 🎯 Features

### ✅ Complete Admin Dashboard
- **Real-time Statistics**: View leads, projects, AI agents, and blog posts count
- **Recent Activity Log**: Track all admin actions
- **Responsive Design**: Works on desktop, tablet, and mobile

### 📊 Leads Management
- View all customer inquiries
- Filter by status and priority
- Update lead status (new, contacted, qualified, won, lost)
- Add notes to each lead
- Search functionality

### 💼 Content Management
- **Projects**: Manage your portfolio items
- **AI Agents**: Showcase your AI solutions
- **Services**: Update service offerings
- **Testimonials**: Manage client reviews
- **Blog Posts**: Create and publish blog content

### 🔐 Security Features
- Secure authentication system
- Password hashing (bcrypt)
- Session management
- Activity logging
- Role-based access control (Super Admin, Admin, Editor)

### 📱 Responsive Design
- Mobile-friendly sidebar navigation
- Touch-optimized controls
- Responsive tables and forms
- Works on all screen sizes

---

## 🔑 Default Login

**Username:** `admin`
**Password:** `admin123`

> ⚠️ **IMPORTANT**: Change the default password immediately after first login!

---

## 📁 File Structure

```
d2w/
├── admin/
│   ├── assets/
│   │   ├── css/
│   │   │   └── admin.css          # Admin panel styles
│   │   └── js/
│   │       └── admin.js           # Admin panel JavaScript
│   ├── config/
│   │   └── database.php           # Database configuration
│   ├── includes/
│   │   ├── auth.php               # Authentication system
│   │   ├── header.php             # Admin header template
│   │   └── footer.php             # Admin footer template
│   ├── index.php                  # Dashboard
│   ├── login.php                  # Login page
│   ├── logout.php                 # Logout handler
│   ├── leads.php                  # Leads management
│   ├── projects.php               # Projects management (to be created)
│   ├── ai-agents.php              # AI Agents management (to be created)
│   ├── services.php               # Services management (to be created)
│   ├── testimonials.php           # Testimonials management (to be created)
│   ├── blog.php                   # Blog management (to be created)
│   ├── settings.php               # Site settings (to be created)
│   └── README.md                  # This file
└── database/
    └── d2w_cms.sql                # Database schema
```

---

## 📖 Usage Guide

### Managing Leads

1. Navigate to **Leads** from the sidebar
2. View all customer inquiries in a table format
3. **Filter leads** by:
   - Status (new, contacted, qualified, proposal sent, won, lost)
   - Priority (low, medium, high)
   - Search by name, email, or company
4. **View lead details**: Click the eye icon
5. **Update status**: Click the edit icon and update status/notes

### Managing Projects

1. Navigate to **Projects** from the sidebar
2. Add new projects with:
   - Title and description
   - Category (web design, e-commerce, AI, etc.)
   - Client name and project URL
   - Screenshots/images
   - Technologies used
3. Mark projects as featured for homepage display
4. Set display order for portfolio page

### Managing AI Agents

1. Navigate to **AI Agents** from the sidebar
2. Add your AI solutions with:
   - Name and description
   - Category and features
   - Pricing information
   - Demo and documentation links
3. Set status (active, inactive, coming soon)

### Managing Content

- **Services**: Update service offerings shown on the homepage
- **Testimonials**: Add/edit client reviews
- **Blog Posts**: Create blog content with rich text editor
- **Settings**: Update site-wide settings (email, phone, social links)

---

## 🔒 Security Best Practices

1. **Change Default Password**
   - Go to Profile settings
   - Update admin password immediately

2. **Create New Admin Users**
   - Add users with appropriate roles
   - Delete or disable default admin account after creating your own

3. **Regular Backups**
   - Backup database regularly
   - Keep backups in secure location

4. **File Permissions**
   - Set proper file permissions on server
   - Protect config files from public access

5. **HTTPS**
   - Use SSL certificate in production
   - Enable HTTPS for admin panel

---

## 🎨 Customization

### Changing Colors

Edit `admin/assets/css/admin.css`:

```css
:root {
    --primary: #0d6efd;      /* Primary color */
    --success: #10B981;      /* Success color */
    --dark: #2C3E50;         /* Dark color */
    /* ... more variables */
}
```

### Adding New Menu Items

Edit `admin/includes/header.php` to add new menu items to the sidebar.

### Creating New Pages

1. Copy an existing page (e.g., `leads.php`)
2. Update the page title and functionality
3. Add menu link in `includes/header.php`

---

## 📊 Database Tables

- **admin_users**: Admin user accounts
- **leads**: Customer inquiries
- **projects**: Portfolio projects
- **ai_agents**: AI solution listings
- **services**: Service offerings
- **testimonials**: Client testimonials
- **blog_posts**: Blog content
- **settings**: Site-wide settings
- **activity_log**: System activity tracking

---

## 🆘 Troubleshooting

### Cannot Login
- Check database connection in `config/database.php`
- Verify database is imported correctly
- Clear browser cache and cookies

### Database Connection Error
- Verify MySQL is running (XAMPP/WAMP)
- Check database credentials
- Ensure database exists

### Page Not Found
- Check file paths in Apache configuration
- Verify `.htaccess` if using URL rewriting
- Ensure all files are uploaded

---

## 📝 Future Enhancements

The following pages are planned for future development:
- Projects Management (full CRUD)
- AI Agents Management (full CRUD)
- Services Management (full CRUD)
- Testimonials Management (full CRUD)
- Blog Management (full CRUD with rich text editor)
- Settings Page (site configuration)
- User Management (manage admin users)
- Analytics Dashboard (advanced statistics)
- Email Templates
- File Manager

---

## 👨‍💻 Development

Built with:
- **PHP** - Backend logic
- **MySQL** - Database
- **Bootstrap 5** - UI framework
- **Font Awesome** - Icons
- **JavaScript** - Interactivity

---

## 📄 License

Copyright © 2025 Dawn To Web. All rights reserved.

---

## 💬 Support

For support or questions:
- Email: info@dawntoweb.com
- Website: [dawntoweb.com](https://dawntoweb.com)

---

**Happy Managing! 🚀**
