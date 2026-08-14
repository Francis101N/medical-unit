<?php

/**
 * Master Drug Deletion Controller & Processor
 * File: delete_drug.php
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

// 4. Safe extraction and decryption of the target identifier
$encrypted_code = $_GET['code'] ?? ($_POST['code'] ?? '');
$target_drug_code = !empty($encrypted_code) ? decryptId($encrypted_code) : '';

if (empty($target_drug_code)) {
    header("Location: manage_drugs.php?error=" . urlencode("Invalid or corrupted drug identifier payload."));
    exit();
}

// 5. Check if drug exists before taking action
$stmt = $conn->prepare("SELECT drug_name, drug_code FROM drugs_master WHERE drug_code = ? LIMIT 1");
$stmt->bind_param("s", $target_drug_code);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $stmt->close();
    header("Location: manage_drugs.php?error=" . urlencode("Target drug record not found or already deleted."));
    exit();
}

$drug = $result->fetch_assoc();
$stmt->close();

// 6. Execution Gate: Only wipe record if request is explicitly POSTed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $delete_stmt = $conn->prepare("DELETE FROM drugs_master WHERE drug_code = ?");
        $delete_stmt->bind_param("s", $target_drug_code);

        if ($delete_stmt->execute()) {
            $delete_stmt->close();
            $msg = "Successfully purged <strong>" . htmlspecialchars($drug['drug_name']) . "</strong> from the master catalog.";
            header("Location: manage_drugs.php?status=success&msg=" . urlencode($msg));
            exit();
        } else {
            $delete_stmt->close();
            header("Location: manage_drugs.php?error=" . urlencode("Failed to execute deletion statement on database level."));
            exit();
        }
    } catch (Exception $e) {
        header("Location: manage_drugs.php?error=" . urlencode("System Exception: " . $e->getMessage()));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Permanent Removal | Central Catalog</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        .danger-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 15px 35px rgba(239, 68, 68, 0.1);
            border-top: 5px solid #ef4444;
        }

        .icon-shield {
            width: 70px;
            height: 70px;
            background-color: #fee2e2;
            color: #ef4444;
            font-size: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 20px;
        }
    </style>
</head>

<body class="bg-light py-5">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-5 text-center">

                <!-- Confirmation Layout Card -->
                <div class="card danger-card p-4 bg-white text-center">
                    <div class="icon-shield">
                        <i class="bi bi-shield-slash-fill"></i>
                    </div>

                    <h4 class="fw-bold text-dark mb-2">Destructive Action Alert</h4>
                    <p class="text-muted small px-2">
                        You are about to permanently purge this asset definition from the master inventory records. This action cannot be undone.
                    </p>

                    <!-- Target Asset Identity Context Box -->
                    <div class="alert alert-secondary text-start border bg-light my-3 p-3 rounded-3">
                        <div class="small text-uppercase tracking-wider fw-semibold text-muted mb-1" style="font-size:0.75rem;">Selected Master Asset</div>
                        <div class="fw-bold text-dark fs-5"><?php echo htmlspecialchars($drug['drug_name']); ?></div>
                        <div class="font-monospace text-muted small mt-1">SKU Code: <?php echo htmlspecialchars($drug['drug_code']); ?></div>
                    </div>

                    <!-- Form POST Processing Execution Block -->
                    <form action="delete_drug.php?code=<?php echo urlencode($encrypted_code); ?>" method="POST">
                        <input type="hidden" name="code" value="<?php echo htmlspecialchars($encrypted_code); ?>">
                        <input type="hidden" name="confirm_delete" value="1">

                        <div class="d-flex gap-2 mt-3">
                            <a href="manage_drugs.php" class="btn btn-light w-50 py-2.5 fw-semibold border" style="border-radius: 10px;">
                                Cancel, Keep Asset
                            </a>
                            <button type="submit" class="btn btn-danger w-50 py-2.5 fw-semibold" style="border-radius: 10px; background-color: #ef4444; border:none; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);">
                                <i class="bi bi-trash3-fill me-1"></i> Confirm Delete
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Context Footer Hint -->
                <p class="text-muted small mt-4">
                    <i class="bi bi-lock-fill me-1"></i> System action logged under current admin terminal credentials.
                </p>

            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>