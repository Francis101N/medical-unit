<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicals | Enterprise Medical & Clinic Operations Management Suite</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #0f172a;
            --accent-green: #10b981;
            --bg-light: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #334155;
            background-color: #ffffff;
            overflow-x: hidden;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
        }

        .hero-section {
            background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
            padding: 100px 0 80px;
            position: relative;
        }

        .hero-badge {
            background: #dbeafe;
            color: #1e40af;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 6px 14px;
            border-radius: 50rem;
        }

        .feature-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.05), 0 8px 10px -6px rgb(0 0 0 / 0.05);
            border-color: #cbd5e1;
        }

        .pricing-card {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #ffffff;
            transition: all 0.3s ease;
            position: relative;
        }

        .pricing-card.popular {
            border: 2px solid var(--primary-color);
            box-shadow: 0 20px 25px -5px rgb(13 110 253 / 0.1);
        }

        .pricing-card.popular::before {
            content: 'Most Popular Choice';
            position: absolute;
            top: -14px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary-color);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 50rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .faq-accordion .accordion-button:not(.collapsed) {
            background-color: #f1f5f9;
            color: var(--secondary-color);
            font-weight: 700;
        }

        .faq-accordion .accordion-button:focus {
            box-shadow: none;
            border-color: #e2e8f0;
        }

        .testimonial-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 30px;
        }

        footer {
            background: var(--secondary-color);
            color: #94a3b8;
        }
    </style>
</head>

