<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Session Expired! You must log in first.'); window.location.href='auth-login.php';</script>";
    exit();
}

/** @var mysqli $conn */
include('db.php');

$user_role = $_SESSION['role'] ?? '';
$user_branch = $_SESSION['branch'] ?? $_SESSION['staff_branch'] ?? '';

// Handle Report Export Request (Excel Spreadsheet .xls / .xlsx download via HTML Table XML markup)
$export_mode = $_GET['export'] ?? '';
$report_period = $_GET['period'] ?? 'monthly'; // weekly, monthly, yearly, custom
$custom_start = $_GET['start_date'] ?? '';
$custom_end = $_GET['end_date'] ?? '';

// Role-based branch scoping control: super-admin can select or view all, others locked to their branch
if ($user_role === 'super-admin') {
    $target_branch = $_GET['branch'] ?? 'all';
} else {
    $target_branch = $user_branch;
}

// Date interval calculation based on selected period
$date_condition = "1=1";
$params = [];
$types = "";

if ($report_period === 'weekly') {
    $date_condition = "smr.intake_time >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
} elseif ($report_period === 'monthly') {
    $date_condition = "smr.intake_time >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
} elseif ($report_period === 'yearly') {
    $date_condition = "smr.intake_time >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
} elseif ($report_period === 'custom' && !empty($custom_start) && !empty($custom_end)) {
    $date_condition = "DATE(smr.intake_time) BETWEEN ? AND ?";
    $params[] = $custom_start;
    $params[] = $custom_end;
    $types .= "ss";
}

// Branch filtering condition
if ($target_branch !== 'all' && !empty($target_branch)) {
    $date_condition .= " AND smr.staff_branch = ?";
    $params[] = $target_branch;
    $types .= "s";
}

