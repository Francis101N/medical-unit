<?php
// checkout.php - Single page bank transfer checkout workflow

$is_submitted = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and collect form inputs
    $org_name       = trim($_POST['org_name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $email          = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone          = trim($_POST['phone'] ?? '');
    $plan           = trim($_POST['plan'] ?? 'outright');

    // Validation
    if (empty($org_name) || empty($contact_person) || empty($email) || empty($phone)) {
        $error_message = "All fields are required. Please fill out the form completely.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email address format.";
    } else {
        // Pricing Map (Synchronized with Dollar Equivalent at ~₦1,320/$1)
        $pricing = [
            'outright' => ['name' => 'Outright Purchase (Lifetime Ownership)', 'amount' => 4000000, 'formatted' => '₦4,000,000'],
            'annual'   => ['name' => 'Annual SaaS Cloud Subscription', 'amount' => 2640000, 'formatted' => '₦2,640,000'],
            'monthly'  => ['name' => 'Monthly Cloud Subscription', 'amount' => 171600, 'formatted' => '₦171,600']
        ];

        if (!array_key_exists($plan, $pricing)) {
            $plan = 'outright';
        }

        $selected_plan = $pricing[$plan];
        $reference_id  = 'MU-' . strtoupper(substr(uniqid(), -6));
        $is_submitted  = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Transfer Checkout & Confirmation | Medicals Enterprise Suite</title>
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

        .checkout-card {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.05), 0 8px 10px -6px rgb(0 0 0 / 0.05);
        }

        .plan-radio-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .plan-radio-card:hover {
            border-color: #cbd5e1;
        }

        .btn-check:checked+.plan-radio-card {
            border-color: var(--primary-color);
            background-color: #eff6ff;
        }

        .bank-details-box {
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
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
                <span class="fw-bold fs-5 text-dark tracking-tight">Medical<span class="text-primary">s</span></span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small">Need help? <a href="mailto:support@medicals.com" class="text-primary text-decoration-none fw-semibold">Contact Support</a></span>
            </div>
        </div>
    </nav>

    <!-- Main Checkout Container -->
    <main class="flex-grow-1 py-5 px-3">
        <div class="container" style="max-width: 900px;">

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger mb-4 rounded-3 shadow-sm">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!$is_submitted): ?>
                <!-- INITIAL FORM VIEW -->
                <div class="text-center mb-5">
                    <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase tracking-wider px-3 py-2 rounded-pill mb-2">Direct Bank Transfer</span>
                    <h1 class="fw-bold fs-3 text-dark">Complete Your License Subscription</h1>
                    <p class="text-muted">Provide your organization details and select your preferred package to generate payment instructions.</p>
                </div>

                <div class="checkout-card p-4 p-md-5">
                    <form action="" method="POST">

                        <!-- Section 1: Organization Details -->
                        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-building me-2 text-primary"></i>1. Organization Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="orgName" class="form-label small fw-semibold text-muted">Hospital / Pharmacy /Company Name</label>
                                <input type="text" class="form-control" id="orgName" name="org_name" placeholder="e.g. Medplus Pharmacy Nigeria" required>
                            </div>
                            <div class="col-md-6">
                                <label for="contactPerson" class="form-label small fw-semibold text-muted">Administrator / Contact Person</label>
                                <input type="text" class="form-control" id="contactPerson" name="contact_person" placeholder="e.g. Pharm. Joke Adekola" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label small fw-semibold text-muted">Official Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="admin@hospital.com" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label small fw-semibold text-muted">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="+234 800 000 0000" required>
                            </div>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <!-- Section 2: Plan Selection -->
                        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-tag-fill me-2 text-primary"></i>2. Choose Your Licensing Plan</h5>
                        <div class="row g-3 mb-4">

                            <!-- Outright Plan -->
                            <div class="col-12">
                                <input type="radio" class="btn-check" name="plan" id="planOutright" value="outright" checked>
                                <label class="plan-radio-card d-block w-100" for="planOutright">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1">Outright Purchase (Lifetime Ownership)</h6>
                                            <p class="text-muted small mb-0">Full source code & database access for self-hosted enterprise deployment.</p>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-extrabold text-primary fs-5">₦4,000,000</div>
                                            <div class="text-muted small">Lifetime License</div>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- Annual Plan -->
                            <div class="col-12">
                                <input type="radio" class="btn-check" name="plan" id="planAnnual" value="annual">
                                <label class="plan-radio-card d-block w-100" for="planAnnual">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1">Annual SaaS Cloud Subscription</h6>
                                            <p class="text-muted small mb-0">Full multi-branch access with priority 24/7 technical support and automated cloud backups.</p>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-extrabold text-dark fs-5">₦2,640,000</div>
                                            <div class="text-muted small">Per Year</div>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- Monthly Plan -->
                            <div class="col-12">
                                <input type="radio" class="btn-check" name="plan" id="planMonthly" value="monthly">
                                <label class="plan-radio-card d-block w-100" for="planMonthly">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1">Monthly Cloud Subscription</h6>
                                            <p class="text-muted small mb-0">Core multi-branch features and automated drug dispensing with standard hosting.</p>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-extrabold text-dark fs-5">₦171,600</div>
                                            <div class="text-muted small">Per Month</div>
                                        </div>
                                    </div>
                                </label>
                            </div>

                        </div>

                        <div class="bg-light rounded-3 p-3 mb-4 border d-flex align-items-center gap-3">
                            <i class="bi bi-bank2 text-primary fs-4"></i>
                            <p class="text-muted small mb-0">Upon submission, you will be presented with official corporate account transfer details to complete your payment.</p>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-bold py-3 rounded-pill shadow-sm">
                                <i class="bi bi-arrow-right-circle-fill me-2"></i>Generate Bank Transfer Details
                            </button>
                        </div>

                    </form>
                </div>

            <?php else: ?>
                <!-- BANK DETAILS & WHATSAPP CONFIRMATION VIEW -->
                <div class="text-center mb-4">
                    <span class="badge bg-success-subtle text-success fw-bold text-uppercase tracking-wider px-3 py-2 rounded-pill mb-2"><i class="bi bi-check-circle-fill me-1"></i> Order Reserved Successfully</span>
                    <h1 class="fw-bold fs-3 text-dark">Make Your Bank Transfer</h1>
                    <p class="text-muted">Please transfer the exact amount below to our official corporate account, then click the WhatsApp button to verify.</p>
                </div>

                <div class="checkout-card p-4 p-md-5">
                    <div class="alert alert-primary border-0 rounded-3 mb-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <span class="small text-muted d-block">Transaction Reference</span>
                                <strong class="fs-5 text-primary"><?php echo $reference_id; ?></strong>
                            </div>
                            <div class="text-md-end">
                                <span class="small text-muted d-block">Selected Package</span>
                                <span class="fw-bold text-dark"><?php echo $selected_plan['name']; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="bank-details-box p-4 mb-4 text-center">
                        <span class="text-uppercase small fw-bold text-muted tracking-wider d-block mb-1">Amount to Pay</span>
                        <div class="display-5 fw-extrabold text-success mb-3"><?php echo $selected_plan['formatted']; ?></div>

                        <hr class="my-3 opacity-25">

                        <div class="row text-start g-3 mt-2">
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">Bank Name</span>
                                <strong class="fs-6 text-dark">Guaranty Trust Bank (GTB)</strong>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">Account Name</span>
                                <strong class="fs-6 text-dark">Medical Software Ltd</strong>
                            </div>
                            <div class="col-sm-12 mt-2">
                                <span class="text-muted small d-block">Account Number</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fs-4 fw-bold text-primary font-monospace bg-white px-3 py-1 border rounded" id="accNumber">0123456789</span>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="navigator.clipboard.writeText('0123456789'); alert('Account number copied!');">
                                        <i class="bi bi-clipboard"></i> Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-warning-subtle border border-warning-subtle rounded-3 p-3 mb-4 small text-warning-emphasis">
                        <i class="bi bi-info-circle-fill me-1"></i> <strong>Important:</strong> Use your Reference ID (<strong><?php echo $reference_id; ?></strong>) or Organization Name (<strong><?php echo htmlspecialchars($org_name); ?></strong>) as your transfer narration.
                    </div>

                    <div class="d-grid gap-2">
                        <?php
                        // Prepare WhatsApp message payload
                        $whatsapp_number = "+2348069815240"; // Replace with your actual WhatsApp business line
                        $wa_message = "Hello Medicals Support,\n\nI have completed my bank transfer for the software license.\n\n" .
                            "• Ref: {$reference_id}\n" .
                            "• Org: {$org_name}\n" .
                            "• Contact: {$contact_person}\n" .
                            "• Package: {$selected_plan['name']} ({$selected_plan['formatted']})\n\n" .
                            "Attached is my payment receipt for confirmation.";
                        $wa_url = "https://wa.me/" . preg_replace('/[^0-9]/', '', $whatsapp_number) . "?text=" . urlencode($wa_message);
                        ?>
                        <a href="<?php echo $wa_url; ?>" target="_blank" class="btn btn-success fw-bold py-3 rounded-pill shadow-sm fs-5 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-whatsapp fs-4"></i> Send Payment Receipt on WhatsApp
                        </a>
                        <a href="checkout.php" class="btn btn-outline-secondary fw-semibold py-2 rounded-pill mt-2">
                            <i class="bi bi-arrow-left me-1"></i> Start New Order / Reset Form
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- Minimal Footer -->
    <footer class="py-3 bg-black border-top text-center text-white small">
        <div class="container">
            &copy; 2026 Medicals. All rights reserved. Secure Medical Management Infrastructure.
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Dynamic Plan Selector Script from Landing Page -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const planParam = urlParams.get('plan');

            if (planParam) {
                const targetRadio = document.getElementById('plan' + planParam.charAt(0).toUpperCase() + planParam.slice(1));
                if (targetRadio) {
                    targetRadio.checked = true;
                }
            }
        });
    </script>
</body>

</html>