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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branches Records - Medical Unit</title>

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

    /* Branch Header */
    .branch-card-header {
        background-color: #ffffff;
        border-bottom: 1px solid var(--border-color);
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .branch-title-wrap {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .branch-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background-color: var(--primary-soft);
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .branch-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-main);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .branch-subtitle {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 0.1rem;
    }

    /* Modern Counter Pill */
    .branch-counter {
        font-size: 10px;
        font-weight: 600;
        color: var(--primary-color);
        background-color: var(--primary-soft);
        border: 1px solid rgba(13, 110, 253, 0.15);
        padding: 0.35rem 0.85rem;
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
                <div class="page-title mb-4">
                    <div class="row align-items-center g-3">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">Branch Overview Dashboard</h3>
                            <p class="text-subtitle text-muted mb-0" style="font-size: 0.9rem;">
                                Select a operational branch below to inspect clinical profiles and manage staff medical records.
                            </p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb mb-0 bg-transparent p-0" style="font-size: 0.85rem;">
                                    <li class="breadcrumb-item"><a href="index.html" class="text-decoration-none text-primary fw-medium">Dashboard</a></li>
                                    <li class="breadcrumb-item active text-muted fw-semibold" aria-current="page">Branches</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="card border-0 bg-transparent">
                        <div class="container-fluid px-4 mt-4">

                            <?php
                            /** @var mysqli $conn */
                            include('./db.php');

                            if (!function_exists('encryptId')) {
                                function encryptId($id)
                                {
                                    $key = "medical-secret-key";
                                    return rtrim(strtr(base64_encode($id . '|' . $key), '+/', '-_'), '=');
                                }
                            }

                            // Fetch operational branches
                            $branch_query = "SELECT id, branch_name FROM branches ORDER BY branch_name ASC";
                            $branch_result = mysqli_query($conn, $branch_query);

                            $branches_data = [];
                            if ($branch_result && mysqli_num_rows($branch_result) > 0) {
                                while ($b_row = mysqli_fetch_assoc($branch_result)) {
                                    $current_branch = $b_row['branch_name'];
                                    $branch_pk = $b_row['id'];

                                    // Count the logs for each branch upfront for the card overview deck
                                    $count_query = "SELECT COUNT(*) as total FROM staff_medical_records WHERE staff_branch = ?";
                                    $c_stmt = $conn->prepare($count_query);
                                    $c_stmt->bind_param("s", $current_branch);
                                    $c_stmt->execute();
                                    $count_res = $c_stmt->get_result()->fetch_assoc();
                                    $total_records = $count_res['total'] ?? 0;
                                    $c_stmt->close();

                                    $branches_data[] = [
                                        'id' => $branch_pk,
                                        'encrypted_id' => encryptId($branch_pk),
                                        'name' => $current_branch,
                                        'count' => $total_records
                                    ];
                                }
                            }

                            if (!empty($branches_data)):
                            ?>

                                <!-- Modern Branch Overview Grid Cards Deck (Clickable Links) -->
                                <div class="row g-4 mb-5" id="branchCardsDeck">
                                    <?php foreach ($branches_data as $branch): ?>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="branch_details.php?branch_id=<?php echo urlencode($branch['encrypted_id']); ?>" class="text-decoration-none">
                                                <div class="branch-card clickable-branch-card m-0 h-100"
                                                    style="cursor: pointer; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);">

                                                    <div class="branch-card-header border-0 p-4 align-items-center">
                                                        <div class="branch-title-wrap">
                                                            <div class="branch-icon shadow-3xs">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                                                    <path d="M3 0a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h3v-3.5a.5.5 0 0 1 .5-.5h3.5a.5.5 0 0 1 .5.5V16h3a1 1 0 0 0 1-1V1c0-.552-.448-1-1-1zm1 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm0 4a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm0 4a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm5-8a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm0 4a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z" />
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <h5 class="branch-title text-dark font-bold mb-1"><?php echo htmlspecialchars($branch['name']); ?></h5>
                                                                <div class="branch-subtitle fw-semibold text-muted" style="font-size:0.75rem;">Click to view branch records</div>
                                                            </div>
                                                        </div>
                                                        <span class="branch-counter rounded-pill font-bold"><?php echo $branch['count']; ?> Logs</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                            <?php else: ?>
                                <div class="card border-0 shadow-sm p-5 text-center text-muted rounded-3 bg-white">
                                    <div class="fw-bold mb-1">System Configurations Empty</div>
                                    <div class="small">No administrative organizational branches are currently active within database architecture layouts.</div>
                                </div>
                            <?php endif; ?>

                        </div>

                        <!-- Micro-UX Performance CSS Styles Injection -->
                        <style>
                            .clickable-branch-card {
                                border: 2px solid var(--border-color) !important;
                                background-color: #ffffff;
                            }

                            .clickable-branch-card:hover {
                                border-color: var(--primary-color) !important;
                                background-color: #fafcff !important;
                                transform: translateY(-2px);
                                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                            }

                            .shadow-3xs {
                                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
                            }
                        </style>
                    </div>
                </section>

            </div>

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