// Fetch complete generated report metrics & comprehensive records with absolute full details
$report_query = "SELECT smr.*, s.passport, s.gender, s.phone, s.email, s.role FROM staff_medical_records smr LEFT JOIN staffs s ON smr.staff_name = s.fullname WHERE $date_condition ORDER BY smr.intake_time DESC";
$stmt = $conn->prepare($report_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$report_result = $stmt->get_result();

// Handle Native Excel Spreadsheet Download Export Trigger (.xls format containing complete detailed markup)
if ($export_mode === 'excel') {
    $filename = 'complete_medical_report_' . $report_period . '_' . date('Y-m-d') . '.xls';
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><style>table { border-collapse: collapse; width: 100%; } th, td { border: 1px solid #999; padding: 8px; text-align: left; font-size: 11pt; font-family: Calibri, sans-serif; } th { background-color: #0d6efd; color: #ffffff; font-weight: bold; }</style></head>';
    echo '<body>';
    echo '<h2>Comprehensive Clinical Medical Records Report</h2>';
    echo '<p><strong>Report Period:</strong> ' . ucwords($report_period) . ' | <strong>Branch Scope:</strong> ' . htmlspecialchars($target_branch) . ' | <strong>Generated Date:</strong> ' . date('Y-m-d H:i:s') . '</p>';
    echo '<table>';
    echo '<thead><tr>';
    echo '<th>S/N</th>';
    echo '<th>Staff Full Name</th>';
    echo '<th>Staff Gender</th>';
    echo '<th>Phone Number</th>';
    echo '<th>Email Address</th>';
    echo '<th>Operational Branch</th>';
    echo '<th>Department</th>';
    echo '<th>Intake Timestamp</th>';
    echo '<th>Release Timestamp</th>';
    echo '<th>Primary Diagnosis</th>';
    echo '<th>Prescription & Treatment Given</th>';
    echo '<th>Medical Record Status</th>';
    echo '</tr></thead><tbody>';

    $excel_row_index = 1;
    while ($row = $report_result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $excel_row_index++ . '</td>';
        echo '<td>' . htmlspecialchars($row['staff_name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['gender'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['phone'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['email'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['staff_branch']) . '</td>';
        echo '<td>' . htmlspecialchars($row['department']) . '</td>';
        echo '<td>' . htmlspecialchars($row['intake_time']) . '</td>';
        echo '<td>' . htmlspecialchars($row['release_time'] ? $row['release_time'] : 'Not Released') . '</td>';
        echo '<td>' . htmlspecialchars($row['diagnosis']) . '</td>';
        echo '<td>' . htmlspecialchars($row['treatment_given']) . '</td>';
        echo '<td>' . ucwords(str_replace('_', ' ', htmlspecialchars($row['record_status']))) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '</body></html>';
    exit();
}

// Fetch active branches list for the report generator filter dropdown (Super-admin only)
$branches_dropdown_query = "SELECT DISTINCT branch_name FROM branches ORDER BY branch_name ASC";
$branches_dropdown_res = mysqli_query($conn, $branches_dropdown_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinical Reports Generator - Medical Unit</title>

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

    .badge-status {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
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
                            <h3 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">Staff Clinical Reports Generator</h3>
                            <p class="text-subtitle text-muted mb-0" style="font-size: 0.9rem;">
                                <?php echo $user_role === 'super-admin' ? 'Super-Admin View: Generating clinical reports across all branches.' : 'Branch View: Generating clinical reports restricted to ' . htmlspecialchars($user_branch) . '.'; ?>
                            </p>
                        </div>
                        <div class="col-12 col-md-6 text-md-end no-print">
                            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm fw-bold px-3 py-2 me-2">
                                <i class="bi bi-printer me-1"></i> Print PDF
                            </button>
                            <a href="?export=excel&period=<?php echo $report_period; ?>&branch=<?php echo urlencode($target_branch); ?>&start_date=<?php echo $custom_start; ?>&end_date=<?php echo $custom_end; ?>" class="btn btn-success btn-sm fw-bold px-3 py-2 text-white">
                                <i class="bi bi-file-earmark-excel me-1"></i> Download Excel Spreadsheet
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Report Filter Control Panel Card -->
                <div class="report-card p-4 no-print">
                    <form method="GET" action="" class="row g-3 align-items-end">
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold text-secondary" style="font-size: 0.8rem;">Reporting Time Frame</label>
                            <select name="period" id="reportPeriodSelect" class="form-select form-select-sm" onchange="toggleCustomDates(this.value)">
                                <option value="weekly" <?php echo $report_period === 'weekly' ? 'selected' : ''; ?>>Past Week (Weekly)</option>
                                <option value="monthly" <?php echo $report_period === 'monthly' ? 'selected' : ''; ?>>Past Month (Monthly)</option>
                                <option value="yearly" <?php echo $report_period === 'yearly' ? 'selected' : ''; ?>>Past Year (Yearly)</option>
                                <option value="custom" <?php echo $report_period === 'custom' ? 'selected' : ''; ?>>Custom Date Range</option>
                            </select>
                        </div>

                        <?php if ($user_role === 'super-admin'): ?>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-bold text-secondary" style="font-size: 0.8rem;">Target Branch Filter</label>
                                <select name="branch" class="form-select form-select-sm">
                                    <option value="all">All Operational Branches</option>
                                    <?php while ($b_row = mysqli_fetch_assoc($branches_dropdown_res)) { ?>
                                        <option value="<?php echo htmlspecialchars($b_row['branch_name']); ?>" <?php echo $target_branch === $b_row['branch_name'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($b_row['branch_name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-bold text-secondary" style="font-size: 0.8rem;">Locked Branch Scope</label>
                                <input type="text" class="form-control form-control-sm bg-light" value="<?php echo htmlspecialchars($user_branch); ?>" readonly>
                                <input type="hidden" name="branch" value="<?php echo htmlspecialchars($user_branch); ?>">
                            </div>
                        <?php endif; ?>

                        <!-- Custom Date Range Fields -->
                        <div class="col-12 col-md-4 custom-date-fields" style="display: <?php echo $report_period === 'custom' ? 'block' : 'none'; ?>;">
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

                        <div class="col-12 col-md-2">
                            <button type="submit" class="btn btn-dark btn-sm w-100 fw-bold py-2">
                                Generate Report
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Printable Report Content Container -->
                <div id="printableReportArea">
                    <div class="report-card m-0">
                        <div class="p-4 border-bottom bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-dark mb-1">Comprehensive Clinical Medical Records Report</h5>
                                <p class="text-muted mb-0" style="font-size: 0.82rem;">
                                    Period: <span class="fw-semibold text-capitalize"><?php echo htmlspecialchars($report_period); ?></span> |
                                    Branch Scope: <span class="fw-semibold"><?php echo htmlspecialchars($target_branch); ?></span> |
                                    Total Records Found: <span class="badge bg-primary"><?php echo $report_result->num_rows; ?></span>
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
                                        <th>Staff Full Name</th>
                                        <th>Gender</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Branch</th>
                                        <th>Department</th>
                                        <th>Intake Timestamp</th>
                                        <th>Release Timestamp</th>
                                        <th>Primary Diagnosis</th>
                                        <th>Prescription & Treatment</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($report_result->num_rows > 0) {
                                        $serial_number = 1;
                                        while ($row = $report_result->fetch_assoc()) {
                                            $record_status = strtolower($row['record_status']);
                                            if ($record_status === "open") $status_class = "badge-status-open";
                                            elseif ($record_status === "under_treatment") $status_class = "badge-status-treatment";
                                            else $status_class = "badge-status-closed";
                                    ?>
                                            <tr>
                                                <td><span class="font-monospace text-muted"><?php echo $serial_number++; ?></span></td>
                                                <td><strong><?php echo htmlspecialchars($row['staff_name']); ?></strong></td>
                                                <td><span class="text-secondary"><?php echo htmlspecialchars($row['gender'] ?? 'N/A'); ?></span></td>
                                                <td><span class="text-secondary"><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></span></td>
                                                <td><span class="text-secondary"><?php echo htmlspecialchars($row['email'] ?? 'N/A'); ?></span></td>
                                                <td><span class="text-secondary"><?php echo htmlspecialchars($row['staff_branch']); ?></span></td>
                                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['department']); ?></span></td>
                                                <td><small class="text-muted"><?php echo htmlspecialchars($row['intake_time']); ?></small></td>
                                                <td><small class="text-muted"><?php echo htmlspecialchars($row['release_time'] ? $row['release_time'] : '—'); ?></small></td>
                                                <td><?php echo htmlspecialchars($row['diagnosis']); ?></td>
                                                <td class="text-secondary"><?php echo htmlspecialchars($row['treatment_given']); ?></td>
                                                <td>
                                                    <span class="badge-status <?php echo $status_class; ?>">
                                                        <?php echo ucwords(str_replace('_', ' ', htmlspecialchars($record_status))); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="12" class="text-center py-5 text-muted">
                                                No clinical records found matching the selected reporting parameters.
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