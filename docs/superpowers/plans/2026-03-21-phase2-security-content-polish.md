# Phase 2: Security Hardening, AI Agents Showcase & Polish — Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Harden security (credentials, CSRF, sessions, rate limiting), surface AI agents on the services page, add FAQ with schema markup, add Copy Link to blog sharing, and consolidate inline CSS.

**Architecture:** PHP-only changes — no framework additions. Security helpers go in `includes/`. CSRF is session-based. Rate limiting uses a new DB table. AI agents use existing `getAIAgents()`. FAQ is hardcoded Bootstrap accordion with JSON-LD.

**Tech Stack:** PHP 8+, MySQL (PDO), Bootstrap 5, Font Awesome 6, JSON-LD schema

---

## Chunk 1: Security Hardening

### Task 1: Environment-based credentials

**Files:**
- Create: `config.env.php`
- Create: `.env.example`
- Modify: `includes/db_config.php:14-26`
- Modify: `admin/config/database.php:14-26`
- Modify: `.gitignore`

- [ ] **Step 1: Create `.env.example` with placeholder values**

```
# BizNexa Environment Configuration
# Copy this to .env and fill in real values

DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=d2w_cms

# Production values (Hostinger)
# DB_HOST=localhost
# DB_USER=u286257250_d2w
# DB_PASS=your_password_here
# DB_NAME=u286257250_d2w_cms

# Set to true only when running install.php
ALLOW_INSTALL=false
```

- [ ] **Step 2: Create `config.env.php` — shared .env loader**

```php
<?php
/**
 * Environment Configuration Loader
 * Reads .env file if it exists, otherwise falls back to hardcoded values.
 */

function loadEnv($path = null) {
    $envFile = $path ?? __DIR__ . '/.env';

    if (!file_exists($envFile)) {
        return false;
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) continue;

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        // Remove surrounding quotes
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
        putenv("$key=$value");
    }

    return true;
}

// Load .env from project root
loadEnv(__DIR__ . '/.env');
```

- [ ] **Step 3: Update `includes/db_config.php` to use env vars**

Replace lines 1-26 with:

```php
<?php
/**
 * Frontend Database Configuration
 * BizNexa Website
 */

require_once __DIR__ . '/../config.env.php';

// Check if we're on local or production server
$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'])
           || strpos($_SERVER['SERVER_NAME'] ?? '', '.local') !== false
           || (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1');

if (!defined('DB_HOST')) {
    if ($isLocal) {
        define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
        define('DB_USER', getenv('DB_USER') ?: 'root');
        define('DB_PASS', getenv('DB_PASS') ?: '');
        define('DB_NAME', getenv('DB_NAME') ?: 'd2w_cms');
    } else {
        // Production MUST use .env — no hardcoded credentials
        $dbHost = getenv('DB_HOST');
        $dbUser = getenv('DB_USER');
        $dbPass = getenv('DB_PASS');
        $dbName = getenv('DB_NAME');
        if (!$dbHost || !$dbUser || !$dbName) {
            error_log('BizNexa: Missing .env database credentials for production');
            die('Database configuration error. Contact administrator.');
        }
        define('DB_HOST', $dbHost);
        define('DB_USER', $dbUser);
        define('DB_PASS', $dbPass);
        define('DB_NAME', $dbName);
    }
}
```

> Note: Local dev falls back to XAMPP defaults. Production requires `.env` — no hardcoded credentials. `defined()` guards prevent fatal errors if both DB configs are loaded.

- [ ] **Step 4: Update `admin/config/database.php` the same way**

Replace lines 1-27 with:

```php
<?php
/**
 * Database Configuration
 * BizNexa CMS
 */

require_once __DIR__ . '/../../config.env.php';

$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'])
           || strpos($_SERVER['SERVER_NAME'] ?? '', '.local') !== false
           || (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1');

if (!defined('DB_HOST')) {
    if ($isLocal) {
        define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
        define('DB_USER', getenv('DB_USER') ?: 'root');
        define('DB_PASS', getenv('DB_PASS') ?: '');
        define('DB_NAME', getenv('DB_NAME') ?: 'd2w_cms');
    } else {
        $dbHost = getenv('DB_HOST');
        $dbUser = getenv('DB_USER');
        $dbPass = getenv('DB_PASS');
        $dbName = getenv('DB_NAME');
        if (!$dbHost || !$dbUser || !$dbName) {
            error_log('BizNexa: Missing .env database credentials for production');
            die('Database configuration error. Contact administrator.');
        }
        define('DB_HOST', $dbHost);
        define('DB_USER', $dbUser);
        define('DB_PASS', $dbPass);
        define('DB_NAME', $dbName);
    }
}
define('DB_CHARSET', 'utf8mb4');
```

