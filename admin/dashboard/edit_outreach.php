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

// Fetch existing outreach record from database
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
    <title>Edit Outreach - Medical Unit</title>

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
                            <h3 class="mb-1">Edit Outreach</h3>
                            <p class="text-subtitle text-muted mb-0">
                                Update the operational details, title, location, schedule duration, or timeline dates for this outreach.
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
                                        Edit Outreach
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div> <br>

                <!-- Edit Outreach Section -->
                <section class="section">
                    <div class="row justify-content-center" id="table-hover-row">
                        <div class="col-12 col-lg-8">

                            <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">

                                <!-- CARD HEADER -->
                                <div class="card-header bg-success text-white py-3 d-flex justify-content-between align-items-center px-4">
                                    <h4 class="mb-0 text-white font-semibold">Update Outreach Details</h4>
                                    <a href="outreach.php" class="btn btn-light btn-sm fw-medium text-success px-3" style="border-radius: 8px;">
                                        &larr; Back to List
                                    </a>
                                </div> <br>

                                <?php
                                // Retrieve message and message type from session if available
                                $msg = $_SESSION['msg'] ?? '';
                                $msg_type = $_SESSION['msg_type'] ?? 'success';

                                // Clear session messages so they disappear on page reload
                                if (isset($_SESSION['msg'])) {
                                    unset($_SESSION['msg']);
                                    unset($_SESSION['msg_type']);
                                }

                                // Only show alert if message exists
                                if (!empty($msg)) {
                                ?>
                                    <div class="alert m-3 alert-<?php echo $msg_type; ?> alert-dismissible show fade" role="alert">
                                        <?php echo $msg; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php
                                }
                                ?>

                                <!-- CARD BODY -->
                                <div class="card-content">
                                    <div class="card-body p-4">

                                        <form action="proc_edit_outreach.php" method="POST">

                                            <!-- Hidden ID Field -->
                                            <input type="hidden" name="outreach_id" value="<?php echo htmlspecialchars($outreach_id); ?>">

                                            <!-- Project Title -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-bold">Project Title</label>
                                                <input type="text" name="project_title"
                                                    class="form-control form-control-lg shadow-sm"
                                                    value="<?php echo htmlspecialchars($outreach['project_title']); ?>"
                                                    placeholder="Enter outreach project title" required>
                                            </div>

                                            <!-- Location -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-bold">Location</label>
                                                <input type="text" name="location"
                                                    class="form-control form-control-lg shadow-sm"
                                                    value="<?php echo htmlspecialchars($outreach['location']); ?>"
                                                    placeholder="Enter outreach location" required>
                                            </div>

                                            <!-- Duration -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-bold">Duration</label>
                                                <input type="text" name="duration"
                                                    class="form-control form-control-lg shadow-sm"
                                                    value="<?php echo htmlspecialchars($outreach['duration'] ?? ''); ?>"
                                                    placeholder="e.g., 3 Days, 1 Week, 2 Months">
                                            </div>

                                            <!-- Start Date & End Date Row -->
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-4">
                                                        <label class="form-label fw-bold">Start Date</label>
                                                        <input type="date" name="start_date"
                                                            class="form-control form-control-lg shadow-sm"
                                                            value="<?php echo htmlspecialchars($outreach['start_date'] ?? ''); ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-4">
                                                        <label class="form-label fw-bold">End Date</label>
                                                        <input type="date" name="end_date"
                                                            class="form-control form-control-lg shadow-sm"
                                                            value="<?php echo htmlspecialchars($outreach['end_date'] ?? ''); ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- BUTTONS BAR -->
                                            <div class="mt-4 d-flex gap-2">
                                                <button type="submit" name="submit"
                                                    class="btn btn-success shadow-sm px-4 py-2">
                                                    Update Changes
                                                </button>
                                                <a href="outreach.php" class="btn btn-light border px-4 py-2 shadow-sm">
                                                    Cancel
                                                </a>
                                            </div>

                                        </form>

                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </section>
                <!-- Edit Outreach Section End -->
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