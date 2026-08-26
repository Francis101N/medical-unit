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
    <title>Outreach - Medical Unit</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.css">

    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
</head>
<style>
    /* Modern Outreach Management Table Theme */
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
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
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

    /* Action Buttons Layout */
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

    /* Filter Input Custom Alignments & Sizing */
    .table-filter-bar input,
    .table-filter-bar select {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        color: #334155;
        font-size: 0.875rem;
        height: 42px;
        transition: all 0.2s ease;
    }

    .table-filter-bar input:focus,
    .table-filter-bar select:focus {
        border-color: #435ebe;
        box-shadow: 0 0 0 3px rgba(67, 94, 190, 0.15);
        outline: none;
    }

    @keyframes pulse-pulse {
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
                            <h3>Outreach</h3>
                            <p class="text-subtitle text-muted">
                                Manage and view all outreaches and their locations.
                            </p>
                        </div>

                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Outreach</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Hoverable rows start -->
                <section class="section">

                    <!-- Client-Side Omni Search and Filters Component Layer -->
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; background: linear-gradient(145deg, #ffffff, #fcfdff);">
                        <div class="card-body p-4">
                            <div class="row g-3 align-items-center justify-content-between table-filter-bar">

                                <!-- Omnibox Full text search entry field -->
                                <div class="col-12 col-md-6 col-lg-5">
                                    <div class="input-group dashboard-search-group shadow-3xs" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
                                        <span class="input-group-text bg-white border-0 pe-2 ps-3 text-muted">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color: #94a3b8;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </span>
                                        <input type="text" id="omniOutreachSearch" class="form-control border-0 bg-white py-2 text-dark font-medium"
                                            placeholder="Search project titles, locations, duration..." style="font-size: 0.9rem; box-shadow: none;">
                                    </div>
                                </div>

                                <!-- Location Filter Dropdown -->
                                <div class="col-12 col-md-6 col-lg-4">
                                    <select id="outreachLocationFilter" class="form-select form-select-sm shadow-3xs py-2">
                                        <option value="">All Locations</option>
                                        <!-- Programmatically populated dynamically below -->
                                    </select>
                                </div>

                                <!-- Processing Diagnostic Metric Telemetry Feedback -->
                                <div class="col-12 col-lg-3 text-lg-end">
                                    <div class="d-inline-flex align-items-center bg-light border px-3 py-2" style="border-radius: 12px; background-color: #f8fafc !important;">
                                        <span class="d-inline-block bg-success rounded-circle me-2" style="width: 8px; height: 8px;"></span>
                                        <span class="text-secondary font-semibold" style="font-size: 0.85rem;">
                                            Visible: <strong id="visibleOutreachesCount" class="text-dark fw-bold" style="font-size: 0.95rem;">0</strong> / <span id="totalOutreachesCount">0</span>
                                        </span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="row" id="table-hover-row">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">

                                <!-- HEADER -->
                                <div class="card-header d-flex justify-content-between align-items-center px-4 py-3 bg-white border-bottom">
                                    <h4 class="card-title mb-0" style="color: #1e293b; font-weight: 600;">Outreach List</h4>
                                    <a href="add_outreach.php" class="btn btn-success btn-sm px-3 py-2 fw-medium" style="border-radius: 8px;">
                                        + ADD OUTREACH
                                    </a>
                                </div>

                                <?php
                                // Retrieve message and message type from session if available, otherwise fallback to local variables
                                $msg = $_SESSION['msg'] ?? ($msg ?? '');
                                $msg_type = $_SESSION['msg_type'] ?? ($msg_type ?? 'success');

                                // Clear session messages so they disappear on page reload
                                if (isset($_SESSION['msg'])) {
                                    unset($_SESSION['msg']);
                                    unset($_SESSION['msg_type']);
                                }

                                // Only show alert if message exists
                                if (!empty($msg)) {
                                ?>
                                    <div class="alert m-3 alert-<?php echo $msg_type; ?> alert-dismissible show fade" role="alert">
                                        <?php echo $msg; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                <?php
                                }
                                ?>

                                <div class="card-content">
                                    <div class="card-body px-4 py-2">
                                        <p class="mb-0 text-muted">List of all outreaches with project schedules and operational details.</p>
                                    </div>

                                    <!-- TABLE WRAPPER -->
                                    <div class="table-responsive px-3 pb-3">
                                        <div class="custom-table-container">
                                            <table class="modern-table mb-0" id="outreachManagementWorkspaceTable" style="min-width: 1200px; width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th class="small-field">SN</th>
                                                        <th class="medium-field">PROJECT TITLE</th>
                                                        <th class="small-field">LOCATION</th>
                                                        <th class="medium-field">DURATION</th>
                                                        <th class="medium-field">START DATE</th>
                                                        <th class="medium-field">END DATE</th>
                                                        <th class="medium-field">DATE CREATED</th>
                                                        <th class="medium-field text-end">ACTION</th>
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
                                                            return rtrim(strtr(base64_encode($id . '|' . $key), '+/', '-_'), '=');
                                                        }
                                                    }

                                                    $sql = "SELECT * FROM outreach ORDER BY id DESC";
                                                    $result = mysqli_query($conn, $sql);

                                                    if ($result && mysqli_num_rows($result) > 0) {
                                                        $sn = 1;

                                                        while ($row = mysqli_fetch_assoc($result)) {
                                                            $location_clean = strtolower(trim($row['location']));

                                                            // Consolidate full column details down to indexing map strings
                                                            $search_payload = strtolower(implode(' ', array_filter([
                                                                $sn,
                                                                $row['project_title'],
                                                                $row['location'],
                                                                $row['duration']
                                                            ])));
                                                    ?>
                                                            <tr class="searchable-outreach-row"
                                                                data-search-index="<?php echo htmlspecialchars($search_payload); ?>"
                                                                data-location-value="<?php echo htmlspecialchars($location_clean); ?>">

                                                                <!-- Dynamic Row Counter -->
                                                                <td>
                                                                    <span class="sn-badge">#<?php echo sprintf('%02d', $sn++); ?></span>
                                                                </td>

                                                                <!-- Project Title -->
                                                                <td><strong><?php echo htmlspecialchars($row['project_title']); ?></strong></td>

                                                                <!-- Location -->
                                                                <td><span class="text-muted fw-semibold"><?php echo htmlspecialchars($row['location']); ?></span></td>

                                                                <!-- Duration -->
                                                                <td><span class="text-dark fw-medium"><?php echo htmlspecialchars($row['duration']); ?></span></td>

                                                                <!-- Start Date -->
                                                                <td><span class="text-dark fw-medium"><?php echo htmlspecialchars($row['start_date']); ?></span></td>

                                                                <!-- End Date -->
                                                                <td><span class="text-dark fw-medium"><?php echo htmlspecialchars($row['end_date']); ?></span></td>

                                                                <!-- Clean Timestamp Format -->
                                                                <td>
                                                                    <small class="text-muted fw-medium">
                                                                        <?php echo date("Y-m-d H:i", strtotime($row['created_at'])); ?>
                                                                    </small>
                                                                </td>

                                                                <!-- Action Triggers -->
                                                                <td class="text-end">
                                                                    <div class="action-btns justify-content-end">
                                                                        <a href="view_outreach.php?id=<?php echo urlencode(encryptId($row['id'])); ?>"
                                                                            class="btn btn-outline-info btn-icon-sm">
                                                                            View
                                                                        </a>
                                                                        <a href="edit_outreach.php?id=<?php echo urlencode(encryptId($row['id'])); ?>"
                                                                            class="btn btn-outline-primary btn-icon-sm">
                                                                            Edit
                                                                        </a>
                                                                        <a href="delete_outreach.php?id=<?php echo urlencode(encryptId($row['id'])); ?>"
                                                                            class="btn btn-outline-danger btn-icon-sm"
                                                                            onclick="return confirm('Are you sure you want to delete this outreach?');">
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
                                                            <td colspan="6" class="text-center text-muted py-5">
                                                                <div class="py-3">No operational outreaches registered down in system infrastructure.</div>
                                                            </td>
                                                        </tr>
                                                    <?php
                                                    }
                                                    mysqli_close($conn);
                                                    ?>

                                                    <!-- JavaScript Dynamic Zero Filter Match Container Row -->
                                                    <tr id="jsZeroMatchOutreachRow" class="d-none">
                                                        <td colspan="6" class="text-center py-5 text-muted bg-light-subtle fw-medium">
                                                            No operational outreaches discovered matching your filter matching configurations.
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </section>
                <!-- Hoverable rows end -->
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const outreachWorkspaceTable = document.getElementById('outreachManagementWorkspaceTable');
                    if (!outreachWorkspaceTable) return;

                    // Select Control Interface Nodes
                    const omniInput = document.getElementById('omniOutreachSearch');
                    const locationSelect = document.getElementById('outreachLocationFilter');

                    // Telemetry and Fallback UI DOM Nodes
                    const rows = outreachWorkspaceTable.querySelectorAll('.searchable-outreach-row');
                    const zeroMatchFallback = document.getElementById('jsZeroMatchOutreachRow');
                    const visibleCounter = document.getElementById('visibleOutreachesCount');
                    const totalCounter = document.getElementById('totalOutreachesCount');

                    // 1. Programmatically scan table row cells to extract unique locations dynamically
                    const locationFilterRegistry = new Set();
                    rows.forEach(row => {
                        const rawLocationAttr = row.getAttribute('data-location-value');
                        if (rawLocationAttr) {
                            const displayLocationName = row.cells[2]?.textContent?.trim();
                            if (displayLocationName) {
                                locationFilterRegistry.add(JSON.stringify({
                                    value: rawLocationAttr,
                                    display: displayLocationName
                                }));
                            }
                        }
                    });

                    // Populate unique locations down into the selection filter dropdown list
                    locationFilterRegistry.forEach(locationJson => {
                        const locationObj = JSON.parse(locationJson);
                        const optionNode = document.createElement('option');
                        optionNode.value = locationObj.value;
                        optionNode.textContent = locationObj.display;
                        locationSelect.appendChild(optionNode);
                    });

                    // Establish base total counter markers
                    if (totalCounter) totalCounter.textContent = rows.length;

                    // 2. High-Performance Multi-Variable Logic Evaluation Filter Pass
                    function pipelineOutreachMatrixFilters() {
                        const queryStr = omniInput.value.toLowerCase().trim();
                        const targetedLocation = locationSelect.value;

                        let activeCount = 0;

                        rows.forEach(row => {
                            const indexString = row.getAttribute('data-search-index') || '';
                            const locationValue = row.getAttribute('data-location-value') || '';

                            // Matrix comparison conditions matching validation layer flags
                            const criteriaSearch = queryStr === '' || indexString.includes(queryStr);
                            const criteriaLocation = targetedLocation === '' || locationValue === targetedLocation;

                            if (criteriaSearch && criteriaLocation) {
                                row.classList.remove('d-none');
                                activeCount++;
                            } else {
                                row.classList.add('d-none');
                            }
                        });

                        // Sync live counters telemetry display element
                        if (visibleCounter) visibleCounter.textContent = activeCount;

                        // Toggle zero records placeholder node row
                        if (rows.length > 0) {
                            if (activeCount === 0) {
                                zeroMatchFallback.classList.remove('d-none');
                            } else {
                                zeroMatchFallback.classList.add('d-none');
                            }
                        }
                    }

                    // Attach active Event Observers to controls
                    omniInput.addEventListener('input', pipelineOutreachMatrixFilters);
                    locationSelect.addEventListener('change', pipelineOutreachMatrixFilters);

                    // Run baseline compilation setup on template layout mounted load
                    pipelineOutreachMatrixFilters();
                });
            </script>

            <?php
            include('./inc/footer.php');
            ?>
        </div>
    </div>
    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script src="assets/js/main.js"></script>
</body>

</html>