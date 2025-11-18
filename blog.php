<?php 
$currentPage = 'blog';
$pageTitle = 'Blog';
include 'includes/header.php'; 
?>

<section class="page-header">
    <div class="container">
        <h1 data-aos="fade-up">Our Blog</h1>
        <p data-aos="fade-up" data-aos-delay="100">Insights, Tips, and Industry News</p>
    </div>
</section>

<section class="blog-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up">
                <article class="blog-card">
                    <div class="blog-image">
                        <img src="https://cdn.pixabay.com/photo/2018/05/18/15/30/web-design-3411373_1280.jpg" alt="Web Design Trends">
                        <div class="blog-date">Nov 18, 2025</div>
                    </div>
                    <div class="blog-content">
                        <div class="blog-category">Web Design</div>
                        <h3>Top Web Design Trends for 2025</h3>
                        <p>Discover the latest web design trends that will shape the digital landscape in 2025. From minimalist aesthetics to immersive experiences...</p>
                        <a href="#" class="blog-read-more">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
            </div>

            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <article class="blog-card">
                    <div class="blog-image">
                        <img src="https://cdn.pixabay.com/photo/2023/05/04/17/00/ai-generated-7970187_1280.png" alt="Agentic AI">
                        <div class="blog-date">Nov 15, 2025</div>
                    </div>
                    <div class="blog-content">
                        <div class="blog-category">Artificial Intelligence</div>
                        <h3>How Agentic AI is Transforming Business</h3>
                        <p>Learn how agentic AI is revolutionizing business operations by automating complex tasks and improving decision-making processes...</p>
                        <a href="#" class="blog-read-more">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
            </div>

            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <article class="blog-card">
                    <div class="blog-image">
                        <img src="https://cdn.pixabay.com/photo/2017/06/26/19/03/social-media-2444991_1280.jpg" alt="SEO Tips">
                        <div class="blog-date">Nov 12, 2025</div>
                    </div>
                    <div class="blog-content">
                        <div class="blog-category">SEO</div>
                        <h3>Essential SEO Strategies for Small Businesses</h3>
                        <p>Boost your online visibility with these proven SEO strategies designed specifically for small businesses looking to grow...</p>
                        <a href="#" class="blog-read-more">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
            </div>

            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up">
                <article class="blog-card">
                    <div class="blog-image">
                        <img src="https://cdn.pixabay.com/photo/2016/11/29/08/41/apple-1868496_1280.jpg" alt="E-commerce">
                        <div class="blog-date">Nov 8, 2025</div>
                    </div>
                    <div class="blog-content">
                        <div class="blog-category">E-Commerce</div>
                        <h3>Building a Successful E-Commerce Store</h3>
                        <p>Essential tips for launching and growing your online store. From platform selection to conversion optimization...</p>
                        <a href="#" class="blog-read-more">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
            </div>

            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <article class="blog-card">
                    <div class="blog-image">
                        <img src="https://cdn.pixabay.com/photo/2015/02/05/08/06/macbook-624707_1280.jpg" alt="Digital Marketing">
                        <div class="blog-date">Nov 5, 2025</div>
                    </div>
                    <div class="blog-content">
                        <div class="blog-category">Digital Marketing</div>
                        <h3>Social Media Marketing Best Practices</h3>
                        <p>Master social media marketing with these proven strategies to engage your audience and drive business growth...</p>
                        <a href="#" class="blog-read-more">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
            </div>

            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <article class="blog-card">
                    <div class="blog-image">
                        <img src="https://cdn.pixabay.com/photo/2016/11/30/20/58/programming-1873854_1280.png" alt="Web Development">
                        <div class="blog-date">Nov 1, 2025</div>
                    </div>
                    <div class="blog-content">
                        <div class="blog-category">Development</div>
                        <h3>Choosing the Right Technology Stack</h3>
                        <p>A comprehensive guide to selecting the perfect technology stack for your web development project...</p>
                        <a href="#" class="blog-read-more">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
            </div>
        </div>

        <nav aria-label="Blog pagination" class="mt-5">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
