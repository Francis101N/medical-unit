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

// Check if user is logged in and is strictly a super-admin
if ($_SESSION['role'] !== 'super-admin') {
    echo "
    <script>
        alert('Access Denied! Unauthorized page access.');
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
    <title>Users - Medical Unit</title>

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
    /* Modern Users Table Theme */
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
                            <h3>Users/Admin Management</h3>
                            <p class="text-subtitle text-muted">
                                View, manage, and monitor all registered Users records and information.
                            </p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Users</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                <section class="section">

                    <!-- High-Performance Interactive users Roster Omni-Filter Component Layer -->
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; background: linear-gradient(145deg, #ffffff, #fcfdff);">
                        <div class="card-body p-4">
                            <div class="row g-3 align-items-center justify-content-between table-filter-bar">

                                <!-- Full-Text Search Omnibox Entry -->
                                <div class="col-12 col-md-4 col-lg-4">
                                    <div class="input-group dashboard-search-group shadow-3xs" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
                                        <span class="input-group-text bg-white border-0 pe-2 ps-3 text-muted">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color: #94a3b8;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </span>
                                        <input type="text" id="omniStaffSearch" class="form-control border-0 bg-white py-2 text-dark font-medium"
                                            placeholder="Search name, username, email..." style="font-size: 0.9rem; box-shadow: none;">
                                    </div>
                                </div>

                                <!-- Matrix Option Select Segment Lists Dropdowns (Matched to Table Columns) -->
                                <div class="col-12 col-md-8 col-lg-5">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <select id="staffRoleFilter" class="form-select form-select-sm shadow-3xs">
                                                <option value="">All Roles</option>
                                                <!-- Auto-populated by JS below -->
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <select id="staffBranchFilter" class="form-select form-select-sm shadow-3xs">
                                                <option value="">All Branches</option>
                                                <!-- Auto-populated by JS below -->
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
                            <h4 class="card-title mb-0" style="color: #1e293b; font-weight: 600;">User Records Directory</h4>
                            <a href="add_user.php" class="btn btn-success btn-sm px-3 py-2 fw-medium" style="border-radius: 8px;">
                                + ADD USER / ADMIN
                            </a>
                        </div>

                        <div class="table-responsive">
                            <?php
                            $msg = $_SESSION['msg'] ?? '';
                            $msg_type = $_SESSION['msg_type'] ?? 'success';

                            // Clear session messages after grabbing them so they don't persist on refresh
                            unset($_SESSION['msg']);
                            unset($_SESSION['msg_type']);

                            if (!empty($msg)) {
                                // Map variables to your alert template names
                                $alert_msg = $msg;
                                $alert_type = $msg_type;
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
                                <table class="modern-table table-hover align-middle mb-0" id="staffRecordsDirectoryWorkspaceTable" style="width: 100%; min-width: 1400px;">
                                    <thead>
                                        <tr>
                                            <th class="small-field">S/N</th>
                                            <th class="medium-field">FULLNAMES</th>
                                            <th class="small-field text-center">PASSPORT</th>
                                            <th class="medium-field">USERNAME</th>
                                            <th class="wide-field">PASSWORD</th>
                                            <th class="medium-field">EMAIL</th>
                                            <th class="small-field">ROLE</th>
                                            <th class="medium-field">BRANCH</th>
                                            <th class="medium-field">CREATED AT</th>
                                            <th class="medium-field text-end">ACTIONS</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        /** @var mysqli $conn */
                                        include('db.php');

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

                                        // Reversible OpenSSL Decryption Helper for passwords
                                        if (!function_exists('decryptPassword')) {
                                            function decryptPassword($data)
                                            {
                                                if (empty($data)) {
                                                    return 'N/A';
                                                }
                                                $encryption_key = 'techbyfrancis1972$';
                                                $cipher = "AES-128-CBC";

                                                if (strpos($data, '::') === false) {
                                                    return $data; // Fallback if data is raw/unencrypted
                                                }

                                                list($encrypted_data, $iv) = explode('::', $data, 2);
                                                $decrypted = openssl_decrypt($encrypted_data, $cipher, $encryption_key, 0, base64_decode($iv));
                                                return $decrypted !== false ? $decrypted : $data;
                                            }
                                        }

                                        $user_role = strtolower($_SESSION['role'] ?? '');
                                        $user_branch = $_SESSION['branch'] ?? '';

                                        if ($user_role === 'super-admin') {
                                            $stmt = $conn->prepare("SELECT * FROM users ORDER BY id DESC");
                                        } else {
                                            $stmt = $conn->prepare("SELECT * FROM users WHERE LOWER(TRIM(branch)) = LOWER(TRIM(?)) ORDER BY id DESC");
                                            $stmt->bind_param("s", $user_branch);
                                        }

                                        $stmt->execute();
                                        $select_staff = $stmt->get_result();

                                        if ($select_staff && $select_staff->num_rows > 0) {
                                            $sn = 1;

                                            while ($row = $select_staff->fetch_assoc()) {
                                                $id         = $row['id'];
                                                $fullname   = $row['fullname'] ?? 'N/A';
                                                $username   = $row['username'] ?? 'N/A';

                                                // Decrypt the stored password string back into plain text view
                                                $password   = decryptPassword($row['password'] ?? 'N/A');

                                                $email      = $row['email'] ?? 'N/A';
                                                $role       = $row['role'] ?? 'N/A';
                                                $branch     = $row['branch'] ?? 'N/A';
                                                $passport   = $row['passport'] ?? '';
                                                $created_at = $row['date_created'] ?? 'N/A';

                                                $search_payload = strtolower(implode(' ', array_filter([
                                                    $sn,
                                                    $fullname,
                                                    $username,
                                                    $email,
                                                    $role,
                                                    $branch
                                                ])));
                                        ?>
                                                <tr class="searchable-staff-row"
                                                    data-search-index="<?php echo htmlspecialchars($search_payload); ?>"
                                                    data-branch-state="<?php echo htmlspecialchars(strtolower(trim($branch))); ?>"
                                                    data-role-state="<?php echo htmlspecialchars(strtolower(trim($role))); ?>">

                                                    <td>
                                                        <span class="sn-badge">#<?php echo sprintf('%02d', $sn++); ?></span>
                                                    </td>

                                                    <td><strong><?php echo htmlspecialchars($fullname); ?></strong></td>

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

                                                    <td><span class="text-dark fw-semibold font-monospace"><?php echo htmlspecialchars($username); ?></span></td>

                                                    <!-- Password with Mask and Toggle View -->
                                                    <td>
                                                        <div class="input-group input-group-sm" style="max-width: 180px;">
                                                            <input type="password"
                                                                class="form-control font-monospace bg-light border-0 px-2 text-dark"
                                                                value="<?php echo htmlspecialchars($password); ?>"
                                                                readonly
                                                                style="font-size: 0.85rem;"
                                                                id="pwd_<?php echo $id; ?>">
                                                            <button class="btn btn-outline-secondary border-0 bg-light text-muted px-2"
                                                                type="button"
                                                                onclick="togglePasswordVisibility('pwd_<?php echo $id; ?>', this)"
                                                                title="Toggle Password Visibility">
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                        </div>
                                                    </td>

                                                    <td><span class="text-muted"><?php echo htmlspecialchars($email); ?></span></td>
                                                    <td><span class="badge bg-light-secondary text-secondary border"><?php echo htmlspecialchars($role); ?></span></td>
                                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($branch); ?></span></td>
                                                    <td><small class="text-muted"><?php echo htmlspecialchars($created_at); ?></small></td>

                                                    <td class="text-end">
                                                        <div class="action-btns justify-content-end">
                                                            <?php $secure_id = encryptId($id); ?>
                                                            <a href="view_user.php?id=<?php echo urlencode($secure_id); ?>" class="btn btn-outline-info btn-icon-sm">View</a>
                                                            <a href="edit_user.php?id=<?php echo urlencode($secure_id); ?>" class="btn btn-outline-primary btn-icon-sm">Edit</a>
                                                            <a href="delete_user.php?id=<?php echo urlencode($secure_id); ?>"
                                                                class="btn btn-outline-danger btn-icon-sm"
                                                                onclick="return confirm('Are you sure you want to delete this user record?');">
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
                                                <td colspan="10" class="text-center text-muted py-5">
                                                    <div class="py-3">No user profiles found registered for your assigned branch directory.</div>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        $stmt->close();
                                        ?>

                                        <tr id="jsZeroMatchStaffRow" class="d-none">
                                            <td colspan="10" class="text-center py-5 text-muted bg-light-subtle fw-medium">
                                                No user profiles matched your active query filter configurations.
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
                    const roleSelect = document.getElementById('staffRoleFilter');
                    const branchSelect = document.getElementById('staffBranchFilter');

                    // Feedback Telemetry & Placeholder Elements 
                    const rows = workspaceTable.querySelectorAll('.searchable-staff-row');
                    const zeroMatchRow = document.getElementById('jsZeroMatchStaffRow');
                    const visibleLogsIndicator = document.getElementById('visibleStaffCount');
                    const totalLogsIndicator = document.getElementById('totalStaffCount');

                    // 1. Scan Table Collection to Harvest Unique Select Option Values Dynamically
                    const uniqueRoles = new Set();
                    const uniqueBranches = new Set();

                    rows.forEach(row => {
                        const rawRoleAttr = row.getAttribute('data-role-state');
                        const rawBranchAttr = row.getAttribute('data-branch-state');

                        if (rawRoleAttr) {
                            // Read index 6 (Role Column index after adding password column)
                            const roleText = row.cells[6]?.textContent?.trim();
                            if (roleText) uniqueRoles.add(JSON.stringify({
                                value: rawRoleAttr,
                                display: roleText
                            }));
                        }
                        if (rawBranchAttr) {
                            // Read index 7 (Branch Column index after adding password column)
                            const branchText = row.cells[7]?.textContent?.trim();
                            if (branchText) uniqueBranches.add(JSON.stringify({
                                value: rawBranchAttr,
                                display: branchText
                            }));
                        }
                    });

                    // Populate Dynamic Role Dropdown Options
                    if (roleSelect) {
                        uniqueRoles.forEach(jsonItem => {
                            const item = JSON.parse(jsonItem);
                            const opt = document.createElement('option');
                            opt.value = item.value;
                            opt.textContent = item.display;
                            roleSelect.appendChild(opt);
                        });
                    }

                    // Populate Dynamic Branch Dropdown Options
                    if (branchSelect) {
                        uniqueBranches.forEach(jsonItem => {
                            const item = JSON.parse(jsonItem);
                            const opt = document.createElement('option');
                            opt.value = item.value;
                            opt.textContent = item.display;
                            branchSelect.appendChild(opt);
                        });
                    }

                    // Initialize baseline counters 
                    if (totalLogsIndicator) totalLogsIndicator.textContent = rows.length;

                    // 2. Multivariable Roster Processing Pipeline Logic Routine
                    function evaluateRosterMatrixFilters() {
                        const query = omniInput ? omniInput.value.toLowerCase().trim() : '';
                        const targetRole = roleSelect ? roleSelect.value : '';
                        const targetBranch = branchSelect ? branchSelect.value : '';

                        let visibleCount = 0;

                        rows.forEach(row => {
                            const indexPayload = row.getAttribute('data-search-index') || '';
                            const roleState = row.getAttribute('data-role-state') || '';
                            const branchState = row.getAttribute('data-branch-state') || '';

                            // Matrix conditional validations
                            const checkSearch = query === '' || indexPayload.includes(query);
                            const checkRole = targetRole === '' || roleState === targetRole;
                            const checkBranch = targetBranch === '' || branchState === targetBranch;

                            if (checkSearch && checkRole && checkBranch) {
                                row.classList.remove('d-none');
                                visibleCount++;
                            } else {
                                row.classList.add('d-none');
                            }
                        });

                        // 3. Update Real-Time Counter Telemetry Interface
                        if (visibleLogsIndicator) visibleLogsIndicator.textContent = visibleCount;

                        if (rows.length > 0 && zeroMatchRow) {
                            if (visibleCount === 0) {
                                zeroMatchRow.classList.remove('d-none');
                            } else {
                                zeroMatchRow.classList.add('d-none');
                            }
                        }
                    }

                    // Attach Event Matrix Core Observers
                    if (omniInput) omniInput.addEventListener('input', evaluateRosterMatrixFilters);
                    if (roleSelect) roleSelect.addEventListener('change', evaluateRosterMatrixFilters);
                    if (branchSelect) branchSelect.addEventListener('change', evaluateRosterMatrixFilters);

                    // Bootstrap execution logic pass on template mount load
                    evaluateRosterMatrixFilters();
                });

                function togglePasswordVisibility(fieldId, btnElement) {
                    const passwordInput = document.getElementById(fieldId);
                    const icon = btnElement.querySelector('i');

                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    }
                }
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