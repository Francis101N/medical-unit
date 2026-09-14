<?php

/**
 * Super-Admin Drug Stock Allocation Dispatch Center
 */
/** @var mysqli $conn */
include('db.php');
session_start();

// Enforce Role-Based Privilege Authentication Controls
$user_role = strtolower($_SESSION['role'] ?? '');
// if ($user_role !== 'super-admin') {
//     $_SESSION['error'] = "Access denied: Administrative clearance parameters missing.";
//     header("Location: dashboard.php");
//     exit();
// }

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
    <link rel="icon" type="image/x-icon" href="./assets/images/favicon.png">
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
                                Manage and allocate stock across different outreach locations.
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
                $display_msg = $msg ?? $_SESSION['msg'] ?? $_GET['msg'] ?? $_GET['error'] ?? null;
                $display_type = $msg_type ?? $_SESSION['msg_type'] ?? (isset($_GET['error']) ? 'danger' : 'success');

                if (isset($_SESSION['msg'])) {
                    unset($_SESSION['msg'], $_SESSION['msg_type']);
                }

                if (!empty($display_msg)) {
                    $icon_class = match ($display_type) {
                        'danger'  => 'bi-exclamation-triangle-fill text-danger',
                        'warning' => 'bi-exclamation-circle-fill text-warning',
                        'info'    => 'bi-info-circle-fill text-info',
                        default   => 'bi-check-circle-fill text-success',
                    };
                ?>
                    <div class="alert alert-<?php echo htmlspecialchars($display_type); ?> alert-dismissible fade show mb-4 shadow-sm border-0" role="alert" style="border-radius: 12px;">
                        <div class="d-flex align-items-center">
                            <div class="alert-icon-wrapper me-3">
                                <i class="bi <?php echo $icon_class; ?> fs-4"></i>
                            </div>
                            <div class="alert-message-text text-dark">
                                <?php echo (isset($_GET['msg']) || isset($_GET['error'])) ? $display_msg : htmlspecialchars($display_msg); ?>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php } ?>

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
                                    Stock dispatches process via database transaction sequences. Changes instantly update target outreach location views and balance local warehouse inventory caps.
                                </p>

                                <hr class="text-muted opacity-25 my-4">

                                <!-- Modern Interactive Real-time Dynamic Card Tracker View -->
                                <div class="bg-light p-3 rounded-4 border border-light-subtle" style="background-color: #f8fafc !important;">
                                    <span class="text-muted small d-block mb-1 font-semibold uppercase tracking-wider" style="font-size:0.7rem; letter-spacing: 0.05em;">LIVE TRACKING SELECTION</span>
                                    <div id="liveTrackingTargetName" class="text-dark fw-bold mb-2" style="font-size: 1.05rem;">No Item Selected</div>

                                    <div class="row g-2 pt-2 text-center">
                                        <div class="col-6">
                                            <div class="bg-white p-2 rounded-3 border border-light-subtle shadow-3xs">
                                                <span class="text-muted d-block small mb-1" style="font-size: 0.72rem;">Category</span>
                                                <span id="liveTrackingTargetCategory" class="fw-bold text-secondary small">—</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-white p-2 rounded-3 border border-light-subtle shadow-3xs">
                                                <span class="text-muted d-block small mb-1" style="font-size: 0.72rem;">Unit Type</span>
                                                <span id="liveTrackingTargetUnit" class="fw-bold text-secondary small">—</span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="bg-white p-2 rounded-3 border border-light-subtle shadow-3xs">
                                                <span class="text-muted d-block small mb-1" style="font-size: 0.72rem;">Available Central Quantity</span>
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
                                <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom border-light">Outreach Allocation Specification Matrix</h5>

                                <form action="process_branch_allocation.php" method="POST" id="premiumAllocationForm">
                                    <div class="row g-4">

                                        <!-- 1. Item Field Dropdown Layer -->
                                        <div class="col-12 form-premium-group">
                                            <label for="item_id" class="form-label fw-semibold text-secondary small uppercase">Inventory Asset</label>
                                            <select class="form-select" id="item_id" name="item_id" required style="border-radius: 10px; padding: 0.6rem 1rem;">
                                                <option value="" disabled selected hidden>Select central inventory catalog asset...</option>
                                                <?php
                                                $items = $conn->query("SELECT id, item_name, category, unit, total_quantity FROM outreach_inventory ORDER BY item_name ASC");
                                                while ($item = $items->fetch_assoc()) {
                                                    $val_string = htmlspecialchars($item['item_name']) . ' (' . htmlspecialchars($item['unit']) . ' - ' . htmlspecialchars($item['category']) . ')';
                                                    echo '<option value="' . intval($item['id']) . '" 
                                            data-name="' . htmlspecialchars($item['item_name']) . '" 
                                            data-category="' . htmlspecialchars($item['category']) . '" 
                                            data-unit="' . htmlspecialchars($item['unit']) . '"
                                            data-master-qty="' . intval($item['total_quantity']) . '">
                                            ' . $val_string . ' — Available: [' . number_format($item['total_quantity']) . ']
                                          </option>';
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- 2. Outreach Location Name Field Layer -->
                                        <div class="col-12 col-md-6 form-premium-group">
                                            <label for="branch_name" class="form-label fw-semibold text-secondary small uppercase">Outreach Location Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text premium-input-icon" style="background-color: #f8fafc; border-right: none; border-radius: 10px 0 0 10px;"><i class="bi bi-geo-alt"></i></span>
                                                <select class="form-select" id="branch_name" name="branch_name" required style="border-radius: 0 10px 10px 0; padding: 0.6rem 1rem;">
                                                    <option value="" disabled selected hidden>Select target outreach location...</option>
                                                    <?php
                                                    $locations = $conn->query("SELECT DISTINCT location FROM outreach ORDER BY location ASC");
                                                    while ($loc = $locations->fetch_assoc()) {
                                                        if (!empty($loc['location'])) {
                                                            echo '<option value="' . htmlspecialchars($loc['location']) . '">' . htmlspecialchars($loc['location']) . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- 3. Count Input Box Layer -->
                                        <div class="col-12 col-md-6 form-premium-group">
                                            <label for="allocated_quantity" class="form-label fw-semibold text-secondary small uppercase">Allocation Volume (Units)</label>
                                            <div class="input-group">
                                                <span class="input-group-text premium-input-icon" style="background-color: #f8fafc; border-right: none; border-radius: 10px 0 0 10px;"><i class="bi bi-box-seam"></i></span>
                                                <input type="number" class="form-control" id="allocated_quantity" name="allocated_quantity" min="1" required placeholder="Select an item first" disabled style="border-radius: 0 10px 10px 0; padding: 0.6rem 1rem;">
                                            </div>
                                            <div class="form-text text-muted small id-limits-caption"></div>
                                        </div>

                                        <!-- Form Submission Action Segment Buttons Layout -->
                                        <div class="col-12 text-end pt-3">
                                            <a href="outreach_inventory.php" class="btn btn-light px-4 py-2.5 me-2 fw-medium" style="border-radius: 10px; border: 1px solid #cbd5e1; color:#64748b;">Cancel</a>
                                            <button type="submit" name="allocate_stock" class="btn btn-primary px-5 py-2.5 fw-semibold" style="border-radius: 10px; background-color: #435ebe; border:none; box-shadow: 0 4px 12px rgba(67, 94, 190, 0.25);">
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
                                                <input type="text" id="omniLogSearch" class="form-control border-0 bg-white py-2 text-dark text-sm" placeholder="Filter allocations by location, item name...">
                                            </div>
                                        </div>

                                        <!-- Location Filter Dropdown Selector -->
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <select id="logBranchFilter" class="form-select form-select-sm py-2" style="border-radius: 10px;">
                                                <option value="">All Locations</option>
                                                <?php
                                                $locations_filter = $conn->query("SELECT DISTINCT branch_name FROM branch_allocations ORDER BY branch_name ASC");
                                                while ($l_row = $locations_filter->fetch_assoc()) {
                                                    echo '<option value="' . htmlspecialchars(strtolower(trim($l_row['branch_name']))) . '">' . htmlspecialchars($l_row['branch_name']) . '</option>';
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
                                                <th>Outreach Location</th>
                                                <th>Item Name</th>
                                                <th>Category</th>
                                                <th>Allocated Qty</th>
                                                <th>Allocated By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $logs_query = "
                                SELECT ba.*, oi.item_name, oi.category, oi.unit 
                                FROM branch_allocations ba
                                JOIN outreach_inventory oi ON ba.item_id = oi.id
                                ORDER BY ba.id DESC LIMIT 50
                            ";
                                            $logs_res = $conn->query($logs_query);
                                            $total_logs = 0;
                                            if ($logs_res && $logs_res->num_rows > 0):
                                                $total_logs = $logs_res->num_rows;
                                                while ($log = $logs_res->fetch_assoc()):
                                                    $search_payload = strtolower(implode(' ', array_filter([
                                                        $log['allocation_date'],
                                                        $log['branch_name'],
                                                        $log['item_name'],
                                                        $log['category'],
                                                        $log['allocated_by']
                                                    ])));
                                            ?>
                                                    <tr class="searchable-log-row"
                                                        data-search-index="<?php echo htmlspecialchars($search_payload); ?>"
                                                        data-branch-node="<?php echo htmlspecialchars(strtolower(trim($log['branch_name']))); ?>">
                                                        <td class="ps-4 text-muted"><small><?php echo htmlspecialchars($log['allocation_date']); ?></small></td>
                                                        <td><span class="fw-semibold text-dark"><?php echo htmlspecialchars($log['branch_name']); ?></span></td>
                                                        <td><span class="text-secondary fw-medium"><?php echo htmlspecialchars($log['item_name']); ?></span></td>
                                                        <td><span class="badge bg-light text-primary border"><?php echo htmlspecialchars($log['category']); ?></span></td>
                                                        <td class="fw-bold text-success">+<?php echo number_format($log['allocated_quantity']); ?> <?php echo htmlspecialchars($log['unit']); ?></td>
                                                        <td class="text-muted"><small><?php echo htmlspecialchars($log['allocated_by'] ?? 'Admin'); ?></small></td>
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
                        const itemSelect = document.getElementById('item_id');
                        const quantityInput = document.getElementById('allocated_quantity');
                        const limitsCaption = document.querySelector('.id-limits-caption');

                        const liveName = document.getElementById('liveTrackingTargetName');
                        const liveCategory = document.getElementById('liveTrackingTargetCategory');
                        const liveUnit = document.getElementById('liveTrackingTargetUnit');
                        const liveQty = document.getElementById('liveTrackingTargetQty');

                        if (itemSelect) {
                            itemSelect.addEventListener('change', function() {
                                const selectedOpt = this.options[this.selectedIndex];

                                const name = selectedOpt.getAttribute('data-name') || 'No Item Selected';
                                const category = selectedOpt.getAttribute('data-category') || '—';
                                const unit = selectedOpt.getAttribute('data-unit') || '—';
                                const maxQty = parseInt(selectedOpt.getAttribute('data-master-qty') || '0', 10);

                                // Populate Interactive Tracking Dashboard
                                liveName.textContent = name;
                                liveCategory.textContent = category;
                                liveUnit.textContent = unit;
                                liveQty.textContent = maxQty.toLocaleString();

                                // Handle Input Lock Caps based on actual system balances
                                if (maxQty > 0) {
                                    quantityInput.disabled = false;
                                    quantityInput.max = maxQty;
                                    quantityInput.placeholder = `Max ${maxQty}`;
                                    limitsCaption.innerHTML = `<span class="text-success"><i class="bi bi-info-circle-fill"></i> Maximum dispensable dispatch volume ceiling is ${maxQty.toLocaleString()} units.</span>`;
                                } else {
                                    quantityInput.disabled = true;
                                    quantityInput.value = '';
                                    quantityInput.placeholder = 'Out of stock';
                                    limitsCaption.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Central inventory volume depleted. Allocation restricted.</span>`;
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