- [ ] **Step 5: Add `.env` to `.gitignore`**

Append to `.gitignore`:
```
.env
```

- [ ] **Step 6: Commit**

```bash
git add config.env.php .env.example includes/db_config.php admin/config/database.php .gitignore
git commit -m "feat: add .env-based credential loading with hardcoded fallback"
```

---

### Task 2: CSRF token protection

**Files:**
- Create: `includes/csrf.php`
- Modify: `contact.php` (add hidden field to form, ~line 112)
- Modify: `php/contact-form-handler.php` (validate token, ~line 7)
- Modify: `includes/footer.php` (add hidden field to newsletter form, ~line 60)
- Modify: `admin/login.php` (add hidden field + validate, ~lines 30 and 67)

- [ ] **Step 1: Create `includes/csrf.php`**

```php
<?php
/**
 * CSRF Protection
 * BizNexa
 */

function generateCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

function validateCsrfToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    $valid = hash_equals($_SESSION['csrf_token'], $token);

    // Rotate token after validation
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    return $valid;
}
```

- [ ] **Step 2: Add CSRF field to contact form in `contact.php`**

Add `require_once 'includes/csrf.php';` near the top of `contact.php` (after `include_once 'includes/db_config.php';`).

Find the `<form id="contactForm"` tag (~line 112) and add right after it:

```php
                <?php echo csrfField(); ?>
```

- [ ] **Step 3: Validate CSRF in `php/contact-form-handler.php`**

Add after the `include_once` line (~line 5):

```php
// Start session and validate CSRF
session_start();
require_once __DIR__ . '/../includes/csrf.php';

if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid form submission. Please refresh and try again.']);
    exit;
}
```

- [ ] **Step 4: Add CSRF to newsletter form in `includes/footer.php`**

Find `<form class="newsletter-form" id="newsletterForm">` (~line 60) and add inside:

```php
                        <?php echo csrfField(); ?>
```

Note: `csrf.php` is already loaded via the page that includes footer.php. If not, add `require_once __DIR__ . '/csrf.php';` at top of footer.php.

- [ ] **Step 5: Add CSRF to admin login form in `admin/login.php`**

Add `require_once __DIR__ . '/includes/csrf_admin.php';` — wait, admin already has `session_start()` in auth.php. Just include csrf.php.

After `require_once 'includes/auth.php';` (line 2), add:
```php
require_once __DIR__ . '/../includes/csrf.php';
```

Add `<?php echo csrfField(); ?>` inside the `<form method="POST">` tag (~line 67).

Add CSRF validation in the POST handler (before `$auth->login()`):
```php
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please refresh and try again.';
    } else if (empty($username) || empty($password)) {
```

- [ ] **Step 6: Add CSRF to all admin CRUD forms**

For each admin page that has a POST form (`admin/services.php`, `admin/projects.php`, `admin/blog.php`, `admin/testimonials.php`, `admin/ai-agents.php`, `admin/clients.php`, `admin/billing.php`, `admin/settings.php`, `admin/profile.php`):

1. Add `require_once __DIR__ . '/../includes/csrf.php';` at top (after auth.php include)
2. Add `<?php echo csrfField(); ?>` inside each `<form>` tag
3. Add CSRF validation at the start of each POST handler block:
```php
if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $error = 'Invalid form submission. Please refresh and try again.';
} else {
    // existing POST handling code
}
```

> Note: The newsletter form in footer.php has no server-side handler, so the CSRF field is just future-proofing. Skip if no handler exists.

- [ ] **Step 7: Commit**

```bash
git add includes/csrf.php contact.php php/contact-form-handler.php includes/footer.php admin/login.php admin/services.php admin/projects.php admin/blog.php admin/testimonials.php admin/ai-agents.php admin/clients.php admin/billing.php admin/settings.php admin/profile.php
git commit -m "feat: add CSRF token protection to all public and admin forms"
```

---

### Task 3: Session hardening

**Files:**
- Modify: `admin/includes/auth.php:36-48` (login method — add session_regenerate_id)
- Modify: `admin/includes/auth.php:76-81` (requireLogin method — add timeout + UA check)

- [ ] **Step 1: Add session_regenerate_id on login**

In `admin/includes/auth.php`, inside the `login()` method, after `if (password_verify($password, $user['password'])) {` (line 36), add:

```php
                    // Regenerate session ID to prevent fixation
                    session_regenerate_id(true);

                    // Store session security data
                    $_SESSION['last_activity'] = time();
                    $_SESSION['user_agent_hash'] = md5($_SERVER['HTTP_USER_AGENT'] ?? '');
```

- [ ] **Step 2: Add timeout and UA check to requireLogin**

