# Phase 2: Security Hardening, AI Agents Showcase & Polish

## Overview
Phase 2 builds on the Phase 1 foundation (SEO, service pillars, animation fixes) with security hardening, surfacing the AI agents catalog on the frontend, CSS consolidation, FAQ section with schema markup, and blog enhancements.

## 1. Security Hardening

### 1a. Environment-based credentials
- Create `config.env.php` that reads credentials from a `.env` file
- Fall back to current hardcoded values when `.env` doesn't exist (local dev convenience)
- Update `includes/db_config.php` and `admin/config/database.php` to use the new config
- Add `.env` to `.gitignore`
- `.env.example` committed with placeholder values for documentation

**Files affected:** `includes/db_config.php`, `admin/config/database.php`, new `config.env.php`, new `.env.example`, `.gitignore`

### 1b. CSRF token protection
- Create `includes/csrf.php` with:
  - `generateCsrfToken()` — generates and stores token in `$_SESSION`
  - `csrfField()` — returns hidden input HTML
  - `validateCsrfToken($token)` — validates and rotates token
- Add CSRF hidden field to all forms:
  - Contact form (`contact.php`)
  - Newsletter form (`includes/footer.php`)
  - Admin login (`admin/login.php`)
  - All admin CRUD forms (services, projects, blog, testimonials, ai-agents, clients, billing, settings, profile)
- Validate token in all POST handlers:
  - `php/contact-form-handler.php`
  - All admin action handlers

**Files affected:** new `includes/csrf.php`, `contact.php`, `includes/footer.php`, `php/contact-form-handler.php`, `admin/login.php`, `admin/services.php`, `admin/projects.php`, `admin/blog.php`, `admin/testimonials.php`, `admin/ai-agents.php`, `admin/clients.php`, `admin/billing.php`, `admin/settings.php`, `admin/profile.php`

### 1c. Session hardening
- Call `session_regenerate_id(true)` on successful login in admin
- Implement 30-minute idle timeout (check `$_SESSION['last_activity']` on each request)
- Store user-agent hash in session and validate on each request
- Add these checks to `admin/includes/auth.php`

**Files affected:** `admin/includes/auth.php`, `admin/login.php`

### 1d. Install script lockdown
- Gate `admin/install.php` behind an `ALLOW_INSTALL` environment variable
- If `ALLOW_INSTALL` is not set to `true`, return 404
- Add warning comment in `.env.example`

**Files affected:** `admin/install.php`

### 1e. Contact form rate limiting
- Create `rate_limits` table: `id`, `ip_address`, `action`, `created_at`
- Add `checkRateLimit($ip, $action, $maxAttempts, $windowMinutes)` function to `includes/db_config.php`
- Apply to contact form handler: max 5 submissions per IP per 60 minutes
- Return friendly JSON error message when rate limited
- Clean up old rate limit records periodically (on each check, delete records older than window)

**Files affected:** `includes/db_config.php`, `php/contact-form-handler.php`, `database/d2w_cms.sql` (add table)

## 2. AI Agents Showcase

### Frontend display
- Add "AI Solutions" subsection inside `services.php` `#ai-automation` pillar section
- Query active agents via existing `getAIAgents()` function
- Display as card grid (responsive: 3 cols desktop, 2 tablet, 1 mobile)
- Each card shows:
  - Icon (Font Awesome class from `icon` field)
  - Name
  - Description (truncated to 120 chars)
  - Features list (comma-separated `features` field, displayed as pills/tags)
  - Pricing (if set, formatted as "Starting at $X")
  - "Try Demo" button (if `demo_url` set)
  - "Learn More" button (if `documentation_url` set)
- `coming_soon` status agents show a "Coming Soon" badge overlay, buttons disabled
- Fallback: 3 hardcoded AI agent cards when DB returns empty

### SEO
- Add `Product` JSON-LD schema for each active agent (name, description, offers with price)
- Integrate into existing `renderJsonLd()` pattern in `includes/seo.php`

**Files affected:** `services.php`, `includes/seo.php`, `assets/css/style.css` (agent card styles)

## 3. CSS Consolidation

### Inline style migration
- Extract inline `<style>` blocks from all page PHP files into `assets/css/style.css`
- Pages to process: `index.php`, `services.php`, `about.php`, `portfolio.php`, `blog.php`, `blog-post.php`, `contact.php`
- Add clear section comment headers for each page's extracted styles
- Replace remaining hardcoded hex values with CSS variables where applicable

### Dead rule cleanup
- Identify and remove unused CSS selectors that no longer have matching HTML
- Remove any duplicate rule blocks

**Files affected:** `assets/css/style.css`, `index.php`, `services.php`, `about.php`, `portfolio.php`, `blog.php`, `blog-post.php`, `contact.php`

## 4. FAQ Section with Schema

### Content
- Add collapsible FAQ section to `services.php` (after service pillars, before CTA)
- Use Bootstrap accordion component for expand/collapse
- 6-8 questions covering:
  - Web development process and timeline
  - AI automation capabilities
  - Digital marketing approach and results
  - Pricing and engagement models
  - Support and maintenance
- Hardcoded content (not DB-driven — these are evergreen business FAQs)

### SEO
- Add `FAQPage` JSON-LD schema with all Q&A pairs
- Integrate into services page `$pageMeta` and `renderJsonLd()`

**Files affected:** `services.php`, `includes/seo.php`, `assets/css/style.css` (FAQ styles)

## 5. Blog Enhancements

### Social sharing
- Add sharing buttons to `blog-post.php` (below post title, above content)
- Buttons: WhatsApp, Facebook, LinkedIn, Twitter/X, Copy Link
- Use direct share URLs (no third-party scripts)
- "Copy Link" shows brief "Copied!" tooltip feedback
- Minimal CSS — styled as icon row matching site design

### Pagination SEO
- Add `rel="next"` and `rel="prev"` link tags in `<head>` on `blog.php` when paginated
- Integrate into existing SEO meta tag rendering

**Files affected:** `blog-post.php`, `blog.php`, `includes/seo.php`, `assets/css/style.css` (share button styles)

## Implementation Order
1. Security hardening (1a → 1b → 1c → 1d → 1e) — foundation, no visual changes
2. CSS consolidation — cleanup before adding new styles
3. AI agents showcase — new content section
4. FAQ section — new content section
5. Blog enhancements — smaller additions

## Out of Scope
- CSS splitting/minification (not needed at current scale)
- SMTP email migration (separate effort)
- Admin dashboard metrics
- Blog search functionality
- Google Analytics integration