<body>

    <!-- Header / Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="bi bi-hospital-fill"></i>
                </div>
                <span class="fw-bold fs-4 text-dark tracking-tight">Medical<span class="text-primary">s</span></span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-lg-3">
                    <li class="nav-item"><a class="nav-link fw-medium text-dark" href="#overview">Overview</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium text-dark" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium text-dark" href="#pricing">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium text-dark" href="#testimonials">Reviews</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium text-dark" href="#faq">FAQ</a></li>
                    <li class="nav-item ms-lg-3">
                        <a href="restriction.php" class="btn btn-outline-primary px-4 fw-semibold rounded-pill">Sign In</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 text-center text-lg-start">
                    <span class="hero-badge mb-3 d-inline-block"><i class="bi bi-shield-check-fill me-1 text-primary"></i> Multi-Branch Enterprise Medical Suite</span>
                    <h1 class="display-4 fw-extrabold text-dark tracking-tight mb-4 lh-base">
                        Advanced Medical Workflow & <span class="text-primary">Pharmaceutical Vault Management</span>
                    </h1>
                    <p class="lead text-secondary mb-5">
                        A modern, high-performance ecosystem built for clinics, hospitals, and corporate medical outfits. Seamlessly track multi-branch inventories, real-time dispensing, outreach programs, staff directories, and comprehensive reporting.
                    </p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3">
                        <a href="#pricing" class="btn btn-primary btn-lg px-4 fw-bold rounded-pill shadow-sm">Explore Outright Purchase</a>
                        <a href="restriction.php" class="btn btn-light btn-lg px-4 fw-bold rounded-pill border text-dark">Access Portal <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white p-2">
                        <div class="bg-light p-3 rounded-3 border text-center">
                            <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill mb-3 fw-semibold">
                                <i class="bi bi-broadcast me-1"></i> Real-Time Live Tracking & Vault Synchronization Active
                            </div>
                            <div class="row text-start g-3">
                                <div class="col-12">
                                    <div class="p-3 bg-white rounded-3 border shadow-3xs">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="font-monospace text-muted small">SECURE DISPENSING LEDGER</span>
                                            <span class="badge bg-primary-subtle text-primary border">ACID Synchronized</span>
                                        </div>
                                        <h6 class="fw-bold mb-1 text-dark">Multi-Drug Prescription & Inventory Vaults</h6>
                                        <p class="text-muted small mb-0">Instant stock row-locking, multi-branch tracking, patient reporting, and outreach records unification.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Overview Section -->
    <section id="overview" class="py-5 bg-white border-bottom">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="text-primary fw-bold text-uppercase tracking-wider small">System Architecture</span>
                    <h2 class="fw-bold fs-2 mt-2 mb-4">Engineered for Complete Healthcare Visibility</h2>
                    <p class="text-muted mb-4">
                        Medical Unit unifies every facet of medical operations into a single pane of glass. Whether managing primary branch facilities or mobile health outreaches, administrators gain complete situational awareness through live tracking engines.
                    </p>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2 text-dark fw-semibold small">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i> Multi-Branch Operations
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2 text-dark fw-semibold small">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i> Real-Time Live Tracking
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2 text-dark fw-semibold small">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i> Referral Generations
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2 text-dark fw-semibold small">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i> Automated Audit Logs
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="p-4 bg-light rounded-4 border">
                        <h4 class="fw-bold fs-5 mb-3 text-dark"><i class="bi bi-speedometer2 text-primary me-2"></i> Comprehensive Operational Metrics</h4>
                        <ul class="list-unstyled d-flex flex-column gap-3 mb-0 text-secondary small">
                            <li class="d-flex justify-content-between border-bottom pb-2">
                                <span>Staff Directory & Profiles</span>
                                <strong class="text-dark">Global & Branch-Isolated</strong>
                            </li>
                            <li class="d-flex justify-content-between border-bottom pb-2">
                                <span>Medical & Treatment Logs</span>
                                <strong class="text-dark">Vitals, Diagnosis, Notes</strong>
                            </li>
                            <li class="d-flex justify-content-between border-bottom pb-2">
                                <span>Pharmaceutical Vaults</span>
                                <strong class="text-dark">Dynamic Stock & Multi-Dispense</strong>
                            </li>
                            <li class="d-flex justify-content-between pb-0">
                                <span>Outreach & Reports</span>
                                <strong class="text-dark">Patient Registry & Analytics</strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Overview -->
    <section id="features" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center max-w-xl mx-auto mb-5">
                <span class="text-primary fw-bold text-uppercase tracking-wider small">Comprehensive Modules</span>
                <h2 class="fw-bold fs-2 mt-2">Built for Precision, Security, and Speed</h2>
                <p class="text-muted">Explore the powerful toolsets designed specifically for clinical excellence and pharmacy compliance.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card p-4 h-100 bg-white">
                        <div class="bg-primary-subtle text-primary rounded-3 d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-capsule fs-4"></i>
                        </div>
                        <h4 class="fw-bold fs-5 text-dark mb-2">Drugs Vault & Dispensing</h4>
                        <p class="text-muted small mb-0">Multi-select drug prescriptions with automatic vault balance checks, ACID-safe inventory deductions, and real-time transaction logging.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card p-4 h-100 bg-white">
                        <div class="bg-success-subtle text-success rounded-3 d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                        <h4 class="fw-bold fs-5 text-dark mb-2">Staff & Medical Records</h4>
                        <p class="text-muted small mb-0">Centralized profile tracking with encrypted credential views, role-based directory filtering, vitals monitoring, and thorough patient history.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card p-4 h-100 bg-white">
                        <div class="bg-warning-subtle text-warning rounded-3 d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-heart-pulse-fill fs-4"></i>
                        </div>
                        <h4 class="fw-bold fs-5 text-dark mb-2">Outreach Section & Records</h4>
                        <p class="text-muted small mb-0">Manage community and corporate medical outreach programs, track external patient logs, and maintain unified health metrics.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card p-4 h-100 bg-white">
                        <div class="bg-info-subtle text-info rounded-3 d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-file-earmark-bar-graph-fill fs-4"></i>
                        </div>
                        <h4 class="fw-bold fs-5 text-dark mb-2">Reports & Referral Generation</h4>
                        <p class="text-muted small mb-0">Instantly generate professional patient reports, medical summaries, and external clinic/hospital referral documentation.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card p-4 h-100 bg-white">
                        <div class="bg-danger-subtle text-danger rounded-3 d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-diagram-3-fill fs-4"></i>
                        </div>
                        <h4 class="fw-bold fs-5 text-dark mb-2">Cross-Branch Operations</h4>
                        <p class="text-muted small mb-0">Global administrative oversight for super admins combined with secure, isolated data boundaries for regional branch clinics.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card p-4 h-100 bg-white">
                        <div class="bg-secondary-subtle text-secondary rounded-3 d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-shield-check fs-4"></i>
                        </div>
                        <h4 class="fw-bold fs-5 text-dark mb-2">Live Tracking & Security</h4>
                        <p class="text-muted small mb-0">Real-time live telemetry tracking counters, secure tokenized IDs, and robust AES-128-CBC credential encryption standards.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center max-w-xl mx-auto mb-5">
                <span class="text-primary fw-bold text-uppercase tracking-wider small">Flexible Licensing</span>
                <h2 class="fw-bold fs-2 mt-2">Transparent Plans for Any Organization Size</h2>
                <p class="text-muted">Choose outright perpetual ownership or flexible monthly and yearly cloud subscriptions.</p>
            </div>
            <div class="row g-4 align-items-stretch">
                <!-- Outright Purchase (Most Popular) -->
                <div class="col-lg-4">
                    <div class="pricing-card popular p-4 p-lg-5 h-100 d-flex flex-column justify-content-between bg-white shadow-sm">
                        <div>
                            <h3 class="fw-bold fs-4 mb-2">Outright Purchase</h3>
                            <p class="text-muted small">Complete source license for self-hosted enterprise deployment. Preferred by most clinics.</p>
                            <div class="my-4">
                                <div class="display-6 fw-extrabold text-primary">$3,025<span class="fs-6 text-muted fw-normal"> / lifetime</span></div>
                                <div class="text-muted fw-semibold small mt-1">₦4,000,000 / lifetime</div>
                            </div>
                            <ul class="list-unstyled d-flex flex-column gap-3 mb-4">
                                <li class="d-flex align-items-center gap-2 text-dark small"><i class="bi bi-check-circle-fill text-success"></i> Full Source Code & Database Access</li>
                                <li class="d-flex align-items-center gap-2 text-dark small"><i class="bi bi-check-circle-fill text-success"></i> One-Time Payment, Lifetime Ownership</li>
                                <li class="d-flex align-items-center gap-2 text-dark small"><i class="bi bi-check-circle-fill text-success"></i> On-Premise / Custom Server Setup</li>
                                <li class="d-flex align-items-center gap-2 text-dark small"><i class="bi bi-check-circle-fill text-success"></i> All Modules & Multi-Branch Tools Included</li>
                                <li class="d-flex align-items-center gap-2 text-dark small"><i class="bi bi-check-circle-fill text-success"></i> 3 Months Free Maintenance & Customization</li>
                            </ul>
                        </div>
                        <a href="checkout.php?plan=outright" class="btn btn-primary fw-bold rounded-pill py-2 w-100 shadow-sm">Get Outright License</a>
                    </div>
                </div>
                <!-- Yearly Plan -->
                <div class="col-lg-4">
                    <div class="pricing-card p-4 p-lg-5 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="fw-bold fs-4 mb-2">Annual SaaS</h3>
                            <p class="text-muted small">Cloud-hosted convenience for growing medical centers. Save 20% annually.</p>
                            <div class="my-4">
                                <div class="display-6 fw-extrabold text-dark">$2,000<span class="fs-6 text-muted fw-normal"> / year</span></div>
                                <div class="text-muted fw-semibold small mt-1">₦2,640,000 / year</div>
                            </div>
                            <ul class="list-unstyled d-flex flex-column gap-3 mb-4">
                                <li class="d-flex align-items-center gap-2 text-dark small"><i class="bi bi-check-circle-fill text-success"></i> Full Multi-Branch Roster & Vault Access</li>
                                <li class="d-flex align-items-center gap-2 text-dark small"><i class="bi bi-check-circle-fill text-success"></i> Priority 24/7 Technical Support</li>
                                <li class="d-flex align-items-center gap-2 text-dark small"><i class="bi bi-check-circle-fill text-success"></i> Advanced Audit & Stock Analytics</li>
                                <li class="d-flex align-items-center gap-2 text-dark small"><i class="bi bi-check-circle-fill text-success"></i> Automated Cloud Backups & Updates</li>
                            </ul>
                        </div>
                        <a href="checkout.php?plan=annual" class="btn btn-outline-primary fw-bold rounded-pill py-2 w-100">Choose Annual Plan</a>
                    </div>
                </div>
                <!-- Monthly Plan -->
                <div class="col-lg-4">
                    <div class="pricing-card p-4 p-lg-5 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="fw-bold fs-4 mb-2">Monthly Cloud</h3>
                            <p class="text-muted small">Ideal for pilot testing and small clinics looking for low initial startup costs.</p>
                            <div class="my-4">
                                <div class="display-6 fw-extrabold text-dark">$130<span class="fs-6 text-muted fw-normal"> / month</span></div>
                                <div class="text-muted fw-semibold small mt-1">₦171,600 / month</div>
                            </div>
                            <ul class="list-unstyled d-flex flex-column gap-3 mb-4">
                                <li class="d-flex align-items-center gap-2 text-dark small"><i class="bi bi-check-circle-fill text-success"></i> Core Multi-Branch Features</li>
                                <li class="d-flex align-items-center gap-2 text-dark small"><i class="bi bi-check-circle-fill text-success"></i> Automated Multi-Drug Dispensing</li>
                                <li class="d-flex align-items-center gap-2 text-dark small"><i class="bi bi-check-circle-fill text-success"></i> Standard Cloud Hosting</li>
                                <li class="d-flex align-items-center gap-2 text-dark small"><i class="bi bi-check-circle-fill text-success"></i> Standard Email Support</li>
                            </ul>
                        </div>
                        <a href="checkout.php?plan=monthly" class="btn btn-outline-dark fw-bold rounded-pill py-2 w-100">Get Started Monthly</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials & Recommendations -->
    <section id="testimonials" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center max-w-xl mx-auto mb-5">
                <span class="text-primary fw-bold text-uppercase tracking-wider small">Trusted Feedback</span>
                <p class="text-muted">See what pharmacy operators and medical directors say about Medical Unit.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="testimonial-card h-100 d-flex flex-column justify-content-between bg-white">
                        <div>
                            <div class="text-warning mb-3">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            </div>
                            <p class="text-dark fst-italic mb-4">"Managing multi-branch operations and inventory synchronization across our retail pharmacy outlets used to be cumbersome. Medical Unit's vault management and real-time live tracking changed everything for us."</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary text-white fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">MP</div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Pharm. Joke Adekola</h6>
                                <p class="text-muted small mb-0">Operations Director, Medplus Pharmacy Nigeria</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="testimonial-card h-100 d-flex flex-column justify-content-between bg-white">
                        <div>
                            <div class="text-warning mb-3">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            </div>
                            <p class="text-dark fst-italic mb-4">"The security, accuracy of patient records, and seamless referral generation tools have streamlined our clinical workflows immensely. It's a robust solution that meets high Canadian healthcare compliance standards."</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success text-white fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">TH</div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Dr. Robert Chen</h6>
                                <p class="text-muted small mb-0">Chief Medical Officer, Toronto General Hospital Network, Canada</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- FAQ Section -->
    <section id="faq" class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center max-w-xl mx-auto mb-5">
                <span class="text-primary fw-bold text-uppercase tracking-wider small">Got Questions?</span>
                <h2 class="fw-bold fs-2 mt-2">Frequently Asked Questions</h2>
                <p class="text-muted">Everything you need to know about multi-branch setup, vault security, and licensing.</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion faq-accordion" id="faqAccordion">
                        <div class="accordion-item border rounded-3 mb-3 overflow-hidden shadow-3xs">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                    How does the multi-branch drug vault and dispensing work?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted small lh-base">
                                    When medical personnel prescribe one or multiple drugs during consultation, the system executes an ACID-compliant transaction. It locks the specific branch vault row (`FOR UPDATE`), verifies balance availability, deducts precise quantities, and logs every action in the transparency audit trail.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border rounded-3 mb-3 overflow-hidden shadow-3xs">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                    What are the benefits of the Outright Purchase plan?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted small lh-base">
                                    The Outright Purchase gives you complete ownership of the source code and database architecture for a single one-time payment. You can host it on your own private local or cloud servers with zero recurring subscription fees and complete data sovereignty.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border rounded-3 mb-3 overflow-hidden shadow-3xs">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                    How do outreach sections and referral generations function?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted small lh-base">
                                    The outreach module lets you record external community patients and medical events separately. Furthermore, the referral generation engine allows practitioners to instantly produce standardized professional referral letters and patient status summaries.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border rounded-3 mb-3 overflow-hidden shadow-3xs">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                                    Can we pay in Nigerian Naira (₦) as well as US Dollars ($)?
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted small lh-base">
                                    Yes! Local institutions in Nigeria can complete payments directly in Naira via our supported local payment gateways and bank transfer options, while international clients can pay securely in USD.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border rounded-3 mb-3 overflow-hidden shadow-3xs">
                            <h2 class="accordion-header" id="headingFive">
                                <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive">
                                    Is patient data secure and compliant with health regulations?
                                </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted small lh-base">
                                    Absolutely. Medical Unit is built with industry-standard encryption protocols, role-based access control, and complete audit logs to ensure total compliance with data privacy acts and medical confidentiality standards.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border rounded-3 mb-3 overflow-hidden shadow-3xs">
                            <h2 class="accordion-header" id="headingSix">
                                <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix">
                                    What kind of technical support is provided after purchase?
                                </button>
                            </h2>
                            <div id="collapseSix" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted small lh-base">
                                    All outright purchases include 1 year of free maintenance, system updates, and technical assistance. Annual and monthly cloud subscribers enjoy continuous 24/7 priority or standard support depending on their chosen tier.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-5 text-white bg-black">
        <div class="container py-4">
            <div class="row g-4 justify-content-between align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-hospital-fill"></i>
                        </div>
                        <span class="fw-bold fs-5 text-white tracking-tight">Medical<span class="text-primary">s</span></span>
                    </div>
                    <p class="text-white small mb-0">Enterprise-grade clinic operations management suite built for maximum compliance, cross-branch tracking, and pharmaceutical vault security.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <p class="text-white small mb-0">&copy; 2026 Medicals. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>