Replace the `requireLogin()` method (lines 76-81) with:

```php
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }

        // 30-minute idle timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 1800) {
            $this->logout();
            header('Location: login.php?timeout=1');
            exit();
        }
        $_SESSION['last_activity'] = time();

        // User-agent consistency check
        $currentUA = md5($_SERVER['HTTP_USER_AGENT'] ?? '');
        if (isset($_SESSION['user_agent_hash']) && $_SESSION['user_agent_hash'] !== $currentUA) {
            $this->logout();
            header('Location: login.php');
            exit();
        }
    }
```

- [ ] **Step 3: Commit**

```bash
git add admin/includes/auth.php
git commit -m "feat: add session hardening — regeneration, timeout, UA validation"
```

---

### Task 4: Install script lockdown

**Files:**
- Modify: `admin/install.php:1-7`

- [ ] **Step 1: Add environment gate to `admin/install.php`**

Replace lines 1-7 with:

```php
<?php
/**
 * Database Installation Script
 * BizNexa CMS
 *
 * Run this file once to set up the database.
 * Requires ALLOW_INSTALL=true in .env to run.
 */

require_once __DIR__ . '/../config.env.php';

if (getenv('ALLOW_INSTALL') !== 'true') {
    http_response_code(404);
    echo '<!DOCTYPE html><html><body><h1>404 Not Found</h1></body></html>';
    exit;
}
```

- [ ] **Step 2: Commit**

```bash
git add admin/install.php
git commit -m "feat: gate install.php behind ALLOW_INSTALL env var"
```

---

### Task 5: Contact form rate limiting

**Files:**
- Modify: `includes/db_config.php` (add `checkRateLimit()` function, append after `saveLead()`)
- Modify: `php/contact-form-handler.php` (add rate limit check before validation)
- Create: `database/migration_rate_limits.sql`

- [ ] **Step 1: Create migration SQL**

Create `database/migration_rate_limits.sql`:

```sql
-- Rate Limiting Table
CREATE TABLE IF NOT EXISTS rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    action VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_rate_lookup (ip_address, action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

- [ ] **Step 2: Add `checkRateLimit()` to `includes/db_config.php`**

Append before the closing `?>` tag:

```php
// Rate limiting
function checkRateLimit($ip, $action, $maxAttempts = 5, $windowMinutes = 60) {
    $db = getDBConnection();
    if (!$db) return true; // Allow if DB unavailable

    try {
        // Clean up old records
        $cleanup = $db->prepare("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL :window MINUTE)");
        $cleanup->execute([':window' => $windowMinutes]);

        // Count recent attempts
        $stmt = $db->prepare("SELECT COUNT(*) as attempts FROM rate_limits WHERE ip_address = :ip AND action = :action AND created_at > DATE_SUB(NOW(), INTERVAL :window MINUTE)");
        $stmt->execute([':ip' => $ip, ':action' => $action, ':window' => $windowMinutes]);
        $result = $stmt->fetch();

        if ($result['attempts'] >= $maxAttempts) {
            return false; // Rate limited
        }

        // Record this attempt
        $insert = $db->prepare("INSERT INTO rate_limits (ip_address, action) VALUES (:ip, :action)");
        $insert->execute([':ip' => $ip, ':action' => $action]);

        return true; // Allowed
    } catch (PDOException $e) {
        return true; // Allow on error
    }
}
```

- [ ] **Step 3: Add rate limit check to `php/contact-form-handler.php`**

After the CSRF validation block (added in Task 2), add:

```php
// Rate limiting — max 5 submissions per IP per hour
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
if (!checkRateLimit($clientIp, 'contact_form', 5, 60)) {
    echo json_encode(['success' => false, 'message' => 'Too many submissions. Please wait before trying again.']);
    exit;
}
```

- [ ] **Step 4: Run migration on local DB**

```bash
cd c:/xampp/htdocs/d2w && php -r "
require 'includes/db_config.php';
\$db = getDBConnection();
\$sql = file_get_contents('database/migration_rate_limits.sql');
\$db->exec(\$sql);
echo 'rate_limits table created';
"
```

- [ ] **Step 5: Commit**

```bash
git add includes/db_config.php php/contact-form-handler.php database/migration_rate_limits.sql
git commit -m "feat: add IP-based rate limiting to contact form (5/hour)"
```

---

## Chunk 2: AI Agents Showcase & FAQ

### Task 6: AI Agents showcase on services page

**Files:**
- Modify: `services.php:323` (insert AI agents section after the `#ai-automation` pillar closing `</section>` at line 323, before the Digital Marketing pillar)

Wait — re-reading the page structure: the AI agents should go INSIDE the `#ai-automation` pillar, after the existing content. But the pillar is self-contained. Better approach: add a new section right after the AI pillar (line 323) as a sub-section.

