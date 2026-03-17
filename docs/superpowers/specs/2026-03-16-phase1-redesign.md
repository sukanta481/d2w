# Phase 1: BizNexa Website Redesign & SEO Foundation

## Overview
Refocus BizNexa around 3 service pillars (Web Development, AI & Automation, Digital Marketing) with full SEO foundation, accessibility fixes, animation smoothing, and CSS consolidation.

## Service Pillars
1. **Web Development** — Custom websites, E-commerce, Responsive design, CMS
2. **AI & Automation** — AI Chatbots, Workflow automation, AI integration, Smart analytics
3. **Digital Marketing** — SEO, Social media, Content marketing, PPC campaigns

## SEO Layer
- New `includes/seo.php` helper generating meta tags, OG tags, Twitter cards, JSON-LD
- Per-page `$pageMeta` array pattern
- `robots.txt`, `sitemap.xml`, `404.php` created
- JSON-LD: Organization, WebSite, BreadcrumbList, LocalBusiness, Service, Article, ContactPoint

## Content Changes
- Homepage hero refocused on 3 pillars
- Navigation simplified (remove Company dropdown, direct About/Blog links, Services dropdown with 3 pillars)
- Footer services column updated
- Dead links fixed

## Animation Smoothing
- AOS: 1000ms → 600ms, easing → ease-out
- Hover effects: subtler transforms, will-change hints
- WhatsApp animation simplified

## Accessibility
- aria-labels on all icon-only links
- Replace emoji chatbot icon
- lazy loading on images
- Fix dead href="#" links

## CSS Consolidation
- Move inline styles from all page files to style.css
- Move mobile menu inline styles to CSS classes
- Replace hardcoded hex with CSS variables

## Security
- Sanitize blog post HTML output
