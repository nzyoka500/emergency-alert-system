<?php

/**
 * index.php - Responda Official Landing Page
 * Modern, Production-Ready SaaS UI
 */
include '../includes/header.php';
?>

<style>
    :root {
        --primary: #4f46e5;
        --primary-hover: #4338ca;
        --slate-50: #f8fafc;
        --slate-200: #f6f9fc;
        --slate-700: #334155;
        --slate-900: #0f172a;
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #ffffff;
        color: var(--slate-900);
    }

    /* --- Navbar Polish --- */
    .navbar {
        background: rgba(248, 248, 248, 0.97);
        backdrop-filter: saturate(180%) blur(12px);
        -webkit-backdrop-filter: saturate(180%) blur(12px);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        padding: 1.25rem 0;
    }

    .navbar-brand img {
        transition: transform 0.3s ease;
    }

    .navbar-brand:hover img {
        transform: rotate(-10deg);
    }

    .nav-link {
        color: var(--slate-700) !important;
        font-size: 0.95rem;
        font-weight: 500;
        padding: 0.5rem 1.2rem !important;
        transition: color 0.2s ease;
    }

    .nav-link:hover {
        color: var(--primary) !important;
    }

    /* --- Hero Section --- */
    .hero-section {
        background: #0f172a;
        background: radial-gradient(circle at top right, #1e293b, #020617);
        padding: 160px 0 100px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .hero-section::after {
        content: "";
        position: absolute;
        width: 400px;
        height: 400px;
        background: var(--primary);
        filter: blur(150px);
        opacity: 0.15;
        top: 10%;
        left: 50%;
        z-index: 1;
    }

    .hero-title {
        font-size: clamp(2.5rem, 6vw, 4.5rem);
        font-weight: 800;
        letter-spacing: -0.05em;
        line-height: 1.1;
        margin-bottom: 1.5rem;
    }

    .hero-badge {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 8px 16px;
        border-radius: 100px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #a5b4fc;
        display: inline-flex;
        align-items: center;
        margin-bottom: 2rem;
    }

    /* --- Features & Stats --- */
    .feature-card {
        border: 1px solid var(--slate-200);
        border-radius: 24px;
        padding: 40px;
        background: #fff;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
    }

    .feature-card:hover {
        border-color: var(--primary);
        transform: translateY(-8px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
    }

    .icon-box {
        width: 48px;
        height: 48px;
        background-color: #eef2ff;
        color: var(--primary);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
    }

    /* --- Buttons --- */
    .btn-primary {
        background: var(--primary) !important;
        border: none !important;
        padding: 0.8rem 2rem !important;
        font-weight: 700;
        border-radius: 100px !important;
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
    }

    .btn-primary:hover {
        background: var(--primary-hover) !important;
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.4);
    }

    /* --- Mobile drawer menu --- */
    @media (max-width: 991.98px) {
        .navbar-collapse {
            background: white;
            padding: 2rem;
            margin-top: 1rem;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--slate-200);
        }
    }
</style>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <!-- 1. Left: Brand -->
        <a class="navbar-brand d-flex align-items-center fw-bold text-dark" href="/">
            <img src="assets/images/logo-white.png" style="width: 38px; height: 38px; border-radius: 50%; background: var(--primary); padding: 6px; margin-right: 12px;" alt="Responda Logo">
            <span style="letter-spacing: -0.5px; font-size: 1.25rem;">Responda</span>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- 2. Middle & Right: Menu -->
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="#features">Solution</a></li>
                <li class="nav-item"><a class="nav-link" href="#technology">Technology</a></li>
                <li class="nav-item"><a class="nav-link" href="#community">Community</a></li>
            </ul>

            <div class="d-flex align-items-center flex-column flex-lg-row gap-2">
                <a href="login.php" class="btn btn-link text-dark text-decoration-none fw-bold px-3">Login</a>
                <a href="register.php" class="btn btn-primary px-4 shadow-sm fw-bold">Get Started</a>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 text-center text-lg-start" style="z-index: 2;">
                <div class="hero-badge">
                    <span class="me-2">🚨</span> Real-time Emergency Coordination
                </div>
                <h1 class="hero-title">Protecting lives <br><span style="color: #818cf8;">at scale.</span></h1>
                <p class="lead text-slate-300 mb-5" style="font-size: 1.25rem; line-height: 1.6; opacity: 0.8;">
                    Responda bridges the critical gap between incident reporting and professional response with real-time verification and GPS precision.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                    <a href="register.php" class="btn btn-primary btn-lg px-5">Join the Network</a>
                    <a href="#features" class="btn btn-outline-light btn-lg px-5 rounded-pill border-opacity-25">See how it works</a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="position-relative">
                    <img src="assets/screenshots/dashboard.png" class="img-fluid rounded-4 shadow-2xl"
                        style="transform: perspective(1000px) rotateY(-15deg) rotateX(5deg); border: 1px solid rgba(255,255,255,0.1);"
                        alt="Dashboard Preview">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Row -->
<div class="bg-white border-bottom py-5">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-3">
                <h2 class="fw-bold text-dark mb-0">0.8s</h2>
                <p class="text-muted small fw-bold text-uppercase">Alert Latency</p>
            </div>
            <div class="col-md-3 border-start">
                <h2 class="fw-bold text-dark mb-0">100%</h2>
                <p class="text-muted small fw-bold text-uppercase">Vetted Reports</p>
            </div>
            <div class="col-md-3 border-start">
                <h2 class="fw-bold text-dark mb-0">99.9%</h2>
                <p class="text-muted small fw-bold text-uppercase">System Uptime</p>
            </div>
            <div class="col-md-3 border-start">
                <h2 class="fw-bold text-dark mb-0">GPS</h2>
                <p class="text-muted small fw-bold text-uppercase">Mapping API</p>
            </div>
        </div>
    </div>
</div>

<!-- Features -->
<section id="features" class="py-100 mt-5">
    <div class="container">
        <div class="text-center mb-5">
            <h6 class="text-primary fw-bold text-uppercase mb-2" style="letter-spacing: 2px;">The Infrastructure</h6>
            <h2 class="display-5 fw-bold text-dark">Engineered for Reliability</h2>
        </div>
        <div class="row g-4 pt-4 mb-5 pb-5">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="icon-box">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.63 8.31m5.96 6.06a14.98 14.98 0 01-12.12 6.16" />
                        </svg>
                    </div>
                    <h4 class="fw-bold mb-3">Rapid Dispatch</h4>
                    <p class="text-muted">Bypass manual queues with automated incident routing to the nearest available emergency responder units.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="icon-box">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.333 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                    <h4 class="fw-bold mb-3">Verified Intel</h4>
                    <p class="text-muted">Multi-step verification ensures that resources are never wasted on false alarms or unverified claims.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="icon-box">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 11 15 0z" />
                        </svg>
                    </div>
                    <h4 class="fw-bold mb-3">Geo-Precision</h4>
                    <p class="text-muted">High-precision GPS tagging helps responders find the exact site of an emergency even in crowded urban centers.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Technology Section -->
<section id="technology" class="section-padding bg-white">
    <div class="container">
        <div class="text-center mb-5 pb-lg-4">
            <h6 class="text-primary fw-bold text-uppercase" style="letter-spacing: 2px;">The Tech Stack</h6>
            <h2 class="display-5 fw-bold text-dark">Built for Scale & Security</h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">Our infrastructure is engineered using industry-standard technologies to ensure sub-second latency and 99.9% availability during critical emergencies.</p>
        </div>

        <div class="row g-4 pt-4">
            <!-- PHP Backend -->
            <div class="col-lg-3 col-md-6">
                <div class="feature-card border-slate-100 shadow-hover text-center">
                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                        <img src="assets/tech/php.png" alt="PHP" style="width: 40px; height: auto;" onerror="this.src='https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg'">
                    </div>
                    <h5 class="fw-bold">Enterprise PHP</h5>
                    <p class="text-muted small">Robust server-side logic handling real-time incident routing and secure data processing.</p>
                </div>
            </div>

            <!-- React UI -->
            <div class="col-lg-3 col-md-6">
                <div class="feature-card border-slate-100 shadow-hover text-center">
                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                        <img src="assets/tech/react.png" alt="React" style="width: 40px; height: auto;" onerror="this.src='https://cdn.jsdelivr.net/gh/devicons/devicon/icons/react/react-original.svg'">
                    </div>
                    <h5 class="fw-bold">Reactive UI</h5>
                    <p class="text-muted small">A lightning-fast, state-driven interface designed for high-pressure emergency dispatch environments.</p>
                </div>
            </div>

            <!-- MySQL -->
            <div class="col-lg-3 col-md-6">
                <div class="feature-card border-slate-100 shadow-hover text-center">
                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                        <img src="assets/tech/my-sql.png" alt="MySQL" style="width: 40px; height: auto;" onerror="this.src='https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg'">
                    </div>
                    <h5 class="fw-bold">Secure Storage</h5>
                    <p class="text-muted small">ACID-compliant relational database ensuring 100% data integrity for every emergency log.</p>
                </div>
            </div>

            <!-- Docker -->
            <div class="col-lg-3 col-md-6">
                <div class="feature-card border-slate-100 shadow-hover text-center">
                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                        <img src="assets/tech/docker.png" alt="Docker" style="width: 40px; height: auto;" onerror="this.src='https://cdn.jsdelivr.net/gh/devicons/devicon/icons/docker/docker-original.svg'">
                    </div>
                    <h5 class="fw-bold">Cloud Native</h5>
                    <p class="text-muted small">Containerized architecture for seamless deployment and infinite horizontal scalability.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Community Section -->
<section id="community" class="section-padding bg-slate-50">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <h6 class="text-primary fw-bold text-uppercase" style="letter-spacing: 2px;">Join Responda</h6>
                <h2 class="display-5 fw-bold text-dark mb-4">Be Part of the Solution</h2>
                <p class="lead text-muted mb-5">Responda isn't just software it's a lifeline. We connect vigilant citizens with dedicated responders to create a safer environment for everyone.</p>

                <div class="d-flex align-items-center p-4 bg-white rounded-4 shadow-sm border-start border-primary border-4 mb-4">
                    <div class="h3 fw-bold text-primary mb-0 me-3">5k+</div>
                    <div class="text-muted small fw-bold text-uppercase">Active Community Members</div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="row g-4">
                    <!-- Citizens -->
                    <div class="col-sm-6">
                        <div class="feature-card h-100 shadow-sm border-0">
                            <div class="icon-box bg-primary text-white">
                                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <h4 class="fw-bold mb-3">For Citizens</h4>
                            <p class="text-muted">Report incidents with one tap. Our GPS-verified system ensures help arrives at your exact location without confusion.</p>
                            <a href="register.php" class="text-primary fw-bold text-decoration-none small">Join as Citizen →</a>
                        </div>
                    </div>

                    <!-- Responders -->
                    <div class="col-sm-6">
                        <div class="feature-card h-100 shadow-sm border-0">
                            <div class="icon-box bg-danger text-white">
                                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h4 class="fw-bold mb-3">For Responders</h4>
                            <p class="text-muted">Get high-fidelity data. View incident severity, live coordinates, and situation reports before you even arrive on site.</p>
                            <a href="login.php" class="text-danger fw-bold text-decoration-none small">Responder Portal →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>