Actually the best placement is after ALL 3 pillars (line 371) and before the Process section (line 373). This way it's a standalone "Our AI Solutions" showcase.

- Modify: `services.php:371-373` (insert new section between pillar sections and process section)
- Modify: `includes/db_config.php` (update `getAIAgents()` to include `coming_soon` status)
- Modify: `assets/css/style.css` (append AI agent card styles)
- Modify: `includes/seo.php` (add Product schema — done in Task 7 alongside FAQPage)

- [ ] **Step 1: Add AI Agents section to `services.php`**

Insert after line 371 (`</section>` closing Digital Marketing pillar) and before line 373 (`<!-- Our Process Section`):

```php
<!-- AI Solutions Showcase -->
<section class="section-light-animated ai-solutions-section" style="padding: 100px 0;">
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
    </div>

    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <div class="section-badge d-inline-flex" style="background: rgba(13, 110, 253, 0.1); border-color: rgba(13, 110, 253, 0.2);">
                <i class="fas fa-robot" style="color: #0d6efd;"></i>
                <span style="color: #0d6efd;">AI Solutions</span>
            </div>
            <h2 style="color: #1e293b; font-size: 2.5rem; font-weight: 800; margin-top: 15px;">Our AI <span style="color: #0d6efd;">Agents</span></h2>
            <p style="color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 15px auto 0;">Intelligent AI agents built to automate, engage, and grow your business.</p>
        </div>

        <div class="row">
            <?php
            $aiAgents = getAIAgents();
            if (empty($aiAgents)) {
                // Fallback hardcoded agents
                $aiAgents = [
                    [
                        'name' => 'Customer Support Bot',
                        'description' => 'AI-powered chatbot that handles customer queries 24/7 with natural conversation, ticket creation, and smart escalation.',
                        'icon' => 'fas fa-headset',
                        'features' => 'Multi-language,24/7 Availability,Smart Escalation,Ticket Integration',
                        'pricing' => null,
                        'demo_url' => null,
                        'documentation_url' => null,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Lead Qualifier Agent',
                        'description' => 'Automatically scores and qualifies incoming leads based on behavior, demographics, and engagement patterns.',
                        'icon' => 'fas fa-filter',
                        'features' => 'Lead Scoring,CRM Sync,Auto Follow-up,Analytics Dashboard',
                        'pricing' => null,
                        'demo_url' => null,
                        'documentation_url' => null,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Content Generator',
                        'description' => 'Generate SEO-optimized blog posts, social media content, and marketing copy tailored to your brand voice.',
                        'icon' => 'fas fa-pen-fancy',
                        'features' => 'SEO Optimized,Brand Voice,Multi-format,Batch Generation',
                        'pricing' => null,
                        'demo_url' => null,
                        'documentation_url' => null,
                        'status' => 'active',
                    ],
                ];
            }

            foreach ($aiAgents as $index => $agent):
                $features = !empty($agent['features']) ? array_map('trim', explode(',', $agent['features'])) : [];
                $isComingSoon = ($agent['status'] ?? '') === 'coming_soon';
            ?>
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                <div class="ai-agent-card <?php echo $isComingSoon ? 'coming-soon' : ''; ?>">
                    <?php if ($isComingSoon): ?>
                    <span class="ai-agent-badge">Coming Soon</span>
                    <?php endif; ?>

                    <div class="ai-agent-icon">
                        <i class="<?php echo htmlspecialchars($agent['icon'] ?? 'fas fa-robot'); ?>"></i>
                    </div>

                    <h3 class="ai-agent-name"><?php echo htmlspecialchars($agent['name']); ?></h3>

                    <p class="ai-agent-desc"><?php echo htmlspecialchars(substr($agent['description'] ?? '', 0, 120)); ?><?php echo strlen($agent['description'] ?? '') > 120 ? '...' : ''; ?></p>

                    <?php if (!empty($features)): ?>
                    <div class="ai-agent-features">
                        <?php foreach (array_slice($features, 0, 4) as $feature): ?>
                        <span class="ai-feature-tag"><?php echo htmlspecialchars($feature); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($agent['pricing'])): ?>
                    <div class="ai-agent-pricing">Starting at $<?php echo htmlspecialchars($agent['pricing']); ?></div>
                    <?php endif; ?>

                    <div class="ai-agent-actions">
                        <?php if (!empty($agent['demo_url']) && !$isComingSoon): ?>
                        <a href="<?php echo htmlspecialchars($agent['demo_url']); ?>" class="btn-agent-demo" target="_blank">
                            <i class="fas fa-play"></i> Try Demo
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($agent['documentation_url'])): ?>
                        <a href="<?php echo htmlspecialchars($agent['documentation_url']); ?>" class="btn-agent-docs" target="_blank">
                            Learn More <i class="fas fa-arrow-right"></i>
                        </a>
                        <?php elseif (!$isComingSoon): ?>
                        <a href="contact.php" class="btn-agent-docs">
                            Get Started <i class="fas fa-arrow-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
```

