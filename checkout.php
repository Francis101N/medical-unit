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

    <link rel="icon" type="image/x-icon" href="assets/img/favicon.png">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- jsPDF Library v2.5.1 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img
                    src="./admin/dashboard/assets/images/logo/Gemini_Generated_Image_mrlvttmrlvttmrlv-removebg-preview.png"
                    alt="Logo"
                    height="400"
                    class="img-fluid"
                    style="max-width: 80px;">
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
                <!-- BANK DETAILS & WHATSAPP / EMAIL CONFIRMATION VIEW -->
                <div class="text-center mb-4">
                    <span class="badge bg-success-subtle text-success fw-bold text-uppercase tracking-wider px-3 py-2 rounded-pill mb-2"><i class="bi bi-check-circle-fill me-1"></i> Order Reserved Successfully</span>
                    <h1 class="fw-bold fs-3 text-dark">Make Your Bank Transfer</h1>
                    <p class="text-muted">Download your invoice PDF, make your transfer, and send your confirmation via WhatsApp or Email.</p>
                </div>

                <div class="checkout-card p-4 p-md-5">
                    <div class="alert alert-primary border-0 rounded-3 mb-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <span class="small text-muted d-block">Transaction Reference</span>
                                <strong class="fs-5 text-primary" id="refIdText"><?php echo $reference_id; ?></strong>
                            </div>
                            <div class="text-md-end">
                                <span class="small text-muted d-block">Selected Package</span>
                                <span class="fw-bold text-dark" id="planNameText"><?php echo $selected_plan['name']; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="bank-details-box p-4 mb-4 text-center">
                        <span class="text-uppercase small fw-bold text-muted tracking-wider d-block mb-1">Amount to Pay</span>
                        <div class="display-5 fw-extrabold text-success mb-3" id="planAmountText"><?php echo $selected_plan['formatted']; ?></div>

                        <hr class="my-3 opacity-25">

                        <div class="row text-start g-3 mt-2">
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">Bank Name</span>
                                <strong class="fs-6 text-dark">Providus Bank</strong>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">Account Name</span>
                                <strong class="fs-6 text-dark">Beaconify Limited</strong>
                            </div>
                            <div class="col-sm-12 mt-2">
                                <span class="text-muted small d-block">Account Number</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fs-4 fw-bold text-primary font-monospace bg-white px-3 py-1 border rounded" id="accNumber">1307872051</span>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="navigator.clipboard.writeText('1307872051'); alert('Account number copied!');">
                                        <i class="bi bi-clipboard"></i> Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-warning-subtle border border-warning-subtle rounded-3 p-3 mb-4 small text-warning-emphasis">
                        <i class="bi bi-info-circle-fill me-1"></i> <strong>Important:</strong> Use your Reference ID (<strong><?php echo $reference_id; ?></strong>) or Organization Name (<strong><?php echo htmlspecialchars($org_name); ?></strong>) as your transfer narration.
                    </div>

                    <!-- Hidden data containers for jsPDF generation -->
                    <div id="invoiceData" class="d-none"
                        data-org="<?php echo htmlspecialchars($org_name); ?>"
                        data-contact="<?php echo htmlspecialchars($contact_person); ?>"
                        data-email="<?php echo htmlspecialchars($email); ?>"
                        data-phone="<?php echo htmlspecialchars($phone); ?>"></div>

                    <div class="d-grid gap-2">
                        <!-- Download PDF Button -->
                        <button type="button" id="downloadPdfBtn" class="btn btn-dark fw-bold py-3 rounded-pill shadow-sm fs-5 d-flex align-items-center justify-content-center gap-2 mb-2">
                            <i class="bi bi-file-earmark-pdf-fill fs-4 text-danger"></i> Download Invoice PDF
                        </button>

                        <?php
                        // Prepare WhatsApp message payload
                        $whatsapp_number = "+2347010010811"; // Replace with your actual WhatsApp business line
                        $wa_message = "Hello Medicals Support,\n\nI have generated my invoice and completed my bank transfer for the software license.\n\n" .
                            "• Ref: {$reference_id}\n" .
                            "• Org: {$org_name}\n" .
                            "• Contact: {$contact_person}\n" .
                            "• Email: {$email}\n" .
                            "• Phone: {$phone}\n" .
                            "• Package: {$selected_plan['name']} ({$selected_plan['formatted']})\n\n" .
                            "I have downloaded my official invoice PDF and attached/forwarded it along with my payment receipt here for confirmation.";
                        $wa_url = "https://wa.me/" . preg_replace('/[^0-9]/', '', $whatsapp_number) . "?text=" . urlencode($wa_message);

                        // Prepare Email payload
                        $email_recipient = "accounts@beaconifyglobal.com";
                        $email_subject = "Payment Receipt - " . $reference_id . " - " . $org_name;
                        $email_body = "Hello Medicals Support,\n\nI the organization " . $org_name . " have completed my bank transfer for the software license.\n\n" .
                            "• Ref: " . $reference_id . "\n" .
                            "• Org: " . $org_name . "\n" .
                            "• Contact: " . $contact_person . "\n" .
                            "• Email: " . $email . "\n" .
                            "• Phone: " . $phone . "\n" .
                            "• Package: " . $selected_plan['name'] . " (" . $selected_plan['formatted'] . ")\n\n" .
                            "Please find my downloaded invoice PDF and payment receipt attached for confirmation.";
                        $email_url = "mailto:" . $email_recipient . "?subject=" . urlencode($email_subject) . "&body=" . urlencode($email_body);
                        ?>
                        <a href="<?php echo $wa_url; ?>" target="_blank" class="btn btn-success fw-bold py-3 rounded-pill shadow-sm fs-5 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-whatsapp fs-4"></i> Send Invoice & Receipt on WhatsApp
                        </a>
                        <a href="<?php echo $email_url; ?>" class="btn btn-outline-primary fw-bold py-3 rounded-pill shadow-sm fs-5 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-envelope-at fs-4"></i> Send Receipt via Email
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

    <!-- Bulletproof jsPDF Initialization & Fallback Download Script -->
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            // Plan Selector Query String Handler
            const urlParams = new URLSearchParams(window.location.search);
            const planParam = urlParams.get('plan');

            if (planParam) {
                const targetRadio = document.getElementById('plan' + planParam.charAt(0).toUpperCase() + planParam.slice(1));
                if (targetRadio) {
                    targetRadio.checked = true;
                }
            }

            const downloadBtn = document.getElementById('downloadPdfBtn');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Check if jsPDF loaded successfully
                    if (typeof window.jspdf === 'undefined') {
                        alert('jsPDF library failed to load from CDN. Please check your internet connection or adblocker settings.');
                        return;
                    }

                    try {
                        const {
                            jsPDF
                        } = window.jspdf;
                        const doc = new jsPDF({
                            orientation: 'portrait',
                            unit: 'mm',
                            format: 'a4'
                        });

                        const refId = document.getElementById('refIdText').innerText.trim();
                        const planName = document.getElementById('planNameText').innerText.trim();
                        const planAmount = document.getElementById('planAmountText').innerText.trim();

                        const invoiceData = document.getElementById('invoiceData');
                        const orgName = invoiceData ? invoiceData.getAttribute('data-org') : '';
                        const contact = invoiceData ? invoiceData.getAttribute('data-contact') : '';
                        const email = invoiceData ? invoiceData.getAttribute('data-email') : '';
                        const phone = invoiceData ? invoiceData.getAttribute('data-phone') : '';

                        // Header Background Bar (RGB)
                        doc.setFillColor(13, 110, 253);
                        doc.rect(0, 0, 210, 35, 'F');

                        // Header Title
                        doc.setTextColor(255, 255, 255);
                        doc.setFont("helvetica", "bold");
                        doc.setFontSize(20);
                        doc.text("MEDICALS MANAGEMENT SYSTEM", 14, 22);

                        doc.setFontSize(9);
                        doc.text("OFFICIAL INVOICE", 150, 22);

                        // Metadata Section
                        doc.setTextColor(51, 65, 85);
                        doc.setFontSize(10);
                        doc.setFont("helvetica", "normal");

                        let startY = 48;
                        doc.text("Reference ID: " + refId, 14, startY);
                        doc.text("Date: " + new Date().toLocaleDateString(), 150, startY);

                        startY += 8;
                        doc.text("Organization: " + orgName, 14, startY);
                        startY += 7;
                        doc.text("Contact Person: " + contact, 14, startY);
                        startY += 7;
                        doc.text("Email: " + email, 14, startY);
                        startY += 7;
                        doc.text("Phone: " + phone, 14, startY);

                        // Table Headers
                        startY += 12;
                        doc.setFillColor(241, 245, 249);
                        doc.rect(14, startY, 182, 10, 'F');
                        doc.setFont("helvetica", "bold");
                        doc.text("Item Description", 18, startY + 7);
                        doc.text("Total", 170, startY + 7);

                        // Table Content
                        startY += 12;
                        doc.setFont("helvetica", "normal");
                        doc.text(planName, 18, startY + 4);
                        doc.text(planAmount, 170, startY + 4);

                        // Divider Line (Removed setLineColor to prevent build mismatch errors)
                        startY += 12;
                        doc.setLineWidth(0.4);
                        doc.line(14, startY, 196, startY);

                        // Total Due Section
                        startY += 10;
                        doc.setFont("helvetica", "bold");
                        doc.setFontSize(13);
                        doc.text("Amount Due: " + planAmount, 125, startY + 5);

                        // Bank Details Box
                        startY += 18;
                        doc.setFillColor(248, 250, 252);
                        doc.roundedRect(14, startY, 182, 32, 3, 3, 'FD');

                        doc.setFontSize(9);
                        doc.setTextColor(100, 116, 139);
                        doc.text("DIRECT BANK TRANSFER INSTRUCTIONS", 18, startY + 7);

                        doc.setTextColor(51, 65, 85);
                        doc.setFont("helvetica", "bold");
                        doc.text("Bank Name: Providus Bank", 18, startY + 14);
                        doc.text("Account Name: Beaconify Limited", 18, startY + 21);
                        doc.text("Account Number: 1307872051", 18, startY + 28);

                        // Footer Notes
                        startY += 42;
                        doc.setFont("helvetica", "italic");
                        doc.setFontSize(8);
                        doc.setTextColor(148, 163, 184);
                        doc.text("Please use your Reference ID or Organization Name as narration when making your transfer.", 14, startY);
                        doc.text("Send your payment receipt and this invoice PDF via WhatsApp or email to activate your license.", 14, startY + 5);

                        // Trigger download
                        try {
                            doc.save("Invoice_" + refId + ".pdf");
                        } catch (saveErr) {
                            const pdfBlob = doc.output('blob');
                            const blobUrl = URL.createObjectURL(pdfBlob);
                            const downloadLink = document.createElement('a');
                            downloadLink.href = blobUrl;
                            downloadLink.download = "Invoice_" + refId + ".pdf";
                            document.body.appendChild(downloadLink);
                            downloadLink.click();
                            document.body.removeChild(downloadLink);
                            URL.revokeObjectURL(blobUrl);
                        }

                    } catch (err) {
                        console.error("PDF Generation Error Details:", err);
                        alert("An error occurred while generating the PDF. Check browser console for details.");
                    }
                });
            }
        });
    </script>

</body>

</html>