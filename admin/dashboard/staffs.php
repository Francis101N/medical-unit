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
    <title>Staffs - Medical Unit</title>

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
    /* Modern Staff Table Theme */
    .custom-table-container {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        border: 1px solid #eef2f6;
        overflow-x: auto;
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.875rem;
        color: #334155;
    }

    .modern-table thead th {
        background-color: #f8fafc;
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

    /* Passport Avatar Frame */
    .passport-container {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .passport-frame {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        overflow: hidden;
        background: #f1f5f9;
        border: 2px solid #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.06);
        transition: transform 0.2s;
    }

    .passport-frame:hover {
        transform: scale(1.1);
    }

    .passport-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Biological Metrics Chips */
    .bio-chip {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.78rem;
    }

    .bio-chip.blood {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    .bio-chip.geno {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #7dd3fc;
    }

    /* Modern Soft Badges */
    .badge-soft {
        padding: 5px 12px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
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

    .badge-soft-danger {
        background: #fee2e2;
        color: #b91c1c;
    }

    /* Dynamic Multi-line Wrappers */
    .text-truncate-modern {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        max-width: 220px;
        white-space: normal;
        line-height: 1.4;
        font-size: 0.825rem;
    }

    /* Action Controls Layout */
    .action-btns {
        display: flex;
        gap: 6px;
    }

    .btn-icon-sm {
        padding: 5px 12px;
        font-size: 0.78rem;
        border-radius: 8px;
        font-weight: 500;
        transition: transform 0.15s ease;
    }

    .btn-icon-sm:hover {
        transform: translateY(-1px);
    }

    /* Bio / Clinical Metric Chips */
    .bio-chip {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
        border: 1px solid #cbd5e1;
    }

    .bio-chip.blood {
        background-color: #fef2f2;
        color: #dc2626;
        border-color: #fee2e2;
    }

    .bio-chip.geno {
        background-color: #f0fdf4;
        color: #16a34a;
        border-color: #dcfce7;
    }

    /* Expanded Status Configurations */
    .badge-soft-danger {
        background-color: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fee2e2;
    }

    /* Filter System Controls */
    .table-filter-bar input,
    .table-filter-bar select {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        color: #334155;
        font-size: 0.875rem;
        height: 42px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .table-filter-bar input:focus,
    .table-filter-bar select:focus {
        border-color: #435ebe;
        box-shadow: 0 0 0 3px rgba(67, 94, 190, 0.15);
        outline: none;
    }

    @keyframes live-pulse {
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
                            <h3>Staff Management</h3>
                            <p class="text-subtitle text-muted">
                                View, manage, and monitor all registered staff records and information.
                            </p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Staffs</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                <section class="section">

                    <!-- High-Performance Interactive Staff Roster Omni-Filter Component Layer -->
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; background: linear-gradient(145deg, #ffffff, #fcfdff);">
                        <div class="card-body p-4">
                            <div class="row g-3 align-items-center justify-content-between table-filter-bar">

                                <!-- Full-Text Search Omnibox Entry -->
                                <div class="col-12 col-md-4 col-lg-3">
                                    <div class="input-group dashboard-search-group shadow-3xs" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
                                        <span class="input-group-text bg-white border-0 pe-2 ps-3 text-muted">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color: #94a3b8;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </span>
                                        <input type="text" id="omniStaffSearch" class="form-control border-0 bg-white py-2 text-dark font-medium"
                                            placeholder="Search ID, name, bio tags..." style="font-size: 0.9rem; box-shadow: none;">
                                    </div>
                                </div>

                                <!-- Matrix Option Select Segment Lists Dropdowns -->
                                <div class="col-12 col-md-8 col-lg-6">
                                    <div class="row g-2">
                                        <div class="col-6 col-sm">
                                            <select id="staffDepartmentFilter" class="form-select form-select-sm shadow-3xs">
                                                <option value="">All Departments</option>
                                                <!-- Auto-populated by JS below -->
                                            </select>
                                        </div>
                                        <div class="col-6 col-sm">
                                            <select id="staffBranchFilter" class="form-select form-select-sm shadow-3xs">
                                                <option value="">All Branches</option>
                                                <!-- Auto-populated by JS below -->
                                            </select>
                                        </div>
                                        <div class="col-6 col-sm">
                                            <select id="staffCompanyFilter" class="form-select form-select-sm shadow-3xs">
                                                <option value="">All Companies</option>
                                                <!-- Auto-populated by JS below -->
                                            </select>
                                        </div>
                                        <div class="col-6 col-sm">
                                            <select id="staffStatusFilter" class="form-select form-select-sm shadow-3xs">
                                                <option value="">Status</option>
                                                <option value="active">Active</option>
                                                <option value="suspended">Suspended</option>
                                                <option value="inactive">Inactive</option>
                                                <option value="terminated">Terminated</option>
                                            </select>
                                        </div>
                                        <div class="col-6 col-sm">
                                            <select id="staffFitnessFilter" class="form-select form-select-sm shadow-3xs">
                                                <option value="">Fitness</option>
                                                <option value="fit">Fit</option>
                                                <option value="under_observation">Observation</option>
                                                <option value="unfit">Unfit</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Real-time Live Counters Interface Display Block -->
                                <div class="col-12 col-lg-3 text-lg-end">
                                    <div class="d-inline-flex align-items-center bg-light border px-3 py-2" style="border-radius: 12px; background-color: #f8fafc !important;">
                                        <span class="d-inline-block bg-success rounded-circle me-2" style="width: 8px; height: 8px; animation: live-pulse 2s infinite;"></span>
                                        <span class="text-secondary font-semibold" style="font-size: 0.85rem;">
                                            Matched Roster: <strong id="visibleStaffCount" class="text-dark fw-bold" style="font-size: 0.95rem;">0</strong> / <span id="totalStaffCount">0</span>
                                        </span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>


                    <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">

                        <!-- HEADER -->
                        <div class="card-header d-flex justify-content-between align-items-center px-4 py-3 bg-white border-bottom">
                            <h4 class="card-title mb-0" style="color: #1e293b; font-weight: 600;">Staff Records Directory</h4>
                            <a href="add_staff.php" class="btn btn-success btn-sm px-3 py-2 fw-medium" style="border-radius: 8px;">
                                + ADD STAFF
                            </a>
                        </div>

                        <div class="table-responsive">
                            <?php
                            // Initialize alert properties
                            $alert_msg = '';
                            $alert_type = 'success';

                            // Extract flash states out of the active session context
                            if (isset($_SESSION['success'])) {
                                $alert_msg = $_SESSION['success'];
                                $alert_type = 'success';
                                unset($_SESSION['success']);
                            } elseif (isset($_SESSION['error'])) {
                                $alert_msg = $_SESSION['error'];
                                $alert_type = 'danger';
                                unset($_SESSION['error']);
                            }

                            // Render dynamic contextual notifications UI
                            if (!empty($alert_msg)) {
                            ?>
                                <div class="alert alert-<?php echo $alert_type; ?> alert-dismissible fade show m-3 shadow-sm border-0" role="alert">
                                    <div class="d-flex align-items-center">
                                        <?php if ($alert_type === 'success') { ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check-circle-fill me-2 text-success" viewBox="0 0 16 16">
                                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                            </svg>
                                        <?php } else { ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-exclamation-triangle-fill me-2 text-danger" viewBox="0 0 16 16">
                                                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                                            </svg>
                                        <?php } ?>
                                        <div class="text-dark">
                                            <?php echo htmlspecialchars($alert_msg); ?>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php } ?>

                       <div class="custom-table-container">
                                <table class="modern-table table-hover align-middle mb-0" id="staffRecordsDirectoryWorkspaceTable" style="width: 100%; min-width: 2700px;">
                                    <thead>
                                        <tr>
                                            <th class="small-field">S/N</th>
                                            <th class="medium-field">STAFF ID</th>
                                            <th class="small-field text-center">PASSPORT</th>
                                            <th class="medium-field">FULL NAME</th>
                                            <th class="wide-field">EMAIL ADDRESS</th>
                                            <th class="medium-field">PHONE</th>
                                            <th class="small-field">GENDER</th>
                                            <th class="medium-field">DATE OF BIRTH</th>

                                            <th class="small-field">BRANCH</th>
                                            <th class="small-field">COMPANY</th>
                                            <th class="medium-field">DEPARTMENT</th>
                                            <th class="medium-field">ROLE</th>
                                            <th class="medium-field">EMPLOYMENT TYPE</th>
                                            <th class="medium-field">HIRE DATE</th>
                                            <th class="small-field">STATUS</th>

                                            <th class="wide-field">ADDRESS</th>

                                            <th class="medium-field">NEXT OF KIN</th>
                                            <th class="medium-field">NEXT OF KIN PHONE</th>

                                            <th class="small-field">BLOOD GROUP</th>
                                            <th class="small-field">GENOTYPE</th>

                                            <th class="wide-field">ALLERGIES</th>
                                            <th class="wide-field">MEDICAL CONDITIONS</th>

                                            <th class="medium-field">EMERGENCY CONTACT</th>
                                            <th class="medium-field">EMERGENCY PHONE</th>

                                            <th class="medium-field">LAST CHECKUP</th>
                                            <th class="medium-field">FITNESS STATUS</th>

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
                                                $token = $id . '|' . $key;
                                                return strtr(base64_encode($token), '+/', '-_');
                                            }
                                        }

                                        $user_role = strtolower($_SESSION['role'] ?? '');
                                        $user_branch = $_SESSION['branch'] ?? '';

                                        if ($user_role === 'super-admin') {
                                            $stmt = $conn->prepare(" SELECT s.*, b.branch_name FROM staffs s LEFT JOIN branches b ON s.branch_id = b.id ORDER BY s.id DESC ");
                                        } else {
                                            $stmt = $conn->prepare(" SELECT s.*, b.branch_name FROM staffs s LEFT JOIN branches b ON s.branch_id = b.id WHERE LOWER(TRIM(b.branch_name)) = LOWER(TRIM(?)) OR s.branch_id = ? ORDER BY s.id DESC");
                                            $stmt->bind_param("ss", $user_branch, $user_branch);
                                        }

                                        $stmt->execute();
                                        $select_staff = $stmt->get_result();

                                        if ($select_staff && $select_staff->num_rows > 0) {
                                            $sn = 1;

                                            while ($row = $select_staff->fetch_assoc()) {
                                                $id = $row['id'];
                                                $staff_id = $row['staff_id'];
                                                $fullname = $row['fullname'];
                                                $email = $row['email'];
                                                $phone = $row['phone'];
                                                $gender = $row['gender'];
                                                $dob = $row['dob'];
                                                $passport = $row['passport'];

                                                $branch_name = !empty($row['branch_name']) ? $row['branch_name'] : 'Branch ID: ' . $row['branch_id'];
                                                $company = $row['company'];
                                                $department = $row['department'];
                                                $role = $row['role'];
                                                $employment_type = $row['employment_type'];
                                                $hire_date = $row['hire_date'];
                                                $status = strtolower(trim($row['status']));

                                                $address = $row['address'];

                                                $next_of_kin = $row['next_of_kin'];
                                                $next_of_kin_phone = $row['next_of_kin_phone'];

                                                $blood_group = $row['blood_group'];
                                                $genotype = $row['genotype'];
                                                $allergies = $row['allergies'];
                                                $medical_conditions = $row['medical_conditions'];

                                                $emergency_contact_name = $row['emergency_contact_name'];
                                                $emergency_contact_phone = $row['emergency_contact_phone'];

                                                $last_medical_checkup = $row['last_medical_checkup'];
                                                $fitness_status = strtolower(trim($row['fitness_status']));

                                                // Setup standardized fitness query flags
                                                $fitness_query_val = $fitness_status;
                                                if ($fitness_status === 'observation') {
                                                    $fitness_query_val = 'under_observation';
                                                }

                                                if ($status == "active") {
                                                    $status_class = "badge-soft-success";
                                                } elseif ($status == "suspended") {
                                                    $status_class = "badge-soft-warning";
                                                } elseif ($status == "inactive") {
                                                    $status_class = "badge-soft-secondary";
                                                } else {
                                                    $status_class = "badge-soft-danger";
                                                }

                                                if ($fitness_status == "fit") {
                                                    $fitness_class = "badge-soft-success";
                                                } elseif ($fitness_status == "under_observation" || $fitness_status == "observation") {
                                                    $fitness_class = "badge-soft-warning";
                                                } else {
                                                    $fitness_class = "badge-soft-danger";
                                                }

                                                // Structural Payload Matrix Compilation String Mapping across elements
                                                $search_payload = strtolower(implode(' ', array_filter([
                                                    $sn,
                                                    $staff_id,
                                                    $fullname,
                                                    $email,
                                                    $phone,
                                                    $gender,
                                                    $branch_name,
                                                    $company,
                                                    $department,
                                                    $role,
                                                    $employment_type,
                                                    $status,
                                                    $blood_group,
                                                    $genotype,
                                                    $allergies,
                                                    $medical_conditions,
                                                    $fitness_status
                                                ])));
                                        ?>
                                                <!-- Active Structured Targeted Data Nodes -->
                                                <tr class="searchable-staff-row"
                                                    data-search-index="<?php echo htmlspecialchars($search_payload); ?>"
                                                    data-dept-state="<?php echo htmlspecialchars(strtolower(trim($department))); ?>"
                                                    data-branch-state="<?php echo htmlspecialchars(strtolower(trim($branch_name))); ?>"
                                                    data-company-state="<?php echo htmlspecialchars(strtolower(trim($company ?? ''))); ?>"
                                                    data-status-state="<?php echo $status; ?>"
                                                    data-fitness-state="<?php echo $fitness_query_val; ?>">

                                                    <!-- Dynamic Row Counter -->
                                                    <td>
                                                        <span class="sn-badge">#<?php echo sprintf('%02d', $sn++); ?></span>
                                                    </td>

                                                    <td><span class="text-dark fw-semibold"><?php echo htmlspecialchars($staff_id); ?></span></td>

                                                    <!-- Passport Frame Component -->
                                                    <td class="text-center">
                                                        <div class="passport-container d-flex justify-content-center align-items-center">
                                                            <?php if (!empty($passport) && file_exists("uploads/" . $passport)) { ?>
                                                                <div class="passport-frame shadow-sm" style="width: 45px; height: 45px; overflow: hidden; border-radius: 50%; border: 2px solid #e9ecef;">
                                                                    <img src="uploads/<?php echo htmlspecialchars($passport); ?>" alt="Passport" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                                                                </div>
                                                            <?php } else { ?>
                                                                <span class="badge bg-light text-muted border text-xs py-1 px-2">No Photo</span>
                                                            <?php } ?>
                                                        </div>
                                                    </td>

                                                    <td><strong><?php echo htmlspecialchars($fullname); ?></strong></td>
                                                    <td><span class="text-muted"><?php echo htmlspecialchars($email); ?></span></td>
                                                    <td><span class="text-dark fw-medium"><?php echo htmlspecialchars($phone); ?></span></td>
                                                    <td><?php echo ucfirst(htmlspecialchars($gender)); ?></td>
                                                    <td><small class="text-muted"><?php echo htmlspecialchars($dob); ?></small></td>

                                                    <!-- Display Branch Name -->
                                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($branch_name); ?></span></td>

                                                    <!-- Display Company Name -->
                                                    <td><span class="fw-medium text-dark"><?php echo htmlspecialchars($company ? $company : '—'); ?></span></td>

                                                    <td><span class="fw-medium text-dark"><?php echo htmlspecialchars($department); ?></span></td>
                                                    <td><span class="text-muted"><?php echo htmlspecialchars($role); ?></span></td>
                                                    <td><?php echo ucfirst(htmlspecialchars($employment_type)); ?></td>
                                                    <td><small class="text-muted"><?php echo htmlspecialchars($hire_date); ?></small></td>

                                                    <!-- Soft Status Badge -->
                                                    <td>
                                                        <span class="badge-soft <?php echo $status_class; ?>">
                                                            <?php echo ucfirst(htmlspecialchars($status)); ?>
                                                        </span>
                                                    </td>

                                                    <!-- Address Wrapper -->
                                                    <td>
                                                        <div class="text-truncate-modern" title="<?php echo htmlspecialchars($address); ?>">
                                                            <?php echo htmlspecialchars($address); ?>
                                                        </div>
                                                    </td>

                                                    <td><span class="fw-medium"><?php echo htmlspecialchars($next_of_kin); ?></span></td>
                                                    <td><span class="text-muted"><?php echo htmlspecialchars($next_of_kin_phone); ?></span></td>

                                                    <!-- Stylized Bio Chips -->
                                                    <td><span class="bio-chip blood"><?php echo htmlspecialchars($blood_group); ?></span></td>
                                                    <td><span class="bio-chip geno"><?php echo htmlspecialchars($genotype); ?></span></td>

                                                    <!-- Clinical Information Fields -->
                                                    <td>
                                                        <div class="text-truncate-modern text-danger" title="<?php echo htmlspecialchars($allergies); ?>">
                                                            <?php echo htmlspecialchars($allergies ? $allergies : 'None'); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate-modern" title="<?php echo htmlspecialchars($medical_conditions); ?>">
                                                            <?php echo htmlspecialchars($medical_conditions ? $medical_conditions : 'None'); ?>
                                                        </div>
                                                    </td>

                                                    <td><span class="fw-medium"><?php echo htmlspecialchars($emergency_contact_name); ?></span></td>
                                                    <td><span class="text-muted"><?php echo htmlspecialchars($emergency_contact_phone); ?></span></td>
                                                    <td><small class="text-muted"><?php echo htmlspecialchars($last_medical_checkup ? $last_medical_checkup : '—'); ?></small></td>

                                                    <!-- Fitness soft badge -->
                                                    <td>
                                                        <span class="badge-soft <?php echo $fitness_class; ?>">
                                                            <?php echo ucfirst(htmlspecialchars(str_replace('_', ' ', $fitness_status))); ?>
                                                        </span>
                                                    </td>

                                                    <td><small class="text-muted"><?php echo htmlspecialchars($row['created_at']); ?></small></td>
                                                    <td><small class="text-muted"><?php echo htmlspecialchars($row['updated_at']); ?></small></td>

                                                    <!-- Actions Layout -->
                                                    <td class="text-end">
                                                        <div class="action-btns justify-content-end">
                                                            <?php $secure_id = encryptId($id); ?>
                                                            <a href="view_staff.php?id=<?php echo urlencode($secure_id); ?>" class="btn btn-outline-info btn-icon-sm">View</a>
                                                            <a href="edit_staff.php?id=<?php echo urlencode($secure_id); ?>" class="btn btn-outline-primary btn-icon-sm">Edit</a>
                                                            <a href="delete_staff.php?id=<?php echo urlencode($secure_id); ?>"
                                                                class="btn btn-outline-danger btn-icon-sm"
                                                                onclick="return confirm('Are you sure you want to delete this staff record?');">
                                                                Delete
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php
                                            }
                                        } else {
                                            ?>
                                            <tr class="db-empty-fallback-row">
                                                <td colspan="29" class="text-center text-muted py-5">
                                                    <div class="py-3">No profiles found registered for your assigned branch directory.</div>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        $stmt->close();
                                        ?>

                                        <!-- Hidden JavaScript Empty Filter State Row Structure Layer -->
                                        <tr id="jsZeroMatchStaffRow" class="d-none">
                                            <td colspan="29" class="text-center py-5 text-muted bg-light-subtle fw-medium">
                                                No staff profiles matched your active query filter configurations.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                </section>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const workspaceTable = document.getElementById('staffRecordsDirectoryWorkspaceTable');
                    if (!workspaceTable) return;

                    // Connect Input & Selector UI Nodes
                    const omniInput = document.getElementById('omniStaffSearch');
                    const deptSelect = document.getElementById('staffDepartmentFilter');
                    const branchSelect = document.getElementById('staffBranchFilter');
                    const companySelect = document.getElementById('staffCompanyFilter');
                    const statusSelect = document.getElementById('staffStatusFilter');
                    const fitnessSelect = document.getElementById('staffFitnessFilter');

                    // Feedback Telemetry & Placeholder Elements 
                    const rows = workspaceTable.querySelectorAll('.searchable-staff-row');
                    const zeroMatchRow = document.getElementById('jsZeroMatchStaffRow');
                    const visibleLogsIndicator = document.getElementById('visibleStaffCount');
                    const totalLogsIndicator = document.getElementById('totalStaffCount');

                    // 1. Scan Table Collection to Harvest Unique Select Option Values Dynamically
                    const uniqueDepartments = new Set();
                    const uniqueBranches = new Set();
                    const uniqueCompanies = new Set();

                    rows.forEach(row => {
                        const rawDeptAttr = row.getAttribute('data-dept-state');
                        const rawBranchAttr = row.getAttribute('data-branch-state');
                        const rawCompanyAttr = row.getAttribute('data-company-state');

                        if (rawDeptAttr) {
                            // Read index 10 (Department Column index shifted due to Company column)
                            const deptText = row.cells[10]?.textContent?.trim();
                            if (deptText) uniqueDepartments.add(JSON.stringify({
                                value: rawDeptAttr,
                                display: deptText
                            }));
                        }
                        if (rawBranchAttr) {
                            // Read index 8 (Branch Column)
                            const branchText = row.cells[8]?.textContent?.trim();
                            if (branchText) uniqueBranches.add(JSON.stringify({
                                value: rawBranchAttr,
                                display: branchText
                            }));
                        }
                        if (rawCompanyAttr && rawCompanyAttr !== '—') {
                            // Read index 9 (Company Column)
                            const companyText = row.cells[9]?.textContent?.trim();
                            if (companyText && companyText !== '—') uniqueCompanies.add(JSON.stringify({
                                value: rawCompanyAttr,
                                display: companyText
                            }));
                        }
                    });

                    // Populate Dynamic Department Dropdown Options
                    uniqueDepartments.forEach(jsonItem => {
                        const item = JSON.parse(jsonItem);
                        const opt = document.createElement('option');
                        opt.value = item.value;
                        opt.textContent = item.display;
                        deptSelect.appendChild(opt);
                    });

                    // Populate Dynamic Branch Dropdown Options
                    uniqueBranches.forEach(jsonItem => {
                        const item = JSON.parse(jsonItem);
                        const opt = document.createElement('option');
                        opt.value = item.value;
                        opt.textContent = item.display;
                        branchSelect.appendChild(opt);
                    });

                    // Populate Dynamic Company Dropdown Options
                    uniqueCompanies.forEach(jsonItem => {
                        const item = JSON.parse(jsonItem);
                        const opt = document.createElement('option');
                        opt.value = item.value;
                        opt.textContent = item.display;
                        companySelect.appendChild(opt);
                    });

                    // Initialize baseline counters 
                    if (totalLogsIndicator) totalLogsIndicator.textContent = rows.length;

                    // 2. Multivariable Roster Processing Pipeline Logic Routine
                    function evaluateRosterMatrixFilters() {
                        const query = omniInput.value.toLowerCase().trim();
                        const targetDept = deptSelect.value;
                        const targetBranch = branchSelect.value;
                        const targetCompany = companySelect.value;
                        const targetStatus = statusSelect.value;
                        const targetFitness = fitnessSelect.value;

                        let visibleCount = 0;

                        rows.forEach(row => {
                            const indexPayload = row.getAttribute('data-search-index') || '';
                            const deptState = row.getAttribute('data-dept-state') || '';
                            const branchState = row.getAttribute('data-branch-state') || '';
                            const companyState = row.getAttribute('data-company-state') || '';
                            const statusState = row.getAttribute('data-status-state') || '';
                            const fitnessState = row.getAttribute('data-fitness-state') || '';

                            // Matrix conditional validations
                            const checkSearch = query === '' || indexPayload.includes(query);
                            const checkDept = targetDept === '' || deptState === targetDept;
                            const checkBranch = targetBranch === '' || branchState === targetBranch;
                            const checkCompany = targetCompany === '' || companyState === targetCompany;
                            const checkStatus = targetStatus === '' || statusState === targetStatus;
                            const checkFitness = targetFitness === '' || fitnessState === targetFitness ||
                                (targetFitness === 'under_observation' && fitnessState === 'observation');

                            if (checkSearch && checkDept && checkBranch && checkCompany && checkStatus && checkFitness) {
                                row.classList.remove('d-none');
                                visibleCount++;
                            } else {
                                row.classList.add('d-none');
                            }
                        });

                        // 3. Update Real-Time Counter Telemetry Interface
                        if (visibleLogsIndicator) visibleLogsIndicator.textContent = visibleCount;

                        if (rows.length > 0) {
                            if (visibleCount === 0) {
                                zeroMatchRow.classList.remove('d-none');
                            } else {
                                zeroMatchRow.classList.add('d-none');
                            }
                        }
                    }

                    // Attach Event Matrix Core Observers
                    omniInput.addEventListener('input', evaluateRosterMatrixFilters);
                    deptSelect.addEventListener('change', evaluateRosterMatrixFilters);
                    branchSelect.addEventListener('change', evaluateRosterMatrixFilters);
                    companySelect.addEventListener('change', evaluateRosterMatrixFilters);
                    statusSelect.addEventListener('change', evaluateRosterMatrixFilters);
                    fitnessSelect.addEventListener('change', evaluateRosterMatrixFilters);

                    // Bootstrap execution logic pass on template mount load
                    evaluateRosterMatrixFilters();
                });
            </script>
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