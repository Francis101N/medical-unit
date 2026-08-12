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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branches Records - Medical Unit</title>

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
    /* ==========================================
           Modern SaaS Dashboard Table Styles
           ========================================== */
    :root {
        --primary-color: #0d6efd;
        --primary-soft: #f0f4ff;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --bg-table-header: #f8fafc;
        --border-color: #e2e8f0;
        --hover-bg: #f8fafc;
        --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.06), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }

    /* Branch Section Container */
    .branch-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        margin-bottom: 2.5rem;
        overflow: hidden;
        transition: box-shadow 0.25s ease-in-out;
    }

    .branch-card:hover {
        box-shadow: var(--shadow-md);
    }

    /* Branch Header */
    .branch-card-header {
        background-color: #ffffff;
        border-bottom: 1px solid var(--border-color);
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .branch-title-wrap {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .branch-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background-color: var(--primary-soft);
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .branch-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-main);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .branch-subtitle {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 0.1rem;
    }

    /* Modern Counter Pill */
    .branch-counter {
        font-size: 10px;
        font-weight: 600;
        color: var(--primary-color);
        background-color: var(--primary-soft);
        border: 1px solid rgba(13, 110, 253, 0.15);
        padding: 0.35rem 0.85rem;
    }

    /* Table Framework */
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

    .modern-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Sequential S/N Index Pill */
    .sn-badge {
        font-family: monospace, sans-serif;
        font-size: 0.82rem;
        font-weight: 700;
        color: #94a3b8;
    }

    /* Passport Avatar Frame */
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

    /* Department Badge */
    .dept-badge {
        background-color: #f1f5f9;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }

    /* Text Truncation Container */
    .truncate-field {
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Custom Soft Status Badges */
    .badge-status {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
    }

    .badge-status-open {
        background-color: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .badge-status-treatment {
        background-color: #fffbe3;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .badge-status-closed {
        background-color: #f1f5f9;
        color: #64748b;
        border: 1px solid #cbd5e1;
    }

    /* Action Control Buttons */
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

    .btn-action-delete {
        background-color: transparent;
        color: #ef4444;
        border: none;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-block;
    }

    .btn-action-delete:hover {
        background-color: #fef2f2;
        color: #dc2626;
    }

    .empty-state-wrapper {
        text-align: center;
        padding: 3.5rem 1rem;
        color: var(--text-muted);
    }
</style>

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
                    <div class="row">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3>Branch Records</h3>
                            <p class="text-subtitle text-muted">
                                View, manage, and monitor all registered branch records and information.
                            </p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Branch Records</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                <section class="section">

                    <div class="card">

                        <div class="container-fluid px-4 mt-5">

                            <?php
                            /** @var mysqli $conn */
                            include('./db.php');

                            // Declare encryption helper function safely if not already declared globally
                            if (!function_exists('encryptId')) {
                                function encryptId($id)
                                {
                                    $key = "medical-secret-key";
                                    return rtrim(strtr(base64_encode($id . '|' . $key), '+/', '-_'), '=');
                                }
                            }

                            // Fetch operational branches from settings definition layout
                            $branch_query = "SELECT id, branch_name FROM branches ORDER BY branch_name ASC";
                            $branch_result = mysqli_query($conn, $branch_query);

                            if ($branch_result && mysqli_num_rows($branch_result) > 0) {
                                while ($branch_row = mysqli_fetch_assoc($branch_result)) {
                                    $current_branch = $branch_row['branch_name'];

                                    // Prepared statement engine pulling records belonging strictly to current row branch
                                    $log_query = "SELECT smr.*, s.passport 
                              FROM staff_medical_records smr 
                              LEFT JOIN staffs s ON smr.staff_name = s.fullname 
                              WHERE smr.staff_branch = ? 
                              ORDER BY smr.id DESC";

                                    $stmt = $conn->prepare($log_query);
                                    $stmt->bind_param("s", $current_branch);
                                    $stmt->execute();
                                    $records_result = $stmt->get_result();
                            ?>

                                    <!-- Individual Corporate Branch Panel -->
                                    <div class="branch-card">
                                        <div class="branch-card-header">
                                            <div class="branch-title-wrap">
                                                <div class="branch-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                                        <path d="M3 0a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h3v-3.5a.5.5 0 0 1 .5-.5h3.5a.5.5 0 0 1 .5.5V16h3a1 1 0 0 0 1-1V1c0-.552-.448-1-1-1zm1 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm0 4a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm0 4a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm5-8a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm0 4a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h5 class="branch-title"><?php echo htmlspecialchars($current_branch); ?> Branch</h5>
                                                    <div class="branch-subtitle">Medical Tracking Logs</div>
                                                </div>
                                            </div>
                                            <span class="branch-counter"><?php echo $records_result->num_rows; ?> Records</span>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="modern-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 70px;">S/N</th>
                                                        <th>Staff Profile Name</th>
                                                        <th class="text-center" style="width: 90px;">Avatar</th>
                                                        <th>Department</th>
                                                        <th>Intake Frame</th>
                                                        <th>Release Time</th>
                                                        <th>Primary Diagnosis</th>
                                                        <th>Prescription & Treatment</th>
                                                        <th style="width: 130px;">Status</th>
                                                        <th class="text-end" style="width: 160px;">Control Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if ($records_result->num_rows > 0) {
                                                        $sn = 1;
                                                        while ($row = $records_result->fetch_assoc()) {
                                                            $id = $row['id'];
                                                            $staff_name = $row['staff_name'];
                                                            $passport = trim($row['passport'] ?? '');
                                                            $department = $row['department'];
                                                            $intake_time = $row['intake_time'];
                                                            $release_time = $row['release_time'];
                                                            $diagnosis = $row['diagnosis'];
                                                            $treatment_given = $row['treatment_given'];
                                                            $record_status = strtolower($row['record_status']);

                                                            // Map classes cleanly based on database ENUM values
                                                            if ($record_status === "open") {
                                                                $status_class = "badge-status-open";
                                                            } elseif ($record_status === "under_treatment") {
                                                                $status_class = "badge-status-treatment";
                                                            } else {
                                                                $status_class = "badge-status-closed";
                                                            }

                                                            // Dynamic asset path structural depth check logic loop
                                                            $passport_src = '';
                                                            if (!empty($passport)) {
                                                                $paths_to_test = [
                                                                    "uploads/" . $passport,
                                                                    "../uploads/" . $passport,
                                                                    "../../uploads/" . $passport
                                                                ];
                                                                foreach ($paths_to_test as $test_path) {
                                                                    if (file_exists($test_path) && !is_dir($test_path)) {
                                                                        $passport_src = $test_path;
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                    ?>
                                                            <tr>
                                                                <!-- Sequential Numeric badge Indexing -->
                                                                <td>
                                                                    <span class="sn-badge">#<?php echo sprintf('%02d', $sn++); ?></span>
                                                                </td>

                                                                <!-- Staff Identity -->
                                                                <td>
                                                                    <strong style="color: var(--text-main);"><?php echo htmlspecialchars($staff_name); ?></strong>
                                                                </td>

                                                                <!-- Passport Presentation Layer -->
                                                                <td class="text-center">
                                                                    <?php if (!empty($passport_src)) { ?>
                                                                        <div class="passport-avatar-wrap">
                                                                            <img src="<?php echo htmlspecialchars($passport_src); ?>" alt="Passport Avatar" loading="lazy">
                                                                        </div>
                                                                    <?php } else { ?>
                                                                        <div class="passport-avatar-placeholder">N/A</div>
                                                                    <?php } ?>
                                                                </td>

                                                                <!-- Department Tag Badge -->
                                                                <td>
                                                                    <span class="dept-badge"><?php echo htmlspecialchars($department); ?></span>
                                                                </td>

                                                                <!-- Logging Frame Entry -->
                                                                <td>
                                                                    <small class="text-muted"><?php echo htmlspecialchars($intake_time); ?></small>
                                                                </td>

                                                                <!-- Release Time -->
                                                                <td>
                                                                    <small class="text-muted"><?php echo htmlspecialchars($release_time); ?></small>
                                                                </td>

                                                                <!-- Scaled Width Truncation Framework (Diagnosis) -->
                                                                <td>
                                                                    <div class="truncate-field" title="<?php echo htmlspecialchars($diagnosis); ?>">
                                                                        <?php echo htmlspecialchars($diagnosis); ?>
                                                                    </div>
                                                                </td>

                                                                <!-- Scaled Width Truncation Framework (Treatment) -->
                                                                <td>
                                                                    <div class="truncate-field text-secondary" title="<?php echo htmlspecialchars($treatment_given); ?>">
                                                                        <?php echo htmlspecialchars($treatment_given); ?>
                                                                    </div>
                                                                </td>

                                                                <!-- Soft System Status Engine Mapping -->
                                                                <td>
                                                                    <span class="badge-status <?php echo $status_class; ?>">
                                                                        <?php echo ucwords(str_replace('_', ' ', htmlspecialchars($record_status))); ?>
                                                                    </span>
                                                                </td>

                                                                <!-- Controls Interfacing Button Layout Matrix -->
                                                                <td class="text-end">
                                                                    <div class="d-inline-flex gap-2">
                                                                        <a href="edit_log.php?id=<?php echo urlencode(encryptId($id)); ?>" class="btn-action-edit">
                                                                            Edit
                                                                        </a>
                                                                        <a href="delete_log.php?id=<?php echo urlencode(encryptId($id)); ?>" class="btn-action-delete" onclick="return confirm('Confirm permanent deletion of this clinical log query record?');">
                                                                            Delete
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php
                                                        }
                                                    } else {
                                                        ?>
                                                        <!-- Fallback View: Branch Data Record Layer Empty -->
                                                        <tr>
                                                            <td colspan="9" class="p-0">
                                                                <div class="empty-state-wrapper">
                                                                    <!-- <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 12H4M12 5v14" />
                                                                    </svg> -->
                                                                    <div class="small fw-medium">No medical intake tracking entries discovered within this branch context profile.</div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php
                                    $stmt->close();
                                }
                            } else {
                                ?>
                                <div class="card border-0 shadow-sm p-5 text-center text-muted rounded-3">
                                    <div class="fw-bold mb-1">System Configurations Empty</div>
                                    <div class="small">No administrative organizational branches are currently active within database architecture layouts.</div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>

                    </div>

                </section>
            </div>

            <?php
            include('./inc/footer.php');
            ?>
        </div>
    </div>
    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script src="assets/vendors/simple-datatables/simple-datatables.js"></script>
    <script>
        // Simple Datatable
        let table1 = document.querySelector('#table1');
        let dataTable = new simpleDatatables.DataTable(table1);
    </script>

    <script src="assets/js/main.js"></script>
</body>

</html>