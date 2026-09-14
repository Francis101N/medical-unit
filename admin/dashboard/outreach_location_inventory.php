<?php

/**
 * Outreach Location Inventory Vault Workspace
 * File: outreach_branch_drugs.php
 */

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

// 1. Authentication & Role Clearance Guard
$user_role = strtolower($_SESSION['role'] ?? '');
if (empty($user_role)) {
    header("Location: login.php");
    exit();
}

// Determine User Scope
$is_super_admin = ($user_role === 'super-admin');

// Pull the location value from the session (your login script stores outreach location in $_SESSION['location'] or $_SESSION['branch'])
$user_location_val = $_SESSION['location'] ?? $_SESSION['branch'] ?? '';

// Final security check for non-super-admin users if location is missing
if (!$is_super_admin && empty($user_location_val)) {
    header("Location: login.php?error=" . urlencode("Outreach location assignment mapping failed from session profile."));
    exit();
}

// Optional Location Filter parameter (Super admin can use this, regular users are locked to their own location)
$selected_location = isset($_GET['location']) ? trim($_GET['location']) : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Outreach Location Inventory Vaults - Medical Unit</title>

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
</style>

<body>
    <div id="app">
        <?php include('./inc/side-nav.php'); ?>
        <div id="main">

            <div class="page-heading">
                <div class="container-fluid py-4">

                    <div class="row mb-4 align-items-center">
                        <div class="col-12 col-md-6">
                            <h3 class="fw-bold text-dark mb-1">
                                <?php echo $is_super_admin ? "Global Outreach Location Inventory Vaults" : "Outreach Location Inventory Vault"; ?>
                            </h3>
                            <p class="text-muted small mb-0">Real-time oversight of outreach stock distribution, current operational balances, and location allocations.</p>
                        </div>
                        <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
                            <?php if ($is_super_admin): ?>
                                <a href="allocate_stock.php" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm" style="border-radius: 10px; background-color: #435ebe; border:none;">
                                    <i class="bi bi-box-arrow-right me-2"></i> Allocate New Stock
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

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
                                    <?php echo htmlspecialchars($message_text); ?>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-body p-3">
                            <div class="row g-3 align-items-center justify-content-between">

                                <div class="col-12 col-md-6 col-lg-<?php echo $is_super_admin ? '5' : '8'; ?>">
                                    <div class="input-group dashboard-search-group" style="border-radius: 10px; overflow: hidden; border: 1px solid #e2e8f0;">
                                        <span class="input-group-text bg-white border-0 pe-2 ps-3 text-muted">
                                            <i class="bi bi-search" style="color: #94a3b8;"></i>
                                        </span>
                                        <input type="text" id="vaultSearchInput" class="form-control border-0 bg-white py-2 text-dark text-sm" placeholder="Filter vault inventory by item name, category...">
                                    </div>
                                </div>

                                <?php if ($is_super_admin): ?>
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <select id="vaultBranchFilter" class="form-select form-select-sm py-2" style="border-radius: 10px;">
                                            <option value="">All Outreach Locations</option>
                                            <?php
                                            $loc_filter = $conn->query("SELECT DISTINCT location FROM outreach ORDER BY location ASC");
                                            while ($l_row = $loc_filter->fetch_assoc()) {
                                                if (!empty($l_row['location'])) {
                                                    $selected = ($selected_location === $l_row['location']) ? 'selected' : '';
                                                    echo '<option value="' . htmlspecialchars(strtolower(trim($l_row['location']))) . '" ' . $selected . '>' . htmlspecialchars($l_row['location']) . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                <?php endif; ?>

                                <div class="col-12 col-lg-3 text-lg-end">
                                    <div class="d-inline-flex align-items-center px-3 py-2 bg-light border-0" style="border-radius: 10px;">
                                        <span class="text-secondary fw-semibold small">
                                            Vault Records: <strong id="matchedVaultCount" class="text-dark">0</strong> / <span id="totalVaultCount">0</span>
                                        </span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card pharmacy-card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
                        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">
                                <?php echo $is_super_admin ? "Localized Outreach Inventory Matrix" : "Assigned Outreach Location Inventory Stock"; ?>
                            </h5>
                            <span class="badge bg-light text-secondary border px-2 py-1 small">Live Active Balances</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle table-hover mb-0" id="branchVaultTable" style="font-size:0.88rem; width:100%;">
                                <thead class="bg-light-subtle text-muted" style="font-size: 0.8rem; text-transform: uppercase;">
                                    <tr>
                                        <th class="ps-4">Outreach Location</th>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Unit Type</th>
                                        <th>Current Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Construct query based on clearance level using branch_stock_balance and outreach_inventory
                                    if ($is_super_admin) {
                                        $vault_query = "
                                            SELECT bsb.*, oi.item_name, oi.category, oi.unit
                                            FROM branch_stock_balance bsb
                                            JOIN outreach_inventory oi ON bsb.item_id = oi.id
                                            ORDER BY bsb.branch_name ASC, oi.item_name ASC
                                        ";
                                        $stmt = $conn->prepare($vault_query);
                                    } else {
                                        // Restrict query strictly to the logged-in user's assigned outreach location name
                                        $vault_query = "
                                            SELECT bsb.*, oi.item_name, oi.category, oi.unit
                                            FROM branch_stock_balance bsb
                                            JOIN outreach_inventory oi ON bsb.item_id = oi.id
                                            WHERE bsb.branch_name = ?
                                            ORDER BY oi.item_name ASC
                                        ";
                                        $stmt = $conn->prepare($vault_query);
                                        $stmt->bind_param("s", $user_location_val);
                                    }

                                    $stmt->execute();
                                    $vault_res = $stmt->get_result();
                                    $total_records = 0;

                                    if ($vault_res && $vault_res->num_rows > 0):
                                        $total_records = $vault_res->num_rows;
                                        while ($row = $vault_res->fetch_assoc()):
                                            $search_index = strtolower(implode(' ', array_filter([
                                                $row['branch_name'],
                                                $row['item_name'],
                                                $row['category'],
                                                $row['unit']
                                            ])));
                                    ?>
                                            <tr class="searchable-vault-row"
                                                data-search-index="<?php echo htmlspecialchars($search_index); ?>"
                                                data-branch-node="<?php echo htmlspecialchars(strtolower(trim($row['branch_name']))); ?>">
                                                <td class="ps-4"><span class="fw-bold text-dark"><?php echo htmlspecialchars($row['branch_name']); ?></span></td>
                                                <td><span class="fw-semibold text-dark"><?php echo htmlspecialchars($row['item_name']); ?></span></td>
                                                <td><span class="badge bg-light text-primary border"><?php echo htmlspecialchars($row['category']); ?></span></td>
                                                <td><span class="text-secondary"><?php echo htmlspecialchars($row['unit']); ?></span></td>
                                                <td>
                                                    <span class="badge <?php echo (intval($row['quantity_remaining']) > 10) ? 'bg-success-subtle text-success border border-success' : 'bg-warning-subtle text-warning border border-warning'; ?> px-2 py-1" style="font-size: 0.8rem;">
                                                        <?php echo number_format($row['quantity_remaining']); ?> <?php echo htmlspecialchars($row['unit']); ?> available
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php
                                        endwhile;
                                    else:
                                        ?>
                                        <tr class="empty-vault-fallback">
                                            <td colspan="5" class="text-center py-5 text-muted font-medium">
                                                <i class="bi bi-folder-x fs-3 d-block opacity-50 mb-2"></i>
                                                No outreach location inventory balances have been recorded for this vault yet.
                                            </td>
                                        </tr>
                                    <?php endif;
                                    if (isset($stmt)) $stmt->close();
                                    ?>

                                    <tr id="jsZeroVaultFallback" class="d-none">
                                        <td colspan="5" class="text-center py-5 text-muted bg-light-subtle fw-medium">
                                            No outreach vault records match your filter criteria.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const searchInput = document.getElementById('vaultSearchInput');
                        const branchFilter = document.getElementById('vaultBranchFilter');
                        const vaultRows = document.querySelectorAll('.searchable-vault-row');
                        const matchedMetric = document.getElementById('matchedVaultCount');
                        const totalMetric = document.getElementById('totalVaultCount');
                        const zeroFallback = document.getElementById('jsZeroVaultFallback');
                        const defaultFallback = document.querySelector('.empty-vault-fallback');

                        const totalCount = vaultRows.length;
                        if (totalMetric) totalMetric.textContent = totalCount;
                        if (matchedMetric) matchedMetric.textContent = totalCount;

                        function filterVault() {
                            if (totalCount === 0 || defaultFallback) return;

                            const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
                            const branch = branchFilter ? branchFilter.value.toLowerCase().trim() : '';
                            let matchedCount = 0;

                            vaultRows.forEach(row => {
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

                        if (searchInput) searchInput.addEventListener('input', filterVault);
                        if (branchFilter) branchFilter.addEventListener('change', filterVault);
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