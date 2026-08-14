<?php

/**
 * Edit Master Drug Definition
 * File: edit_drug.php
 */
/** @var mysqli $conn */
include('db.php');

// 1. Ensure Session Initialization
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Security & Encryption Helpers
if (!function_exists('decryptId')) {
    function decryptId($encrypted)
    {
        $key = "drug-catalog-secret-key";
        $decoded = base64_decode(strtr($encrypted, '-_', '+/'));
        $parts = explode('|', $decoded);
        if (count($parts) === 2 && $parts[1] === $key) {
            return $parts[0];
        }
        return false;
    }
}

// 3. Access Control: Super-Admin authorization check
$user_role = strtolower($_SESSION['role'] ?? '');
if ($user_role !== 'super-admin') {
    header("Location: manage_drugs.php?error=" . urlencode("Unauthorized access clearance required."));
    exit();
}

$error_msg = '';

// Helper to safely decrypt the code parameter
$encrypted_code = $_GET['code'] ?? '';
$target_drug_code = !empty($encrypted_code) ? decryptId($encrypted_code) : '';

// 4. Handle Form POST Submission (Update Processing)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $original_code = trim($_POST['original_code'] ?? '');
    $drug_code     = trim($_POST['drug_code'] ?? '');
    $drug_name     = trim($_POST['drug_name'] ?? '');
    $generic_name  = trim($_POST['generic_name'] ?? '');
    $category      = trim($_POST['category'] ?? '');
    $strength      = trim($_POST['strength'] ?? '');
    $quantity_raw  = trim($_POST['quantity'] ?? '');
    $dosage_form   = trim($_POST['dosage_form'] ?? '');

    if (
        empty($original_code) ||
        empty($drug_code) ||
        empty($drug_name) ||
        empty($generic_name) ||
        empty($category) ||
        empty($strength) ||
        $quantity_raw === '' ||
        empty($dosage_form)
    ) {
        $error_msg = "All fields are required to update the asset definition.";
    } elseif (!is_numeric($quantity_raw) || (int)$quantity_raw < 0) {
        $error_msg = "Quantity must be a non-negative integer.";
    } else {
        $quantity = (int)$quantity_raw;
        try {
            // Check for duplicate drug_code if code is being changed
            if ($original_code !== $drug_code) {
                $check_stmt = $conn->prepare("SELECT drug_code FROM drugs_master WHERE drug_code = ? LIMIT 1");
                $check_stmt->bind_param("s", $drug_code);
                $check_stmt->execute();
                $check_res = $check_stmt->get_result();
                if ($check_res && $check_res->num_rows > 0) {
                    $error_msg = "The SKU/Code '{$drug_code}' is already assigned to another drug asset.";
                }
                $check_stmt->close();
            }

            if (empty($error_msg)) {
                $update_stmt = $conn->prepare("UPDATE drugs_master SET drug_code = ?, drug_name = ?, generic_name = ?, category = ?, strength = ?, quantity = ?, dosage_form = ? WHERE drug_code = ?");
                $update_stmt->bind_param("sssssiss", $drug_code, $drug_name, $generic_name, $category, $strength, $quantity, $dosage_form, $original_code);

                if ($update_stmt->execute()) {
                    $update_stmt->close();
                    $msg = "Successfully updated drug asset <strong>" . htmlspecialchars($drug_name) . "</strong>.";
                    header("Location: manage_drugs.php?status=success&msg=" . urlencode($msg));
                    exit();
                } else {
                    $error_msg = "Failed to update record in the database.";
                    $update_stmt->close();
                }
            }
        } catch (Exception $e) {
            $error_msg = "System Exception: " . $e->getMessage();
        }
    }
}

// 5. Fetch Record for Display
$lookup_code = !empty($target_drug_code) ? $target_drug_code : ($_POST['original_code'] ?? '');

if (empty($lookup_code)) {
    header("Location: manage_drugs.php?error=" . urlencode("Invalid or corrupted drug identifier."));
    exit();
}

$stmt = $conn->prepare("SELECT * FROM drugs_master WHERE drug_code = ? LIMIT 1");
$stmt->bind_param("s", $lookup_code);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $stmt->close();
    header("Location: manage_drugs.php?error=" . urlencode("Target master drug record not found."));
    exit();
}

$drug = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Drug - Medical Unit</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.css">

    <link rel="stylesheet" href="assets/vendors/simple-datatables/style.css">

    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
