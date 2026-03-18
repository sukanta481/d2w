<?php
$currentPage = 'services';

// Include database helper
include_once 'includes/db_config.php';
$services = getServices();
$technologies = getTechnologies();
$settings = getAllSettings();

$pageMeta = [
    'title' => 'Web Development, AI Automation & Digital Marketing Services',
    'description' => 'BizNexa offers custom web development, AI-powered automation, and results-driven digital marketing services for small businesses. Get a free consultation today.',
    'canonical' => '/services.php',
    'schema' => 'Service',
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => '/'],
        ['name' => 'Services', 'url' => '/services.php'],
    ],
];

include 'includes/header.php';
?>

<!-- Services Hero Section with Dark Animated Background -->
<section class="section-dark-animated" style="padding-top: 120px;">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
        <div class="bg-shape bg-shape-3"></div>
    </div>

    <!-- Floating Code Elements -->
    <div class="floating-elements">
        <div class="floating-element">&lt;code/&gt;</div>
        <div class="floating-element">{...}</div>
        <div class="floating-element">&lt;div&gt;</div>
        <div class="floating-element">( )</div>
    </div>

    <div class="container">
        <!-- Hero Title -->
        <div class="section-title-animated" data-aos="fade-up">
            <div class="section-badge">
                <i class="fas fa-rocket"></i>
                <span>What We Offer</span>
            </div>
            <h1 style="font-size: 3rem; color: #fff;">Our <span class="text-gradient">Services</span></h1>
            <p style="color: #94a3b8;">Web development, AI automation, and digital marketing — three pillars to accelerate your business growth.</p>
            <div class="header-breadcrumb mt-3" data-aos="fade-up" data-aos-delay="100" style="justify-content: center;">
                <a href="index.php" style="color: #94a3b8;"><i class="fas fa-home"></i> Home</a>
                <i class="fas fa-chevron-right" style="color: #64748b;"></i>
                <span style="color: #fff;">Services</span>
            </div>
        </div>

        <!-- Services Grid (Desktop) -->
        <div class="row services-grid-desktop">
            <?php if (!empty($services)): ?>
                <?php $delay = 0; foreach ($services as $service): ?>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <div class="service-card-animated h-100">
                        <div class="service-icon-animated">
                            <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                        <p><?php echo htmlspecialchars($service['short_description']); ?></p>
                        <?php if (!empty($service['features'])): ?>
                        <ul class="service-features-list">
                            <?php
                            $features = explode("\n", $service['features']);
                            $count = 0;
                            foreach ($features as $feature):
                                if (trim($feature) && $count < 4):
                            ?>
                                <li><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars(trim($feature)); ?></li>
                            <?php
                                $count++;
                                endif;
                            endforeach;
                            ?>
                        </ul>
                        <?php endif; ?>
                        <a href="contact.php" class="btn-service">
                            Get Started <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php $delay = $delay >= 200 ? 0 : $delay + 100; endforeach; ?>
            <?php else: ?>
                <!-- Fallback: 3 Service Pillar Cards -->
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up">
                    <div class="service-card-animated h-100">
                        <div class="service-icon-animated">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <h4>Web Development</h4>
                        <p>Custom websites, e-commerce platforms, and web applications built with modern technologies for performance and conversions.</p>
                        <ul class="service-features-list">
                            <li><i class="fas fa-check-circle"></i> Custom website design</li>
                            <li><i class="fas fa-check-circle"></i> E-commerce solutions</li>
                            <li><i class="fas fa-check-circle"></i> CMS &amp; admin panels</li>
                            <li><i class="fas fa-check-circle"></i> API integrations</li>
                        </ul>
                        <a href="#web-development" class="btn-service">
                            Learn More <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-card-animated h-100">
                        <div class="service-icon-animated">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h4>AI &amp; Automation</h4>
                        <p>Intelligent AI chatbots and workflow automation to streamline operations, engage customers, and scale your business efficiently.</p>
                        <ul class="service-features-list">
                            <li><i class="fas fa-check-circle"></i> AI chatbot deployment</li>
                            <li><i class="fas fa-check-circle"></i> Workflow automation</li>
                            <li><i class="fas fa-check-circle"></i> CRM integration</li>
                            <li><i class="fas fa-check-circle"></i> Smart analytics</li>
                        </ul>
                        <a href="#ai-automation" class="btn-service">
                            Learn More <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-card-animated h-100">
                        <div class="service-icon-animated">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h4>Digital Marketing</h4>
                        <p>Data-driven SEO, PPC campaigns, and social media marketing to drive targeted traffic and generate qualified leads.</p>
                        <ul class="service-features-list">
                            <li><i class="fas fa-check-circle"></i> SEO optimization</li>
                            <li><i class="fas fa-check-circle"></i> Google &amp; Meta Ads</li>
                            <li><i class="fas fa-check-circle"></i> Social media marketing</li>
                            <li><i class="fas fa-check-circle"></i> ROI tracking</li>
                        </ul>
                        <a href="#digital-marketing" class="btn-service">
                            Learn More <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Services Slider (Mobile) -->
        <div class="swiper services-swiper-mobile" data-aos="fade-up">
            <div class="swiper-wrapper">
                <?php if (!empty($services)): ?>
                    <?php foreach ($services as $service): ?>
                    <div class="swiper-slide">
                        <div class="service-card-animated h-100">
                            <div class="service-icon-animated">
                                <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                            </div>
                            <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                            <p><?php echo htmlspecialchars($service['short_description']); ?></p>
                            <?php if (!empty($service['features'])): ?>
                            <ul class="service-features-list">
                                <?php
                                $features = explode("\n", $service['features']);
                                $count = 0;
                                foreach ($features as $feature):
                                    if (trim($feature) && $count < 4):
                                ?>
                                    <li><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars(trim($feature)); ?></li>
                                <?php
                                    $count++;
                                    endif;
                                endforeach;
                                ?>
                            </ul>
                            <?php endif; ?>
                            <a href="contact.php" class="btn-service">
                                Get Started <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="swiper-slide">
                        <div class="service-card-animated h-100">
                            <div class="service-icon-animated">
                                <i class="fas fa-laptop-code"></i>
                            </div>
                            <h4>Web Development</h4>
                            <p>Custom websites, e-commerce, and web apps built for performance and conversions.</p>
                            <ul class="service-features-list">
                                <li><i class="fas fa-check-circle"></i> Custom website design</li>
                                <li><i class="fas fa-check-circle"></i> E-commerce solutions</li>
                                <li><i class="fas fa-check-circle"></i> API integrations</li>
                            </ul>
                            <a href="#web-development" class="btn-service">
                                Learn More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="service-card-animated h-100">
                            <div class="service-icon-animated">
                                <i class="fas fa-robot"></i>
                            </div>
                            <h4>AI &amp; Automation</h4>
                            <p>AI chatbots and workflow automation to streamline your business operations.</p>
                            <ul class="service-features-list">
                                <li><i class="fas fa-check-circle"></i> AI chatbot deployment</li>
                                <li><i class="fas fa-check-circle"></i> Workflow automation</li>
                                <li><i class="fas fa-check-circle"></i> Smart analytics</li>
                            </ul>
                            <a href="#ai-automation" class="btn-service">
                                Learn More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="service-card-animated h-100">
                            <div class="service-icon-animated">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <h4>Digital Marketing</h4>
                            <p>SEO, PPC, and social media marketing to drive traffic and generate leads.</p>
                            <ul class="service-features-list">
                                <li><i class="fas fa-check-circle"></i> SEO optimization</li>
                                <li><i class="fas fa-check-circle"></i> Google &amp; Meta Ads</li>
                                <li><i class="fas fa-check-circle"></i> ROI tracking</li>
                            </ul>
                            <a href="#digital-marketing" class="btn-service">
                                Learn More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="swiper-pagination services-page-pagination"></div>
        </div>
    </div>