- [ ] **Step 2: Add AI agent card CSS to `assets/css/style.css`**

Append to the end of the file (before any media queries or after the last rule):

```css
/* ====================== AI Agent Cards ====================== */
.ai-agent-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 30px 25px;
    height: 100%;
    display: flex;
    flex-direction: column;
    position: relative;
    transition: transform 0.2s ease-out, box-shadow 0.2s ease-out, border-color 0.2s ease-out;
}

.ai-agent-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
    border-color: rgba(13, 110, 253, 0.3);
}

.ai-agent-card.coming-soon {
    opacity: 0.7;
}

.ai-agent-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.ai-agent-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(145deg, #0d6efd, #6610f2);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}

.ai-agent-icon i {
    font-size: 24px;
    color: #fff;
}

.ai-agent-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 10px;
}

.ai-agent-desc {
    color: #64748b;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 15px;
    flex-grow: 1;
}

.ai-agent-features {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 15px;
}

.ai-feature-tag {
    background: #f1f5f9;
    color: #475569;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
}

.ai-agent-pricing {
    color: #0d6efd;
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 15px;
}

.ai-agent-actions {
    display: flex;
    gap: 10px;
    margin-top: auto;
}

.btn-agent-demo {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #0d6efd, #6610f2);
    color: #fff;
    padding: 8px 18px;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
}

.btn-agent-demo:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    color: #fff;
}

.btn-agent-docs {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: transparent;
    color: #0d6efd;
    padding: 8px 18px;
    border: 1px solid rgba(13, 110, 253, 0.3);
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    transition: transform 0.2s ease-out, background 0.2s ease-out;
}

.btn-agent-docs:hover {
    background: rgba(13, 110, 253, 0.05);
    transform: translateY(-2px);
    color: #0d6efd;
}
```

- [ ] **Step 3: Update `getAIAgents()` in `includes/db_config.php`**

Change the WHERE clause from `status = 'active'` to include `coming_soon`:

```php
$stmt = $db->query("SELECT * FROM ai_agents WHERE status IN ('active', 'coming_soon') ORDER BY display_order ASC, id ASC");
```

- [ ] **Step 4: Pass AI agents data to `$pageMeta` in `services.php`**

In `services.php`, find where `$pageMeta` is defined (near the top). Add after the `$pageMeta` array definition:

```php
// Load AI agents for schema
$pageMeta['ai_agents'] = getAIAgents();
```

- [ ] **Step 5: Commit**

```bash
git add services.php assets/css/style.css includes/seo.php
git commit -m "feat: add AI agents showcase section to services page with Product schema"
```

---

### Task 7: FAQ section with FAQPage schema

**Files:**
- Modify: `services.php` (insert FAQ section before the inline `<style>` block, which starts around line after Tech Stack section)
- Modify: `includes/seo.php` (add FAQPage schema support)
- Modify: `assets/css/style.css` (add FAQ styles)

- [ ] **Step 1: Find insertion point in `services.php`**

The FAQ goes after the Tech Stack section and before the inline `<style>` block. Look for the closing `</section>` of the Tech Stack section, and insert before the `<style>` tag.

Insert the FAQ section in `services.php` before the `<style>` block (which starts the inline styles):

