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
        alert('Access denied: Only Super-Admins can access this page.');
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
    <title>Outreach Locations Overview - Medical Unit</title>

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
                            <h3 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">Outreach Locations Overview</h3>
                            <p class="text-subtitle text-muted mb-0" style="font-size: 0.9rem;">
                                Select an outreach location below to inspect and manage assigned medical records.
                            </p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb mb-0 bg-transparent p-0" style="font-size: 0.85rem;">
                                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-primary fw-medium">Dashboard</a></li>
                                    <li class="breadcrumb-item active text-muted fw-semibold" aria-current="page">Outreach Locations</li>
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
                                    $key = "outreach-secret-key";
                                    return rtrim(strtr(base64_encode($id . '|' . $key), '+/', '-_'), '=');
                                }
                            }

                            // Fetch outreach locations
                            $location_query = "SELECT id, location, project_title FROM outreach ORDER BY location ASC";
                            $location_result = mysqli_query($conn, $location_query);

                            $locations_data = [];
                            if ($location_result && mysqli_num_rows($location_result) > 0) {
                                while ($l_row = mysqli_fetch_assoc($location_result)) {
                                    $current_location = $l_row['location'];
                                    $project_title = $l_row['project_title'] ?? '';
                                    $location_pk = $l_row['id'];

                                    // Count the medical records for each outreach location upfront for the card overview deck
                                    $count_query = "SELECT COUNT(*) as total FROM patient_medical_records WHERE patient_location = ?";
                                    $c_stmt = $conn->prepare($count_query);
                                    $c_stmt->bind_param("s", $current_location);
                                    $c_stmt->execute();
                                    $count_res = $c_stmt->get_result()->fetch_assoc();
                                    $total_records = $count_res['total'] ?? 0;
                                    $c_stmt->close();

                                    $locations_data[] = [
                                        'id' => $location_pk,
                                        'encrypted_id' => encryptId($location_pk),
                                        'name' => $current_location,
                                        'project_title' => $project_title,
                                        'count' => $total_records
                                    ];
                                }
                            }

                            if (!empty($locations_data)):
                            ?>

                                <!-- Modern Outreach Locations Overview Grid Cards Deck (Clickable Links) -->
                                <div class="row g-4 mb-5" id="locationCardsDeck">
                                    <?php foreach ($locations_data as $loc): ?>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a href="outreach_location_medical_records.php?location_id=<?php echo urlencode($loc['encrypted_id']); ?>" class="text-decoration-none">
                                                <div class="branch-card clickable-branch-card m-0 h-100"
                                                    style="cursor: pointer; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);">

                                                    <div class="branch-card-header border-0 p-4 align-items-center">
                                                        <div class="branch-title-wrap">
                                                            <div class="branch-icon shadow-3xs">
                                                                <i class="bi bi-geo-alt-fill fs-5"></i>
                                                            </div>
                                                            <div>
                                                                <h5 class="branch-title text-dark font-bold mb-1"><?php echo htmlspecialchars($loc['name']); ?></h5>
                                                                <?php if (!empty($loc['project_title'])): ?>
                                                                    <div class="text-primary fw-bold mb-1" style="font-size: 0.8rem;"><?php echo htmlspecialchars($loc['project_title']); ?></div>
                                                                <?php endif; ?>
                                                                <div class="branch-subtitle fw-semibold text-muted" style="font-size:0.75rem;">Click to view medical records</div>
                                                            </div>
                                                        </div>
                                                        <span class="branch-counter rounded-pill font-bold"><?php echo $loc['count']; ?> Records</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                            <?php else: ?>
                                <div class="card border-0 shadow-sm p-5 text-center text-muted rounded-3 bg-white">
                                    <div class="fw-bold mb-1">Outreach Locations Empty</div>
                                    <div class="small">No administrative outreach locations are currently active within database architecture layouts.</div>
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
    <script src="assets/js/main.js"></script>
</body>

</html>