</section>

<!-- Service Pillar 1: Web Development -->
<section id="web-development" class="service-pillar-section service-pillar-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <div class="service-pillar-icon-wrap">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <h2 class="service-pillar-title">Web Development</h2>
                <p class="service-pillar-desc">From custom business websites to full-scale e-commerce platforms, we build fast, responsive, and SEO-optimized web solutions that convert visitors into customers.</p>
                <ul class="service-pillar-features">
                    <li><i class="fas fa-check-circle"></i> Custom Website Design &amp; Development</li>
                    <li><i class="fas fa-check-circle"></i> E-Commerce Solutions (Shopify, WooCommerce, Custom)</li>
                    <li><i class="fas fa-check-circle"></i> Responsive &amp; Mobile-First Layouts</li>
                    <li><i class="fas fa-check-circle"></i> CMS Integration (WordPress, Custom Admin Panels)</li>
                    <li><i class="fas fa-check-circle"></i> API Development &amp; Third-Party Integrations</li>
                    <li><i class="fas fa-check-circle"></i> Performance Optimization &amp; Security</li>
                </ul>
                <a href="contact.php" class="btn-service-pillar">
                    Discuss Your Project <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="service-pillar-visual">
                    <div class="pillar-code-block">
                        <div class="code-header">
                            <span class="code-dot red"></span>
                            <span class="code-dot yellow"></span>
                            <span class="code-dot green"></span>
                        </div>
                        <pre class="code-content"><code>&lt;html&gt;
  &lt;head&gt;
    &lt;title&gt;Your Business&lt;/title&gt;
  &lt;/head&gt;
  &lt;body&gt;
    &lt;h1&gt;Welcome&lt;/h1&gt;
    <span class="code-highlight">&lt;!-- Built by BizNexa --&gt;</span>
  &lt;/body&gt;