```php
<!-- FAQ Section -->
<section class="section-dark-animated faq-section" style="padding: 100px 0;">
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
    </div>

    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <div class="section-badge d-inline-flex" style="background: rgba(13, 110, 253, 0.2); border-color: rgba(13, 110, 253, 0.3);">
                <i class="fas fa-question-circle" style="color: #60a5fa;"></i>
                <span style="color: #60a5fa;">FAQ</span>
            </div>
            <h2 style="color: #fff; font-size: 2.5rem; font-weight: 800; margin-top: 15px;">Frequently Asked <span class="text-gradient">Questions</span></h2>
            <p style="color: #D1D5DB; font-size: 1.1rem; max-width: 600px; margin: 15px auto 0;">Common questions about our services, process, and pricing.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
                <div class="accordion faq-accordion" id="servicesFAQ">
                    <?php
                    $faqs = [
                        [
                            'q' => 'How long does it take to build a website?',
                            'a' => 'A typical business website takes 2-4 weeks from design approval to launch. E-commerce sites and complex web applications may take 4-8 weeks depending on features and integrations required.',
                        ],
                        [
                            'q' => 'What AI automation solutions do you offer?',
                            'a' => 'We build custom AI chatbots (powered by GPT-4, Claude, and Gemini), workflow automation using n8n and Zapier, lead qualification agents, content generation tools, and smart analytics dashboards tailored to your business needs.',
                        ],
                        [
                            'q' => 'How does your digital marketing pricing work?',
                            'a' => 'We offer flexible engagement models — monthly retainers for ongoing SEO and social media management, project-based pricing for campaigns and ad setup, and performance-based models for PPC management. Contact us for a custom quote based on your goals.',
                        ],
                        [
                            'q' => 'Do you provide ongoing support after launch?',
                            'a' => 'Yes. Every project includes 30 days of post-launch support. We also offer monthly maintenance plans covering security updates, content changes, performance monitoring, and priority bug fixes.',
                        ],
                        [
                            'q' => 'Can you work with my existing website or do I need a new one?',
                            'a' => 'We work with both. We can redesign and optimize your existing website, add new features, improve performance, or build a completely new site from scratch depending on what makes the most sense for your business.',
                        ],
                        [
                            'q' => 'What technologies do you use for web development?',
                            'a' => 'We use modern, industry-standard technologies including React, Next.js, and Vue.js for frontends; PHP, Node.js, and Python for backends; MySQL and MongoDB for databases; and AWS, Vercel, and Hostinger for hosting and deployment.',
                        ],
                        [
                            'q' => 'How do I get started with your services?',
                            'a' => 'Simply fill out our contact form or reach us via WhatsApp, email, or phone. We\'ll schedule a free 30-minute discovery call to understand your requirements, after which we\'ll send you a detailed proposal with scope, timeline, and pricing.',
                        ],
                    ];

                    foreach ($faqs as $i => $faq):
                    ?>
                    <div class="accordion-item faq-item">
                        <h3 class="accordion-header" id="faqHead<?php echo $i; ?>">
                            <button class="accordion-button <?php echo $i > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faqBody<?php echo $i; ?>" aria-expanded="<?php echo $i === 0 ? 'true' : 'false'; ?>" aria-controls="faqBody<?php echo $i; ?>">
                                <?php echo htmlspecialchars($faq['q']); ?>
                            </button>
                        </h3>
                        <div id="faqBody<?php echo $i; ?>" class="accordion-collapse collapse <?php echo $i === 0 ? 'show' : ''; ?>" aria-labelledby="faqHead<?php echo $i; ?>" data-bs-parent="#servicesFAQ">
                            <div class="accordion-body">
                                <?php echo htmlspecialchars($faq['a']); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
```

- [ ] **Step 2: Add FAQPage schema support to `includes/seo.php`**

Add a new case in the `switch ($schemaType)` block, after the `case 'ContactPage':` block:

```php
        case 'FAQService':
            // Service schemas (same as 'Service')
            $services = [
                ['name' => 'Web Development', 'description' => 'Custom website design and development, e-commerce solutions, responsive web applications, and CMS development.'],
                ['name' => 'AI & Automation', 'description' => 'AI chatbots, workflow automation, intelligent integrations, and smart analytics for business growth.'],
                ['name' => 'Digital Marketing', 'description' => 'SEO optimization, social media marketing, content strategy, and PPC campaign management.'],
            ];
            foreach ($services as $service) {
                $schemas[] = [
                    '@context' => 'https://schema.org',
                    '@type' => 'Service',
                    'serviceType' => $service['name'],
                    'name' => $service['name'],
                    'description' => $service['description'],
                    'provider' => ['@type' => 'Organization', 'name' => 'BizNexa', 'url' => $siteUrl],
                ];
            }
            // FAQPage schema
            if (!empty($pageMeta['faqs'])) {
                $faqItems = [];
                foreach ($pageMeta['faqs'] as $faq) {
                    $faqItems[] = [
                        '@type' => 'Question',
                        'name' => $faq['q'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $faq['a'],
                        ],
                    ];
                }
                $schemas[] = [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => $faqItems,
                ];
            }
            // AI Agent Product schemas
            if (!empty($pageMeta['ai_agents'])) {
                foreach ($pageMeta['ai_agents'] as $agent) {
                    $productSchema = [
                        '@context' => 'https://schema.org',
                        '@type' => 'Product',
                        'name' => $agent['name'],
                        'description' => $agent['description'] ?? '',
                        'brand' => ['@type' => 'Brand', 'name' => 'BizNexa'],
                        'category' => 'AI Software',
                    ];
                    if (!empty($agent['pricing'])) {
                        $productSchema['offers'] = [
                            '@type' => 'Offer',
                            'price' => $agent['pricing'],
                            'priceCurrency' => 'USD',
                            'availability' => ($agent['status'] === 'active') ? 'https://schema.org/InStock' : 'https://schema.org/PreOrder',
                        ];
                    }
                    $schemas[] = $productSchema;
                }
            }
            break;
```

