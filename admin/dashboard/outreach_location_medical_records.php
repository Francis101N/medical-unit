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

// Ensure user is logged in AND has the super-admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super-admin') {
    echo "
    <script>
        alert('Access denied: You do not have permission to view this page.');
        window.location.href='index.php';
    </script>
    ";
    exit();
}

/** @var mysqli $conn */
include('db.php');

// Decryption helper function
if (!function_exists('decryptId')) {
    function decryptId($encrypted)
    {
        $key = "outreach-secret-key";
        $decoded = base64_decode(strtr($encrypted, '-_', '+/'));
        $parts = explode('|', $decoded);
        if (count($parts) === 2 && $parts[1] === $key) {
            return $parts[0];
        }
        return false;
    }
}

$location_id_raw = $_GET['location_id'] ?? '';
$location_id = decryptId($location_id_raw);

if (!$location_id) {
    echo "<script>alert('Invalid or missing location identifier.'); window.location.href='outreach_locations_overview.php';</script>";
    exit();
}

// Fetch location details from outreach table
$loc_stmt = $conn->prepare("SELECT location, project_title FROM outreach WHERE id = ?");
$loc_stmt->bind_param("i", $location_id);
$loc_stmt->execute();
$loc_res = $loc_stmt->get_result()->fetch_assoc();
$loc_stmt->close();

if (!$loc_res) {
    echo "<script>alert('Outreach location not found.'); window.location.href='outreach_locations_overview.php';</script>";
    exit();
}

$current_location_name = $loc_res['location'];
$current_project_title = $loc_res['project_title'];

// Fetch patient medical records for this location name
$records_stmt = $conn->prepare("SELECT * FROM patient_medical_records WHERE patient_location = ? ORDER BY id DESC");
$records_stmt->bind_param("s", $current_location_name);
$records_stmt->execute();
$records_result = $records_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Records for <?php echo htmlspecialchars($current_location_name); ?> - Medical Unit</title>

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
    :root {
        --primary-color: #0d6efd;
        --primary-soft: #f0f4ff;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --bg-table-header: #f8fafc;
        --border-color: #e2e8f0;
        --hover-bg: #f8fafc;
        --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.875rem;
        color: var(--text-main);
    }

    .modern-table thead th {
        background-color: var(--bg-table-header);
        color: var(--text-muted);
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 0.95rem 1.25rem;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }

    .modern-table tbody tr {
        transition: background-color 0.15s ease-in-out;
    }

    .modern-table tbody tr:hover {
        background-color: var(--hover-bg);
    }

    .modern-table tbody td {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
        white-space: nowrap;
    }

    .sn-badge {
        font-family: monospace, sans-serif;
        font-size: 0.82rem;
        font-weight: 700;
        color: #94a3b8;
    }

    .passport-avatar-wrap {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 1px var(--border-color);
        display: inline-block;
        vertical-align: middle;
    }

    .passport-avatar-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .passport-avatar-placeholder {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background-color: #f1f5f9;
        color: #94a3b8;
        font-size: 0.65rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px dashed var(--border-color);
    }

    .btn-action-edit {
        background-color: #ffffff;
        color: var(--text-main);
        border: 1px solid var(--border-color);
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-block;
    }

    .btn-action-edit:hover {
        background-color: var(--primary-soft);
        color: var(--primary-color);
        border-color: rgba(13, 110, 253, 0.3);
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
                <div class="page-title mb-4">
                    <div class="row align-items-center g-3">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;"><?php echo htmlspecialchars($current_location_name); ?> Medical Records</h3>
                            <?php if (!empty($current_project_title)): ?>
                                <p class="text-primary fw-bold mb-1" style="font-size: 0.9rem;"><?php echo htmlspecialchars($current_project_title); ?></p>
                            <?php endif; ?>
                            <p class="text-subtitle text-muted mb-0" style="font-size: 0.85rem;">
                                Inspecting medical logs and patient entries registered for this specific location.
                            </p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first text-start text-md-end">
                            <a href="outreach_locations_overview.php" class="btn btn-secondary btn-sm fw-bold">
                                <i class="bi bi-arrow-left"></i> Back to Locations
                            </a>
                        </div>
                    </div>
                </div>

                <section class="section">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="modern-table" id="table1">
                                    <thead>
                                        <tr>
                                            <th>S/N</th>
                                            <th>Patient</th>
                                            <th>Full Name</th>
                                            <th>Gender</th>
                                            <th>Phone</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($records_result && mysqli_num_rows($records_result) > 0) {
                                            $sn = 1;
                                            while ($row = mysqli_fetch_assoc($records_result)) {
                                        ?>
                                                <tr>
                                                    <td><span class="sn-badge">#<?php echo str_pad($sn++, 2, '0', STR_PAD_LEFT); ?></span></td>
                                                    <td>
                                                        <?php if (!empty($row['passport'])): ?>
                                                            <div class="passport-avatar-wrap">
                                                                <img src="./<?php echo htmlspecialchars($row['passport']); ?>" alt="Patient Photo">
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="passport-avatar-placeholder">N/A</div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['patient_name'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($row['gender'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($row['phone_number'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <a href="view_patient_medical_record.php?id=<?php echo $row['id']; ?>" class="btn-action-edit">View Record</a>
                                                    </td>
                                                </tr>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
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
    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendors/simple-datatables/simple-datatables.js"></script>
    <script>
        let table1 = document.querySelector('#table1');
        if (table1) {
            new simpleDatatables.DataTable(table1);
        }
    </script>
    <script src="assets/js/main.js"></script>
</body>

</html>