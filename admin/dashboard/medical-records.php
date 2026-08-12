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
    <title>Medical Records - Medical Unit</title>

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
    /* Modern Medical Table Theme */
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
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        white-space: nowrap;
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

    /* Vital Signs Pills */
    .vital-chip {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.78rem;
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
    }

    .vital-chip.temp {
        background: #fff7ed;
        color: #c2410c;
        border-color: #ffedd5;
    }

    .vital-chip.bp {
        background: #f0fdf4;
        color: #15803d;
        border-color: #dcfce7;
    }

    .vital-chip.pulse {
        background: #fef2f2;
        color: #b91c1c;
        border-color: #fee2e2;
    }

    .passport-frame {
        width: 400px;
        height: 200px;
        overflow: hidden;
        display: flex;
        justify-content: center;
    }

    /* Status Badges */
    .badge-soft {
        padding: 6px 12px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
    }

    .badge-soft-success {
        background: #dcfce7;
        color: #15803d;
    }

    .badge-soft-warning {
        background: #fef3c7;
        color: #b45309;
    }

    .badge-soft-secondary {
        background: #f1f5f9;
        color: #475569;
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
                            <h3>Medical Records</h3>
                            <p class="text-subtitle text-muted">
                                View, manage, and monitor all registered medical records and information.
                            </p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Medical Records</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                <section class="section">

                    <div class="card">

                        <!-- HEADER -->
                        <div class="card-header d-flex justify-content-between align-items-center px-4 py-3">

                            <h4 class="card-title mb-0">Medical Records</h4>

                            <a href="add_medical_record.php" class="btn btn-success btn-sm px-3 py-2">
                                + ADD MEDICAL RECORD
                            </a>

                        </div>

                        <div class="table-responsive">

                            <?php
                            // 1. Initialize Default Fallback States
                            $msg = '';
                            $msg_type = 'success';

                            // 2. Query String Parsing Logic
                            if (isset($_GET['msg']) && !empty($_GET['msg'])) {
                                $msg = htmlspecialchars($_GET['msg']);

                                // Explicit status parsing mapping (success -> success green, error -> danger red)
                                if (isset($_GET['status'])) {
                                    $msg_type = ($_GET['status'] === 'error') ? 'danger' : 'success';
                                }
                                // Fallback detection logic if error flag is concurrently passed
                                elseif (isset($_GET['error'])) {
                                    $msg_type = 'danger';
                                }
                            }
                            // 3. System Code Exception Routing Strategy (e.g., ?error=invalid_id)
                            elseif (isset($_GET['error']) && !empty($_GET['error'])) {
                                $msg_type = 'danger';

                                switch ($_GET['error']) {
                                    case 'invalid_id':
                                        $msg = '<strong>System Conflict:</strong> The requested medical log record ID is invalid or corrupted.';
                                        break;
                                    case 'update_failed':
                                        $msg = '<strong>Database Error:</strong> Unable to commit updates to the log table schema.';
                                        break;
                                    case 'stmt_compilation_failed':
                                        $msg = '<strong>Engine Error:</strong> SQL prepared statement generation failed mapping columns.';
                                        break;
                                    default:
                                        $msg = '<strong>Processing Exception:</strong> ' . htmlspecialchars($_GET['error']);
                                        break;
                                }
                            }

                            // 4. UI Rendering Container Layer
                            if (!empty($msg)) {
                            ?>
                                <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show m-3 shadow-sm border-0" role="alert">
                                    <div class="d-flex align-items-center">
                                        <!-- Contextual Layout Icons -->
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
                                    <!-- Native Bootstrap Dismiss Control System -->
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php } ?>
                            <div class="custom-table-container">
                                <table class="modern-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="small-field">S/N</th>
                                            <th class="medium-field">STAFF NAME</th>
                                            <th class="medium-field text-center">STAFF PASSPORT</th>
                                            <th class="medium-field">STAFF BRANCH</th>
                                            <th class="medium-field">DEPARTMENT</th>
                                            <th class="medium-field">INTAKE TIME</th>
                                            <th class="medium-field">RELEASE TIME</th>

                                            <th class="wide-field">DIAGNOSIS</th>
                                            <th class="wide-field">SYMPTOMS</th>
                                            <th class="wide-field">MEDICAL NOTES</th>
                                            <th class="wide-field">TREATMENT GIVEN</th>
                                            <th class="wide-field">DRUGS GIVEN</th>
                                            <th class="medium-field">DOSAGE INSTRUCTIONS</th>

                                            <th class="medium-field">ATTENDED BY</th>
                                            <th class="medium-field">CONDITION ON ADMISSION</th>
                                            <th class="medium-field">CONDITION ON RELEASE</th>

                                            <th class="small-field">BLOOD PRESSURE</th>
                                            <th class="small-field">TEMPERATURE</th>
                                            <th class="small-field">PULSE RATE</th>

                                            <th class="small-field">FOLLOW UP REQUIRED</th>
                                            <th class="medium-field">FOLLOW UP DATE</th>
                                            <th class="small-field">RECORD STATUS</th>

                                            <th class="medium-field">CREATED AT</th>
                                            <th class="medium-field">UPDATED AT</th>

                                            <th class="medium-field text-end">ACTIONS</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        /** @var mysqli $conn */
                                        include('./db.php');

                                        // Ensure session parameters are active
                                        if (session_status() === PHP_SESSION_NONE) {
                                            session_start();
                                        }

                                        // Declare encryption helper function safely if not already declared globally
                                        if (!function_exists('encryptId')) {
                                            function encryptId($id)
                                            {
                                                $key = "medical-secret-key";
                                                return rtrim(strtr(base64_encode($id . '|' . $key), '+/', '-_'), '=');
                                            }
                                        }

                                        // Fallback tracking metrics from session contexts
                                        $user_role = strtolower($_SESSION['role'] ?? '');
                                        $user_branch = $_SESSION['branch'] ?? '';

                                        // Structural validation: Router isolates log views strictly by branch ownership limits
                                        if ($user_role === 'super-admin') {
                                            $query = "SELECT smr.*, s.passport 
                                                      FROM staff_medical_records smr 
                                                      LEFT JOIN staffs s ON smr.staff_name = s.fullname 
                                                      ORDER BY smr.id DESC";
                                            $stmt = $conn->prepare($query);
                                        } else {
                                            $query = "SELECT smr.*, s.passport 
                                                      FROM staff_medical_records smr 
                                                      LEFT JOIN staffs s ON smr.staff_name = s.fullname 
                                                      WHERE LOWER(TRIM(smr.staff_branch)) = LOWER(TRIM(?))
                                                      ORDER BY smr.id DESC";
                                            $stmt = $conn->prepare($query);
                                            $stmt->bind_param("s", $user_branch);
                                        }

                                        $stmt->execute();
                                        $select_logs = $stmt->get_result();

                                        if ($select_logs && $select_logs->num_rows > 0) {
                                            $sn = 1; // Dynamic row counter

                                            while ($row = $select_logs->fetch_assoc()) {
                                                $id = $row['id'];
                                                $staff_name = $row['staff_name'];
                                                $passport = trim($row['passport'] ?? '');
                                                $staff_branch = $row['staff_branch'];
                                                $department = $row['department'];
                                                $intake_time = $row['intake_time'];
                                                $release_time = $row['release_time'];

                                                $diagnosis = $row['diagnosis'];
                                                $symptoms = $row['symptoms'];
                                                $medical_notes = $row['medical_notes'];
                                                $treatment_given = $row['treatment_given'];
                                                $drugs_given = $row['drugs_given'];
                                                $dosage_instructions = $row['dosage_instructions'];

                                                $attended_by = $row['attended_by'];

                                                $condition_on_admission = $row['condition_on_admission'];
                                                $condition_on_release = $row['condition_on_release'];

                                                $blood_pressure = $row['blood_pressure'];
                                                $temperature = $row['temperature'];
                                                $pulse_rate = $row['pulse_rate'];

                                                $follow_up_required = $row['follow_up_required'];
                                                $follow_up_date = $row['follow_up_date'];
                                                $record_status = strtolower($row['record_status']);

                                                $created_at = $row['created_at'];
                                                $updated_at = $row['updated_at'];

                                                // Fix: Updated Status Badge System to target actual ENUM schema definitions
                                                if ($record_status === "open") {
                                                    $status_class = "badge-soft-success";
                                                } elseif ($record_status === "under_treatment") {
                                                    $status_class = "badge-soft-warning";
                                                } else {
                                                    $status_class = "badge-soft-secondary";
                                                }

                                                // Determine image file paths dynamically relative to application context depth
                                                $passport_src = '';
                                                if (!empty($passport)) {
                                                    $paths_to_test = [
                                                        "uploads/" . $passport,
                                                        "../uploads/" . $passport,
                                                        "../../uploads/" . $passport,
                                                        "admin/uploads/" . $passport,
                                                        "../admin/uploads/" . $passport
                                                    ];

                                                    foreach ($paths_to_test as $test_path) {
                                                        if (file_exists($test_path) && !is_dir($test_path)) {
                                                            $passport_src = $test_path;
                                                            break;
                                                        }
                                                    }
                                                }
                                        ?>
                                                <tr>
                                                    <!-- Dynamic S/N Counter -->
                                                    <td>
                                                        <span class="sn-badge">#<?php echo sprintf('%02d', $sn++); ?></span>
                                                    </td>
                                                    <td><strong><?php echo htmlspecialchars($staff_name); ?></strong></td>

                                                    <!-- Lined Passport Avatar Frame Layer -->
                                                    <td class="text-center">
                                                        <div class="passport-container d-flex justify-content-center align-items-center">
                                                            <?php if (!empty($passport_src)) { ?>
                                                                <div class="passport-frame shadow-sm" style="width: 45px; height: 45px; overflow: hidden; border-radius: 50%; border: 2px solid #e9ecef;">
                                                                    <img src="<?php echo htmlspecialchars($passport_src); ?>" alt="Staff Passport" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                                                                </div>
                                                            <?php } else { ?>
                                                                <span class="badge bg-light text-muted border text-xs py-1 px-2">No Photo</span>
                                                            <?php } ?>
                                                        </div>
                                                    </td>

                                                    <td><span class="text-dark fw-medium"><?php echo htmlspecialchars($staff_branch); ?></span></td>
                                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($department); ?></span></td>
                                                    <td><small class="text-muted"><?php echo htmlspecialchars($intake_time); ?></small></td>
                                                    <td><small class="text-muted"><?php echo htmlspecialchars($release_time ? $release_time : '—'); ?></small></td>

                                                    <!-- Dynamic Text Truncation wrappers to balance layout density -->
                                                    <td>
                                                        <div class="text-truncate-modern" title="<?php echo htmlspecialchars($diagnosis); ?>">
                                                            <?php echo htmlspecialchars($diagnosis); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate-modern" title="<?php echo htmlspecialchars($symptoms); ?>">
                                                            <?php echo htmlspecialchars($symptoms); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate-modern" title="<?php echo htmlspecialchars($medical_notes); ?>">
                                                            <?php echo htmlspecialchars($medical_notes ? $medical_notes : '—'); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate-modern" title="<?php echo htmlspecialchars($treatment_given); ?>">
                                                            <?php echo htmlspecialchars($treatment_given); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate-modern" title="<?php echo htmlspecialchars($drugs_given); ?>">
                                                            <span class="text-danger fw-medium"><?php echo htmlspecialchars($drugs_given); ?></span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate-modern" title="<?php echo htmlspecialchars($dosage_instructions); ?>">
                                                            <?php echo htmlspecialchars($dosage_instructions); ?>
                                                        </div>
                                                    </td>

                                                    <td><strong><?php echo htmlspecialchars($attended_by); ?></strong></td>

                                                    <td><?php echo htmlspecialchars($condition_on_admission); ?></td>
                                                    <td><?php echo htmlspecialchars($condition_on_release ? $condition_on_release : '—'); ?></td>

                                                    <!-- Clinical Medical Vitals Representation -->
                                                    <td><span class="vital-chip bp"><?php echo htmlspecialchars($blood_pressure); ?></span></td>
                                                    <td><span class="vital-chip temp"><?php echo htmlspecialchars($temperature); ?>°C</span></td>
                                                    <td><span class="vital-chip pulse"><?php echo htmlspecialchars($pulse_rate); ?> bpm</span></td>

                                                    <td><?php echo ucfirst(htmlspecialchars($follow_up_required)); ?></td>
                                                    <td><small class="text-muted"><?php echo htmlspecialchars($follow_up_date ? $follow_up_date : '—'); ?></small></td>

                                                    <td>
                                                        <span class="badge <?php echo $status_class; ?>">
                                                            <?php echo ucwords(str_replace('_', ' ', htmlspecialchars($record_status))); ?>
                                                        </span>
                                                    </td>

                                                    <td><small class="text-muted"><?php echo htmlspecialchars($created_at); ?></small></td>
                                                    <td><small class="text-muted"><?php echo htmlspecialchars($updated_at); ?></small></td>

                                                    <!-- Action Controls Layout (With Encrypted IDs) -->
                                                    <td class="text-end">
                                                        <div class="action-btns justify-content-end">
                                                            <a href="edit_log.php?id=<?php echo urlencode(encryptId($id)); ?>" class="btn btn-primary btn-icon-sm">Edit</a>
                                                            <a href="delete_log.php?id=<?php echo urlencode(encryptId($id)); ?>"
                                                                class="btn btn-danger btn-icon-sm"
                                                                onclick="return confirm('Are you sure you want to delete this medical log?');">
                                                                Delete
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php
                                            }
                                        } else {
                                            ?>
                                            <tr>
                                                <td colspan="25" class="text-center py-5 text-muted">
                                                    <div class="py-3">No medical intake records found registered down inside logs database for your branch.</div>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        $stmt->close();
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

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