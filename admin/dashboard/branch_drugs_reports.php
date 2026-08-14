<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Session Expired! You must log in first.'); window.location.href='auth-login.php';</script>";
    exit();
}

// Ensure user has a valid role
if (!isset($_SESSION['role'])) {
    echo "<script>alert('Access denied: You do not have permission to view this page.'); window.location.href='index.php';</script>";
    exit();
}

/** @var mysqli $conn */
include('db.php');

// Handle Report Export Request (Excel Spreadsheet .xls download via HTML Table XML markup)
$export_mode = $_GET['export'] ?? '';
$report_period = $_GET['period'] ?? 'monthly'; // weekly, monthly, yearly, custom
$target_category = $_GET['category'] ?? 'all';
$custom_start = $_GET['start_date'] ?? '';
$custom_end = $_GET['end_date'] ?? '';

// Determine branch target based on user role (using branch_id / branch name stored in session)
$is_super_admin = ($_SESSION['role'] === 'super-admin');
$target_branch = $_GET['branch_id'] ?? ($is_super_admin ? 'all' : ($_SESSION['branch_id'] ?? $_SESSION['branch'] ?? ''));

// Date interval calculation based on selected period
$date_condition = "1=1";
$params = [];
$types = "";

if ($report_period === 'weekly') {
    $date_condition = "da.date_created >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
} elseif ($report_period === 'monthly') {
    $date_condition = "da.date_created >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
} elseif ($report_period === 'yearly') {
    $date_condition = "da.date_created >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
} elseif ($report_period === 'custom' && !empty($custom_start) && !empty($custom_end)) {
    $date_condition = "DATE(da.date_created) BETWEEN ? AND ?";
    $params[] = $custom_start;
    $params[] = $custom_end;
    $types .= "ss";
}

// Drug Category filtering condition from drugs_master
if ($target_category !== 'all' && !empty($target_category)) {
    $date_condition .= " AND dm.category = ?";
    $params[] = $target_category;
    $types .= "s";
}

// Branch filtering condition
if ($is_super_admin) {
    if ($target_branch !== 'all' && !empty($target_branch)) {
        $date_condition .= " AND da.branch_id = ?";
        $params[] = $target_branch;
        $types .= "s";
    }
} else {
    // Non-super-admin users are restricted to their session branch (can be numeric ID or text name like "Omisore Lekki Phase1")
    if (!isset($_SESSION['branch_id']) && !isset($_SESSION['branch'])) {
        echo "<script>alert('Access denied: No branch assigned to your account.'); window.location.href='index.php';</script>";
        exit();
    }
    $target_branch = $_SESSION['branch_id'] ?? $_SESSION['branch'];

    if (is_numeric($target_branch)) {
        $date_condition .= " AND da.branch_id = ?";
        $params[] = $target_branch;
        $types .= "s";
    } else {
        // If stored as text name in session, match against the joined branch_name
        $date_condition .= " AND b.branch_name = ?";
        $params[] = $target_branch;
        $types .= "s";
    }
}

// Fetch allocated branch drugs records by joining drugs_allocations, drugs_master, and branches
$report_query = "SELECT da.*, dm.drug_code, dm.drug_name, dm.generic_name, dm.category, dm.strength, dm.dosage_form, b.branch_name 
                 FROM drugs_allocations da 
                 LEFT JOIN drugs_master dm ON da.drug_id = dm.id 
                 LEFT JOIN branches b ON da.branch_id = b.id
                 WHERE $date_condition 
                 ORDER BY da.date_created DESC";