&lt;/html&gt;</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Service Pillar 2: AI & Automation -->
<section id="ai-automation" class="service-pillar-section service-pillar-dark">
    <div class="container">
        <div class="row align-items-center flex-lg-row-reverse">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-left">
                <div class="service-pillar-icon-wrap">
                    <i class="fas fa-robot"></i>
                </div>
                <h2 class="service-pillar-title">AI &amp; Automation</h2>
                <p class="service-pillar-desc">Leverage intelligent AI agents and workflow automation to reduce manual tasks, improve customer engagement, and scale your operations efficiently.</p>
                <ul class="service-pillar-features">
                    <li><i class="fas fa-check-circle"></i> Custom AI Chatbots (GPT-4, Claude, Gemini)</li>
                    <li><i class="fas fa-check-circle"></i> Workflow Automation with n8n &amp; Zapier</li>
                    <li><i class="fas fa-check-circle"></i> CRM &amp; Lead Management Automation</li>
                    <li><i class="fas fa-check-circle"></i> AI-Powered Content Generation</li>
                    <li><i class="fas fa-check-circle"></i> Smart Analytics &amp; Reporting Dashboards</li>
                    <li><i class="fas fa-check-circle"></i> Email &amp; Social Media Automation</li>
                </ul>
                <a href="contact.php" class="btn-service-pillar">
                    Automate Your Business <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="col-lg-6" data-aos="fade-right">
                <div class="service-pillar-visual">
                    <div class="pillar-flow-diagram">
                        <div class="flow-node flow-trigger"><i class="fas fa-bolt"></i> Trigger</div>
                        <div class="flow-connector"></div>
                        <div class="flow-node flow-ai"><i class="fas fa-brain"></i> AI Process</div>
                        <div class="flow-connector"></div>
                        <div class="flow-node flow-action"><i class="fas fa-paper-plane"></i> Action</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Service Pillar 3: Digital Marketing -->
<section id="digital-marketing" class="service-pillar-section service-pillar-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <div class="service-pillar-icon-wrap">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <h2 class="service-pillar-title">Digital Marketing</h2>
                <p class="service-pillar-desc">Drive targeted traffic, build brand authority, and generate qualified leads with our data-driven digital marketing strategies tailored for small businesses.</p>
                <ul class="service-pillar-features">
                    <li><i class="fas fa-check-circle"></i> Search Engine Optimization (SEO)</li>
                    <li><i class="fas fa-check-circle"></i> Google Ads &amp; Meta Ads (PPC Campaigns)</li>
                    <li><i class="fas fa-check-circle"></i> Social Media Marketing &amp; Management</li>
                    <li><i class="fas fa-check-circle"></i> Content Marketing &amp; Blog Strategy</li>
                    <li><i class="fas fa-check-circle"></i> Email Marketing &amp; Drip Campaigns</li>
                    <li><i class="fas fa-check-circle"></i> Analytics, Reporting &amp; ROI Tracking</li>
                </ul>
                <a href="contact.php" class="btn-service-pillar">
                    Grow Your Brand <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="service-pillar-visual">
                    <div class="pillar-stats-grid">
                        <div class="pillar-stat">
                            <span class="pillar-stat-number">3x</span>
                            <span class="pillar-stat-label">Traffic Growth</span>
                        </div>
                        <div class="pillar-stat">
                            <span class="pillar-stat-number">85%</span>
                            <span class="pillar-stat-label">Lead Increase</span>
                        </div>
                        <div class="pillar-stat">
                            <span class="pillar-stat-number">2.5x</span>
                            <span class="pillar-stat-label">ROI Average</span>
                        </div>
                        <div class="pillar-stat">
                            <span class="pillar-stat-number">40%</span>
                            <span class="pillar-stat-label">Cost Reduction</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Process Section - Dark Theme -->
