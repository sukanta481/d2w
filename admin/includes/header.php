<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>BizNexa CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a href="index.php" class="sidebar-brand">
                    <img src="../assets/images/logo.png" alt="BizNexa" height="40" style="margin-right: 10px;">
                    <div class="sidebar-brand-text">
                        <p>CMS Admin</p>
                    </div>
                </a>
            </div>

            <nav class="sidebar-menu">
                <div class="menu-section-title">Main</div>
                <ul>
                    <li class="menu-item">
                        <a href="index.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="leads.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) === 'leads.php' ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i>
                            <span>Leads</span>
                            <?php
                            // Get new leads count
                            try {
                                $stmt = $db->query("SELECT COUNT(*) as count FROM leads WHERE status = 'new'");
                                $newLeadsCount = $stmt->fetch()['count'];
                                if ($newLeadsCount > 0) {
                                    echo '<span class="menu-badge">' . $newLeadsCount . '</span>';
                                }
                            } catch(Exception $e) {}
                            ?>
                        </a>
                    </li>
                </ul>

                <div class="menu-section-title">Billing</div>
                <ul>
                    <li class="menu-item">
                        <a href="clients.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) === 'clients.php' ? 'active' : ''; ?>">
                            <i class="fas fa-user-tie"></i>
                            <span>Clients</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="billing.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) === 'billing.php' ? 'active' : ''; ?>">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Bills</span>
                            <?php
                            // Get unpaid bills count
                            try {
                                $stmt = $db->query("SELECT COUNT(*) as count FROM bills WHERE payment_status = 'unpaid'");
                                $unpaidCount = $stmt->fetch()['count'];
                                if ($unpaidCount > 0) {
                                    echo '<span class="menu-badge bg-danger">' . $unpaidCount . '</span>';
                                }
                            } catch(Exception $e) {}
                            ?>
                        </a>
                    </li>
                </ul>

                <div class="menu-section-title">Content</div>
                <ul>
                    <li class="menu-item">
                        <a href="projects.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) === 'projects.php' ? 'active' : ''; ?>">
                            <i class="fas fa-briefcase"></i>
                            <span>Projects</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="ai-agents.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) === 'ai-agents.php' ? 'active' : ''; ?>">
                            <i class="fas fa-robot"></i>
                            <span>AI Agents</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="services.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) === 'services.php' ? 'active' : ''; ?>">
                            <i class="fas fa-cogs"></i>
                            <span>Services</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="testimonials.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) === 'testimonials.php' ? 'active' : ''; ?>">
                            <i class="fas fa-star"></i>
                            <span>Testimonials</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="blog.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) === 'blog.php' ? 'active' : ''; ?>">
                            <i class="fas fa-blog"></i>
                            <span>Blog Posts</span>
                        </a>
                    </li>
                </ul>

                <div class="menu-section-title">Settings</div>
                <ul>
                    <li class="menu-item">
                        <a href="settings.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
                            <i class="fas fa-sliders-h"></i>
                            <span>Site Settings</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="profile.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">
                            <i class="fas fa-user-circle"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="logout.php" class="menu-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>

                    <div class="header-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search...">
                    </div>
                </div>

                <div class="header-right">
                    <button class="header-icon-btn" title="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>

                    <button class="header-icon-btn" title="Settings">
                        <i class="fas fa-cog"></i>
                    </button>

                    <div class="user-dropdown" onclick="window.location.href='profile.php'">
                        <div class="user-avatar">
                            <?php
                            if (!empty($_SESSION['admin_avatar'])) {
                                echo '<img src="' . htmlspecialchars($_SESSION['admin_avatar']) . '" alt="Avatar" style="width:100%;height:100%;border-radius:10px;">';
                            } else {
                                $initials = strtoupper(substr($_SESSION['admin_full_name'], 0, 1));
                                echo $initials;
                            }
                            ?>
                        </div>
                        <div class="user-info">
                            <h4><?php echo htmlspecialchars($_SESSION['admin_full_name']); ?></h4>
                            <p><?php echo ucfirst($_SESSION['admin_role']); ?></p>
                        </div>
                    </div>
                </div>
            </header>
