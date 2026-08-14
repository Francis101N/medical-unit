<?php

/**
 * Super-Admin Drug Stock Allocation Dispatch Center
 */
/** @var mysqli $conn */
include('db.php');
session_start();

// Enforce Role-Based Privilege Authentication Controls
$user_role = strtolower($_SESSION['role'] ?? '');
if ($user_role !== 'super-admin') {
    $_SESSION['error'] = "Access denied: Administrative clearance parameters missing.";
    header("Location: dashboard.php");
    exit();
}

$currentPage = 'allocate_stock.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allocate Stock - Medical Unit</title>

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
    /* Custom Premium Form Variables & Structural Extensions */
    .pharmacy-card {
        border: none;
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(226, 232, 240, 0.4);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .form-premium-group .form-label {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        color: #475569;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .form-premium-group .form-control,
    .form-premium-group .form-select {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 0.95rem;
        color: #1e293b;
        background-color: #f8fafc;
        transition: all 0.2s ease-in-out;
    }

    .form-premium-group .form-control:focus,
    .form-premium-group .form-select:focus {
        background-color: #ffffff;
        border-color: #435ebe;
        box-shadow: 0 0 0 4px rgba(67, 94, 190, 0.12);
        outline: none;
    }

    /* Informational Icon Vector Badge Containers */
    .info-icon-wrapper {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: rgba(67, 94, 190, 0.08);
        color: #435ebe;
        margin-bottom: 16px;
    }

    /* Audit Ledger Micro Badges */
    .badge-action-type {
        padding: 5px 10px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.72rem;
        letter-spacing: 0.02em;
    }

    .badge-type-alloc {
        background-color: #e0f2fe;
        color: #0369a1;
    }

    .badge-type-disp {
        background-color: #fef2f2;
        color: #b91c1c;
    }

    /* Interactive Input Group Icons */
    .premium-input-icon {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-right: none;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
        color: #94a3b8;
        padding-left: 16px;
    }

    .premium-input-icon+.form-control {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
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
                            <h3 class="fw-bold text-dark">Allocate Stock</h3>
                            <p class="text-subtitle text-muted">
                                Manage and allocate stock across different branches.
                            </p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Allocate Stock</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <br>

                <!-- Contextual Notifications Layer -->
                <?php
                // Capture messages from URL parameters (redirect pattern) or Session fallback
                $message_text = '';
                $message_type = '';

                if (isset($_GET['status']) && $_GET['status'] === 'success' && !empty($_GET['msg'])) {
                    $message_text = $_GET['msg']; // Note: Allows HTML tags like <strong> passed via urlencode/code
                    $message_type = 'success';
                } elseif (isset($_GET['error']) && !empty($_GET['error'])) {
                    $message_text = urldecode($_GET['error']);
                    $message_type = 'danger';
                } elseif (isset($_SESSION['success']) || isset($_SESSION['error'])) {
                    $message_text = isset($_SESSION['success']) ? $_SESSION['success'] : $_SESSION['error'];
                    $message_type = isset($_SESSION['success']) ? 'success' : 'danger';
                    unset($_SESSION['success'], $_SESSION['error']);
                }
                ?>

                <?php if (!empty($message_text)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
                        <div class="d-flex align-items-center py-1">
                            <i class="bi <?php echo ($message_type === 'success') ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger'; ?> fs-4 me-3"></i>
                            <div class="text-dark fw-medium">
                                <?php
                                // If message came from session, htmlspecialchars it for security. If from URL msg, it safely retains HTML formatting tags like <strong>.
                                echo (isset($_GET['msg']) || isset($_GET['error'])) ? $message_text : htmlspecialchars($message_text);
                                ?>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="page-content">
                    <div class="row g-4">

                        <!-- LEFT COLUMN: Informational Summary & Live Metrics -->
                        <div class="col-12 col-xl-4">
                            <div class="card pharmacy-card p-4 h-100 shadow-sm border-0" style="border-radius: 16px;">
                                <div class="info-icon-wrapper mb-3" style="color: #435ebe;">
                                    <i class="bi bi-shield-check fs-4"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-2">Inventory Safeguards</h5>
                                <p class="text-muted small mb-4">
                                    Stock dispatches process via database transaction sequences. Changes instantly update target branch views and balance local warehouse inventory caps.
                                </p>

                                <hr class="text-muted opacity-25 my-4">

                                <!-- Modern Interactive Real-time Dynamic Card Tracker View -->
                                <div class="bg-light p-3 rounded-4 border border-light-subtle" style="background-color: #f8fafc !important;">
                                    <span class="text-muted small d-block mb-1 font-semibold uppercase tracking-wider" style="font-size:0.7rem; letter-spacing: 0.05em;">LIVE TRACKING SELECTION</span>
                                    <div id="liveTrackingTargetName" class="text-dark fw-bold mb-2" style="font-size: 1.05rem;">No Drug Selected</div>

                                    <div class="row g-2 pt-2 text-center">
                                        <div class="col-6">
                                            <div class="bg-white p-2 rounded-3 border border-light-subtle shadow-3xs">
                                                <span class="text-muted d-block small mb-1" style="font-size: 0.72rem;">Selected Code</span>
                                                <span id="liveTrackingTargetCode" class="fw-bold text-secondary font-monospace small">—</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-white p-2 rounded-3 border border-light-subtle shadow-3xs">
                                                <span class="text-muted d-block small mb-1" style="font-size: 0.72rem;">Form Factor</span>
                                                <span id="liveTrackingTargetForm" class="fw-bold text-secondary small">—</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-white p-2 rounded-3 border border-light-subtle shadow-3xs">
                                                <span class="text-muted d-block small mb-1" style="font-size: 0.72rem;">Strength</span>
                                                <span id="liveTrackingTargetStrength" class="fw-bold text-dark small">—</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-white p-2 rounded-3 border border-light-subtle shadow-3xs">
                                                <span class="text-muted d-block small mb-1" style="font-size: 0.72rem;">Available Master</span>
                                                <span id="liveTrackingTargetQty" class="fw-bold text-primary font-monospace small">—</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Premium Core Form Interface -->
                        <div class="col-12 col-xl-8">
                            <div class="card pharmacy-card p-4 shadow-sm border-0" style="border-radius: 16px;">
                                <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom border-light">Allocation Specification Matrix</h5>

                                <form action="process_allocation.php" method="POST" id="premiumAllocationForm">
                                    <div class="row g-4">

                                        <!-- 1. Drug Field Dropdown Layer -->
                                        <div class="col-12 form-premium-group">
                                            <label for="drug_id" class="form-label fw-semibold text-secondary small uppercase">Pharmaceutical Agent Asset</label>
                                            <select class="form-select" id="drug_id" name="drug_id" required style="border-radius: 10px; padding: 0.6rem 1rem;">
                                                <option value="" disabled selected hidden>Select corporate master catalog allocation asset...</option>
                                                <?php
                                                $drugs = $conn->query("SELECT id, drug_name, drug_code, strength, quantity, dosage_form FROM drugs_master ORDER BY drug_name ASC");
                                                while ($drug = $drugs->fetch_assoc()) {
                                                    $strength_val = !empty($drug['strength']) ? htmlspecialchars($drug['strength']) : 'N/A';
                                                    $val_string = htmlspecialchars($drug['drug_name']) . ' ' . $strength_val . ' (' . htmlspecialchars($drug['dosage_form']) . ')';
                                                    echo '<option value="' . intval($drug['id']) . '" 
                                            data-code="' . htmlspecialchars($drug['drug_code']) . '" 
                                            data-form="' . htmlspecialchars($drug['dosage_form']) . '" 
                                            data-name="' . htmlspecialchars($drug['drug_name']) . '"
                                            data-strength="' . $strength_val . '"
                                            data-master-qty="' . intval($drug['quantity']) . '">
                                            ' . $val_string . ' — Available: [' . number_format($drug['quantity']) . ']
                                          </option>';
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- 2. Branch Destination Field Layer -->
                                        <div class="col-12 col-md-6 form-premium-group">
                                            <label for="branch_id" class="form-label fw-semibold text-secondary small uppercase">Destination Receiving Branch</label>
                                            <select class="form-select" id="branch_id" name="branch_id" required style="border-radius: 10px; padding: 0.6rem 1rem;">
                                                <option value="" disabled selected hidden>Select targeted network node...</option>
                                                <?php
                                                $branches = $conn->query("SELECT id, branch_name FROM branches ORDER BY branch_name ASC");
                                                while ($branch = $branches->fetch_assoc()) {
                                                    echo '<option value="' . intval($branch['id']) . '">' . htmlspecialchars($branch['branch_name']) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- 3. Count Input Box Layer -->
                                        <div class="col-12 col-md-6 form-premium-group">
                                            <label for="quantity" class="form-label fw-semibold text-secondary small uppercase">Allocation Volume (Units)</label>
                                            <div class="input-group">
                                                <span class="input-group-text premium-input-icon" style="background-color: #f8fafc; border-right: none; border-radius: 10px 0 0 10px;"><i class="bi bi-box-seam"></i></span>
                                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" required placeholder="Select a drug first" disabled style="border-radius: 0 10px 10px 0; padding: 0.6rem 1rem;">
                                            </div>
                                            <div class="form-text text-muted small id-limits-caption"></div>
                                        </div>

                                        <!-- Form Submission Action Segment Buttons Layout -->
                                        <div class="col-12 text-end pt-3">
                                            <a href="branch_drugs.php" class="btn btn-light px-4 py-2.5 me-2 fw-medium" style="border-radius: 10px; border: 1px solid #cbd5e1; color:#64748b;">Cancel</a>
                                            <button type="submit" class="btn btn-primary px-5 py-2.5 fw-semibold" style="border-radius: 10px; background-color: #435ebe; border:none; box-shadow: 0 4px 12px rgba(67, 94, 190, 0.25);">
                                                Confirm Stock Dispatch
                                            </button>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>

                    <!-- BOTTOM ROW: Streamlined Dynamic Inline Micro-Audit Ledger Section -->
                    <div class="row mt-4">
                        <div class="col-12">

                            <!-- Filter & Search Toolbar Container -->
                            <div class="card border-0 shadow-sm mb-3" style="border-radius: 16px;">
                                <div class="card-body p-3">
                                    <div class="row g-3 align-items-center justify-content-between">

                                        <!-- Real-time Text Filter Box -->
                                        <div class="col-12 col-md-6 col-lg-5">
                                            <div class="input-group dashboard-search-group" style="border-radius: 10px; overflow: hidden; border: 1px solid #e2e8f0;">
                                                <span class="input-group-text bg-white border-0 pe-2 ps-3 text-muted">
                                                    <i class="bi bi-search" style="color: #94a3b8;"></i>
                                                </span>
                                                <input type="text" id="omniLogSearch" class="form-control border-0 bg-white py-2 text-dark text-sm" placeholder="Filter logs by branch, drug name, or notes...">
                                            </div>
                                        </div>

                                        <!-- Branch Filter Dropdown Selector -->
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <select id="logBranchFilter" class="form-select form-select-sm py-2" style="border-radius: 10px;">
                                                <option value="">All Destinations</option>
                                                <?php
                                                $branches_filter = $conn->query("SELECT branch_name FROM branches ORDER BY branch_name ASC");
                                                while ($b_row = $branches_filter->fetch_assoc()) {
                                                    echo '<option value="' . htmlspecialchars(strtolower(trim($b_row['branch_name']))) . '">' . htmlspecialchars($b_row['branch_name']) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- Log Metric Counter -->
                                        <div class="col-12 col-lg-3 text-lg-end">
                                            <div class="d-inline-flex align-items-center px-3 py-2 bg-light border-0" style="border-radius: 10px;">
                                                <span class="text-secondary fw-semibold small">
                                                    Matches: <strong id="matchedLogsCount" class="text-dark">0</strong> / <span id="totalLogsCount">0</span>
                                                </span>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Ledger Master Workspace -->
                            <div class="card pharmacy-card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
                                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">Recent Operational Allocation Dispatches</h5>
                                    <span class="badge bg-light text-secondary border px-2 py-1 small">Audit Footprint Ledger</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle table-hover mb-0" id="allocationLogTable" style="font-size:0.88rem; width:100%;">
                                        <thead class="bg-light-subtle text-muted" style="font-size: 0.8rem; text-transform: uppercase;">
                                            <tr>
                                                <th class="ps-4">Timestamp</th>
                                                <th>Branch Destination</th>
                                                <th>Pharmaceutical Metric</th>
                                                <th>Vector Type</th>
                                                <th>Log Count</th>
                                                <th class="pe-4">Operation Audit Footnote</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $logs_query = "
                                SELECT l.*, dm.drug_name, dm.strength, b.branch_name 
                                FROM drugs_stock_logs l
                                JOIN drugs_master dm ON l.drug_id = dm.id
                                JOIN branches b ON l.branch_id = b.id
                                WHERE l.transaction_type = 'allocation'
                                ORDER BY l.id DESC LIMIT 50
                            ";
                                            $logs_res = $conn->query($logs_query);
                                            $total_logs = 0;
                                            if ($logs_res && $logs_res->num_rows > 0):
                                                $total_logs = $logs_res->num_rows;
                                                while ($log = $logs_res->fetch_assoc()):
                                                    $log_strength = !empty($log['strength']) ? ' ' . $log['strength'] : '';
                                                    $full_drug_title = $log['drug_name'] . $log_strength;

                                                    $search_payload = strtolower(implode(' ', array_filter([
                                                        $log['created_at'],
                                                        $log['branch_name'],
                                                        $full_drug_title,
                                                        $log['notes']
                                                    ])));
                                            ?>
                                                    <tr class="searchable-log-row"
                                                        data-search-index="<?php echo htmlspecialchars($search_payload); ?>"
                                                        data-branch-node="<?php echo htmlspecialchars(strtolower(trim($log['branch_name']))); ?>">
                                                        <td class="ps-4 text-muted"><small><?php echo htmlspecialchars($log['created_at']); ?></small></td>
                                                        <td><span class="fw-semibold text-dark"><?php echo htmlspecialchars($log['branch_name']); ?></span></td>
                                                        <td><span class="text-secondary fw-medium"><?php echo htmlspecialchars($full_drug_title); ?></span></td>
                                                        <td>
                                                            <span class="badge bg-primary-subtle text-primary border border-primary- Normandy font-monospace small" style="font-size: 0.75rem; padding: 0.25em 0.6em;">ALLOCATION</span>
                                                        </td>
                                                        <td class="fw-bold text-success">+<?php echo number_format($log['quantity']); ?></td>
                                                        <td class="text-muted pe-4"><small><?php echo htmlspecialchars($log['notes']); ?></small></td>
                                                    </tr>
                                                <?php
                                                endwhile;
                                            else:
                                                ?>
                                                <tr class="empty-logs-fallback">
                                                    <td colspan="6" class="text-center py-5 text-muted font-medium">
                                                        <i class="bi bi-folder-x fs-3 d-block opacity-50 mb-2"></i>
                                                        No recent asset allocations found recorded inside systemic track chains.
                                                    </td>
                                                </tr>
                                            <?php endif; ?>

                                            <tr id="jsZeroLogsFallback" class="d-none">
                                                <td colspan="6" class="text-center py-5 text-muted bg-light-subtle fw-medium">
                                                    No transaction logs found matching your criteria.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <footer>
                        <div class="footer clearfix mb-0 text-muted px-4 mt-5">
                            <div class="float-start">
                                <p>2026 &copy; Medical Management System</p>
                            </div>
                        </div>
                    </footer>
                </div>

                <!-- Dynamic Tracking, Cap Safety, and Engine Matching Script Block -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // --- PART 1: UI Allocation Limit Guards & Live View Triggers ---
                        const drugSelect = document.getElementById('drug_id');
                        const quantityInput = document.getElementById('quantity');
                        const limitsCaption = document.querySelector('.id-limits-caption');

                        const liveName = document.getElementById('liveTrackingTargetName');
                        const liveCode = document.getElementById('liveTrackingTargetCode');
                        const liveForm = document.getElementById('liveTrackingTargetForm');
                        const liveStrength = document.getElementById('liveTrackingTargetStrength');
                        const liveQty = document.getElementById('liveTrackingTargetQty');

                        if (drugSelect) {
                            drugSelect.addEventListener('change', function() {
                                const selectedOpt = this.options[this.selectedIndex];

                                const name = selectedOpt.getAttribute('data-name') || 'No Drug Selected';
                                const code = selectedOpt.getAttribute('data-code') || '—';
                                const form = selectedOpt.getAttribute('data-form') || '—';
                                const strength = selectedOpt.getAttribute('data-strength') || '—';
                                const maxQty = parseInt(selectedOpt.getAttribute('data-master-qty') || '0', 10);

                                // Populate Interactive Tracking Dashboard
                                liveName.textContent = name;
                                liveCode.textContent = code;
                                liveForm.textContent = form;
                                liveStrength.textContent = strength;
                                liveQty.textContent = maxQty.toLocaleString();

                                // Handle Input Lock Caps based on actual system balances
                                if (maxQty > 0) {
                                    quantityInput.disabled = false;
                                    quantityInput.max = maxQty;
                                    quantityInput.placeholder = `Max ${maxQty}`;
                                    limitsCaption.innerHTML = `<span class="text-success"><i class="bi bi-info-circle-fill"></i> Maximum dispensable dispatch volume ceiling is ${maxQty} units.</span>`;
                                } else {
                                    quantityInput.disabled = true;
                                    quantityInput.value = '';
                                    quantityInput.placeholder = 'Out of stock';
                                    limitsCaption.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Master warehouse volume depleted. Allocation restricted.</span>`;
                                }
                            });
                        }

                        // --- PART 2: Client-Side Ledger Filter Engine ---
                        const logSearch = document.getElementById('omniLogSearch');
                        const branchFilter = document.getElementById('logBranchFilter');
                        const logRows = document.querySelectorAll('.searchable-log-row');
                        const matchedMetric = document.getElementById('matchedLogsCount');
                        const totalMetric = document.getElementById('totalLogsCount');
                        const zeroFallback = document.getElementById('jsZeroLogsFallback');
                        const defaultFallback = document.querySelector('.empty-logs-fallback');

                        const totalLogsCount = logRows.length;
                        if (totalMetric) totalMetric.textContent = totalLogsCount;
                        if (matchedMetric) matchedMetric.textContent = totalLogsCount;

                        function filterLogs() {
                            if (totalLogsCount === 0 || defaultFallback) return;

                            const query = logSearch ? logSearch.value.toLowerCase().trim() : '';
                            const branch = branchFilter ? branchFilter.value.toLowerCase().trim() : '';
                            let matchedCount = 0;

                            logRows.forEach(row => {
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

                        if (logSearch) logSearch.addEventListener('input', filterLogs);
                        if (branchFilter) branchFilter.addEventListener('change', filterLogs);
                    });
                </script>
            </div>

            <script src="assets/js/bootstrap.bundle.min.js"></script>
            <script src="assets/js/main.js"></script>


</body>

</html>