<section class="section-dark-animated process-section" style="padding: 100px 0;">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
    </div>

    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <div class="section-badge d-inline-flex" style="background: rgba(13, 110, 253, 0.2); border-color: rgba(13, 110, 253, 0.3);">
                <i class="fas fa-route" style="color: #60a5fa;"></i>
                <span style="color: #60a5fa;">How We Work</span>
            </div>
            <h2 style="color: #fff; font-size: 2.5rem; font-weight: 800; margin-top: 15px;">Our <span class="text-gradient">Process</span></h2>
            <p style="color: #D1D5DB; font-size: 1.1rem; max-width: 600px; margin: 15px auto 0;">We follow a structured, transparent approach to deliver exceptional results on time and within budget.</p>
        </div>

        <!-- Process Steps with Connector Line -->
        <div class="process-timeline-dark" data-aos="fade-up" data-aos-delay="100" style="position: relative; z-index: 10; padding-top: 30px;">
            <!-- Horizontal Connector Line (Desktop only) -->
            <div class="process-connector-line d-none d-lg-block" style="position: absolute; top: 70px; left: 15%; right: 15%; height: 3px; background: linear-gradient(90deg, rgba(13, 110, 253, 0.4), rgba(102, 16, 242, 0.6), rgba(102, 16, 242, 0.6), rgba(13, 110, 253, 0.4)); z-index: 1;"></div>
            
            <div class="row justify-content-center">
                <!-- Step 1: Discovery -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <div class="process-step-dark" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(96, 165, 250, 0.2); border-radius: 24px; padding: 35px 25px 40px; text-align: center; position: relative; height: 100%; transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;">
                        <span style="position: absolute; top: -20px; right: 5px; font-size: 100px; font-weight: 900; color: transparent; -webkit-text-stroke: 2px rgba(96, 165, 250, 0.15); line-height: 1; z-index: 0; pointer-events: none;">01</span>
                        <div style="width: 80px; height: 80px; background: linear-gradient(145deg, #0d6efd, #6610f2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; position: relative; z-index: 2; box-shadow: 0 15px 40px rgba(13, 110, 253, 0.4), 0 0 0 6px rgba(13, 110, 253, 0.15);">
                            <i class="fas fa-lightbulb" style="font-size: 32px; color: #fff;"></i>
                        </div>
                        <div style="position: relative; z-index: 2;">
                            <h4 style="color: #fff; font-size: 1.3rem; font-weight: 700; margin-bottom: 12px;">Discovery</h4>
                            <p style="color: #D1D5DB; font-size: 0.95rem; line-height: 1.6; margin-bottom: 18px;">We dive deep into your business goals and project requirements.</p>
                            <ul style="list-style: none; padding: 0; margin: 0; text-align: left; border-top: 1px solid rgba(96, 165, 250, 0.2); padding-top: 15px;">
                                <li style="color: #E5E7EB; font-size: 0.9rem; padding: 8px 0; padding-left: 25px; position: relative;"><i class="fas fa-check" style="position: absolute; left: 0; color: #10B981; font-size: 12px;"></i> Requirement gathering</li>
                                <li style="color: #E5E7EB; font-size: 0.9rem; padding: 8px 0; padding-left: 25px; position: relative;"><i class="fas fa-check" style="position: absolute; left: 0; color: #10B981; font-size: 12px;"></i> Competitor analysis</li>
                                <li style="color: #E5E7EB; font-size: 0.9rem; padding: 8px 0; padding-left: 25px; position: relative;"><i class="fas fa-check" style="position: absolute; left: 0; color: #10B981; font-size: 12px;"></i> Scope definition</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Strategy -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <div class="process-step-dark" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(96, 165, 250, 0.2); border-radius: 24px; padding: 35px 25px 40px; text-align: center; position: relative; height: 100%; transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;">
                        <span style="position: absolute; top: -20px; right: 5px; font-size: 100px; font-weight: 900; color: transparent; -webkit-text-stroke: 2px rgba(96, 165, 250, 0.15); line-height: 1; z-index: 0; pointer-events: none;">02</span>
                        <div style="width: 80px; height: 80px; background: linear-gradient(145deg, #0d6efd, #6610f2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; position: relative; z-index: 2; box-shadow: 0 15px 40px rgba(13, 110, 253, 0.4), 0 0 0 6px rgba(13, 110, 253, 0.15);">
                            <i class="fas fa-chess-knight" style="font-size: 32px; color: #fff;"></i>
                        </div>
                        <div style="position: relative; z-index: 2;">
                            <h4 style="color: #fff; font-size: 1.3rem; font-weight: 700; margin-bottom: 12px;">Strategy</h4>
                            <p style="color: #D1D5DB; font-size: 0.95rem; line-height: 1.6; margin-bottom: 18px;">We create a detailed roadmap with wireframes and timelines.</p>
                            <ul style="list-style: none; padding: 0; margin: 0; text-align: left; border-top: 1px solid rgba(96, 165, 250, 0.2); padding-top: 15px;">
                                <li style="color: #E5E7EB; font-size: 0.9rem; padding: 8px 0; padding-left: 25px; position: relative;"><i class="fas fa-check" style="position: absolute; left: 0; color: #10B981; font-size: 12px;"></i> Information architecture</li>
                                <li style="color: #E5E7EB; font-size: 0.9rem; padding: 8px 0; padding-left: 25px; position: relative;"><i class="fas fa-check" style="position: absolute; left: 0; color: #10B981; font-size: 12px;"></i> Wireframes & mockups</li>
                                <li style="color: #E5E7EB; font-size: 0.9rem; padding: 8px 0; padding-left: 25px; position: relative;"><i class="fas fa-check" style="position: absolute; left: 0; color: #10B981; font-size: 12px;"></i> Tech stack planning</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Development -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <div class="process-step-dark" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(96, 165, 250, 0.2); border-radius: 24px; padding: 35px 25px 40px; text-align: center; position: relative; height: 100%; transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;">
                        <span style="position: absolute; top: -20px; right: 5px; font-size: 100px; font-weight: 900; color: transparent; -webkit-text-stroke: 2px rgba(96, 165, 250, 0.15); line-height: 1; z-index: 0; pointer-events: none;">03</span>
                        <div style="width: 80px; height: 80px; background: linear-gradient(145deg, #0d6efd, #6610f2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; position: relative; z-index: 2; box-shadow: 0 15px 40px rgba(13, 110, 253, 0.4), 0 0 0 6px rgba(13, 110, 253, 0.15);">
                            <i class="fas fa-code" style="font-size: 32px; color: #fff;"></i>
                        </div>
                        <div style="position: relative; z-index: 2;">
                            <h4 style="color: #fff; font-size: 1.3rem; font-weight: 700; margin-bottom: 12px;">Development</h4>
                            <p style="color: #D1D5DB; font-size: 0.95rem; line-height: 1.6; margin-bottom: 18px;">We build your solution with agile sprints and regular updates.</p>
                            <ul style="list-style: none; padding: 0; margin: 0; text-align: left; border-top: 1px solid rgba(96, 165, 250, 0.2); padding-top: 15px;">
                                <li style="color: #E5E7EB; font-size: 0.9rem; padding: 8px 0; padding-left: 25px; position: relative;"><i class="fas fa-check" style="position: absolute; left: 0; color: #10B981; font-size: 12px;"></i> Agile development sprints</li>
                                <li style="color: #E5E7EB; font-size: 0.9rem; padding: 8px 0; padding-left: 25px; position: relative;"><i class="fas fa-check" style="position: absolute; left: 0; color: #10B981; font-size: 12px;"></i> Weekly progress updates</li>
                                <li style="color: #E5E7EB; font-size: 0.9rem; padding: 8px 0; padding-left: 25px; position: relative;"><i class="fas fa-check" style="position: absolute; left: 0; color: #10B981; font-size: 12px;"></i> Quality assurance testing</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Launch -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <div class="process-step-dark" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(96, 165, 250, 0.2); border-radius: 24px; padding: 35px 25px 40px; text-align: center; position: relative; height: 100%; transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;">
                        <span style="position: absolute; top: -20px; right: 5px; font-size: 100px; font-weight: 900; color: transparent; -webkit-text-stroke: 2px rgba(96, 165, 250, 0.15); line-height: 1; z-index: 0; pointer-events: none;">04</span>
                        <div style="width: 80px; height: 80px; background: linear-gradient(145deg, #0d6efd, #6610f2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; position: relative; z-index: 2; box-shadow: 0 15px 40px rgba(13, 110, 253, 0.4), 0 0 0 6px rgba(13, 110, 253, 0.15);">
                            <i class="fas fa-rocket" style="font-size: 32px; color: #fff;"></i>
                        </div>
                        <div style="position: relative; z-index: 2;">
                            <h4 style="color: #fff; font-size: 1.3rem; font-weight: 700; margin-bottom: 12px;">Launch</h4>
                            <p style="color: #D1D5DB; font-size: 0.95rem; line-height: 1.6; margin-bottom: 18px;">We deploy your project and provide ongoing support.</p>
                            <ul style="list-style: none; padding: 0; margin: 0; text-align: left; border-top: 1px solid rgba(96, 165, 250, 0.2); padding-top: 15px;">
                                <li style="color: #E5E7EB; font-size: 0.9rem; padding: 8px 0; padding-left: 25px; position: relative;"><i class="fas fa-check" style="position: absolute; left: 0; color: #10B981; font-size: 12px;"></i> Deployment & go-live</li>
                                <li style="color: #E5E7EB; font-size: 0.9rem; padding: 8px 0; padding-left: 25px; position: relative;"><i class="fas fa-check" style="position: absolute; left: 0; color: #10B981; font-size: 12px;"></i> Training & documentation</li>
                                <li style="color: #E5E7EB; font-size: 0.9rem; padding: 8px 0; padding-left: 25px; position: relative;"><i class="fas fa-check" style="position: absolute; left: 0; color: #10B981; font-size: 12px;"></i> Post-launch support</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Technology Section with Tabbed Categories -->
