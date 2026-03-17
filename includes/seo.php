<?php
/**
 * SEO Helper - Generates meta tags, Open Graph, Twitter Cards, and JSON-LD schema
 *
 * Usage: Set $pageMeta array before including header.php
 * $pageMeta = [
 *     'title' => 'Page Title',
 *     'description' => 'Page description...',
 *     'canonical' => '/page.php',
 *     'og_image' => '/assets/images/og-default.jpg',
 *     'type' => 'website', // website, article
 *     'schema' => 'WebPage', // WebPage, Service, Article, ContactPage, CollectionPage
 *     'article' => ['author' => '', 'published' => '', 'category' => ''], // for blog posts
 *     'breadcrumbs' => [['name' => 'Home', 'url' => '/']], // breadcrumb trail
 * ];
 */

function getSiteUrl() {
    $isLocal = (strpos($_SERVER['HTTP_HOST'] ?? 'localhost', 'localhost') !== false);
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'biznexa.tech';
    return $isLocal ? "{$protocol}://{$host}/d2w" : "https://biznexa.tech";
}

function renderMetaTags($pageMeta = []) {
    $siteUrl = getSiteUrl();
    $siteName = 'BizNexa';

    $title = isset($pageMeta['title'])
        ? htmlspecialchars($pageMeta['title']) . ' | ' . $siteName
        : $siteName . ' | Web Development, AI & Automation, Digital Marketing';

    $description = htmlspecialchars($pageMeta['description'] ?? 'BizNexa provides professional web development, AI & automation solutions, and digital marketing services for small businesses. Transform your digital presence today.');

    $canonical = $siteUrl . ($pageMeta['canonical'] ?? $_SERVER['REQUEST_URI'] ?? '/');
    $canonical = strtok($canonical, '?#'); // Remove query params for canonical

    $ogImage = $siteUrl . ($pageMeta['og_image'] ?? '/assets/images/og-default.jpg');
    $ogType = $pageMeta['type'] ?? 'website';

    $output = '';

    // Title
    $output .= "<title>{$title}</title>\n";

    // Meta tags
    $output .= "    <meta name=\"description\" content=\"{$description}\">\n";
    $output .= "    <meta name=\"robots\" content=\"index, follow\">\n";

    // Canonical
    $output .= "    <link rel=\"canonical\" href=\"{$canonical}\">\n";

    // Open Graph
    $output .= "    <meta property=\"og:title\" content=\"{$title}\">\n";
    $output .= "    <meta property=\"og:description\" content=\"{$description}\">\n";
    $output .= "    <meta property=\"og:image\" content=\"{$ogImage}\">\n";
    $output .= "    <meta property=\"og:url\" content=\"{$canonical}\">\n";
    $output .= "    <meta property=\"og:type\" content=\"{$ogType}\">\n";
    $output .= "    <meta property=\"og:site_name\" content=\"{$siteName}\">\n";
    $output .= "    <meta property=\"og:locale\" content=\"en_IN\">\n";

    // Twitter Card
    $output .= "    <meta name=\"twitter:card\" content=\"summary_large_image\">\n";
    $output .= "    <meta name=\"twitter:title\" content=\"{$title}\">\n";
    $output .= "    <meta name=\"twitter:description\" content=\"{$description}\">\n";
    $output .= "    <meta name=\"twitter:image\" content=\"{$ogImage}\">\n";

    // Article-specific meta
    if ($ogType === 'article' && !empty($pageMeta['article'])) {
        $article = $pageMeta['article'];
        if (!empty($article['published'])) {
            $output .= "    <meta property=\"article:published_time\" content=\"" . htmlspecialchars($article['published']) . "\">\n";
        }
        if (!empty($article['author'])) {
            $output .= "    <meta property=\"article:author\" content=\"" . htmlspecialchars($article['author']) . "\">\n";
        }
        if (!empty($article['category'])) {
            $output .= "    <meta property=\"article:section\" content=\"" . htmlspecialchars($article['category']) . "\">\n";
        }
    }

    return $output;
}

