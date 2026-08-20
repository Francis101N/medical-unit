<?php

/** @var mysqli $conn */
include('./db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// Decrypt ID function matching the drug catalog list script
if (!function_exists('decryptId')) {
    function decryptId($hash)
    {
        $key = "medical-secret-key"; // Adjust secret key if your app uses a separate key for drugs, or keep consistent
        $decoded = base64_decode(strtr($hash, '-_', '+/'));
        if ($decoded === false) {
            return false;
        }
        $parts = explode('|', $decoded);

        if (count($parts) !== 2) {
            return false;
        }

        return $parts[0];
    }
}

$encrypted_code = $_GET['code'] ?? '';
$drug_code_pk = decryptId($encrypted_code);

if (!$drug_code_pk) {
    die("Invalid or tampered drug record reference.");
}

// Fetch drug master details
$stmt = $conn->prepare("SELECT * FROM drugs_master WHERE drug_code = ? LIMIT 1");
$stmt->bind_param("s", $drug_code_pk);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Drug definition record not found.");
}

$drug = $result->fetch_assoc();
$stmt->close();

$quantity = $drug['quantity'] ?? 0;
$stock_class = $quantity > 10 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Drug - <?php echo htmlspecialchars($drug['drug_name']); ?></title>
    <!-- Include Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <!-- Navigation and Actions Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="drugs.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Drug Catalog</a>
            <div>
                <a href="edit_drug.php?code=<?php echo urlencode($encrypted_code); ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Edit Definition</a>
            </div>
        </div>

        <!-- Drug Overview Header Card -->
        <div class="card p-4">
            <div class="row align-items-center">
                <div class="col-md">
                    <div class="mb-2">
                        <span class="badge bg-light text-dark border font-monospace px-2 py-1"><?php echo htmlspecialchars($drug['drug_code']); ?></span>
                        <span class="badge bg-light text-primary border ms-2"><?php echo htmlspecialchars($drug['category']); ?></span>
                    </div>
                    <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($drug['drug_name']); ?></h3>
                    <p class="text-secondary mb-2 fw-medium">Generic Name: <strong><?php echo htmlspecialchars($drug['generic_name']); ?></strong></p>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge <?php echo $stock_class; ?> border">Available Quantity: <strong><?php echo number_format($quantity); ?></strong></span>
                        <span class="badge bg-light text-dark border">Dosage Form: <?php echo htmlspecialchars($drug['dosage_form']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Drug Specifications & Strength -->
            <div class="col-lg-6">
                <div class="card p-4 h-100">
                    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle"></i> Drug Specifications</h5>
                    <table class="table table-borderless align-middle mb-0">
                        <tr>
                            <th class="text-muted w-40">SKU / Code:</th>
                            <td><span class="font-monospace"><?php echo htmlspecialchars($drug['drug_code']); ?></span></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Trade Name:</th>
                            <td><strong><?php echo htmlspecialchars($drug['drug_name']); ?></strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Generic Name:</th>
                            <td><?php echo htmlspecialchars($drug['generic_name']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Category:</th>
                            <td><?php echo htmlspecialchars($drug['category']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Strength:</th>
                            <td><?php echo htmlspecialchars($drug['strength'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Dosage Form:</th>
                            <td><?php echo htmlspecialchars($drug['dosage_form']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Inventory Status & Timestamps -->
            <div class="col-lg-6">
                <div class="card p-4 h-100">
                    <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-boxes"></i> Inventory & System Metadata</h5>
                    <table class="table table-borderless align-middle mb-0">
                        <tr>
                            <th class="text-muted w-40">Stock Quantity:</th>
                            <td><span class="badge <?php echo $stock_class; ?> px-2 py-1"><?php echo number_format($quantity); ?> units</span></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Created At:</th>
                            <td><small><?php echo htmlspecialchars($drug['created_at'] ?? '—'); ?></small></td>
                        </tr>

                    </table>
                </div>
            </div>
        </div>
    </div>

</body>

</html>