<section class="section-light-animated">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
    </div>

    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <div class="section-badge d-inline-flex" style="background: rgba(13, 110, 253, 0.1); border-color: rgba(13, 110, 253, 0.2);">
                <i class="fas fa-layer-group" style="color: #0d6efd;"></i>
                <span style="color: #0d6efd;">Tech Stack</span>
            </div>
            <h2 style="color: #1e293b; font-size: 2.5rem; font-weight: 800; margin-top: 15px;">Technologies We <span style="color: #0d6efd;">Master</span></h2>
            <p style="color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 15px auto 0;">We use cutting-edge technologies to build secure, scalable, and high-performance solutions.</p>
        </div>

        <!-- Tech Stack Tabs -->
        <div class="tech-stack-tabs" data-aos="fade-up" data-aos-delay="100">
            <ul class="nav nav-pills tech-tabs-nav justify-content-center mb-4" id="techStackTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="frontend-tab" data-bs-toggle="pill" data-bs-target="#frontend" type="button" role="tab" aria-controls="frontend" aria-selected="true">
                        <i class="fas fa-palette"></i>
                        <span>Frontend</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="backend-tab" data-bs-toggle="pill" data-bs-target="#backend" type="button" role="tab" aria-controls="backend" aria-selected="false">
                        <i class="fas fa-server"></i>
                        <span>Backend</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="database-tab" data-bs-toggle="pill" data-bs-target="#database" type="button" role="tab" aria-controls="database" aria-selected="false">
                        <i class="fas fa-database"></i>
                        <span>Database</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cms-tab" data-bs-toggle="pill" data-bs-target="#cms" type="button" role="tab" aria-controls="cms" aria-selected="false">
                        <i class="fas fa-cubes"></i>
                        <span>CMS & Tools</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="techStackTabContent">
                <!-- Frontend Tab -->
                <div class="tab-pane fade show active" id="frontend" role="tabpanel" aria-labelledby="frontend-tab">
                    <div class="row justify-content-center">
                        <div class="col-6 col-md-4 col-lg-2 mb-4" data-aos="zoom-in" data-aos-delay="0">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fab fa-html5" style="color: #E34F26;"></i>
                                </div>
                                <p>HTML5</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 mb-4" data-aos="zoom-in" data-aos-delay="50">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fab fa-css3-alt" style="color: #1572B6;"></i>
                                </div>
                                <p>CSS3</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 mb-4" data-aos="zoom-in" data-aos-delay="100">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fab fa-js-square" style="color: #F7DF1E;"></i>
                                </div>
                                <p>JavaScript</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 mb-4" data-aos="zoom-in" data-aos-delay="150">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fab fa-react" style="color: #61DAFB;"></i>
                                </div>
                                <p>React</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 mb-4" data-aos="zoom-in" data-aos-delay="200">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fab fa-vuejs" style="color: #4FC08D;"></i>
                                </div>
                                <p>Vue.js</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 mb-4" data-aos="zoom-in" data-aos-delay="250">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fab fa-bootstrap" style="color: #7952B3;"></i>
                                </div>
                                <p>Bootstrap</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backend Tab -->
                <div class="tab-pane fade" id="backend" role="tabpanel" aria-labelledby="backend-tab">
                    <div class="row justify-content-center">
                        <div class="col-6 col-md-4 col-lg-2 mb-4">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fab fa-php" style="color: #777BB4;"></i>
                                </div>
                                <p>PHP</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 mb-4">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fab fa-node-js" style="color: #339933;"></i>
                                </div>
                                <p>Node.js</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 mb-4">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fab fa-python" style="color: #3776AB;"></i>
                                </div>
                                <p>Python</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 mb-4">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fab fa-laravel" style="color: #FF2D20;"></i>
                                </div>
                                <p>Laravel</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 mb-4">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fas fa-fire" style="color: #FFCA28;"></i>
                                </div>
                                <p>Firebase</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 mb-4">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fab fa-aws" style="color: #FF9900;"></i>
                                </div>
                                <p>AWS</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Database Tab -->
                <div class="tab-pane fade" id="database" role="tabpanel" aria-labelledby="database-tab">
                    <div class="row justify-content-center">
                        <div class="col-6 col-md-4 col-lg-2 mb-4">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fas fa-database" style="color: #4479A1;"></i>
                                </div>
                                <p>MySQL</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 mb-4">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fas fa-leaf" style="color: #47A248;"></i>
                                </div>
                                <p>MongoDB</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 mb-4">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fas fa-layer-group" style="color: #336791;"></i>
                                </div>
                                <p>PostgreSQL</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 mb-4">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fas fa-bolt" style="color: #DC382D;"></i>
                                </div>
                                <p>Redis</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CMS & Tools Tab -->
                <div class="tab-pane fade" id="cms" role="tabpanel" aria-labelledby="cms-tab">
                    <div class="row justify-content-center">
                        <div class="col-6 col-md-4 col-lg-2 mb-4">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fab fa-wordpress" style="color: #21759B;"></i>
                                </div>
                                <p>WordPress</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 mb-4">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fab fa-shopify" style="color: #7AB55C;"></i>
                                </div>
                                <p>Shopify</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 mb-4">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fab fa-magento" style="color: #EE672F;"></i>
                                </div>
                                <p>Magento</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 mb-4">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fab fa-git-alt" style="color: #F05032;"></i>
                                </div>
                                <p>Git</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 mb-4">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fab fa-docker" style="color: #2496ED;"></i>
                                </div>
                                <p>Docker</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2 mb-4">
                            <div class="tech-item-glass">
                                <div class="tech-icon-wrapper">
                                    <i class="fab fa-figma" style="color: #F24E1E;"></i>
                                </div>
                                <p>Figma</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section with Dark Animated Background -->
