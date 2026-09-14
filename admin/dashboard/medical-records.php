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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records - Medical Unit</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.css">

    <link rel="stylesheet" href="assets/vendors/simple-datatables/style.css">

    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="icon" type="image/x-icon" href="./assets/images/favicon.png">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
</head>
<style>
    /* Custom Table Workspace Theme & Filter Action Bar Layout */
    :root {
        --primary-color: #435ebe;
        --primary-gradient: linear-gradient(135deg, #435ebe, #2c4294);
        --border-color: #e2e8f0;
        --text-main: #334155;
        --text-muted: #64748b;
        --bg-light-pane: #f8fafc;
    }

    .custom-table-container {
        background: #ffffff;
        border-radius: 0 0 16px 16px;
        overflow-x: auto;
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.875rem;
        color: var(--text-main);
    }

    .modern-table thead th {
        background-color: var(--bg-light-pane);
        color: #475569;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.725rem;
        letter-spacing: 0.05em;
        padding: 14px 16px;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .modern-table tbody tr {
        transition: all 0.2s ease;
    }

    .modern-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .modern-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        white-space: nowrap;
    }

    /* Text Truncation Rule */
    .text-truncate-modern {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 160px;
        font-size: 0.85rem;
    }

    /* Row Counter Badge */
    .sn-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 6px;
        background: #f1f5f9;
        color: #475569;
        font-weight: 600;
        border: 1px solid #e2e8f0;
        font-size: 0.78rem;
    }

    /* Vital Signs Pills */
    .vital-chip {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.78rem;
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
    }

    .vital-chip.temp {
        background: #fff7ed;
        color: #c2410c;
        border-color: #ffedd5;
    }

    .vital-chip.bp {
        background: #f0fdf4;
        color: #15803d;
        border-color: #dcfce7;
    }

    .vital-chip.pulse {
        background: #fef2f2;
        color: #b91c1c;
        border-color: #fee2e2;
    }

    /* Status Badges */
    .badge-soft {
        padding: 6px 12px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
    }

    .badge-soft-success {
        background: #dcfce7;
        color: #15803d;
    }

    .badge-soft-warning {
        background: #fef3c7;
        color: #b45309;
    }

    .badge-soft-secondary {
        background: #f1f5f9;
        color: #475569;
    }

    /* Action Buttons */
    .action-btns {
        display: flex;
        gap: 6px;
    }

    .btn-icon-sm {
        padding: 5px 10px;
        font-size: 0.78rem;
        border-radius: 8px;
        font-weight: 500;
        transition: transform 0.15s ease;
    }

    .btn-icon-sm:hover {
        transform: translateY(-1px);
    }

    /* Filter Action Component Input Form Fields Styling */
    .table-filter-bar input,
    .table-filter-bar select {
        border: 1px solid var(--border-color);
        border-radius: 10px;
        color: var(--text-main);
        font-size: 0.875rem;
        height: 42px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .table-filter-bar input:focus,
    .table-filter-bar select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(67, 94, 190, 0.15);
        outline: none;
    }

    @keyframes pulse {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
        }

        70% {
            transform: scale(1);
            box-shadow: 0 0 0 6px rgba(25, 135, 84, 0);
        }

        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(25, 135, 84, 0);
        }
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
                            <h3>Medical Records</h3>
                            <p class="text-subtitle text-muted">
                                View, manage, and monitor all registered medical records and information.
                            </p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Medical Records</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <section class="section">

                    <?php
                    // 1. Initialize Default Fallback States
                    $msg = '';
                    $msg_type = 'success';

                    if (isset($_GET['msg']) && !empty($_GET['msg'])) {
                        $msg = htmlspecialchars($_GET['msg']);

                        if (isset($_GET['status'])) {
                            $msg_type = ($_GET['status'] === 'error') ? 'danger' : 'success';
                        } elseif (isset($_GET['error'])) {
                            $msg_type = 'danger';
                        }
                    } elseif (isset($_GET['error']) && !empty($_GET['error'])) {
                        $msg_type = 'danger';

                        switch ($_GET['error']) {
                            case 'invalid_id':
                                $msg = '<strong>System Conflict:</strong> The requested medical log record ID is invalid or corrupted.';
                                break;
                            case 'update_failed':
                                $msg = '<strong>Database Error:</strong> Unable to commit updates to the log table schema.';
                                break;
                            case 'stmt_compilation_failed':
                                $msg = '<strong>Engine Error:</strong> SQL prepared statement generation failed mapping columns.';
                                break;
                            default:
                                $msg = '<strong>Processing Exception:</strong> ' . htmlspecialchars($_GET['error']);
                                break;
                        }
                    }

                    if (!empty($msg)) {
                    ?>
                        <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show mb-4 shadow-sm border-0" role="alert">
                            <div class="d-flex align-items-center">
                                <div class="alert-icon-wrapper me-3">
                                    <?php if ($msg_type === 'danger'): ?>
                                        <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
                                    <?php else: ?>
                                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="alert-message-text text-dark">
                                    <?php echo $msg; ?>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php } ?>

                    <!-- High-Fidelity Client-Side Interactive Omni-Filter Action Bar Component -->
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; background: linear-gradient(145deg, #ffffff, #fcfdff);">
                        <div class="card-body p-4">
                            <div class="row g-3 align-items-center justify-content-between table-filter-bar">

                                <div class="col-12 col-md-5 col-lg-4">
                                    <div class="input-group dashboard-search-group shadow-3xs" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
                                        <span class="input-group-text bg-white border-0 pe-2 ps-3 text-muted">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color: #94a3b8;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </span>
                                        <input type="text" id="omniMedicalSearch" class="form-control border-0 bg-white py-2 text-dark font-medium"
                                            placeholder="Search across all fields..." style="font-size: 0.9rem; box-shadow: none;">
                                    </div>
                                </div>

                                <div class="col-12 col-md-7 col-lg-5">
                                    <div class="row g-2">
                                        <div class="col-6 col-sm-4">
                                            <select id="medicalStatusFilter" class="form-select form-select-sm shadow-3xs">
                                                <option value="">All Statuses</option>
                                                <option value="open">Open Case</option>
                                                <option value="under_treatment">Under Treatment</option>
                                                <option value="closed">Closed Case</option>
                                            </select>
                                        </div>
                                        <div class="col-6 col-sm-4">
                                            <select id="medicalBranchFilter" class="form-select form-select-sm shadow-3xs">
                                                <option value="">All Branches</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-sm-4">
                                            <input type="date" id="medicalDateFilter" class="form-control form-control-sm" title="Filter by Logged Creation Date">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-3 text-lg-end">
                                    <div class="d-inline-flex align-items-center bg-light border px-3 py-2" style="border-radius: 12px; background-color: #f8fafc !important;">
                                        <span class="d-inline-block bg-success rounded-circle me-2" style="width: 8px; height: 8px; animation: pulse 2s infinite;"></span>
                                        <span class="text-secondary font-semibold" style="font-size: 0.85rem;">
                                            Matched: <strong id="visibleLogsMetric" class="text-dark fw-bold" style="font-size: 0.95rem;">0</strong> / <span id="totalLogsMetric">0</span>
                                        </span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                        <div class="card-header d-flex justify-content-between align-items-center px-4 py-3 bg-white border-bottom">
                            <h4 class="card-title mb-0" style="color: #1e293b; font-weight: 600;">Medical Intake Logs Workspace</h4>
                            <a href="add_medical_record.php" class="btn btn-success btn-sm px-3 py-2 fw-medium" style="border-radius: 8px;">
                                + ADD MEDICAL RECORD
                            </a>
                        </div>

                        <div class="table-responsive">
                            <div class="custom-table-container">
                                <table class="modern-table align-middle mb-0" id="medicalRecordsWorkspaceTable" style="width: 100%; min-width: 2400px;">
                                    <thead>
                                        <tr>
                                            <th class="small-field">S/N</th>
                                            <th class="medium-field">STAFF NAME</th>
                                            <th class="medium-field text-center">STAFF PASSPORT</th>
                                            <th class="medium-field">STAFF BRANCH</th>
                                            <th class="medium-field">COMPANY</th>
                                            <th class="medium-field">DEPARTMENT</th>
                                            <th class="medium-field">INTAKE TIME</th>
                                            <th class="medium-field">RELEASE TIME</th>

                                            <th class="wide-field">DIAGNOSIS</th>
                                            <th class="wide-field">SYMPTOMS</th>
                                            <th class="wide-field">MEDICAL NOTES</th>
                                            <th class="wide-field">TREATMENT GIVEN</th>
                                            <th class="wide-field">DRUGS GIVEN</th>
                                            <th class="medium-field">DOSAGE INSTRUCTIONS</th>

                                            <th class="medium-field">ATTENDED BY</th>
                                            <th class="medium-field">CONDITION ON ADMISSION</th>
                                            <th class="medium-field">CONDITION ON RELEASE</th>

                                            <th class="small-field">BLOOD PRESSURE</th>
                                            <th class="small-field">TEMPERATURE</th>
                                            <th class="small-field">PULSE RATE</th>

                                            <th class="small-field">FOLLOW UP REQUIRED</th>
                                            <th class="medium-field">FOLLOW UP DATE</th>
                                            <th class="medium-field">REFERRED</th>
                                            <th class="medium-field">PDF</th>
                                            <th class="small-field">RECORD STATUS</th>

                                            <th class="medium-field">CREATED AT</th>
                                            <th class="medium-field">UPDATED AT</th>

                                            <th class="medium-field text-end">ACTIONS</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        /** @var mysqli $conn */
                                        include('./db.php');

                                        if (session_status() === PHP_SESSION_NONE) {
                                            session_start();
                                        }

                                        if (!function_exists('encryptId')) {
                                            function encryptId($id)
                                            {
                                                $key = "medical-secret-key";
                                                return rtrim(strtr(base64_encode($id . '|' . $key), '+/', '-_'), '=');
                                            }
                                        }

                                        // Capture and sanitize branch and status parameters from URL query string
                                        $branch_filter = isset($_GET['branch']) ? trim($_GET['branch']) : '';
                                        $status_filter = isset($_GET['status']) ? strtolower(trim($_GET['status'])) : '';
                                        $allowed_statuses = ['open', 'under_treatment', 'closed'];
                                        if (!empty($status_filter) && !in_array($status_filter, $allowed_statuses)) {
                                            $status_filter = '';
                                        }

                                        $user_role = strtolower($_SESSION['role'] ?? '');
                                        $user_branch = $_SESSION['branch'] ?? '';

                                        // Build dynamic SQL query incorporating optional branch and status filters with database parameter binding
                                        if ($user_role === 'super-admin') {
                                            if (!empty($branch_filter) && !empty($status_filter)) {
                                                $query = "SELECT smr.*, s.passport 
                                                          FROM staff_medical_records smr 
                                                          LEFT JOIN staffs s ON smr.staff_name = s.fullname 
                                                          WHERE TRIM(smr.staff_branch) = ? 
                                                          AND LOWER(TRIM(smr.record_status)) = ?
                                                          ORDER BY smr.id DESC";
                                                $stmt = $conn->prepare($query);
                                                $stmt->bind_param("ss", $branch_filter, $status_filter);
                                            } elseif (!empty($branch_filter)) {
                                                $query = "SELECT smr.*, s.passport 
                                                          FROM staff_medical_records smr 
                                                          LEFT JOIN staffs s ON smr.staff_name = s.fullname 
                                                          WHERE TRIM(smr.staff_branch) = ?
                                                          ORDER BY smr.id DESC";
                                                $stmt = $conn->prepare($query);
                                                $stmt->bind_param("s", $branch_filter);
                                            } elseif (!empty($status_filter)) {
                                                $query = "SELECT smr.*, s.passport 
                                                          FROM staff_medical_records smr 
                                                          LEFT JOIN staffs s ON smr.staff_name = s.fullname 
                                                          WHERE LOWER(TRIM(smr.record_status)) = ?
                                                          ORDER BY smr.id DESC";
                                                $stmt = $conn->prepare($query);
                                                $stmt->bind_param("s", $status_filter);
                                            } else {
                                                $query = "SELECT smr.*, s.passport 
                                                          FROM staff_medical_records smr 
                                                          LEFT JOIN staffs s ON smr.staff_name = s.fullname 
                                                          ORDER BY smr.id DESC";
                                                $stmt = $conn->prepare($query);
                                            }
                                        } else {
                                            // Non-super-admins are locked to their assigned user branch
                                            $active_branch = !empty($branch_filter) ? $branch_filter : $user_branch;
                                            if (!empty($status_filter)) {
                                                $query = "SELECT smr.*, s.passport 
                                                          FROM staff_medical_records smr 
                                                          LEFT JOIN staffs s ON smr.staff_name = s.fullname 
                                                          WHERE LOWER(TRIM(smr.staff_branch)) = LOWER(TRIM(?)) 
                                                          AND LOWER(TRIM(smr.record_status)) = ?
                                                          ORDER BY smr.id DESC";
                                                $stmt = $conn->prepare($query);
                                                $stmt->bind_param("ss", $active_branch, $status_filter);
                                            } else {
                                                $query = "SELECT smr.*, s.passport 
                                                          FROM staff_medical_records smr 
                                                          LEFT JOIN staffs s ON smr.staff_name = s.fullname 
                                                          WHERE LOWER(TRIM(smr.staff_branch)) = LOWER(TRIM(?))
                                                          ORDER BY smr.id DESC";
                                                $stmt = $conn->prepare($query);
                                                $stmt->bind_param("s", $active_branch);
                                            }
                                        }

                                        $stmt->execute();
                                        $select_logs = $stmt->get_result();

                                        if ($select_logs && $select_logs->num_rows > 0) {
                                            $sn = 1;

                                            while ($row = $select_logs->fetch_assoc()) {
                                                $id = $row['id'];
                                                $staff_name = $row['staff_name'];
                                                $passport = trim($row['passport'] ?? '');
                                                $staff_branch = $row['staff_branch'];
                                                $company = $row['company'] ?? '';
                                                $department = $row['department'];
                                                $intake_time = $row['intake_time'];
                                                $release_time = $row['release_time'];

                                                $diagnosis = $row['diagnosis'];
                                                $symptoms = $row['symptoms'];
                                                $medical_notes = $row['medical_notes'];
                                                $treatment_given = $row['treatment_given'];
                                                $drugs_given = $row['drugs_given'];
                                                $dosage_instructions = $row['dosage_instructions'];

                                                $attended_by = $row['attended_by'];

                                                $condition_on_admission = $row['condition_on_admission'];
                                                $condition_on_release = $row['condition_on_release'];

                                                $blood_pressure = $row['blood_pressure'];
                                                $temperature = $row['temperature'];
                                                $pulse_rate = $row['pulse_rate'];

                                                $follow_up_required = $row['follow_up_required'];
                                                $follow_up_date = $row['follow_up_date'];
                                                $referred = $row['referred'] ?? '';
                                                $pdf_file = $row['pdf'] ?? '';
                                                $record_status = strtolower($row['record_status']);

                                                $created_at = $row['created_at'];
                                                $updated_at = $row['updated_at'];

                                                if ($record_status === "open") {
                                                    $status_class = "badge-soft-success";
                                                } elseif ($record_status === "under_treatment") {
                                                    $status_class = "badge-soft-warning";
                                                } else {
                                                    $status_class = "badge-soft-secondary";
                                                }

                                                $passport_src = '';
                                                if (!empty($passport)) {
                                                    $paths_to_test = [
                                                        "uploads/" . $passport,
                                                        "../uploads/" . $passport,
                                                        "../../uploads/" . $passport,
                                                        "admin/uploads/" . $passport,
                                                        "../admin/uploads/" . $passport
                                                    ];

                                                    foreach ($paths_to_test as $test_path) {
                                                        if (file_exists($test_path) && !is_dir($test_path)) {
                                                            $passport_src = $test_path;
                                                            break;
                                                        }
                                                    }
                                                }

                                                $search_payload = strtolower(implode(' ', array_filter([
                                                    $sn,
                                                    $staff_name,
                                                    $staff_branch,
                                                    $company,
                                                    $department,
                                                    $intake_time,
                                                    $release_time,
                                                    $diagnosis,
                                                    $symptoms,
                                                    $medical_notes,
                                                    $treatment_given,
                                                    $drugs_given,
                                                    $dosage_instructions,
                                                    $attended_by,
                                                    $condition_on_admission,
                                                    $condition_on_release,
                                                    $blood_pressure,
                                                    $temperature,
                                                    $pulse_rate,
                                                    $follow_up_required,
                                                    $follow_up_date,
                                                    $referred,
                                                    $pdf_file,
                                                    $record_status,
                                                    $created_at,
                                                    $updated_at
                                                ])));
                                        ?>
                                                <tr class="searchable-medical-row"
                                                    data-search-index="<?php echo htmlspecialchars($search_payload); ?>"
                                                    data-status-state="<?php echo $record_status; ?>"
                                                    data-branch-state="<?php echo htmlspecialchars(strtolower(trim($staff_branch))); ?>"
                                                    data-created-date="<?php echo (!empty($created_at) && $created_at !== '0000-00-00 00:00:00') ? date('Y-m-d', strtotime($created_at)) : ''; ?>">

                                                    <td>
                                                        <span class="sn-badge">#<?php echo sprintf('%02d', $sn++); ?></span>
                                                    </td>
                                                    <td><strong><?php echo htmlspecialchars($staff_name); ?></strong></td>

                                                    <td class="text-center">
                                                        <div class="passport-container d-flex justify-content-center align-items-center">
                                                            <?php if (!empty($passport_src)) { ?>
                                                                <div class="passport-frame shadow-sm" style="width: 45px; height: 45px; overflow: hidden; border-radius: 50%; border: 2px solid #e9ecef;">
                                                                    <img src="<?php echo htmlspecialchars($passport_src); ?>" alt="Staff Passport" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                                                                </div>
                                                            <?php } else { ?>
                                                                <span class="badge bg-light text-muted border text-xs py-1 px-2">No Photo</span>
                                                            <?php } ?>
                                                        </div>
                                                    </td>

                                                    <td><span class="text-dark fw-medium"><?php echo htmlspecialchars($staff_branch); ?></span></td>
                                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($company ? $company : '—'); ?></span></td>
                                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($department); ?></span></td>
                                                    <td><small class="text-muted"><?php echo htmlspecialchars($intake_time); ?></small></td>
                                                    <td><small class="text-muted"><?php echo htmlspecialchars($release_time ? $release_time : '—'); ?></small></td>

                                                    <td>
                                                        <div class="text-truncate-modern" title="<?php echo htmlspecialchars($diagnosis); ?>">
                                                            <?php echo htmlspecialchars($diagnosis); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate-modern" title="<?php echo htmlspecialchars($symptoms); ?>">
                                                            <?php echo htmlspecialchars($symptoms ? $symptoms : '—'); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate-modern" title="<?php echo htmlspecialchars($medical_notes); ?>">
                                                            <?php echo htmlspecialchars($medical_notes ? $medical_notes : '—'); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate-modern" title="<?php echo htmlspecialchars($treatment_given); ?>">
                                                            <?php echo htmlspecialchars($treatment_given ? $treatment_given : '—'); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate-modern" title="<?php echo htmlspecialchars($drugs_given); ?>">
                                                            <?php echo htmlspecialchars($drugs_given ? $drugs_given : '—'); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate-modern" title="<?php echo htmlspecialchars($dosage_instructions); ?>">
                                                            <?php echo htmlspecialchars($dosage_instructions ? $dosage_instructions : '—'); ?>
                                                        </div>
                                                    </td>

                                                    <td><span class="fw-medium text-dark"><?php echo htmlspecialchars($attended_by ? $attended_by : '—'); ?></span></td>
                                                    <td><span class="text-secondary"><?php echo htmlspecialchars($condition_on_admission ? $condition_on_admission : '—'); ?></span></td>
                                                    <td><span class="text-secondary"><?php echo htmlspecialchars($condition_on_release ? $condition_on_release : '—'); ?></span></td>

                                                    <td>
                                                        <?php if (!empty($blood_pressure)) { ?>
                                                            <span class="vital-chip bp"><i class="bi bi-heart-pulse me-1"></i><?php echo htmlspecialchars($blood_pressure); ?></span>
                                                        <?php } else { ?>
                                                            <span class="text-muted">—</span>
                                                        <?php } ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($temperature)) { ?>
                                                            <span class="vital-chip temp"><i class="bi bi-thermometer-half me-1"></i><?php echo htmlspecialchars($temperature); ?>°C</span>
                                                        <?php } else { ?>
                                                            <span class="text-muted">—</span>
                                                        <?php } ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($pulse_rate)) { ?>
                                                            <span class="vital-chip pulse"><i class="bi bi-activity me-1"></i><?php echo htmlspecialchars($pulse_rate); ?> bpm</span>
                                                        <?php } else { ?>
                                                            <span class="text-muted">—</span>
                                                        <?php } ?>
                                                    </td>

                                                    <td>
                                                        <?php if (strtolower($follow_up_required) === 'yes') { ?>
                                                            <span class="badge bg-danger-soft text-danger fw-semibold px-2 py-1">Yes</span>
                                                        <?php } else { ?>
                                                            <span class="text-muted">No</span>
                                                        <?php } ?>
                                                    </td>
                                                    <td><small class="text-muted"><?php echo htmlspecialchars($follow_up_date ? $follow_up_date : '—'); ?></small></td>
                                                    <td><small class="text-muted"><?php echo htmlspecialchars($referred ? $referred : 'No'); ?></small></td>
                                                    <td>
                                                        <?php if (!empty($pdf_file)) { ?>
                                                            <a href="uploads/referrals/<?php echo htmlspecialchars($pdf_file); ?>" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.75rem;">
                                                                <i class="bi bi-file-earmark-pdf-fill"></i> View
                                                            </a>
                                                        <?php } else { ?>
                                                            <span class="text-muted">—</span>
                                                        <?php } ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge-soft <?php echo $status_class; ?>">
                                                            <?php echo ucwords(str_replace('_', ' ', htmlspecialchars($record_status))); ?>
                                                        </span>
                                                    </td>

                                                    <td><small class="text-muted"><?php echo htmlspecialchars($created_at); ?></small></td>
                                                    <td><small class="text-muted"><?php echo htmlspecialchars($updated_at ? $updated_at : '—'); ?></small></td>

                                                    <td class="text-end">
                                                        <div class="action-btns justify-content-end">
                                                            <a href="view_log.php?id=<?php echo urlencode(encryptId($id)); ?>" class="btn btn-outline-info btn-icon-sm">View</a>
                                                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'super-admin'): ?>
                                                                <a href="referral-letter.php?ref_id=<?php echo urlencode(encryptId($id)); ?>"
                                                                    class="btn btn-outline-success btn-icon-sm"
                                                                    title="Generate Medical Referral Note"
                                                                    target="_blank">
                                                                    Referral
                                                                </a>
                                                            <?php endif; ?>
                                                            <a href="edit_log.php?id=<?php echo urlencode(encryptId($id)); ?>" class="btn btn-outline-primary btn-icon-sm">Edit</a>
                                                            <a href="delete_log.php?id=<?php echo urlencode(encryptId($id)); ?>"
                                                                class="btn btn-outline-danger btn-icon-sm"
                                                                onclick="return confirm('Are you sure you want to delete this medical log?');">
                                                                Delete
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                        <?php
                                            }
                                        } else {
                                            echo '<tr><td colspan="28" class="text-center py-5 text-muted">No medical records found in the database.</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </section>
            </div>

            <!-- JavaScript Client-Side Omni-Search & Multi-Filter Engine -->
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const searchInput = document.getElementById('omniMedicalSearch');
                    const statusFilter = document.getElementById('medicalStatusFilter');
                    const branchFilter = document.getElementById('medicalBranchFilter');
                    const dateFilter = document.getElementById('medicalDateFilter');

                    const visibleMetric = document.getElementById('visibleLogsMetric');
                    const totalMetric = document.getElementById('totalLogsMetric');

                    const rows = document.querySelectorAll('#medicalRecordsWorkspaceTable tbody tr.searchable-medical-row');

                    if (totalMetric) {
                        totalMetric.textContent = rows.length;
                    }

                    // Dynamically populate unique branch names into the branch filter dropdown
                    const uniqueBranches = new Set();
                    rows.forEach(row => {
                        const branchName = row.getAttribute('data-branch-state');
                        if (branchName) {
                            uniqueBranches.add(row.querySelector('td:nth-child(4)').textContent.trim());
                        }
                    });

                    if (branchFilter) {
                        uniqueBranches.forEach(branch => {
                            const option = document.createElement('option');
                            option.value = branch.toLowerCase();
                            option.textContent = branch;
                            branchFilter.appendChild(option);
                        });

                        // Preselect branch filter dropdown if parameter is present in URL
                        const urlParams = new URLSearchParams(window.location.search);
                        const urlBranch = urlParams.get('branch');
                        if (urlBranch) {
                            branchFilter.value = urlBranch.toLowerCase();
                        }
                    }

                    // Preselect status filter dropdown if parameter is present in URL
                    const urlParamsInit = new URLSearchParams(window.location.search);
                    const urlStatus = urlParamsInit.get('status');
                    if (statusFilter && urlStatus) {
                        statusFilter.value = urlStatus.toLowerCase();
                    }

                    function applyMedicalFilters() {
                        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
                        const selectedStatus = statusFilter ? statusFilter.value.toLowerCase().trim() : '';
                        const selectedBranch = branchFilter ? branchFilter.value.toLowerCase().trim() : '';
                        const selectedDate = dateFilter ? dateFilter.value.trim() : '';

                        let matchCount = 0;

                        rows.forEach(row => {
                            const searchIndex = row.getAttribute('data-search-index') || '';
                            const rowStatus = row.getAttribute('data-status-state') || '';
                            const rowBranch = row.getAttribute('data-branch-state') || '';
                            const rowDate = row.getAttribute('data-created-date') || '';

                            const matchesSearch = query === '' || searchIndex.includes(query);
                            const matchesStatus = selectedStatus === '' || rowStatus === selectedStatus;
                            const matchesBranch = selectedBranch === '' || rowBranch === selectedBranch;
                            const matchesDate = selectedDate === '' || rowDate === selectedDate;

                            if (matchesSearch && matchesStatus && matchesBranch && matchesDate) {
                                row.style.display = '';
                                matchCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        });

                        if (visibleMetric) {
                            visibleMetric.textContent = matchCount;
                        }
                    }

                    if (searchInput) searchInput.addEventListener('input', applyMedicalFilters);
                    if (statusFilter) statusFilter.addEventListener('change', applyMedicalFilters);
                    if (branchFilter) branchFilter.addEventListener('change', applyMedicalFilters);
                    if (dateFilter) dateFilter.addEventListener('change', applyMedicalFilters);

                    // Initialize count on page load
                    applyMedicalFilters();
                });
            </script>

            <?php include('./inc/footer.php'); ?>
        </div>
    </div>
    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>