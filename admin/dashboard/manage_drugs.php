<?php
// Ensure session parameters are active
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

/**
 * Master Drug Catalog Management Workspace
 */
/** @var mysqli $conn */
include('db.php');

// Security Check: Super-Admin authorization
$user_role = strtolower($_SESSION['role'] ?? '');
if ($user_role !== 'super-admin') {
    header("Location: dashboard.php?error=" . urlencode("Unauthorized access clearance."));
    exit();
}

$currentPage = 'manage_drugs.php';

// Encryption Helper Functions
if (!function_exists('encryptId')) {
    function encryptId($id)
    {
        $key = "drug-catalog-secret-key";
        return rtrim(strtr(base64_encode($id . '|' . $key), '+/', '-_'), '=');
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Drug Catalog Management - Medical Unit</title>

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
    .catalog-card {
        border: none;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(226, 232, 240, 0.4);
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
        padding: 10px 14px;
        font-size: 0.9rem;
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

    .premium-input-icon {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-right: none;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
        color: #94a3b8;
        padding-left: 14px;
    }

    .premium-input-icon+.form-control {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .badge-soft-form {
        background-color: #e0f2fe;
        color: #0369a1;
        font-weight: 600;
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 0.78rem;
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
                            <h3 class="fw-bold text-dark">Master Drug Catalog</h3>
                            <p class="text-subtitle text-muted">
                                Register, manage, and monitor all corporate master pharmaceutical asset definitions.
                            </p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Master Drug Catalog</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <section class="section">

                    <?php
                    // 1. Initialize Default Fallback States
                    $msg = '';
                    $msg_type = 'success';

                    // 2. Query String Parsing Logic
                    if (isset($_GET['msg']) && !empty($_GET['msg'])) {
                        $msg = htmlspecialchars($_GET['msg']);
                        if (isset($_GET['status'])) {
                            $msg_type = ($_GET['status'] === 'error') ? 'danger' : 'success';
                        } elseif (isset($_GET['error'])) {
                            $msg_type = 'danger';
                        }
                    }
                    // 3. System Exception Routing Strategy
                    elseif (isset($_GET['error']) && !empty($_GET['error'])) {
                        $msg_type = 'danger';

                        switch ($_GET['error']) {
                            case 'invalid_id':
                                $msg = '<strong>System Conflict:</strong> The requested drug record ID is invalid or corrupted.';
                                break;
                            case 'duplicate_code':
                                $msg = '<strong>Conflict Error:</strong> A drug with this Item Code / SKU already exists in the catalog.';
                                break;
                            case 'update_failed':
                                $msg = '<strong>Database Error:</strong> Unable to commit updates to the master drug schema.';
                                break;
                            case 'stmt_compilation_failed':
                                $msg = '<strong>Engine Error:</strong> SQL prepared statement generation failed.';
                                break;
                            default:
                                $msg = '<strong>Processing Exception:</strong> ' . htmlspecialchars($_GET['error']);
                                break;
                        }
                    }

                    // 4. UI Rendering Container Layer
                    if (!empty($msg)) {
                    ?>
                        <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show mb-4 shadow-sm border-0" role="alert" style="border-radius: 12px;">
                            <div class="d-flex align-items-center">
                                <div class="alert-icon-wrapper me-3">
                                    <?php if ($msg_type === 'danger'): ?>
                                        <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
                                    <?php else: ?>
                                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="alert-message-text text-dark">
                                    <?php echo $msg; ?>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php } ?>
                    <div class="row g-4">
                        <!-- LEFT COLUMN: Register New Master Drug Form -->
                        <div class="col-12 col-xl-4">
                            <div class="card catalog-card p-4">
                                <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom border-light">Add New Drug</h5>

                                <form action="process_master_drug.php" method="POST">
                                    <div class="row g-3">

                                        <!-- Item Code / SKU -->
                                        <div class="col-12 form-premium-group">
                                            <label for="drug_code" class="form-label">Item Code / SKU</label>
                                            <div class="input-group">
                                                <span class="input-group-text premium-input-icon"><i class="bi bi-qr-code"></i></span>
                                                <input type="text" class="form-control" id="drug_code" name="drug_code" required placeholder="e.g. PCT-500-TAB">
                                            </div>
                                        </div>

                                        <!-- Trade / Brand Name -->
                                        <div class="col-12 form-premium-group">
                                            <label for="drug_name" class="form-label">Trade / Brand Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text premium-input-icon"><i class="bi bi-tag-fill"></i></span>
                                                <input type="text" class="form-control" id="drug_name" name="drug_name" required placeholder="e.g. Panadol Extra">
                                            </div>
                                        </div>

                                        <!-- Generic Name -->
                                        <div class="col-12 form-premium-group">
                                            <label for="generic_name" class="form-label">Generic Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text premium-input-icon"><i class="bi bi-capsule"></i></span>
                                                <input type="text" class="form-control" id="generic_name" name="generic_name" required placeholder="e.g. Paracetamol / Caffeine">
                                            </div>
                                        </div>

                                        <!-- Category -->
                                        <div class="col-12 form-premium-group">
                                            <label for="category" class="form-label">Category</label>
                                            <div class="input-group">
                                                <span class="input-group-text premium-input-icon"><i class="bi bi-grid-fill"></i></span>
                                                <input type="text" class="form-control" id="category" name="category" required placeholder="e.g. Analgesic, Antibiotic">
                                            </div>
                                        </div>

                                        <!-- Strength Allocation -->
                                        <div class="col-12 form-premium-group">
                                            <label for="strength" class="form-label">Strength / Concentration</label>
                                            <div class="input-group">
                                                <span class="input-group-text premium-input-icon"><i class="bi bi-speedometer2"></i></span>
                                                <input type="text" class="form-control" id="strength" name="strength" required placeholder="e.g. 500mg, 10ml, 2%">
                                            </div>
                                        </div>

                                        <!-- Quantity Allocation -->
                                        <div class="col-12 form-premium-group">
                                            <label for="quantity" class="form-label">Initial Quantity / Stock</label>
                                            <div class="input-group">
                                                <span class="input-group-text premium-input-icon"><i class="bi bi-layers-half"></i></span>
                                                <input type="number" class="form-control" id="quantity" name="quantity" required min="0" placeholder="e.g. 100">
                                            </div>
                                        </div>

                                        <!-- Dosage Form Select -->
                                        <div class="col-12 form-premium-group">
                                            <label for="dosage_form" class="form-label">Dosage Form Factor</label>
                                            <select class="form-select" id="dosage_form" name="dosage_form" required>
                                                <option value="" disabled selected hidden>Select dosage form...</option>
                                                <option value="Tablet">Tablet</option>
                                                <option value="Capsule">Capsule</option>
                                                <option value="Syrup">Syrup / Liquid Suspension</option>
                                                <option value="Injection">Injection / Intravenous</option>
                                                <option value="Ointment">Ointment / Topical Gel</option>
                                                <option value="Inhaler">Inhaler / Aerosol</option>
                                            </select>
                                        </div>

                                        <div class="col-12 pt-3">
                                            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold" style="border-radius: 10px; background-color: #435ebe; border:none; box-shadow: 0 4px 12px rgba(67, 94, 190, 0.25);">
                                                <i class="bi bi-plus-lg me-1"></i> Register Drug Asset
                                            </button>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Catalog Workspace & Interactive Filter Table -->
                        <div class="col-12 col-xl-8">

                            <!-- High-Fidelity Client-Side Omni-Filter Action Bar -->
                            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; background: linear-gradient(145deg, #ffffff, #fcfdff);">
                                <div class="card-body p-3 p-md-4">
                                    <div class="row g-3 align-items-center justify-content-between table-filter-bar">

                                        <!-- Omnibox Search Input Field -->
                                        <div class="col-12 col-md-6 col-lg-5">
                                            <div class="input-group dashboard-search-group shadow-3xs" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
                                                <span class="input-group-text bg-white border-0 pe-2 ps-3 text-muted">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color: #94a3b8;">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                    </svg>
                                                </span>
                                                <input type="text" id="omniDrugSearch" class="form-control border-0 bg-white py-2 text-dark font-medium"
                                                    placeholder="Search code, trade name, generic, strength..." style="font-size: 0.9rem; box-shadow: none;">
                                            </div>
                                        </div>

                                        <!-- Form Factor Segment Filter -->
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <select id="drugFormFilter" class="form-select form-select-sm shadow-3xs py-2" style="border-radius: 10px;">
                                                <option value="">All Dosage Forms</option>
                                                <option value="tablet">Tablet</option>
                                                <option value="capsule">Capsule</option>
                                                <option value="syrup">Syrup</option>
                                                <option value="injection">Injection</option>
                                                <option value="ointment">Ointment</option>
                                                <option value="inhaler">Inhaler</option>
                                            </select>
                                        </div>

                                        <!-- Counter Metric -->
                                        <div class="col-12 col-lg-3 text-lg-end">
                                            <div class="d-inline-flex align-items-center bg-light border px-3 py-2" style="border-radius: 12px; background-color: #f8fafc !important;">
                                                <span class="d-inline-block bg-primary rounded-circle me-2" style="width: 8px; height: 8px;"></span>
                                                <span class="text-secondary font-semibold" style="font-size: 0.85rem;">
                                                    Matched: <strong id="visibleDrugsMetric" class="text-dark fw-bold" style="font-size: 0.95rem;">0</strong> / <span id="totalDrugsMetric">0</span>
                                                </span>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Master Table Workspace -->
                            <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                                <div class="card-header d-flex justify-content-between align-items-center px-4 py-3 bg-white border-bottom">
                                    <h5 class="card-title mb-0 fw-bold" style="color: #1e293b; font-size: 1.05rem;"> Drugs Catalog Workspace</h5>
                                    <span class="badge bg-light text-secondary border px-2 py-1 small">Centralized Database</span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table align-middle table-hover mb-0" id="masterDrugCatalogTable" style="width: 100%;">
                                        <thead class="bg-light-subtle text-muted" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                            <tr>
                                                <th class="ps-4" style="width: 60px;">S/N</th>
                                                <th>SKU / Code</th>
                                                <th>Trade Name</th>
                                                <th>Generic Name</th>
                                                <th>Category</th>
                                                <th>Strength</th>
                                                <th>Qty</th>
                                                <th>Dosage Form</th>
                                                <th class="pe-4 text-end">Actions</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php
                                            $query = "SELECT * FROM drugs_master ORDER BY drug_name ASC";
                                            $stmt = $conn->prepare($query);
                                            $stmt->execute();
                                            $res = $stmt->get_result();

                                            $total_count = 0;

                                            if ($res && $res->num_rows > 0) {
                                                $sn = 1;
                                                $total_count = $res->num_rows;

                                                while ($row = $res->fetch_assoc()) {
                                                    $drug_code    = $row['drug_code'];
                                                    $drug_name    = $row['drug_name'];
                                                    $generic_name = $row['generic_name'];
                                                    $category     = $row['category'];
                                                    $strength     = $row['strength'] ?? 'N/A';
                                                    $quantity     = $row['quantity'] ?? 0;
                                                    $dosage_form  = $row['dosage_form'];

                                                    $search_payload = strtolower(implode(' ', array_filter([
                                                        $sn,
                                                        $drug_code,
                                                        $drug_name,
                                                        $generic_name,
                                                        $category,
                                                        $strength,
                                                        $dosage_form
                                                    ])));
                                            ?>
                                                    <tr class="searchable-drug-row"
                                                        data-search-index="<?php echo htmlspecialchars($search_payload); ?>"
                                                        data-form-type="<?php echo htmlspecialchars(strtolower(trim($dosage_form))); ?>">

                                                        <td class="ps-4">
                                                            <span class="text-muted small fw-medium">#<?php echo sprintf('%02d', $sn++); ?></span>
                                                        </td>

                                                        <td>
                                                            <span class="badge bg-light text-dark border font-monospace px-2 py-1"><?php echo htmlspecialchars($drug_code); ?></span>
                                                        </td>

                                                        <td>
                                                            <strong class="text-dark"><?php echo htmlspecialchars($drug_name); ?></strong>
                                                        </td>

                                                        <td>
                                                            <span class="text-secondary fw-medium"><?php echo htmlspecialchars($generic_name); ?></span>
                                                        </td>

                                                        <td>
                                                            <span class="badge bg-light text-primary border"><?php echo htmlspecialchars($category); ?></span>
                                                        </td>

                                                        <td>
                                                            <span class="text-dark small fw-semibold"><?php echo htmlspecialchars($strength); ?></span>
                                                        </td>

                                                        <td>
                                                            <span class="badge <?php echo $quantity > 10 ? 'bg-light text-dark border' : 'bg-danger-subtle text-danger'; ?> font-monospace fw-bold">
                                                                <?php echo number_format($quantity); ?>
                                                            </span>
                                                        </td>

                                                        <td>
                                                            <span class="badge-soft-form"><?php echo htmlspecialchars($dosage_form); ?></span>
                                                        </td>

                                                        <td class="pe-4 text-end">
                                                            <div class="d-inline-flex gap-1">
                                                                <a href="view_drug.php?code=<?php echo urlencode(encryptId($drug_code)); ?>" class="btn btn-sm btn-outline-info px-2.5 py-1" style="border-radius: 8px;" title="View Definition">
                                                                    <i class="bi bi-eye"></i> View
                                                                </a>
                                                                <a href="edit_drug.php?code=<?php echo urlencode(encryptId($drug_code)); ?>" class="btn btn-sm btn-outline-primary px-2.5 py-1" style="border-radius: 8px;" title="Edit Definition">
                                                                    <i class="bi bi-pencil-square"></i> Edit
                                                                </a>
                                                                <a href="delete_drug.php?code=<?php echo urlencode(encryptId($drug_code)); ?>"
                                                                    class="btn btn-sm btn-outline-danger px-2.5 py-1" style="border-radius: 8px;"
                                                                    onclick="return confirm('Are you sure you want to delete this drug definition? Existing records referencing this item may be affected.');"
                                                                    title="Delete Definition">
                                                                    <i class="bi bi-trash"></i> Delete
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php
                                                }
                                            } else {
                                                ?>
                                                <tr class="db-empty-fallback-row">
                                                    <td colspan="9" class="text-center py-5 text-muted">
                                                        <i class="bi bi-folder-x fs-2 d-block text-muted opacity-50 mb-2"></i>
                                                        No master drug catalog definitions registered in the system database yet.
                                                    </td>
                                                </tr>
                                            <?php
                                            }
                                            $stmt->close();
                                            ?>

                                            <tr id="jsZeroMatchFallbackRow" class="d-none">
                                                <td colspan="9" class="text-center py-5 text-muted bg-light-subtle fw-medium">
                                                    No drug definitions found matching your selected search parameters.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <footer>
                <div class="footer clearfix mb-0 text-muted px-4 mt-5">
                    <div class="float-start">
                        <p>2026 &copy; Medical Management System</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>

    <!-- Client-Side Omni-Search Engine Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('omniDrugSearch');
            const formFilter = document.getElementById('drugFormFilter');
            const rows = document.querySelectorAll('.searchable-drug-row');
            const visibleMetric = document.getElementById('visibleDrugsMetric');
            const totalMetric = document.getElementById('totalDrugsMetric');
            const fallbackRow = document.getElementById('jsZeroMatchFallbackRow');
            const emptyDbRow = document.querySelector('.db-empty-fallback-row');

            const totalRows = rows.length;
            if (totalMetric) totalMetric.textContent = totalRows;
            if (visibleMetric) visibleMetric.textContent = totalRows;

            function filterTable() {
                // Drop out cleanly if there are zero elements returned by server-side query
                if (totalRows === 0 || emptyDbRow) {
                    if (visibleMetric) visibleMetric.textContent = 0;
                    return;
                }

                const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
                const selectedForm = formFilter ? formFilter.value.toLowerCase().trim() : '';
                let visibleCount = 0;

                rows.forEach(row => {
                    const searchIndex = row.getAttribute('data-search-index') || '';
                    const formType = row.getAttribute('data-form-type') || '';

                    // Structural query tracking - handles strings inside names, codes, and strengths
                    const matchesQuery = query === '' || searchIndex.includes(query);
                    const matchesForm = selectedForm === '' || formType.toLowerCase().trim() === selectedForm;

                    if (matchesQuery && matchesForm) {
                        row.classList.remove('d-none');
                        visibleCount++;
                    } else {
                        row.classList.add('d-none');
                    }
                });

                if (visibleMetric) visibleMetric.textContent = visibleCount;

                // Manage visible fallback blocks on extreme filtering
                if (visibleCount === 0) {
                    if (fallbackRow) fallbackRow.classList.remove('d-none');
                } else {
                    if (fallbackRow) fallbackRow.classList.add('d-none');
                }
            }

            if (searchInput) searchInput.addEventListener('input', filterTable);
            if (formFilter) formFilter.addEventListener('change', filterTable);
        });
    </script>
</body>

</html>