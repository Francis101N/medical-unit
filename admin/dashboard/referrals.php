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

// 1. Handle Delete Request (Decoding base64 ID)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $decoded_id = base64_decode($_GET['id'], true);

    if ($decoded_id !== false && is_numeric($decoded_id)) {
        $delete_id = intval($decoded_id);

        $del_stmt = $conn->prepare("DELETE FROM referral_logs WHERE id = ? LIMIT 1");
        if ($del_stmt) {
            $del_stmt->bind_param("i", $delete_id);
            if ($del_stmt->execute()) {
                $del_stmt->close();
                header("Location: referrals.php?status=success&msg=" . urlencode("Referral record deleted successfully."));
                exit();
            } else {
                $del_stmt->close();
                header("Location: referrals.php?error=" . urlencode("Failed to delete the referral record."));
                exit();
            }
        }
    } else {
        header("Location: referrals.php?error=" . urlencode("Invalid referral record identifier."));
        exit();
    }
}

// 2. Authentication & Role Clearance Guard
$user_role = strtolower($_SESSION['role'] ?? '');
if (empty($user_role)) {
    header("Location: login.php");
    exit();
}

// Determine User Scope
$is_super_admin = ($user_role === 'super-admin');

// Pull the branch value from the session
$user_branch_val = $_SESSION['branch'] ?? '';
$logged_branch_name = '';

if (!$is_super_admin) {
    if (is_numeric($user_branch_val)) {
        $b_lookup = $conn->prepare("SELECT branch_name FROM branches WHERE id = ? LIMIT 1");
        if ($b_lookup) {
            $b_lookup->bind_param("i", $user_branch_val);
            $b_lookup->execute();
            $b_res = $b_lookup->get_result()->fetch_assoc();
            if ($b_res) {
                $logged_branch_name = trim($b_res['branch_name']);
            }
            $b_lookup->close();
        }
    } else {
        $logged_branch_name = trim($user_branch_val);
    }

    if (empty($logged_branch_name)) {
        header("Location: login.php?error=" . urlencode("Branch node assignment mapping failed from session profile."));
        exit();
    }
}