**IMPORTANT:** Also update `services.php` `$pageMeta['schema']` from `'Service'` to `'FAQService'` and add `'faqs'` key with the same FAQ array. This means the FAQ array should be defined in `$pageMeta` before the header include.

- [ ] **Step 3: Update `services.php` `$pageMeta` to include FAQs and use new schema type**

Find where `$pageMeta` is defined in `services.php` (near the top). Change `'schema' => 'Service'` to `'schema' => 'FAQService'` and add the faqs array:

```php
$pageMeta['schema'] = 'FAQService';
$pageMeta['faqs'] = [
    ['q' => 'How long does it take to build a website?', 'a' => 'A typical business website takes 2-4 weeks from design approval to launch. E-commerce sites and complex web applications may take 4-8 weeks depending on features and integrations required.'],
    ['q' => 'What AI automation solutions do you offer?', 'a' => 'We build custom AI chatbots (powered by GPT-4, Claude, and Gemini), workflow automation using n8n and Zapier, lead qualification agents, content generation tools, and smart analytics dashboards tailored to your business needs.'],
    ['q' => 'How does your digital marketing pricing work?', 'a' => 'We offer flexible engagement models — monthly retainers for ongoing SEO and social media management, project-based pricing for campaigns and ad setup, and performance-based models for PPC management. Contact us for a custom quote based on your goals.'],
    ['q' => 'Do you provide ongoing support after launch?', 'a' => 'Yes. Every project includes 30 days of post-launch support. We also offer monthly maintenance plans covering security updates, content changes, performance monitoring, and priority bug fixes.'],
    ['q' => 'Can you work with my existing website or do I need a new one?', 'a' => 'We work with both. We can redesign and optimize your existing website, add new features, improve performance, or build a completely new site from scratch depending on what makes the most sense for your business.'],
    ['q' => 'What technologies do you use for web development?', 'a' => 'We use modern, industry-standard technologies including React, Next.js, and Vue.js for frontends; PHP, Node.js, and Python for backends; MySQL and MongoDB for databases; and AWS, Vercel, and Hostinger for hosting and deployment.'],
    ['q' => 'How do I get started with your services?', 'a' => 'Simply fill out our contact form or reach us via WhatsApp, email, or phone. We\'ll schedule a free 30-minute discovery call to understand your requirements, after which we\'ll send you a detailed proposal with scope, timeline, and pricing.'],
];
```

- [ ] **Step 4: Add FAQ CSS to `assets/css/style.css`**

```css
/* ====================== FAQ Section ====================== */
.faq-accordion .faq-item {
    background: rgba(30, 41, 59, 0.7);
    border: 1px solid rgba(96, 165, 250, 0.15);
    border-radius: 12px !important;
    margin-bottom: 12px;
    overflow: hidden;
}

.faq-accordion .accordion-button {
    background: transparent;
    color: #fff;
    font-weight: 600;
    font-size: 1.05rem;
    padding: 18px 24px;
    box-shadow: none;
    border: none;
}

.faq-accordion .accordion-button:not(.collapsed) {
    background: rgba(13, 110, 253, 0.1);
    color: #60a5fa;
}

.faq-accordion .accordion-button::after {
    filter: brightness(0) invert(1);
}

.faq-accordion .accordion-button:not(.collapsed)::after {
    filter: brightness(0) invert(0.7) sepia(1) hue-rotate(190deg);
}

.faq-accordion .accordion-body {
    color: #D1D5DB;
    padding: 0 24px 18px;
    font-size: 0.95rem;
    line-height: 1.7;
}
```

- [ ] **Step 5: Commit**

```bash
git add services.php includes/seo.php assets/css/style.css
git commit -m "feat: add FAQ section with FAQPage schema to services page"
```

---

## Chunk 3: Blog & CSS Polish

> **Note on blog pagination SEO:** The spec calls for `rel="next"`/`rel="prev"` tags, but `blog.php` currently uses static placeholder pagination (hardcoded page 1/2/3 links with no real page parameter handling). Adding `rel` tags requires real pagination first. **Skipped for now** — will be addressed when real pagination is implemented.

### Task 8: Add Copy Link button to blog sharing

> **Note:** Facebook, Twitter/X, LinkedIn, and WhatsApp share buttons already exist in `blog-post.php:141-144`. Only Copy Link is missing.

**Files:**
- Modify: `blog-post.php:144` (add Copy Link button after WhatsApp)