</head>
<style>
    .edit-card {
        border-radius: 16px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .form-premium-group .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .premium-input-icon {
        background-color: #f8fafc;
        border-right: none;
        color: #64748b;
    }
</style>

<body>
    <div id="app">
        <?php include('./inc/side-nav.php'); ?>
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="page-heading">
                <div class="page-title">
                    <div class="row">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3 class="fw-bold text-dark">Edit Drug</h3>
                            <p class="text-subtitle text-muted">
                                Update the details of an existing drug in the catalog.
                            </p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Edit Drug</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-8 col-lg-6">

                            <!-- Top Navigation -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <a href="manage_drugs.php" class="btn btn-outline-secondary btn-sm px-3 rounded-3">
                                    <i class="bi bi-arrow-left me-1"></i> Back to Catalog
                                </a>
                                <span class="badge bg-white text-dark border font-monospace px-3 py-2">
                                    SKU: <?php echo htmlspecialchars($drug['drug_code']); ?>
                                </span>
                            </div>

                            <!-- Error Banner -->
                            <?php if (!empty($error_msg)): ?>
                                <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error_msg; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <!-- Edit Form Card -->
                            <div class="card edit-card p-4 bg-white">
                                <div class="border-bottom pb-3 mb-4">
                                    <h4 class="fw-bold text-dark mb-1">Edit Master Drug Definition</h4>
                                    <p class="text-muted small mb-0">Update central catalog values for this drug item.</p>
                                </div>

                                <form action="proc_edit_drug.php?code=<?php echo urlencode($encrypted_code); ?>" method="POST">
                                    <input type="hidden" name="original_code" value="<?php echo htmlspecialchars($drug['drug_code']); ?>">

                                    <div class="row g-3">

                                        <!-- Item Code / SKU -->
                                        <div class="col-12 form-premium-group">
                                            <label for="drug_code" class="form-label">Item Code / SKU</label>
                                            <div class="input-group">
                                                <span class="input-group-text premium-input-icon"><i class="bi bi-qr-code"></i></span>
                                                <input type="text" class="form-control" id="drug_code" name="drug_code" required
                                                    value="<?php echo htmlspecialchars($drug['drug_code']); ?>">
                                            </div>
                                        </div>

                                        <!-- Trade / Brand Name -->
                                        <div class="col-12 form-premium-group">
                                            <label for="drug_name" class="form-label">Trade / Brand Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text premium-input-icon"><i class="bi bi-tag-fill"></i></span>
                                                <input type="text" class="form-control" id="drug_name" name="drug_name" required
                                                    value="<?php echo htmlspecialchars($drug['drug_name']); ?>">
                                            </div>
                                        </div>

                                        <!-- Generic Name -->
                                        <div class="col-12 form-premium-group">
                                            <label for="generic_name" class="form-label">Generic Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text premium-input-icon"><i class="bi bi-capsule"></i></span>
                                                <input type="text" class="form-control" id="generic_name" name="generic_name" required
                                                    value="<?php echo htmlspecialchars($drug['generic_name']); ?>">
                                            </div>
                                        </div>

                                        <!-- Category -->
                                        <div class="col-12 form-premium-group">
                                            <label for="category" class="form-label">Category</label>
                                            <div class="input-group">
                                                <span class="input-group-text premium-input-icon"><i class="bi bi-grid-fill"></i></span>
                                                <input type="text" class="form-control" id="category" name="category" required
                                                    value="<?php echo htmlspecialchars($drug['category']); ?>">
                                            </div>
                                        </div>

                                        <!-- Strength Allocation -->
                                        <div class="col-12 form-premium-group">
                                            <label for="strength" class="form-label">Strength / Concentration</label>
                                            <div class="input-group">
                                                <span class="input-group-text premium-input-icon"><i class="bi bi-speedometer2"></i></span>
                                                <input type="text" class="form-control" id="strength" name="strength" required
                                                    value="<?php echo htmlspecialchars($drug['strength'] ?? ''); ?>">
                                            </div>
                                        </div>

                                        <!-- Quantity Allocation -->
                                        <div class="col-12 form-premium-group">
                                            <label for="quantity" class="form-label">Quantity / Stock</label>
                                            <div class="input-group">
                                                <span class="input-group-text premium-input-icon"><i class="bi bi-layers-half"></i></span>
                                                <input type="number" class="form-control" id="quantity" name="quantity" required min="0"
                                                    value="<?php echo htmlspecialchars($drug['quantity'] ?? 0); ?>">
                                            </div>
                                        </div>

                                        <!-- Dosage Form -->
                                        <div class="col-12 form-premium-group">
                                            <label for="dosage_form" class="form-label">Dosage Form Factor</label>
                                            <div class="input-group">
                                                <span class="input-group-text premium-input-icon"><i class="bi bi-box-seam"></i></span>
                                                <select class="form-select" id="dosage_form" name="dosage_form" required>
                                                    <?php
                                                    $forms = ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Ointment', 'Inhaler'];
                                                    foreach ($forms as $form) {
                                                        $selected = (strtolower($drug['dosage_form'] ?? '') === strtolower($form)) ? 'selected' : '';
                                                        echo "<option value=\"{$form}\" {$selected}>{$form}</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="col-12 pt-3 d-flex gap-2">
                                            <a href="manage_drugs.php" class="btn btn-light w-50 py-2.5 fw-semibold" style="border-radius: 10px;">
                                                Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary w-50 py-2.5 fw-semibold" style="border-radius: 10px; background-color: #435ebe; border:none;">
                                                <i class="bi bi-check2-circle me-1"></i> Save Changes
                                            </button>
                                        </div>

                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <footer>
                <div class="footer clearfix mb-0 text-muted px-4 mt-5">
                    <div class="float-start">
                        <p>2026 &copy; Medical Management System</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>

</body>

</html>