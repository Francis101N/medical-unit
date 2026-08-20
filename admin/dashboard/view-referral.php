<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

/** @var mysqli $conn */
include('db.php');

// 1. Decode and Validate ID
$encoded_id = $_GET['id'] ?? '';
$decoded_id = base64_decode($encoded_id, true);

if ($decoded_id === false || !is_numeric($decoded_id)) {
    header("Location: referrals.php?error=" . urlencode("Invalid referral record identifier."));
    exit();
}

$referral_id = intval($decoded_id);

// 2. Authentication & Role Clearance Guard
$user_role = strtolower($_SESSION['role'] ?? '');
if (empty($user_role)) {
    header("Location: login.php");
    exit();
}

$is_super_admin = ($user_role === 'super-admin');

// Pull user branch for role scoping if not super-admin
$user_branch_val = $_SESSION['branch'] ?? '';
$logged_branch_name = '';
$logged_branch_id = 0;

if (!$is_super_admin) {
    if (is_numeric($user_branch_val)) {
        $logged_branch_id = intval($user_branch_val);
        $b_lookup = $conn->prepare("SELECT branch_name FROM branches WHERE id = ? LIMIT 1");
        if ($b_lookup) {
            $b_lookup->bind_param("i", $logged_branch_id);
            $b_lookup->execute();
            $b_res = $b_lookup->get_result()->fetch_assoc();
            if ($b_res) {
                $logged_branch_name = trim($b_res['branch_name']);
            }
            $b_lookup->close();
        }
    } else {
        $logged_branch_name = trim($user_branch_val);
        $b_lookup = $conn->prepare("SELECT id FROM branches WHERE branch_name = ? LIMIT 1");
        if ($b_lookup) {
            $b_lookup->bind_param("s", $logged_branch_name);
            $b_lookup->execute();
            $b_res = $b_lookup->get_result()->fetch_assoc();
            if ($b_res) {
                $logged_branch_id = intval($b_res['id']);
            }
            $b_lookup->close();
        }
    }
}

// 3. Fetch Referral Record using relationship joins
$base_query = "SELECT r.*, s.staff_id, s.fullname AS staff_fullname, s.email, s.gender, s.department, s.company, s.passport AS staff_passport, b.branch_name 
               FROM referral_logs r 
               LEFT JOIN staffs s ON r.staff_name = s.fullname 
               LEFT JOIN branches b ON s.branch_id = b.id";

if ($is_super_admin) {
    $query = $base_query . " WHERE r.id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $referral_id);
} else {
    $query = $base_query . " WHERE r.id = ? AND (b.branch_name = ? OR s.branch_id = ?) LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isi", $referral_id, $logged_branch_name, $logged_branch_id);
}

$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $stmt->close();
    header("Location: referrals.php?error=" . urlencode("Referral record not found or access denied."));
    exit();
}

$row = $result->fetch_assoc();
$stmt->close();

// Assign data variables safely
$staff_name   = $row['staff_fullname'] ?? $row['staff_name'] ?? 'N/A';
$staff_id     = $row['staff_id'] ?? 'N/A';
$email        = $row['email'] ?? 'N/A';
$gender       = $row['gender'] ?? 'N/A';
$department   = $row['department'] ?? 'N/A';
$company      = $row['company'] ?? 'N/A';
$branch_name  = $row['branch_name'] ?? 'N/A';
$serial_id    = $row['serial_id'] ?? 'N/A';
$ref_code     = $row['ref_code'] ?? 'N/A';
$hospital     = $row['hospital_name'] ?? $row['hospital'] ?? 'N/A';
$clinical_notes = $row['clinical_notes'] ?? $row['notes'] ?? 'No clinical notes provided.';
$created_at   = $row['created_at'] ?? 'N/A';

$passport_val = trim($row['staff_passport'] ?? '');
$passport     = !empty($passport_val) ? 'uploads/' . $passport_val : 'assets/images/faces/1.jpg';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Referral Details - Medical Unit</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
    <style>
        .staff-avatar-lg {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e2e8f0;
        }
    </style>
</head>

<body>
    <div id="app">
        <?php include('./inc/side-nav.php'); ?>
        <div id="main">
            <div class="page-heading">
                <div class="container-fluid py-4">

                    <!-- Page Header / Breadcrumb -->
                    <div class="row mb-4 align-items-center">
                        <div class="col-12 col-md-6">
                            <h3 class="fw-bold text-dark mb-1">Referral Details</h3>
                            <p class="text-muted small mb-0">Complete overview of medical clearance and referral assignment.</p>
                        </div>
                        <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
                            <a href="referrals.php" class="btn btn-secondary px-4 py-2 fw-semibold shadow-sm" style="border-radius: 10px;">
                                <i class="bi bi-arrow-left me-2"></i> Back to Referrals
                            </a>
                        </div>
                    </div>

                    <!-- Main Detail Card -->
                    <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold text-dark mb-0">Referral Reference: <span class="text-primary font-monospace"><?php echo htmlspecialchars($ref_code); ?></span></h5>
                            <span class="badge bg-light text-secondary border px-3 py-2">Logged On: <?php echo htmlspecialchars($created_at); ?></span>
                        </div>
                        <div class="card-body p-4">

                            <!-- Staff Profile Summary Section -->
                            <div class="row align-items-center mb-4 pb-4 border-bottom">
                                <div class="col-auto">
                                    <img src="<?php echo htmlspecialchars($passport); ?>" alt="Staff Passport" class="staff-avatar-lg shadow-sm">
                                </div>
                                <div class="col">
                                    <h4 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($staff_name); ?></h4>
                                    <p class="text-muted mb-1">Staff ID: <span class="font-monospace fw-semibold text-primary"><?php echo htmlspecialchars($staff_id); ?></span></p>
                                    <p class="text-muted mb-2">Email: <span class="fw-semibold text-dark"><?php echo htmlspecialchars($email); ?></span></p>
                                    <div>
                                        <span class="badge bg-light-primary text-primary border me-1">Company: <?php echo htmlspecialchars($company); ?></span>
                                        <span class="badge bg-light-secondary text-secondary border me-1">Department: <?php echo htmlspecialchars($department); ?></span>
                                        <span class="badge bg-light-info text-info border">Branch: <?php echo htmlspecialchars($branch_name); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Detailed Metadata Grid -->
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 border-0">
                                        <span class="text-muted small d-block mb-1 text-uppercase fw-bold" style="font-size: 0.75rem;">Gender</span>
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($gender); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 border-0">
                                        <span class="text-muted small d-block mb-1 text-uppercase fw-bold" style="font-size: 0.75rem;">Serial ID</span>
                                        <span class="font-monospace fw-semibold text-dark"><?php echo htmlspecialchars($serial_id); ?></span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            <?php include('./inc/footer.php'); ?>
        </div>
    </div>
    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>