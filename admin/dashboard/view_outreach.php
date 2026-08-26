<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "
    <script>
        alert('Session Expired! You must log in first.');
        window.location.href='auth-login.php';
    </script>
    ";
    exit();
}

// Include database connection
/** @var mysqli $conn */
include('db.php');

// Decrypt ID function
if (!function_exists('decryptId')) {
    function decryptId($hash)
    {
        $key = "medical-secret-key";
        // Reverse safe URL encoding modifications
        $decoded = base64_decode(strtr($hash, '-_', '+/'));
        $parts = explode('|', $decoded);

        if (count($parts) !== 2 || $parts[1] !== $key) {
            return false;
        }

        return $parts[0];
    }
}

// Get and decrypt ID from query string
$encrypted_id = $_GET['id'] ?? '';
$outreach_id = decryptId($encrypted_id);

if (!$outreach_id || !is_numeric($outreach_id)) {
    $_SESSION['msg'] = "Invalid or tampered outreach record identifier.";
    $_SESSION['msg_type'] = "danger";
    header("Location: outreach.php");
    exit();
}

// Fetch outreach record from database
$stmt = mysqli_prepare($conn, "SELECT * FROM outreach WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $outreach_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    $outreach = mysqli_fetch_assoc($result);
} else {
    $_SESSION['msg'] = "Outreach record not found.";
    $_SESSION['msg_type'] = "danger";
    header("Location: outreach.php");
    exit();
}
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Outreach - Medical Unit</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
</head>

<body>
    <div id="app">
        <?php
        include('./inc/side-nav.php');
        ?>
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="page-heading">
                <div class="page-title">
                    <div class="row align-items-center">
                        <!-- LEFT SIDE -->
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3 class="mb-1">Outreach Details</h3>
                            <p class="text-subtitle text-muted mb-0">
                                Comprehensive operational profile view for the selected outreach project.
                            </p>
                        </div>

                        <!-- RIGHT SIDE -->
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item">
                                        <a href="index.php">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="outreach.php">Outreach</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        View Outreach
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div> <br>

                <!-- View Outreach Section -->
                <section class="section">
                    <div class="row justify-content-center">
                        <div class="col-12 col-lg-8">

                            <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">

                                <!-- CARD HEADER -->
                                <div class="card-header bg-success text-white py-3 d-flex justify-content-between align-items-center px-4">
                                    <h4 class="mb-0 text-white font-semibold">Outreach Profile Overview</h4>
                                    <a href="outreach.php" class="btn btn-light btn-sm fw-medium text-success px-3" style="border-radius: 8px;">
                                        &larr; Back to List
                                    </a>
                                </div>

                                <!-- CARD BODY -->
                                <div class="card-body p-4">
                                    <div class="row g-4">

                                        <!-- Project Title -->
                                        <div class="col-12 border-bottom pb-3">
                                            <span class="text-muted d-block small fw-bold text-uppercase tracking-wider mb-1">Project Title</span>
                                            <h5 class="text-dark fw-bold mb-0"><?php echo htmlspecialchars($outreach['project_title']); ?></h5>
                                        </div>

                                        <!-- Location -->
                                        <div class="col-md-6 border-bottom pb-3">
                                            <span class="text-muted d-block small fw-bold text-uppercase tracking-wider mb-1">Location</span>
                                            <span class="text-secondary fw-semibold fs-6"><?php echo htmlspecialchars($outreach['location']); ?></span>
                                        </div>

                                        <!-- Duration -->
                                        <div class="col-md-6 border-bottom pb-3">
                                            <span class="text-muted d-block small fw-bold text-uppercase tracking-wider mb-1">Duration</span>
                                            <span class="text-dark fw-medium fs-6"><?php echo htmlspecialchars($outreach['duration'] ?? 'N/A'); ?></span>
                                        </div>

                                        <!-- Start Date -->
                                        <div class="col-md-6 border-bottom pb-3">
                                            <span class="text-muted d-block small fw-bold text-uppercase tracking-wider mb-1">Start Date</span>
                                            <span class="text-dark fw-medium fs-6">
                                                <?php echo !empty($outreach['start_date']) ? date("F j, Y", strtotime($outreach['start_date'])) : 'N/A'; ?>
                                            </span>
                                        </div>

                                        <!-- End Date -->
                                        <div class="col-md-6 border-bottom pb-3">
                                            <span class="text-muted d-block small fw-bold text-uppercase tracking-wider mb-1">End Date</span>
                                            <span class="text-dark fw-medium fs-6">
                                                <?php echo !empty($outreach['end_date']) ? date("F j, Y", strtotime($outreach['end_date'])) : 'N/A'; ?>
                                            </span>
                                        </div>

                                        <!-- Date Created -->
                                        <div class="col-12 pb-2">
                                            <span class="text-muted d-block small fw-bold text-uppercase tracking-wider mb-1">Date Registered</span>
                                            <span class="text-muted fw-medium">
                                                <?php echo isset($outreach['date_created']) ? date("F j, Y, g:i a", strtotime($outreach['date_created'])) : 'N/A'; ?>
                                            </span>
                                        </div>

                                    </div>

                                    <!-- ACTION BUTTONS BAR -->
                                    <div class="mt-4 pt-3 border-top d-flex gap-2">
                                        <a href="edit_outreach.php?id=<?php echo urlencode($encrypted_id); ?>" class="btn btn-primary px-4 py-2 shadow-sm">
                                            Edit Outreach
                                        </a>
                                        <a href="delete_outreach.php?id=<?php echo urlencode($encrypted_id); ?>" class="btn btn-outline-danger px-4 py-2 shadow-sm" onclick="return confirm('Are you sure you want to delete this outreach record?');">
                                            Delete Outreach
                                        </a>
                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>
                </section>
                <!-- View Outreach Section End -->
            </div>

            <?php
            include('./inc/footer.php');
            ?>
        </div>
    </div>

    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>