<section class="section-dark-animated" style="padding: 100px 0;">
    <!-- Animated Background Shapes -->
    <div class="section-bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
    </div>

    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center" data-aos="zoom-in">
                <div class="section-badge d-inline-flex mb-4" style="background: rgba(13, 110, 253, 0.2); border-color: rgba(13, 110, 253, 0.3);">
                    <i class="fas fa-handshake" style="color: #60a5fa;"></i>
                    <span style="color: #60a5fa;">Let's Work Together</span>
                </div>
                <h2 style="font-size: 2.75rem; color: #fff; font-weight: 800; margin-bottom: 20px;">Need a <span class="text-gradient">Custom Solution</span>?</h2>
                <p style="color: #94a3b8; font-size: 1.2rem; margin-bottom: 35px; max-width: 600px; margin-left: auto; margin-right: auto;">Let's discuss your specific requirements and create something amazing together. Our team is ready to bring your vision to life.</p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="contact.php" class="btn-hero-primary" style="padding: 16px 35px; font-size: 1.1rem;">
                        <span>Start Your Project</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="portfolio.php" class="btn-hero-secondary" style="padding: 16px 35px; font-size: 1.1rem;">
                        <span>View Our Work</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Service Pillar Sections */
.service-pillar-section {
    padding: 100px 0;
    position: relative;
    overflow: hidden;
}