- [ ] **Step 1: Add Copy Link button**

In `blog-post.php`, after the WhatsApp share button (line 144), add:

```php
                        <button onclick="copyPostLink(this)" class="share-btn copy-link" title="Copy link"><i class="fas fa-link"></i></button>
```

- [ ] **Step 2: Add the copyPostLink JS function**

In the inline `<style>` block at the bottom of `blog-post.php`, add after the closing `</style>` tag and before `<?php include 'includes/footer.php'; ?>`:

```html
<script>
function copyPostLink(btn) {
    navigator.clipboard.writeText(window.location.href).then(function() {
        var icon = btn.querySelector('i');
        icon.className = 'fas fa-check';
        btn.style.background = '#10B981';
        setTimeout(function() {
            icon.className = 'fas fa-link';
            btn.style.background = '#6b7280';
        }, 2000);
    });
}
</script>
```

- [ ] **Step 3: Add CSS for copy-link button**

In the inline `<style>` of `blog-post.php`, add:

```css
.share-btn.copy-link {
    background: #6b7280;
    border: none;
    cursor: pointer;
    transition: transform 0.2s ease-out, background 0.2s ease-out;
}
.share-btn.copy-link:hover {
    transform: translateY(-3px);
}
```

- [ ] **Step 4: Commit**

```bash
git add blog-post.php
git commit -m "feat: add Copy Link button to blog post sharing"
```

---

### Task 9: Consolidate inline styles from PHP files to style.css

**Files:**
- Modify: `assets/css/style.css` (append extracted styles)
- Modify: `blog-post.php` (remove inline `<style>` block, lines 207-361)
- Modify: All other page files with inline styles

- [ ] **Step 1: Extract blog-post.php inline styles**

Read the inline `<style>` block from `blog-post.php` (lines 207-361). Move all CSS rules to `assets/css/style.css` under a `/* ====================== Blog Post Page ====================== */` comment. Then delete the `<style>...</style>` block from `blog-post.php`.

- [ ] **Step 2: Extract inline styles from remaining pages**

For each of these files, extract the inline `<style>` block and append to `assets/css/style.css` with section comments:
- `contact.php`
- `about.php`
- `portfolio.php`
- `blog.php`
- `services.php` (large block — keep as separate section)
- `index.php`

For each file: read the inline `<style>` block, copy the CSS to `style.css`, delete the `<style>` block from the PHP file.

> **Note:** Some inline styles may override or conflict with existing `style.css` rules. When moving, check for duplicates and keep only the most specific/latest version.

- [ ] **Step 3: Commit**

```bash
git add assets/css/style.css blog-post.php contact.php about.php portfolio.php blog.php services.php index.php
git commit -m "refactor: consolidate inline styles from all PHP pages into style.css"
```

---

### Task 10: Fix remaining Phase 1 visual bugs

Based on the user's screenshots:
1. Side contact box icons showing at bottom of page instead of fixed right
2. Hero pill buttons ("Web Development", "AI & Automation") text invisible
3. Raw `<div>` and `{...}` text showing in hero

**Files:**
- Modify: `assets/css/style.css` (fix side-contact-box positioning)
- Modify: `index.php` (fix hero pill button markup)

- [ ] **Step 1: Investigate and fix side contact box**

Check `.side-contact-box` CSS in `style.css`. Ensure:
```css
.side-contact-box {
    position: fixed;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    z-index: 9999;
}
```

- [ ] **Step 2: Fix hero pill buttons text visibility**

Read `index.php` hero section. Find the service pills and ensure text is visible. The screenshot shows pill backgrounds are there but text is hidden/clipped.

- [ ] **Step 3: Fix raw HTML/text in hero**

Search for literal `<div>` and `{...}` text in `index.php` hero section and remove/fix any malformed markup.

- [ ] **Step 4: Commit**

```bash
git add assets/css/style.css index.php
git commit -m "fix: side contact box positioning, hero pill text visibility, raw markup cleanup"
```

---

## Summary

| Task | Description | Files |
|------|-------------|-------|
| 1 | Env-based credentials | config.env.php, .env.example, db_config.php, database.php, .gitignore |
| 2 | CSRF protection | csrf.php, contact.php, contact-form-handler.php, footer.php, login.php |
| 3 | Session hardening | auth.php |
| 4 | Install lockdown | install.php |
| 5 | Rate limiting | db_config.php, contact-form-handler.php, migration SQL |
| 6 | AI agents showcase | services.php, style.css, seo.php |
| 7 | FAQ + schema | services.php, seo.php, style.css |
| 8 | Copy Link button | blog-post.php |
| 9 | CSS consolidation | style.css, all page PHP files |
| 10 | Phase 1 bug fixes | style.css, index.php |
