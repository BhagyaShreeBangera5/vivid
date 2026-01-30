<?php
    session_start();
    // Ideally use environment variables for credentials in production
    $conn = new mysqli("localhost", "root", "", "vivid_graphics");

    if ($conn->connect_error) {
        die("DB Connection Failed");
    }

    $error = "";
    $success = "";
    $showModal = false;
    $action = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];

        // ===== REGISTER =====
        if ($action === "register") {
            $name  = trim($_POST['name']);
            $email = trim($_POST['email']);
            $pass  = $_POST['password'];

            if (empty($name) || empty($email) || empty($pass)) {
                $error = "All fields are required.";
                $showModal = true;
            } else {
                $check = $conn->prepare("SELECT id FROM users WHERE email=?");
                $check->bind_param("s", $email);
                $check->execute();
                $check->store_result();

                if ($check->num_rows > 0) {
                    $error = "Email already exists!";
                    $showModal = true;
                } else {
                    $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'user')");
                    $stmt->bind_param("sss", $name, $email, $hashed_pass);
                    if ($stmt->execute()) {
                        $success = "Registration successful! Please login.";
                        $showModal = true;
                    } else {
                        $error = "Something went wrong. Please try again.";
                        $showModal = true;
                    }
                }
            }
        }

        // ===== LOGIN =====
        if ($action === "login") {
            $email = trim($_POST['email']);
            $pass  = $_POST['password'];

            if (empty($email) || empty($pass)) {
                $error = "Please enter both email and password.";
                $showModal = true;
            } else {
                $stmt = $conn->prepare("SELECT password, role, full_name, id FROM users WHERE email=? LIMIT 1");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $res = $stmt->get_result();

                if ($res->num_rows === 1) {
                    $user = $res->fetch_assoc();
                    if (password_verify($pass, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['full_name'] = $user['full_name'];

                        if ($user['role'] === 'admin') {
                            header("Location: admin_dashboard.php");
                        } else {
                            header("Location: index.php");
                        }
                        exit;
                    } else {
                        $error = "Invalid password.";
                        $showModal = true;
                    }
                } else {
                    $error = "No account found with this email.";
                    $showModal = true;
                }
            }
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vivid Graphics</title>
        <link rel="stylesheet" href="astyle.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>

   <body class="loading">
    <!-- ================= VIVID LOADER ================= -->
    <div id="vividLoader">
        <div class="vivid-loader-bg"></div>
        <div class="logo-wrapper">
            <!-- Ensure this image path is correct, or the loader will wait for it -->
            <img src="uploads/vividlogo.jpg" alt="Vivid Graphics Logo" onerror="this.style.display='none'">
        </div>
    </div>

    <!-- ===== HEADER / NAVBAR ===== -->
    <header class="navbar">
        <div class="logo">Vivid Graphics</div>

        <div class="hamburger" id="hamburger">☰</div>

        <nav class="nav-links">
            <a href="abc.php">Home</a>
            <a href="#">Products</a>
            <a href="product.html">Customize</a>
            <a href="portfolio.html">Portfolio</a>
            <a href="javascript:void(0)" id="openContact">Contact</a>
            <?php if(isset($_SESSION['role'])): ?>
                <?php if($_SESSION['role'] === 'admin'): ?>
                    <a href="admin_dashboard.php">Dashboard</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="javascript:void(0)" id="openLogin">Login</a>
            <?php endif; ?>
        </nav>

        <div class="nav-btn">
            <button>Get a Quote</button>
        </div>
    </header>

    <main class="content">

    <section class="hero">
        <div class="hero-text">
            <h1>Custom Printing & Branding Solutions</h1>
            <p>Upload your design, preview it instantly, and get high-quality prints.</p>
            <div class="hero-buttons">
                <button class="primary-btn">Start Printing</button>
                <button class="secondary-btn">Upload Your Design</button>
            </div>
        </div>
        <div class="hero-image">
            <img src="uploads/vividlogo.jpg" alt="Hero Image">
        </div>
    </section>

    <section class="products">
        <h2>Our Products</h2>
        <div class="product-grid">
            <div class="product-card">Flex Printing</div>
            <div class="product-card">Vinyl Printing</div>
            <div class="product-card">Digital Printing</div>
            <div class="product-card">LED Signboard</div>
            <div class="product-card">Inshop Branding</div>
            <div class="product-card">Custom Gifts</div>
        </div>
    </section>

    <section class="how-it-works">
        <h2>How It Works</h2>
        <div class="steps">
            <div class="step">Choose Product</div>
            <div class="step">Upload Design</div>
            <div class="step">Preview & Customize</div>
            <div class="step">Place Order</div>
        </div>
    </section>

    <section class="cta">
        <h2>Ready to Print Your Design?</h2>
        <button class="primary-btn">Get a Quote</button>
    </section>
      
    <!-- ===== PORTFOLIO PREVIEW SLIDER ===== -->
    <section class="portfolio-preview">
      <div class="container">

        <h2>Our Works</h2>
        <p class="subtitle">
          A glimpse of branding and printing works delivered by Vivid Graphics
        </p>

        <div class="portfolio-slider">
          <div class="slider-track">

            <a href="portfolio.html" class="portfolio-item">
                <img src="uploads/customport1.jpg" alt="Branding work">
            </a>

            <a href="portfolio.html" class="portfolio-item">
                <img src="uploads/digitalport1.jpg" alt="Event flex printing">
            </a>

            <a href="portfolio.html" class="portfolio-item">
                <img src="uploads/flexport1.jpg" alt="Flex banner design">
            </a>

            <a href="portfolio.html" class="portfolio-item">
                <img src="uploads/inshopport1.jpg" alt="Shop branding">
            </a>

            <!-- duplicate set for smooth sliding -->
            <a href="portfolio.html" class="portfolio-item">
                <img src="uploads/customport1.jpg" alt="Duplicate 1">
            </a>

            <a href="portfolio.html" class="portfolio-item">
                <img src="uploads/digitalport1.jpg" alt="Duplicate 2">
            </a>
            
            <a href="portfolio.html" class="portfolio-item">
                <img src="uploads/flexport1.jpg" alt="Duplicate 3">
            </a>

            <a href="portfolio.html" class="portfolio-item">
                <img src="uploads/inshopport1.jpg" alt="Duplicate 4">
            </a>
        
        </div>

        </div>
    </div>
        <a href="portfolio.html" class="view-more-btn">
          View Full Portfolio →
        </a>
      </div>
    </section>
    </main>

    <!-- ===== MODAL OVERLAY ===== -->
    <div class="modal-overlay <?php echo $showModal ? 'active' : ''; ?>" id="authModal">
        <div class="modal-card">
            <span class="close-modal" id="closeModal">&times;</span>
            
            <div class="modal-header">
                <h2 id="modalTitle">Welcome Back</h2>
                <p id="modalSubtitle">Please enter your details to login.</p>
            </div>

            <?php if($error): ?>
                <div class="auth-alert error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="auth-alert success"><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- LOGIN FORM -->
            <form action="index.php" method="POST" class="auth-form" id="loginForm">
                <input type="hidden" name="action" value="login">
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="auth-btn">Log In</button>
                <p class="auth-switch">Don't have an account? <a href="javascript:void(0)" id="showRegister">Register</a></p>
            </form>

            <!-- REGISTER FORM -->
            <form action="index.php" method="POST" class="auth-form hidden" id="registerForm">
                <input type="hidden" name="action" value="register">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="name" placeholder="Full Name" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="auth-btn">Create Account</button>
                <p class="auth-switch">Already have an account? <a href="javascript:void(0)" id="showLogin">Log In</a></p>
            </form>
        </div>
    </div>
                
    <!-- ================= REVIEWS & STATS ================= -->
    <section class="reviews-stats">
        <h2>What Our Clients Say</h2>
        <p class="subtitle">Trusted by businesses across the city</p>

        <!-- ===== REVIEWS ===== -->
        <div class="reviews-grid">
            <div class="review-card">
                <p>“Excellent quality flex printing. Delivery was on time!”</p>
                <h4>— Ramesh Kumar</h4>
                <span>⭐⭐⭐⭐⭐</span>
            </div>

            <div class="review-card">
                <p>“Very professional branding work. Highly recommended.”</p>
                <h4>— Anjali Shetty</h4>
                <span>⭐⭐⭐⭐⭐</span>
            </div>

            <div class="review-card">
                <p>“Best printing service in the area. Affordable & fast.”</p>
                <h4>— Faizan Ali</h4>
                <span>⭐⭐⭐⭐</span>
            </div>
        </div>

        <!-- ===== STATS ===== -->
        <div class="stats-grid">
            <div class="stat-box">
                <h3 class="counter" data-target="850">0</h3>
                <p>Projects Completed</p>
            </div>
            <div class="stat-box">
                <h3 class="counter" data-target="500">0</h3>
                <p>Happy Clients</p>
            </div>
            <div class="stat-box">
                <h3 class="counter" data-target="12000">0</h3>
                <p>Work Hours</p>
            </div>
            <div class="stat-box">
                <h3 class="counter" data-target="10">0</h3>
                <p>Years Experience</p>
            </div>
        </div>
    </section>


    <footer class="footer">
        <div class="footer-section">
            <h3>Vivid Graphics</h3>
            <p>Professional printing & branding solutions.</p>
            <div class="footer-social">
            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.instagram.com/vividgraphics.in?igsh=MWIxdzAwZnV0bjBnNQ==" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>

            <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            <button class="auth-btn" id="openFeedback">Give Feedback</button>

        </div>
        </div>
    </footer>

    <!-- ================= STYLES ================= -->
    <style>
    /* ================= LOADER STYLES (Added to ensure functionality) ================= */
    #vividLoader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background-color: #ffffff;
        z-index: 99999; /* Highest priority */
        display: flex;
        justify-content: center;
        align-items: center;
        transition: opacity 0.8s ease, visibility 0.8s ease;
    }

    .vivid-loader-bg {
        position: absolute;
        width: 100%;
        height: 100%;
        background: #ffffff;
    }

    .logo-wrapper img {
        width: 200px; /* BIG logo */
        max-width: 90vw;
        height: auto;
        animation: pulse 3s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); opacity: 0.8; }
        100% { transform: scale(2); opacity: 1; }
    }




    
    /* ================= REVIEWS & STATS ================= */
    .reviews-stats {
        background: #0d0d0d;
        padding: 70px 20px;
        text-align: center;
    }

    .reviews-stats h2 {
        color: #ffcc00;
        font-size: 34px;
        margin-bottom: 10px;
    }

    .reviews-stats .subtitle {
        color: #aaa;
        margin-bottom: 40px;
    }

    /* Reviews */
    .reviews-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 25px;
        max-width: 1100px;
        margin: auto;
    }

    .review-card {
        background: #161616;
        padding: 25px;
        border-radius: 16px;
        color: #eee;
        box-shadow: 0 10px 25px rgba(0,0,0,0.4);
        transition: transform 0.3s;
    }

    .review-card:hover {
        transform: translateY(-6px);
    }

    .review-card h4 {
        margin-top: 15px;
        color: #ffcc00;
    }

    /* Stats */
    .stats-grid {
        margin-top: 60px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 30px;
        max-width: 900px;
        margin-inline: auto;
    }

    .stat-box {
        background: linear-gradient(145deg, #111, #1b1b1b);
        padding: 30px;
        border-radius: 20px;
    }

    .stat-box h3 {
        font-size: 40px;
        color: #ffcc00;
    }

    .stat-box p {
        color: #ccc;
        margin-top: 8px;
    }

  
    /* Modern Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(8px);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .modal-overlay.active {
        display: flex;
        opacity: 1;
    }

    .modal-card {
        background: #ffffff;
        width: 100%;
        max-width: 420px;
        padding: 40px;
        border-radius: 24px;
        position: relative;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        transform: translateY(20px);
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .modal-overlay.active .modal-card {
        transform: translateY(0);
    }

    .close-modal {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 28px;
        color: #999;
        cursor: pointer;
        transition: color 0.2s;
        line-height: 1;
    }

    .close-modal:hover {
        color: #333;
    }

    .modal-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .modal-header h2 {
        font-size: 2rem;
        color: #1a1a1a;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .modal-header p {
        color: #666;
        font-size: 0.95rem;
    }

    .auth-form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .input-group {
        position: relative;
    }

    .input-group i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #ffcc00;
        font-size: 1.1rem;
    }

    .input-group input {
        width: 100%;
        padding: 14px 15px 14px 45px;
        border: 2px solid #f0f0f0;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        outline: none;
    }

    .input-group input:focus {
        border-color: #ffcc00;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(255, 204, 0, 0.1);
    }

    .auth-btn {
        background: #ffcc00;
        color: #000;
        border: none;
        padding: 14px;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 10px;
    }

    .auth-btn:hover {
        background: #e6b800;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 204, 0, 0.3);
    }

    .auth-switch {
        text-align: center;
        margin-top: 20px;
        font-size: 0.9rem;
        color: #666;
    }

    .auth-switch a {
        color: #ffcc00;
        text-decoration: none;
        font-weight: 700;
    }

    .auth-switch a:hover {
        text-decoration: underline;
    }

    .auth-alert {
        padding: 12px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 0.9rem;
        text-align: center;
    }

    .auth-alert.error {
        background: #fff1f0;
        color: #f5222d;
        border: 1px solid #ffa39e;
    }

    .auth-alert.success {
        background: #f6ffed;
        color: #52c41a;
        border: 1px solid #b7eb8f;
    }

    .hidden {
        display: none;
    }
    /* ================= PRODUCT MODAL ================= */
    .product-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.75);
        backdrop-filter: blur(6px);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 2000;
    }

    .product-modal-overlay.active {
        display: flex;
    }

    .product-modal-card {
        background: #fff;
        width: 90%;
        max-width: 420px;
        padding: 35px;
        border-radius: 22px;
        position: relative;
        box-shadow: 0 30px 60px rgba(0,0,0,0.35);
        animation: popUp 0.4s ease;
    }

    .product-modal-card h2 {
        font-size: 26px;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .product-modal-card p {
        font-size: 15px;
        color: #555;
        margin-bottom: 15px;
    }

    .product-modal-card ul {
        padding-left: 18px;
        margin-bottom: 20px;
    }

    .product-modal-card li {
        margin-bottom: 8px;
        font-size: 14px;
    }

    .product-close {
        position: absolute;
        top: 18px;
        right: 20px;
        font-size: 26px;
        cursor: pointer;
        color: #888;
    }

    .product-close:hover {
        color: #ffcc00;
    }

    .product-image {
        width: 100%;
        border-radius: 10px;
        margin-bottom: 15px;
        max-height: 200px;
        object-fit: cover;
    }

    @keyframes popUp {
        from {
            transform: translateY(30px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    </style>

    <!-- ================= PRODUCT POPUP MODAL ================= -->
    <div class="product-modal-overlay" id="productModal">
    <div class="product-modal-card">
        <span class="product-close" id="closeProduct">&times;</span>

        <!-- ✅ Product Image -->
        <img id="productImage" class="product-image" src="" alt="Product Image">

        <h2 id="productTitle">Product Title</h2>
        <p id="productDesc">Product description</p>

        <ul id="productFeatures"></ul>

        <a href="product.html">
            <button class="auth-btn">Get a Quote</button>
        </a>
    </div>
    </div>


    <!-- ================= CONTACT US MODAL ================= -->
    <div class="modal-overlay" id="contactModal">
        <div class="modal-card">
            <span class="close-modal" id="closeContact">&times;</span>

            <div class="modal-header">
                <h2>Contact Us</h2>
                <p>We’d love to hear from you</p>
            </div>

            <form class="auth-form">
                <div class="input-group">
                    <h2>Vivid Graphics</h2>
                </div>

                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" placeholder="Your Email" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-message"></i>
                    <input type="text" placeholder="Your Message" required>
                </div>

                <button type="submit" class="auth-btn">Send Message</button>
            </form>
        </div>
    </div>

    <!-- ================= FEEDBACK MODAL ================= -->
    <div class="modal-overlay" id="feedbackModal">
    <div class="modal-card">
        <span class="close-modal" id="closeFeedback">&times;</span>

        <div class="modal-header">
            <h2>Share Your Feedback</h2>
            <p>Your opinion matters to us</p>
        </div>

        <form class="auth-form feedback-form" method="POST" action="">
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="Your Email" required>

            <select name="rating" required>
                <option value="">Rate Our Service</option>
                <option>⭐⭐⭐⭐⭐ Excellent</option>
                <option>⭐⭐⭐⭐ Very Good</option>
                <option>⭐⭐⭐ Good</option>
                <option>⭐⭐ Average</option>
                <option>⭐ Poor</option>
            </select>

            <textarea name="message" placeholder="Your feedback..." required></textarea>

            <button type="submit">Submit Feedback</button>
        </form>
    </div>
</div>



    <script>
    /* ================= CONSOLIDATED LOGIC ================= */
    
    // 1. Modal & Navbar Logic
    const modal = document.getElementById("authModal");
    const openBtn = document.getElementById("openLogin");
    const closeBtn = document.getElementById("closeModal");
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");
    const showRegister = document.getElementById("showRegister");
    const showLogin = document.getElementById("showLogin");
    const modalTitle = document.getElementById("modalTitle");
    const modalSubtitle = document.getElementById("modalSubtitle");

    document.getElementById("hamburger").onclick = () =>
        document.querySelector(".nav-links").classList.toggle("active");

    if(openBtn) {
        openBtn.onclick = () => {
            modal.classList.add("active");
            switchToLogin();
        };
    }

    closeBtn.onclick = () => modal.classList.remove("active");

    window.onclick = (e) => {
        if (e.target == modal) modal.classList.remove("active");
    };

    if(showRegister) {
        showRegister.onclick = () => {
            loginForm.classList.add("hidden");
            registerForm.classList.remove("hidden");
            modalTitle.innerText = "Join Us";
            modalSubtitle.innerText = "Create an account to get started.";
        };
    }

    if(showLogin) {
        showLogin.onclick = switchToLogin;
    }

    function switchToLogin() {
        registerForm.classList.add("hidden");
        loginForm.classList.remove("hidden");
        modalTitle.innerText = "Welcome Back";
        modalSubtitle.innerText = "Please enter your details to login.";
    }

    // Keep register form visible if there was a registration error
    <?php if($action === 'register'): ?>
        if(showRegister) showRegister.click();
    <?php endif; ?>

    /* ================= PRODUCT DATA ================= */
    const productDetails = {
        "Flex Printing": {
            image: "uploads/products/flex_print.jpg",
            desc: "High-quality flex printing for indoor and outdoor branding.",
            features: ["Weather Resistant", "High Resolution", "Cost Effective"]
        },
        "Vinyl Printing": {
            image: "uploads/products/vinyl.jpg",
            desc: "Premium vinyl printing with glossy finish.",
            features: ["Durable", "Waterproof", "Attractive Finish"]
        },
        "Digital Printing": {
            image: "uploads/products/digital_print.jpg",
            desc: "Fast and sharp digital prints for promotions.",
            features: ["HD Output", "Quick Delivery", "Bulk Orders"]
        },
        "LED Signboard": {
            image: "uploads/products/ledsign.jpg",
            desc: "Eye-catching LED boards for business visibility.",
            features: ["Bright Display", "Energy Efficient", "Custom Sizes"]
        },
        "Inshop Branding": {
            image: "uploads/products/inshop.jpg",
            desc: "Complete interior branding solutions for shops.",
            features: ["Creative Design", "Professional Finish", "Custom Themes"]
        },
        "Custom Gifts": {
            image: "uploads/products/customgift.jpg",
            desc: "Personalized gifts for corporate & events.",
            features: ["Unique Designs", "Bulk Orders", "Fast Delivery"]
        }
    };

    const productModal = document.getElementById("productModal");
    const productTitle = document.getElementById("productTitle");
    const productDesc = document.getElementById("productDesc");
    const productFeatures = document.getElementById("productFeatures");
    const productImage = document.getElementById("productImage");

    document.querySelectorAll(".product-card").forEach(card => {
        card.addEventListener("click", () => {
            const name = card.innerText.trim();
            const data = productDetails[name];

            if (!data) return;

            productTitle.innerText = name;
            productDesc.innerText = data.desc;
            productImage.src = data.image;
            productImage.alt = name;

            productFeatures.innerHTML = "";
            data.features.forEach(feature => {
                const li = document.createElement("li");
                li.textContent = feature;
                productFeatures.appendChild(li);
            });

            productModal.classList.add("active");
        });
    });

    /* CLOSE MODAL */
    const closeProduct = document.getElementById("closeProduct");
    if(closeProduct) {
        closeProduct.onclick = () => productModal.classList.remove("active");
    }

    window.addEventListener("click", e => {
        if (e.target === productModal) {
            productModal.classList.remove("active");
        }
    });


    /* ================= CONTACT MODAL ================= */
    const contactModal = document.getElementById("contactModal");
    const openContact = document.getElementById("openContact");
    const closeContact = document.getElementById("closeContact");

    openContact.onclick = () => {
        contactModal.classList.add("active");
    };

    closeContact.onclick = () => {
        contactModal.classList.remove("active");
    };

    window.addEventListener("click", e => {
        if (e.target === contactModal) {
            contactModal.classList.remove("active");
        }
    });

    /* ================= STATS COUNTER ================= */
    const counters = document.querySelectorAll(".counter");
    let started = false;

    function startCounters() {
        if (started) return;
        started = true;

        counters.forEach(counter => {
            const target = +counter.dataset.target;
            let count = 0;
            const speed = target / 100;

            const update = () => {
                count += speed;
                if (count < target) {
                    counter.innerText = Math.floor(count);
                    requestAnimationFrame(update);
                } else {
                    counter.innerText = target;
                }
            };
            update();
        });
    }

    window.addEventListener("scroll", () => {
        const section = document.querySelector(".reviews-stats");
        if (section && section.getBoundingClientRect().top < window.innerHeight - 100) {
            startCounters();
        }
    });

    /* ================= FEEDBACK MODAL ================= */
    const feedbackModal = document.getElementById("feedbackModal");
    const openFeedback = document.getElementById("openFeedback");
    const closeFeedback = document.getElementById("closeFeedback");

    if(openFeedback) openFeedback.onclick = () => feedbackModal.classList.add("active");
    if(closeFeedback) closeFeedback.onclick = () => feedbackModal.classList.remove("active");

    window.addEventListener("click", e => {
        if (e.target === feedbackModal) {
            feedbackModal.classList.remove("active");
        }
    });

    /* ================= VIVID LOADER FIXED SCRIPT ================= */
    
    // Function to hide loader
    function hideLoader() {
        const loader = document.getElementById("vividLoader");
        if (!loader || loader.style.display === "none") return;

        // Visual fade out
        loader.style.opacity = "0";
        loader.style.transform = "scale(1.05)";
        
        // Actually remove it after transition
        setTimeout(() => {
            loader.style.display = "none";
            // Ensure scrolling is enabled if it was disabled
            document.body.style.overflow = "auto";
        }, 800);
    }

    // 1. Try to run when everything (images/styles) is loaded
    window.addEventListener("load", () => {
        setTimeout(hideLoader, 1500); // 1.5s delay for effect
    });

    // 2. SAFETY NET: If window.load hangs (e.g., missing image), force hide after 4 seconds
    document.addEventListener("DOMContentLoaded", () => {
        setTimeout(hideLoader, 4000);
    });

    </script>

    </body>
    </html>