.service-pillar-light {
    background: #f8fafc;
}

.service-pillar-dark {
    background: #0f172a;
}

.service-pillar-dark .service-pillar-title,
.service-pillar-dark .service-pillar-desc,
.service-pillar-dark .service-pillar-features li {
    color: #e2e8f0;
}

.service-pillar-dark .service-pillar-features i {
    color: #60a5fa;
}

.service-pillar-icon-wrap {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    box-shadow: 0 10px 30px rgba(13, 110, 253, 0.3);
}

.service-pillar-icon-wrap i {
    font-size: 28px;
    color: #fff;
}

.service-pillar-title {
    font-size: 2.2rem;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 15px;
}

.service-pillar-desc {
    font-size: 1.1rem;
    color: #64748b;
    line-height: 1.7;
    margin-bottom: 25px;
}

.service-pillar-features {
    list-style: none;
    padding: 0;
    margin: 0 0 30px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.service-pillar-features li {
    font-size: 0.95rem;
    color: #475569;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.service-pillar-features i {
    color: #0d6efd;
    font-size: 14px;
    margin-top: 3px;
    flex-shrink: 0;
}

.btn-service-pillar {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 30px;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    color: #fff;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
    transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
    box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
}

.btn-service-pillar:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(13, 110, 253, 0.4);
    color: #fff;
}

/* Pillar Visual Elements */
.service-pillar-visual {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 350px;
}

/* Code Block Visual */
.pillar-code-block {
    background: #1e293b;
    border-radius: 16px;
    overflow: hidden;
    width: 100%;
    max-width: 450px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.code-header {
    background: #334155;
    padding: 12px 16px;
    display: flex;
    gap: 8px;
}

.code-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.code-dot.red { background: #ef4444; }
.code-dot.yellow { background: #eab308; }
.code-dot.green { background: #22c55e; }

.code-content {
    padding: 20px;
    margin: 0;
    color: #94a3b8;
    font-size: 0.9rem;
    line-height: 1.8;
    overflow-x: auto;
}

.code-content code {
    color: #94a3b8;
}

.code-highlight {
    color: #60a5fa;
}

/* Flow Diagram Visual */
.pillar-flow-diagram {
    display: flex;
    align-items: center;
    gap: 0;
    flex-wrap: wrap;
    justify-content: center;
}

.flow-node {
    padding: 20px 28px;
    border-radius: 14px;
    font-weight: 700;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 10px;
    white-space: nowrap;
}

.flow-trigger {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
    box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
}

.flow-ai {
    background: linear-gradient(135deg, #0d6efd, #6610f2);
    color: #fff;
    box-shadow: 0 8px 25px rgba(13, 110, 253, 0.3);
}

.flow-action {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}

.flow-connector {
    width: 40px;
    height: 3px;
    background: linear-gradient(90deg, rgba(13, 110, 253, 0.4), rgba(102, 16, 242, 0.4));
    position: relative;
}

.flow-connector::after {
    content: '';
    position: absolute;
    right: -4px;
    top: -4px;
    border: 5px solid transparent;
    border-left: 8px solid rgba(102, 16, 242, 0.5);
}

/* Stats Grid Visual */
.pillar-stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    max-width: 400px;
    margin: 0 auto;
}

.pillar-stat {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 28px 20px;
    text-align: center;
    transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.pillar-stat:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba(13, 110, 253, 0.15);
    border-color: rgba(13, 110, 253, 0.3);
}

.pillar-stat-number {
    display: block;
    font-size: 2rem;
    font-weight: 800;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 5px;
}

.pillar-stat-label {
    font-size: 0.85rem;
    color: #64748b;
    font-weight: 600;
}

/* Pillar Responsive */
@media (max-width: 991px) {
    .service-pillar-section {
        padding: 70px 0;
    }

    .service-pillar-features {
        grid-template-columns: 1fr;
    }

    .service-pillar-visual {
        min-height: 250px;
    }
}

@media (max-width: 767px) {
    .service-pillar-section {
        padding: 50px 0;
    }

    .service-pillar-title {
        font-size: 1.75rem;
    }

    .pillar-flow-diagram {
        flex-direction: column;
    }

    .flow-connector {
        width: 3px;
        height: 30px;
    }

    .flow-connector::after {
        right: auto;
        left: -4px;
        top: auto;
        bottom: -4px;
        border: 5px solid transparent;
        border-top: 8px solid rgba(102, 16, 242, 0.5);
        border-left: 5px solid transparent;
    }
}

/* Tech Stack Tabs Styling */
.tech-stack-tabs {
    position: relative;
    z-index: 10;
}

.tech-tabs-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 40px !important;
}

.tech-tabs-nav .nav-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 28px;
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: 50px;
    color: #64748b;
    font-weight: 600;
    font-size: 0.95rem;
    transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.tech-tabs-nav .nav-link i {
    font-size: 18px;
    transition: transform 0.3s ease;
}

.tech-tabs-nav .nav-link:hover {
    border-color: #0d6efd;
    color: #0d6efd;
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(13, 110, 253, 0.15);
}

.tech-tabs-nav .nav-link:hover i {
    transform: scale(1.2);
}

.tech-tabs-nav .nav-link.active {
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    border-color: transparent;
    color: #fff;
    box-shadow: 0 10px 30px rgba(13, 110, 253, 0.35);
}

.tech-tabs-nav .nav-link.active i {
    color: #fff;
}

/* Glassmorphism Tech Item Cards */
.tech-item-glass {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.5);
    border-radius: 20px;
    padding: 30px 20px;
    text-align: center;
    transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    box-shadow: 
        0 10px 40px rgba(0, 0, 0, 0.08),
        inset 0 1px 1px rgba(255, 255, 255, 0.8);
}

.tech-item-glass::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 50%;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.5) 0%, transparent 100%);
    border-radius: 20px 20px 0 0;
    pointer-events: none;
}

