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

if (!function_exists('decryptId')) {
    function decryptId($encrypted_value)
    {
        $key = "medical-secret-key";
        $decoded = base64_decode(strtr($encrypted_value, '-_', '+/'));
        $parts = explode('|', $decoded);
        if (count($parts) === 2 && $parts[1] === $key) {
            return $parts[0];
        }
        return false;
    }
}

// Retrieve and validate the branch ID from the query string
$branch_id_raw = $_GET['branch_id'] ?? '';
$branch_id = decryptId($branch_id_raw);

if (!$branch_id) {
    echo "
    <script>
        alert('Invalid or missing branch identifier context.');
        window.location.href='branches.php';
    </script>
    ";
    exit();
}

// Fetch branch details securely
$branch_query = "SELECT id, branch_name FROM branches WHERE id = ? LIMIT 1";
$b_stmt = $conn->prepare($branch_query);
$b_stmt->bind_param("i", $branch_id);
$b_stmt->execute();
$branch_res = $b_stmt->get_result();

if ($branch_res->num_rows === 0) {
    echo "
    <script>
        alert('Branch context not found in database records.');
        window.location.href='branches.php';
    </script>
    ";
    exit();
}

$branch_row = $branch_res->fetch_assoc();
$branch_name = $branch_row['branch_name'];
$b_stmt->close();