$selected_branch = isset($_GET['branch']) ? trim($_GET['branch']) : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referrals - Medical Unit</title>

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

    /* Staff Passport Thumbnail */
    .staff-avatar-sm {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e2e8f0;
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
</style>

<body>
    <div id="app">
        <?php include('./inc/side-nav.php'); ?>
        <div id="main">

            <div class="page-heading">
                <div class="container-fluid py-4">

                    <!-- Page Header -->
                    <div class="row mb-4 align-items-center">
                        <div class="col-12 col-md-6">
                            <h3 class="fw-bold text-dark mb-1">
                                <?php echo $is_super_admin ? "Global Medical Referral Logs" : "Branch Medical Referrals (" . htmlspecialchars($logged_branch_name) . ")"; ?>
                            </h3>
                            <p class="text-muted small mb-0">Real-time oversight of issued staff medical referral letters, codes, and records.</p>
                        </div>
                        <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
                            <a href="referral-letter.php" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm" style="border-radius: 10px; background-color: #435ebe; border:none;">
                                <i class="bi bi-file-medical-fill me-2"></i> Create New Referral
                            </a>
                        </div>
                    </div>

                    <!-- Contextual Notifications Layer -->
                    <?php
                    $message_text = '';
                    $message_type = '';
                    if (isset($_GET['status']) && $_GET['status'] === 'success' && !empty($_GET['msg'])) {
                        $message_text = $_GET['msg'];
                        $message_type = 'success';
                    } elseif (isset($_GET['error']) && !empty($_GET['error'])) {
                        $message_text = urldecode($_GET['error']);
                        $message_type = 'danger';
                    }
                    ?>
                    <?php if (!empty($message_text)): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
                            <div class="d-flex align-items-center py-1">
                                <i class="bi <?php echo ($message_type === 'success') ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger'; ?> fs-4 me-3"></i>
                                <div class="text-dark fw-medium">
                                    <?php echo $message_text; ?>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Filter & Search Toolbar Container -->
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-body p-3">
                            <div class="row g-3 align-items-center justify-content-between table-filter-bar">
                                <div class="col-12 col-md-6 col-lg-<?php echo $is_super_admin ? '5' : '8'; ?>">
                                    <div class="input-group dashboard-search-group" style="border-radius: 10px; overflow: hidden; border: 1px solid #e2e8f0;">
                                        <span class="input-group-text bg-white border-0 pe-2 ps-3 text-muted">
                                            <i class="bi bi-search" style="color: #94a3b8;"></i>
                                        </span>
                                        <input type="text" id="referralSearchInput" class="form-control border-0 bg-white py-2 text-dark text-sm" placeholder="Filter referrals by staff name, ID, email, department, company, branch...">
                                    </div>
                                </div>

                                <?php if ($is_super_admin): ?>
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <select id="referralBranchFilter" class="form-select form-select-sm py-2" style="border-radius: 10px;">
                                            <option value="">All Network Nodes (Branches)</option>
                                            <?php
                                            $branches_filter = $conn->query("SELECT DISTINCT branch_name FROM branches ORDER BY branch_name ASC");
                                            while ($b_row = $branches_filter->fetch_assoc()) {
                                                $b_name_val = $b_row['branch_name'];
                                                $selected = (strcasecmp($selected_branch, $b_name_val) === 0) ? 'selected' : '';
                                                echo '<option value="' . htmlspecialchars(strtolower(trim($b_name_val))) . '" ' . $selected . '>' . htmlspecialchars($b_name_val) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                <?php endif; ?>

                                <div class="col-12 col-lg-3 text-lg-end">
                                    <div class="d-inline-flex align-items-center px-3 py-2 bg-light border-0" style="border-radius: 10px;">
                                        <span class="text-secondary fw-semibold small">
                                            Referral Logs: <strong id="matchedReferralCount" class="text-dark">0</strong> / <span id="totalReferralCount">0</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Referral Master Table Workspace -->
                    <div class="card pharmacy-card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
                        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">
                                <?php echo $is_super_admin ? "Global Referral History Matrix" : "Assigned Branch Referral Records"; ?>
                            </h5>
                            <span class="badge bg-light text-secondary border px-2 py-1 small">Live Records</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle table-hover mb-0" id="referralLogsTable" style="font-size:0.88rem; width:100%;">
                                <thead class="bg-light-subtle text-muted" style="font-size: 0.8rem; text-transform: uppercase;">
                                    <tr>
                                        <th class="ps-4">Passport</th>
                                        <th>Staff ID</th>
                                        <th>Staff Name</th>
                                        <th>Email</th>
                                        <th>Gender</th>
                                        <th>Department</th>
                                        <th>Company</th>
                                        <th>Branch</th>
                                        <th>Serial ID</th>
                                        <th>Reference Code</th>
                                        <th class="pe-4 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Added s.email to the select query fields
                                    if ($is_super_admin) {
                                        $referral_query = "SELECT r.*, s.staff_id, s.fullname AS staff_fullname, s.email, s.gender, s.department, s.company, s.passport AS staff_passport, b.branch_name 
                       FROM referral_logs r 
                       LEFT JOIN staffs s ON r.staff_name = s.fullname 
                       LEFT JOIN branches b ON s.branch_id = b.id 
                       ORDER BY r.id DESC";
                                        $stmt = $conn->prepare($referral_query);
                                    } else {
                                        // Cleaned up to filter only by staff branch association (branch name or branch ID)
                                        $referral_query = "SELECT r.*, s.staff_id, s.fullname AS staff_fullname, s.email, s.gender, s.department, s.company, s.passport AS staff_passport, b.branch_name 
                       FROM referral_logs r 
                       LEFT JOIN staffs s ON r.staff_name = s.fullname 
                       LEFT JOIN branches b ON s.branch_id = b.id 
                       WHERE b.branch_name = ? OR s.branch_id = ? 
                       ORDER BY r.id DESC";
                                        $stmt = $conn->prepare($referral_query);
                                        $branch_id_val = is_numeric($user_branch_val) ? intval($user_branch_val) : 0;
                                        $stmt->bind_param("si", $logged_branch_name, $branch_id_val);
                                    }

                                    $stmt->execute();
                                    $referral_res = $stmt->get_result();
                                    $total_records = 0;

                                    if ($referral_res && $referral_res->num_rows > 0):
                                        $total_records = $referral_res->num_rows;
                                        while ($row = $referral_res->fetch_assoc()):
                                            $referral_id_encoded = base64_encode($row['id']);
                                            $staff_name  = $row['staff_fullname'] ?? $row['staff_name'] ?? 'N/A';
                                            $staff_id    = $row['staff_id'] ?? 'N/A';
                                            $email       = $row['email'] ?? 'N/A';
                                            $gender      = $row['gender'] ?? 'N/A';
                                            $department  = $row['department'] ?? 'N/A';
                                            $company     = $row['company'] ?? 'N/A';
                                            $branch_name = $row['branch_name'] ?? $row['branch'] ?? 'N/A';
                                            $serial_id   = $row['serial_id'] ?? '';
                                            $ref_code    = $row['ref_code'] ?? '';

                                            // Pull from the aliased staffs.passport column with fallback
                                            $passport_val = trim($row['staff_passport'] ?? '');
                                            $passport     = !empty($passport_val) ? $passport_val : 'assets/images/faces/1.jpg';

                                            $search_index = strtolower(implode(' ', array_filter([
                                                $branch_name,
                                                $staff_name,
                                                $staff_id,
                                                $email,
                                                $gender,
                                                $department,
                                                $company,
                                                $serial_id,
                                                $ref_code
                                            ])));
                                    ?>
                                            <tr class="searchable-referral-row"
                                                data-search-index="<?php echo htmlspecialchars($search_index); ?>"
                                                data-branch-node="<?php echo htmlspecialchars(strtolower(trim($branch_name))); ?>">
                                                <td class="ps-4">
                                                    <img src="uploads/<?php echo htmlspecialchars($passport); ?>" alt="Passport" class="staff-avatar-sm">
                                                </td>
                                                <td><span class="font-monospace text-primary fw-semibold"><?php echo htmlspecialchars($staff_id); ?></span></td>
                                                <td><span class="fw-semibold text-dark"><?php echo htmlspecialchars($staff_name); ?></span></td>
                                                <td><span class="text-muted"><?php echo htmlspecialchars($email); ?></span></td>
                                                <td><span class="text-secondary"><?php echo htmlspecialchars($gender); ?></span></td>
                                                <td><span class="text-secondary"><?php echo htmlspecialchars($department); ?></span></td>
                                                <td><span class="text-secondary"><?php echo htmlspecialchars($company); ?></span></td>
                                                <td><span class="fw-bold text-dark"><?php echo htmlspecialchars($branch_name); ?></span></td>
                                                <td><span class="font-monospace text-secondary small"><?php echo htmlspecialchars($serial_id); ?></span></td>
                                                <td><span class="badge bg-light text-secondary border"><?php echo htmlspecialchars($ref_code); ?></span></td>
                                                <td class="pe-4 text-end">
                                                    <div class="action-btns justify-content-end">
                                                        <a href="view-referral.php?id=<?php echo $referral_id_encoded; ?>" class="btn btn-sm btn-outline-primary btn-icon-sm" title="View Referral Details">
                                                            <i class="bi bi-eye"></i> View
                                                        </a>
                                                        <a href="referrals.php?action=delete&id=<?php echo $referral_id_encoded; ?>" class="btn btn-sm btn-outline-danger btn-icon-sm" onclick="return confirm('Are you sure you want to delete this referral record?');" title="Delete Record">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                        endwhile;
                                    else:
                                        ?>
                                        <tr class="empty-referral-fallback">
                                            <td colspan="11" class="text-center py-5 text-muted font-medium">
                                                <i class="bi bi-folder-x fs-3 d-block opacity-50 mb-2"></i>
                                                No medical referral logs have been recorded for this branch yet.
                                            </td>
                                        </tr>
                                    <?php endif;
                                    if (isset($stmt)) $stmt->close();
                                    ?>

                                    <tr id="jsZeroReferralFallback" class="d-none">
                                        <td colspan="11" class="text-center py-5 text-muted bg-light-subtle fw-medium">
                                            No referral records match your filter criteria.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- Client-Side Filter Engine Script -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const searchInput = document.getElementById('referralSearchInput');
                        const branchFilter = document.getElementById('referralBranchFilter');
                        const referralRows = document.querySelectorAll('.searchable-referral-row');
                        const matchedMetric = document.getElementById('matchedReferralCount');
                        const totalMetric = document.getElementById('totalReferralCount');
                        const zeroFallback = document.getElementById('jsZeroReferralFallback');
                        const defaultFallback = document.querySelector('.empty-referral-fallback');

                        const totalCount = referralRows.length;
                        if (totalMetric) totalMetric.textContent = totalCount;
                        if (matchedMetric) matchedMetric.textContent = totalCount;

                        function filterReferrals() {
                            if (totalCount === 0 || defaultFallback) return;

                            const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
                            const branch = branchFilter ? branchFilter.value.toLowerCase().trim() : '';
                            let matchedCount = 0;

                            referralRows.forEach(row => {
                                const index = row.getAttribute('data-search-index') || '';
                                const node = row.getAttribute('data-branch-node') || '';

                                const matchesQuery = query === '' || index.includes(query);
                                const matchesBranch = branch === '' || node === branch;

                                if (matchesQuery && matchesBranch) {
                                    row.classList.remove('d-none');
                                    matchedCount++;
                                } else {
                                    row.classList.add('d-none');
                                }
                            });

                            if (matchedMetric) matchedMetric.textContent = matchedCount;

                            if (matchedCount === 0) {
                                if (zeroFallback) zeroFallback.classList.remove('d-none');
                            } else {
                                if (zeroFallback) zeroFallback.classList.add('d-none');
                            }
                        }

                        if (searchInput) searchInput.addEventListener('input', filterReferrals);
                        if (branchFilter) branchFilter.addEventListener('change', filterReferrals);
                    });
                </script>
            </div>

            <?php include('./inc/footer.php'); ?>
        </div>
    </div>
    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>