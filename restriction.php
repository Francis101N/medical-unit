<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Restricted | Medical Unit Enterprise Suite</title>
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
            --bg-light: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-light);
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .restriction-card {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.05), 0 8px 10px -6px rgb(0 0 0 / 0.05);
            max-width: 650px;
            width: 100%;
        }

        .icon-box {
            width: 70px;
            height: 70px;
            background: #eff6ff;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 20px;
            font-size: 2rem;
            border: 1px solid #dbeafe;
        }

        .step-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 14px;
            background: #f1f5f9;
            color: #475569;
            border-radius: 50rem;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body class="d-flex flex-column h-100">

    <!-- Top Navigation Minimal Bar -->
    <nav class="navbar navbar-light bg-white border-bottom py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none" href="index.php">
                <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-hospital-fill"></i>
                </div>
                <span class="fw-bold fs-5 text-dark tracking-tight">Medical<span class="text-primary">Unit</span></span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small d-none d-sm-inline">Already have an active license?</span>
                <a href="auth-login.php" class="btn btn-outline-primary btn-sm fw-semibold rounded-pill px-3">Sign In</a>
            </div>
        </div>
    </nav>

    <!-- Main Restriction Center Container -->
    <main class="flex-grow-1 d-flex align-items-center justify-content-center py-5 px-3">
        <div class="restriction-card p-4 p-md-5 text-center">

            <span class="step-pill">
                <i class="bi bi-shield-lock-fill text-primary"></i> Active License Required
            </span>

            <div class="icon-box">
                <i class="bi bi-key-fill"></i>
            </div>

            <h1 class="fw-bold fs-3 text-dark mb-2">No Active Subscription Found</h1>
            <p class="text-muted mb-4 px-lg-3">
                The account or system instance you are trying to access has not been registered or lacks an active licensing agreement. To unlock multi-branch clinical vaults, patient logs, and real-time live tracking modules, please select a plan, complete payment, or request a temporary trial.
            </p>

            <div class="bg-light rounded-4 p-3 text-start mb-4 border">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-subtle text-primary p-2 rounded-3 fs-5">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark fs-6">Instant Provisioning & Free Demo Access</h6>
                        <p class="text-muted small mb-0">Choose our recommended <strong>Outright Purchase</strong> for permanent lifetime software ownership, or request a <strong>1-week trial demo</strong> strictly for review and testing purposes.</p>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                <a href="index.php#pricing" class="btn btn-primary fw-bold py-2.5 px-4 rounded-pill shadow-sm">
                    <i class="bi bi-cart-check-fill me-2"></i>Select Plan & Make Payment
                </a>
                <a href="mailto:support@medicalunit.com?subject=Request%20for%201-Week%20Demo%20Access" class="btn btn-outline-primary fw-bold py-2.5 px-4 rounded-pill">
                    <i class="bi bi-laptop me-2"></i>Request 1-Week Demo
                </a>
                <a href="index.php" class="btn btn-light fw-semibold py-2.5 px-4 rounded-pill border text-dark">
                    Return to Homepage
                </a>
            </div>

            <hr class="my-4 text-muted opacity-25">

            <div class="text-muted small">
                Need enterprise assistance or custom deployment contracts? <a href="mailto:support@medicalunit.com" class="text-primary text-decoration-none fw-semibold">Contact Support Team</a>
            </div>

        </div>
    </main>

    <!-- Minimal Footer -->
    <footer class="py-3 bg-black border-top text-center text-white small">
        <div class="container">
            &copy; 2026 Medical Unit Suite. All rights reserved. Secure Medical Management Infrastructure.
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>