.tech-item-glass:hover {
    transform: translateY(-6px) scale(1.02);
    box-shadow:
        0 15px 40px rgba(0, 0, 0, 0.1),
        0 0 20px rgba(13, 110, 253, 0.08),
        inset 0 1px 1px rgba(255, 255, 255, 0.9);
    border-color: rgba(13, 110, 253, 0.2);
}

.tech-icon-wrapper {
    width: 80px;
    height: 80px;
    background: linear-gradient(145deg, #f8fafc, #e2e8f0);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    position: relative;
    transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
    box-shadow: 
        0 10px 25px rgba(0, 0, 0, 0.1),
        inset 0 2px 4px rgba(255, 255, 255, 0.8),
        inset 0 -2px 4px rgba(0, 0, 0, 0.05);
    transform: perspective(300px) rotateX(5deg);
}

.tech-icon-wrapper::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 50%;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.6) 0%, transparent 100%);
    border-radius: 20px 20px 0 0;
    pointer-events: none;
}

.tech-icon-wrapper i {
    font-size: 40px;
    transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
    position: relative;
    z-index: 1;
}

.tech-item-glass:hover .tech-icon-wrapper {
    transform: perspective(300px) rotateX(0deg) scale(1.05);
    box-shadow:
        0 10px 25px rgba(0, 0, 0, 0.12),
        inset 0 2px 4px rgba(255, 255, 255, 0.9);
}

.tech-item-glass:hover .tech-icon-wrapper i {
    transform: scale(1.08);
}

.tech-item-glass p {
    margin: 0;
    margin-top: 10px;
    font-weight: 700;
    color: #1e293b;
    font-size: 0.95rem;
    position: relative;
    z-index: 1;
}

/* Tab Content Animation */
.tab-pane {
    animation: fadeInUp 0.4s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Mobile Slider Styles for Services Page */
.services-swiper-mobile {
    display: none;
    padding-bottom: 50px;
}

.services-swiper-mobile .swiper-slide {
    height: auto;
    padding: 10px 5px;
}

.services-swiper-mobile .service-card-animated {
    height: 100%;
}

/* Swiper Pagination Styling */
.services-page-pagination {
    position: relative;
    margin-top: 20px;
}

.services-page-pagination .swiper-pagination-bullet {
    width: 10px;
    height: 10px;
    background: rgba(255, 255, 255, 0.3);
    opacity: 1;
    transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
}

.services-page-pagination .swiper-pagination-bullet-active {
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    width: 30px;
    border-radius: 5px;
}

/* Responsive Styles */
@media (max-width: 991px) {
    .tech-tabs-nav {
        gap: 8px;
    }
    
    .tech-tabs-nav .nav-link {
        padding: 12px 20px;
        font-size: 0.9rem;
    }
    
    .tech-tabs-nav .nav-link span {
        display: none;
    }
    
    .tech-tabs-nav .nav-link i {
        margin: 0;
    }
}

@media (max-width: 767px) {
    .services-grid-desktop {
        display: none !important;
    }

    .services-swiper-mobile {
        display: block;
    }
    
    .tech-tabs-nav .nav-link {
        padding: 12px 16px;
    }
    
    .tech-item-glass {
        padding: 20px 15px;
    }
    
    .tech-icon-wrapper {
        width: 60px;
        height: 60px;
    }
    
    .tech-icon-wrapper i {
        font-size: 30px;
    }
}
</style>

<!-- Mobile Slider Initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Services Swiper for Mobile
    if (window.innerWidth <= 767) {
        new Swiper('.services-swiper-mobile', {
            slidesPerView: 1.1,
            spaceBetween: 15,
            centeredSlides: true,
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.services-page-pagination',
                clickable: true,
            },
            breakpoints: {
                480: {
                    slidesPerView: 1.2,
                    spaceBetween: 20,
                }
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