// Fetch operational staff records belonging strictly to this specific branch
$log_query = "SELECT smr.*, s.passport FROM staff_medical_records smr LEFT JOIN staffs s ON smr.staff_name = s.fullname WHERE smr.staff_branch = ? ORDER BY smr.id DESC";
$stmt = $conn->prepare($log_query);
$stmt->bind_param("s", $branch_name);
$stmt->execute();
$records_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Details: <?php echo htmlspecialchars($branch_name); ?> - Medical Unit</title>

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

    .table-filter-bar input,
    .table-filter-bar select {
        border: 1px solid var(--border-color);
        border-radius: 6px;
        color: var(--text-main);
        font-size: 0.8rem;
        height: 34px;
    }

    .table-filter-bar input:focus,
    .table-filter-bar select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        outline: none;
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
                <!-- Page Title Header -->
                <div class="page-title mb-4">
                    <div class="row align-items-center g-3">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <div class="d-flex align-items-center gap-3 mb-1">
                                <h3 class="fw-bold text-dark mb-0" style="letter-spacing: -0.5px;"><?php echo htmlspecialchars($branch_name); ?> Records</h3>
                                <span class="badge rounded-pill bg-primary px-3 py-2 shadow-3xs" style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.3px;">
                                    <?php echo isset($records_result) ? $records_result->num_rows : 0; ?> Total Logs
                                </span>
                            </div>
                            <p class="text-subtitle text-muted mb-0" style="font-size: 0.9rem;">
                                Inspecting real-time clinical logs and staff medical tracking entries specifically for <?php echo htmlspecialchars($branch_name); ?>.
                            </p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb mb-0 bg-transparent p-0" style="font-size: 0.85rem;">
                                    <li class="breadcrumb-item"><a href="index.html" class="text-decoration-none text-primary fw-medium">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="branches.php" class="text-decoration-none text-primary fw-medium">Branch Overview</a></li>
                                    <li class="breadcrumb-item active text-muted fw-semibold" aria-current="page"><?php echo htmlspecialchars($branch_name); ?></li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Unified Branch Records View -->
                <div class="branch-tables-container">
                    <!-- Focused Workspace Section Content Header Badge & Filter Row -->
                    <div class="row align-items-center g-3 mb-3">
                        <div class="col-12 col-xl-4">
                            <span class="badge rounded-3 bg-primary text-white py-2 px-3 fw-bold shadow-3xs" style="font-size: 0.85rem; letter-spacing: 0.3px;">
                                Active Context: <?php echo htmlspecialchars($branch_name); ?> Records
                            </span>
                        </div>

                        <!-- Instant Search & Dynamic Filter Control Controls -->
                        <div class="col-12 col-xl-8">
                            <div class="row g-2 justify-content-xl-end table-filter-bar" data-target-table="branchRecordsTable">
                                <!-- Text Omnibox Search -->
                                <div class="col-12 col-sm-4 col-md-5">
                                    <input type="text" class="form-control form-control-sm filter-search-input" placeholder="Search name, company, diagnosis, dept...">
                                </div>
                                <!-- Status Selector Filter -->
                                <div class="col-6 col-sm-4 col-md-3">
                                    <select class="form-select form-select-sm filter-status-select">
                                        <option value="">All Statuses</option>
                                        <option value="open">Open</option>
                                        <option value="under_treatment">Under Treatment</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                </div>
                                <!-- Date Intake Range Filter Selector -->
                                <div class="col-6 col-sm-4 col-md-4">
                                    <input type="date" class="form-control form-control-sm filter-date-input" title="Filter by Intake Date">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Table Construction Node Wrap -->
                    <div class="branch-card m-0">
                        <div class="table-responsive">
                            <table class="modern-table" id="branchRecordsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 70px;">S/N</th>
                                        <th>Staff Profile Name</th>
                                        <th class="text-center" style="width: 90px;">Avatar</th>
                                        <th>Company</th>
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
                                            $company = $row['company'] ?? '';
                                            $department = $row['department'];
                                            $intake_time = $row['intake_time'];
                                            $release_time = $row['release_time'];
                                            $diagnosis = $row['diagnosis'];
                                            $treatment_given = $row['treatment_given'];
                                            $record_status = strtolower($row['record_status']);

                                            if ($record_status === "open") {
                                                $status_class = "badge-status-open";
                                            } elseif ($record_status === "under_treatment") {
                                                $status_class = "badge-status-treatment";
                                            } else {
                                                $status_class = "badge-status-closed";
                                            }

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

                                            // Helper function for encrypting record IDs if needed
                                            if (!function_exists('encryptRecordId')) {
                                                function encryptRecordId($record_id)
                                                {
                                                    $key = "medical-secret-key";
                                                    return rtrim(strtr(base64_encode($record_id . '|' . $key), '+/', '-_'), '=');
                                                }
                                            }
                                    ?>
                                            <!-- Embedded Searchable Metadata Parameters on Row Node -->
                                            <tr class="searchable-row"
                                                data-search-payload="<?php echo htmlspecialchars(strtolower("$staff_name $company $department $diagnosis $treatment_given")); ?>"
                                                data-status="<?php echo $record_status; ?>"
                                                data-intake-date="<?php echo (!empty($intake_time) && $intake_time !== '0000-00-00 00:00:00') ? date('Y-m-d', strtotime($intake_time)) : ''; ?>">

                                                <td><span class="sn-badge">#<?php echo sprintf('%02d', $sn++); ?></span></td>
                                                <td><strong style="color: var(--text-main);"><?php echo htmlspecialchars($staff_name); ?></strong></td>
                                                <td class="text-center">
                                                    <?php if (!empty($passport_src)) { ?>
                                                        <div class="passport-avatar-wrap">
                                                            <img src="<?php echo htmlspecialchars($passport_src); ?>" alt="Passport Avatar" loading="lazy">
                                                        </div>
                                                    <?php } else { ?>
                                                        <div class="passport-avatar-placeholder">N/A</div>
                                                    <?php } ?>
                                                </td>
                                                <td><span class="dept-badge"><?php echo htmlspecialchars($company ? $company : '—'); ?></span></td>
                                                <td><span class="dept-badge"><?php echo htmlspecialchars($department); ?></span></td>
                                                <td><small class="text-muted"><?php echo htmlspecialchars($intake_time); ?></small></td>
                                                <td><small class="text-muted"><?php echo htmlspecialchars($release_time ? $release_time : '—'); ?></small></td>
                                                <td>
                                                    <div class="truncate-field" title="<?php echo htmlspecialchars($diagnosis); ?>">
                                                        <?php echo htmlspecialchars($diagnosis); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="truncate-field text-secondary" title="<?php echo htmlspecialchars($treatment_given); ?>">
                                                        <?php echo htmlspecialchars($treatment_given); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge-status <?php echo $status_class; ?>">
                                                        <?php echo ucwords(str_replace('_', ' ', htmlspecialchars($record_status))); ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-inline-flex gap-2">
                                                        <a href="edit_log.php?id=<?php echo urlencode(encryptRecordId($id)); ?>" class="btn-action-edit">Edit</a>
                                                        <a href="delete_log.php?id=<?php echo urlencode(encryptRecordId($id)); ?>" class="btn-action-delete" onclick="return confirm('Confirm permanent deletion of this clinical log query record?');">Delete</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <tr class="empty-state-fallback-row">
                                            <td colspan="11" class="p-0">
                                                <div class="empty-state-wrapper">
                                                    <div class="small fw-medium">No medical intake tracking entries discovered within this branch context profile.</div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>

                                    <!-- Client-side Dynamic Empty Search Target Notification -->
                                    <tr class="js-empty-search-row d-none">
                                        <td colspan="11" class="p-0">
                                            <div class="empty-state-wrapper py-4 text-center text-muted">
                                                <div class="small fw-semibold">No records match your active search filters.</div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- JavaScript Client-Side Filtering & Search Engine Integration -->
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const filterBar = document.querySelector('.table-filter-bar');
                    if (!filterBar) return;

                    const targetTableId = filterBar.getAttribute('data-target-table');
                    const table = document.getElementById(targetTableId);
                    if (!table) return;

                    const searchInput = filterBar.querySelector('.filter-search-input');
                    const statusSelect = filterBar.querySelector('.filter-status-select');
                    const dateInput = filterBar.querySelector('.filter-date-input');

                    const rows = table.querySelectorAll('tbody tr.searchable-row');
                    const emptyRow = table.querySelector('tbody tr.js-empty-search-row');

                    function applyFilters() {
                        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
                        const statusTerm = statusSelect ? statusSelect.value.toLowerCase().trim() : '';
                        const dateTerm = dateInput ? dateInput.value.trim() : '';

                        let visibleCount = 0;

                        rows.forEach(row => {
                            const searchPayload = row.getAttribute('data-search-payload') || '';
                            const rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
                            const rowDate = row.getAttribute('data-intake-date') || '';

                            const matchesSearch = searchTerm === '' || searchPayload.includes(searchTerm);
                            const matchesStatus = statusTerm === '' || rowStatus === statusTerm;
                            const matchesDate = dateTerm === '' || rowDate === dateTerm;

                            if (matchesSearch && matchesStatus && matchesDate) {
                                row.style.display = '';
                                visibleCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        });

                        // Toggle empty search notification row
                        if (emptyRow) {
                            if (visibleCount === 0 && rows.length > 0) {
                                emptyRow.classList.remove('d-none');
                            } else {
                                emptyRow.classList.add('d-none');
                            }
                        }
                    }

                    if (searchInput) searchInput.addEventListener('input', applyFilters);
                    if (statusSelect) statusSelect.addEventListener('change', applyFilters);
                    if (dateInput) dateInput.addEventListener('change', applyFilters);
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