function renderJsonLd($pageMeta = [], $settings = []) {
    $siteUrl = getSiteUrl();
    $schemas = [];

    // Organization schema (every page)
    $schemas[] = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'BizNexa',
        'url' => $siteUrl,
        'logo' => $siteUrl . '/assets/images/logo.png',
        'description' => 'Professional web development, AI & automation, and digital marketing services for small businesses.',
        'email' => $settings['site_email'] ?? 'info@biznexa.tech',
        'telephone' => $settings['site_phone'] ?? '+919433215443',
        'sameAs' => array_filter([
            $settings['facebook_url'] ?? '',
            $settings['twitter_url'] ?? '',
            $settings['linkedin_url'] ?? '',
            $settings['instagram_url'] ?? '',
        ]),
        'address' => [
            '@type' => 'PostalAddress',
            'addressCountry' => 'IN',
        ],
    ];

    // WebSite schema (every page)
    $schemas[] = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'BizNexa',
        'url' => $siteUrl,
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => $siteUrl . '/blog.php?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];

    // BreadcrumbList schema
    if (!empty($pageMeta['breadcrumbs'])) {
        $breadcrumbItems = [];
        foreach ($pageMeta['breadcrumbs'] as $i => $crumb) {
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb['name'],
                'item' => $siteUrl . $crumb['url'],
            ];
        }
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbItems,
        ];
    }

    // Page-specific schemas
    $schemaType = $pageMeta['schema'] ?? 'WebPage';

    switch ($schemaType) {
        case 'LocalBusiness':
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'ProfessionalService',
                'name' => 'BizNexa',
                'url' => $siteUrl,
                'image' => $siteUrl . '/assets/images/logo.png',
                'telephone' => $settings['site_phone'] ?? '+919433215443',
                'email' => $settings['site_email'] ?? 'info@biznexa.tech',
                'priceRange' => '$$',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressCountry' => 'IN',
                ],
                'openingHoursSpecification' => [
                    [
                        '@type' => 'OpeningHoursSpecification',
                        'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                        'opens' => '09:00',
                        'closes' => '17:00',
                    ],
                    [
                        '@type' => 'OpeningHoursSpecification',
                        'dayOfWeek' => 'Saturday',
                        'opens' => '09:00',
                        'closes' => '14:00',
                    ],
                ],
                'hasOfferCatalog' => [
                    '@type' => 'OfferCatalog',
                    'name' => 'Digital Services',
                    'itemListElement' => [
                        ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Web Development']],
                        ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'AI & Automation']],
                        ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Digital Marketing']],
                    ],
                ],
            ];
            break;

        case 'Service':
            $services = [
                [
                    'name' => 'Web Development',
                    'description' => 'Custom website design and development, e-commerce solutions, responsive web applications, and CMS development.',
                ],
                [
                    'name' => 'AI & Automation',
                    'description' => 'AI chatbots, workflow automation, intelligent integrations, and smart analytics for business growth.',
                ],
                [
                    'name' => 'Digital Marketing',
                    'description' => 'SEO optimization, social media marketing, content strategy, and PPC campaign management.',
                ],
            ];
            foreach ($services as $service) {
                $schemas[] = [
                    '@context' => 'https://schema.org',
                    '@type' => 'Service',
                    'serviceType' => $service['name'],
                    'name' => $service['name'],
                    'description' => $service['description'],
                    'provider' => [
                        '@type' => 'Organization',
                        'name' => 'BizNexa',
                        'url' => $siteUrl,
                    ],
                ];
            }
            break;

        case 'Article':
            if (!empty($pageMeta['article'])) {
                $article = $pageMeta['article'];
                $schemas[] = [
                    '@context' => 'https://schema.org',
                    '@type' => 'Article',
                    'headline' => $pageMeta['title'] ?? '',
                    'description' => $pageMeta['description'] ?? '',
                    'image' => !empty($article['image']) ? $article['image'] : ($siteUrl . '/assets/images/og-default.jpg'),
                    'datePublished' => $article['published'] ?? '',
                    'dateModified' => $article['modified'] ?? $article['published'] ?? '',
                    'author' => [
                        '@type' => 'Person',
                        'name' => $article['author'] ?? 'BizNexa Team',
                    ],
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => 'BizNexa',
                        'logo' => [
                            '@type' => 'ImageObject',
                            'url' => $siteUrl . '/assets/images/logo.png',
                        ],
                    ],
                    'mainEntityOfPage' => [
                        '@type' => 'WebPage',
                        '@id' => $siteUrl . ($pageMeta['canonical'] ?? ''),
                    ],
                ];
            }
            break;

        case 'ContactPage':
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'ContactPage',
                'name' => 'Contact BizNexa',
                'url' => $siteUrl . '/contact.php',
                'mainEntity' => [
                    '@type' => 'Organization',
                    'name' => 'BizNexa',
                    'contactPoint' => [
                        '@type' => 'ContactPoint',
                        'telephone' => $settings['site_phone'] ?? '+919433215443',
                        'email' => $settings['site_email'] ?? 'info@biznexa.tech',
                        'contactType' => 'customer service',
                        'availableLanguage' => ['English', 'Hindi'],
                    ],
                ],
            ];
            break;
    }

    $output = '';
    foreach ($schemas as $schema) {
        $output .= '<script type="application/ld+json">' . "\n";
        $output .= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $output .= "\n</script>\n";
    }

    return $output;
}