$stmt = $conn->prepare($report_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$report_result = $stmt->get_result();

// Handle Native Excel Spreadsheet Download Export Trigger (.xls format)
if ($export_mode === 'excel') {
    $filename = 'branch_drugs_report_' . preg_replace('/[^a-zA-Z0-9]/', '_', $target_branch) . '_' . $report_period . '_' . date('Y-m-d') . '.xls';
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><style>table { border-collapse: collapse; width: 100%; } th, td { border: 1px solid #999; padding: 8px; text-align: left; font-size: 11pt; font-family: Calibri, sans-serif; } th { background-color: #0d6efd; color: #ffffff; font-weight: bold; }</style></head>';
    echo '<body>';
    echo '<h2>Branch Allocated Drugs Report</h2>';
    echo '<p><strong>Branch Filter:</strong> ' . htmlspecialchars(strtoupper($target_branch)) . ' | <strong>Report Period:</strong> ' . ucwords($report_period) . ' | <strong>Category Filter:</strong> ' . htmlspecialchars($target_category) . ' | <strong>Generated Date:</strong> ' . date('Y-m-d H:i:s') . '</p>';
    echo '<table>';
    echo '<thead><tr>';
    echo '<th>S/N</th>';
    echo '<th>Branch Name</th>';
    echo '<th>Drug Code</th>';
    echo '<th>Drug Name</th>';
    echo '<th>Generic Name</th>';
    echo '<th>Category</th>';
    echo '<th>Strength</th>';
    echo '<th>Allocated Qty</th>';
    echo '<th>Current Balance</th>';
    echo '<th>Dosage Form</th>';
    echo '<th>Date Created</th>';
    echo '</tr></thead><tbody>';

    $excel_row_index = 1;
    while ($row = $report_result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $excel_row_index++ . '</td>';
        echo '<td>' . htmlspecialchars($row['branch_name'] ?? $row['branch_id'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['drug_code'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['drug_name'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['generic_name'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['category'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['strength'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['allocated_qty'] ?? '0') . '</td>';
        echo '<td>' . htmlspecialchars($row['current_balance'] ?? '0') . '</td>';
        echo '<td>' . htmlspecialchars($row['dosage_form'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['date_created'] ?? 'N/A') . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '</body></html>';
    exit();
}

// Fetch distinct categories for the filter dropdown from drugs_master
$categories_dropdown_query = "SELECT DISTINCT category FROM drugs_master WHERE category IS NOT NULL AND category != '' ORDER BY category ASC";
$categories_dropdown_res = @mysqli_query($conn, $categories_dropdown_query);

// Fetch branches list for super-admin dropdown filter
$branches_dropdown_res = null;
if ($is_super_admin) {
    $branches_dropdown_query = "SELECT id, branch_name FROM branches ORDER BY branch_name ASC";
    $branches_dropdown_res = @mysqli_query($conn, $branches_dropdown_query);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Allocated Drugs - Medical Unit</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
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
        --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    }

    .report-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
        overflow: hidden;
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

    .modern-table tbody td {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
        white-space: nowrap;
    }

    @media print {
        @page {
            size: landscape;
            margin: 10mm;
        }

        body * {
            visibility: hidden;
        }

        #printableReportArea,
        #printableReportArea * {
            visibility: visible;
        }

        #printableReportArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
        }

        .no-print {
            display: none !important;
        }

        .table-responsive {
            overflow: visible !important;
        }

        .modern-table {
            width: 100% !important;
            font-size: 8.5pt !important;
            border-collapse: collapse !important;
        }

        .modern-table th,
        .modern-table td {
            padding: 6px 8px !important;
            white-space: normal !important;
            word-break: break-word !important;
        }

        .report-card {
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
            padding: 0 !important;
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
                <!-- Page Header Title -->
                <div class="page-title mb-4">
                    <div class="row align-items-center g-3">
                        <div class="col-12 col-md-6">
                            <h3 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">Branch Allocated Drugs Report</h3>
                            <p class="text-subtitle text-muted mb-0" style="font-size: 0.9rem;">
                                Monitor, print, and export medications allocated across branches.
                            </p>
                        </div>
                        <div class="col-12 col-md-6 text-md-end no-print">
                            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm fw-bold px-3 py-2 me-2">
                                <i class="bi bi-printer me-1"></i> Print PDF
                            </button>
                            <a href="?export=excel&branch_id=<?php echo urlencode($target_branch); ?>&period=<?php echo $report_period; ?>&category=<?php echo urlencode($target_category); ?>&start_date=<?php echo $custom_start; ?>&end_date=<?php echo $custom_end; ?>" class="btn btn-success btn-sm fw-bold px-3 py-2 text-white">
                                <i class="bi bi-file-earmark-excel me-1"></i> Download Excel Spreadsheet
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Report Filter Control Panel Card -->
                <div class="report-card p-4 no-print">
                    <form method="GET" action="" class="row g-3 align-items-end">
                        <?php if ($is_super_admin): ?>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-bold text-secondary" style="font-size: 0.8rem;">Branch Filter</label>
                                <select name="branch_id" class="form-select form-select-sm">
                                    <option value="all">All Branches</option>
                                    <?php if ($branches_dropdown_res) {
                                        while ($b_row = mysqli_fetch_assoc($branches_dropdown_res)) { ?>
                                            <option value="<?php echo htmlspecialchars($b_row['id']); ?>" <?php echo $target_branch == $b_row['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($b_row['branch_name']); ?>
                                            </option>
                                    <?php }
                                    } ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="col-12 <?php echo $is_super_admin ? 'col-md-3' : 'col-md-4'; ?>">
                            <label class="form-label fw-bold text-secondary" style="font-size: 0.8rem;">Allocation Time Frame</label>
                            <select name="period" id="reportPeriodSelect" class="form-select form-select-sm" onchange="toggleCustomDates(this.value)">
                                <option value="weekly" <?php echo $report_period === 'weekly' ? 'selected' : ''; ?>>Past Week (Weekly)</option>
                                <option value="monthly" <?php echo $report_period === 'monthly' ? 'selected' : ''; ?>>Past Month (Monthly)</option>
                                <option value="yearly" <?php echo $report_period === 'yearly' ? 'selected' : ''; ?>>Past Year (Yearly)</option>
                                <option value="custom" <?php echo $report_period === 'custom' ? 'selected' : ''; ?>>Custom Date Range</option>
                            </select>
                        </div>

                        <div class="col-12 <?php echo $is_super_admin ? 'col-md-2' : 'col-md-3'; ?>">
                            <label class="form-label fw-bold text-secondary" style="font-size: 0.8rem;">Category Filter</label>
                            <select name="category" class="form-select form-select-sm">
                                <option value="all">All Drug Categories</option>
                                <?php if ($categories_dropdown_res) {
                                    while ($c_row = mysqli_fetch_assoc($categories_dropdown_res)) {
                                        if (empty($c_row['category'])) continue; ?>
                                        <option value="<?php echo htmlspecialchars($c_row['category']); ?>" <?php echo $target_category === $c_row['category'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($c_row['category']); ?>
                                        </option>
                                <?php }
                                } ?>
                            </select>
                        </div>

                        <!-- Custom Date Range Fields -->
                        <div class="col-12 col-md-3 custom-date-fields" style="display: <?php echo $report_period === 'custom' ? 'block' : 'none'; ?>;">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label fw-bold text-secondary" style="font-size: 0.75rem;">Start Date</label>
                                    <input type="date" name="start_date" value="<?php echo htmlspecialchars($custom_start); ?>" class="form-control form-control-sm">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold text-secondary" style="font-size: 0.75rem;">End Date</label>
                                    <input type="date" name="end_date" value="<?php echo htmlspecialchars($custom_end); ?>" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-<?php echo $is_super_admin ? '1' : '2'; ?>">
                            <button type="submit" class="btn btn-dark btn-sm w-100 fw-bold py-2">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Printable Report Content Container -->
                <div id="printableReportArea">
                    <div class="report-card m-0">
                        <div class="p-4 border-bottom bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-dark mb-1">Branch Allocated Drugs Inventory</h5>
                                <p class="text-muted mb-0" style="font-size: 0.82rem;">
                                    Period: <span class="fw-semibold text-capitalize"><?php echo htmlspecialchars($report_period); ?></span> |
                                    Category: <span class="fw-semibold"><?php echo htmlspecialchars($target_category); ?></span> |
                                    Total Records: <span class="badge bg-primary"><?php echo $report_result->num_rows; ?></span>
                                </p>
                            </div>
                            <div class="text-end d-none d-md-block">
                                <small class="text-muted">Generated on: <?php echo date('Y-m-d H:i:s'); ?></small>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Branch Name</th>
                                        <th>Drug Code</th>
                                        <th>Drug Name</th>
                                        <th>Generic Name</th>
                                        <th>Category</th>
                                        <th>Strength</th>
                                        <th>Allocated Qty</th>
                                        <th>Current Balance</th>
                                        <th>Dosage Form</th>
                                        <th>Date Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($report_result->num_rows > 0) {
                                        $serial_number = 1;
                                        while ($row = $report_result->fetch_assoc()) {
                                    ?>
                                            <tr>
                                                <td><span class="font-monospace text-muted"><?php echo $serial_number++; ?></span></td>
                                                <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['branch_name'] ?? $row['branch_id'] ?? 'N/A'); ?></span></td>
                                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['drug_code'] ?? 'N/A'); ?></span></td>
                                                <td><strong><?php echo htmlspecialchars($row['drug_name'] ?? 'N/A'); ?></strong></td>
                                                <td><span class="text-secondary"><?php echo htmlspecialchars($row['generic_name'] ?? 'N/A'); ?></span></td>
                                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['category'] ?? 'N/A'); ?></span></td>
                                                <td><span class="text-secondary"><?php echo htmlspecialchars($row['strength'] ?? 'N/A'); ?></span></td>
                                                <td><span class="fw-semibold text-success"><?php echo htmlspecialchars($row['allocated_qty'] ?? '0'); ?></span></td>
                                                <td><span class="fw-semibold text-primary"><?php echo htmlspecialchars($row['current_balance'] ?? '0'); ?></span></td>
                                                <td><span class="text-secondary"><?php echo htmlspecialchars($row['dosage_form'] ?? 'N/A'); ?></span></td>
                                                <td><small class="text-muted"><?php echo htmlspecialchars($row['date_created'] ?? 'N/A'); ?></small></td>
                                            </tr>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="11" class="text-center py-5 text-muted">
                                                No allocated branch drugs found matching the selected parameters.
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <?php include('./inc/footer.php'); ?>
        </div>
    </div>

    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function toggleCustomDates(val) {
            const customFields = document.querySelector('.custom-date-fields');
            if (val === 'custom') {
                customFields.style.display = 'block';
            } else {
                customFields.style.display = 'none';
            }
        }
    </script>
</body>

</html>