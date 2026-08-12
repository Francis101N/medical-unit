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
    <title>Edit Medical Record - Medical Unit</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
</head>

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
                    <div class="row align-items-center">
                        <!-- LEFT SIDE -->
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3 class="mb-1">Edit Medical Record</h3>
                            <p class="text-subtitle text-muted mb-0">
                                Update medical record details and information.
                            </p>
                        </div>

                        <!-- RIGHT SIDE -->
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item">
                                        <a href="index.php">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="medical_records.php">Medical Records</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        Edit Medical Record
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div> <br>

                <!-- Edit Medical Record Section -->
                <section class="section">
                    <div class="row justify-content-center" id="table-hover-row">
                        <div class="col-12 col-lg-10">
                            <div class="card shadow-sm border-0">

                                <!-- CARD HEADER -->
                                <div class="card-header bg-success text-white py-3">
                                    <h4 class="mb-0">Edit Medical Record</h4>
                                </div>

                                <br>

                                <?php
                                /** @var mysqli $conn */
                                include('./db.php');

                                // Secure ID decryption helper function
                                function decryptId($encoded_id)
                                {
                                    $key = "medical-secret-key";
                                    $decoded = base64_decode(strtr($encoded_id, '-_', '+/'));
                                    if ($decoded !== false && strpos($decoded, '|' . $key) !== false) {
                                        return str_replace('|' . $key, '', $decoded);
                                    }
                                    return false;
                                }

                                // Check if ID is provided in query string
                                if (!isset($_GET['id']) || empty($_GET['id'])) {
                                    header("Location: medical_records.php");
                                    exit();
                                }

                                $encrypted_id = $_GET['id'];
                                $id = decryptId($encrypted_id);

                                // If decryption failed or ID is invalid, redirect back
                                if ($id === false) {
                                    header("Location: medical_records.php");
                                    exit();
                                }

                                // Fetch the specific medical log record AND join staffs table to get passport
                                $query = "SELECT smr.*, s.passport 
              FROM staff_medical_records smr 
              LEFT JOIN staffs s ON smr.staff_name = s.fullname 
              WHERE smr.id = ?";

                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("i", $id);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows === 0) {
                                    header("Location: medical_records.php");
                                    exit();
                                }

                                $log = $result->fetch_assoc();
                                $stmt->close();

                                // Passport Path Traversal check logic
                                $passport = trim($log['passport'] ?? '');
                                $passport_src = '';

                                if (!empty($passport)) {
                                    $paths_to_test = [
                                        "uploads/" . $passport,
                                        "../uploads/" . $passport,
                                        "../../uploads/" . $passport
                                    ];
                                    foreach ($paths_to_test as $test_path) {
                                        if (file_exists($test_path) && !is_dir($test_path)) {
                                            $passport_src = $test_path;
                                            break;
                                        }
                                    }
                                }
                                ?>

                                <!-- CARD BODY -->
                                <div class="card-content">
                                    <div class="card-body p-4">

                                        <!-- Profile Overview Header with Clickable Passport -->
                                        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                                            <div>

                                                <p class="text-muted mb-0">Updating clinical logs for <strong><?php echo htmlspecialchars($log['staff_name']); ?></strong></p>
                                            </div>

                                            <!-- Passport Avatar Container -->
                                            <div>
                                                <?php if (!empty($passport_src)) { ?>
                                                    <div class="text-center">
                                                        <a href="#" data-bs-toggle="modal" data-bs-target="#passportModal" title="Click to enlarge passport photo">
                                                            <img src="<?php echo htmlspecialchars($passport_src); ?>"
                                                                alt="<?php echo htmlspecialchars($log['staff_name']); ?>"
                                                                class="rounded-circle img-thumbnail shadow-sm"
                                                                style="width: 130px; height: 130px; object-fit: cover; cursor: pointer; border: 3px solid #198754; transition: transform 0.2s;"
                                                                onmouseover="this.style.transform='scale(1.05)';"
                                                                onmouseout="this.style.transform='scale(1)';" />
                                                        </a>
                                                        <div class="small text-muted mt-1" style="font-size: 0.72rem;">Click to expand</div>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center border" style="width: 130px; height: 130px; font-size: 0.75rem; font-weight: 600;">
                                                        No Photo
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>

                                        <form action="proc_edit_log.php" method="POST">

                                            <!-- HIDDEN ID -->
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

                                            <div class="row">

                                                <!-- Staff Name -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Staff Name</label>
                                                    <input type="text" name="staff_name"
                                                        value="<?php echo htmlspecialchars($log['staff_name']); ?>"
                                                        class="form-control form-control-lg" readonly>
                                                </div>

                                                <!-- Staff Branch -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Staff Branch</label>
                                                    <input type="text" name="staff_branch"
                                                        value="<?php echo htmlspecialchars($log['staff_branch']); ?>"
                                                        class="form-control form-control-lg" readonly>
                                                </div>

                                                <!-- Department -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Department</label>
                                                    <input type="text" name="department"
                                                        value="<?php echo htmlspecialchars($log['department']); ?>"
                                                        class="form-control form-control-lg" readonly>
                                                </div>

                                                <!-- Attended By -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Attended By (Medical Personnel)</label>
                                                    <input type="text" name="attended_by"
                                                        value="<?php echo htmlspecialchars($log['attended_by']); ?>"
                                                        class="form-control form-control-lg" required>
                                                </div>

                                                <!-- Intake Time -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Intake Time</label>
                                                    <input type="datetime-local" name="intake_time"
                                                        value="<?php echo date('Y-m-d\TH:i', strtotime($log['intake_time'])); ?>"
                                                        class="form-control form-control-lg" required>
                                                </div>

                                                <!-- Release Time -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Release Time</label>
                                                    <input type="datetime-local" name="release_time"
                                                        value="<?php echo !empty($log['release_time']) ? date('Y-m-d\TH:i', strtotime($log['release_time'])) : ''; ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- Diagnosis -->
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label fw-bold">Diagnosis</label>
                                                    <textarea name="diagnosis" rows="2" class="form-control form-control-lg" required><?php echo htmlspecialchars($log['diagnosis']); ?></textarea>
                                                </div>

                                                <!-- Symptoms -->
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label fw-bold">Symptoms</label>
                                                    <textarea name="symptoms" rows="2" class="form-control form-control-lg" required><?php echo htmlspecialchars($log['symptoms']); ?></textarea>
                                                </div>

                                                <!-- Medical Notes -->
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label fw-bold">Medical Notes</label>
                                                    <textarea name="medical_notes" rows="2" class="form-control form-control-lg"><?php echo htmlspecialchars($log['medical_notes']); ?></textarea>
                                                </div>

                                                <!-- Treatment Given -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Treatment Given</label>
                                                    <input type="text" name="treatment_given"
                                                        value="<?php echo htmlspecialchars($log['treatment_given']); ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- Drugs Given -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Drugs Given</label>
                                                    <input type="text" name="drugs_given"
                                                        value="<?php echo htmlspecialchars($log['drugs_given']); ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- Dosage Instructions -->
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label fw-bold">Dosage Instructions</label>
                                                    <input type="text" name="dosage_instructions"
                                                        value="<?php echo htmlspecialchars($log['dosage_instructions']); ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- Condition on Admission -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Condition on Admission</label>
                                                    <select name="condition_on_admission" class="form-select form-control-lg">
                                                        <option value="stable" <?php if (strtolower($log['condition_on_admission'] ?? '') === 'stable') echo 'selected'; ?>>Stable</option>
                                                        <option value="critical" <?php if (strtolower($log['condition_on_admission'] ?? '') === 'critical') echo 'selected'; ?>>Critical</option>
                                                        <option value="serious" <?php if (strtolower($log['condition_on_admission'] ?? '') === 'serious') echo 'selected'; ?>>Serious</option>
                                                        <option value="minor" <?php if (strtolower($log['condition_on_admission'] ?? '') === 'minor') echo 'selected'; ?>>Minor</option>
                                                    </select>
                                                </div>

                                                <!-- Condition on Release -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Condition on Release</label>
                                                    <select name="condition_on_release" class="form-select form-control-lg">
                                                        <option value="improved" <?php if (strtolower($log['condition_on_release'] ?? '') === 'improved') echo 'selected'; ?>>Improved</option>
                                                        <option value="stable" <?php if (strtolower($log['condition_on_release'] ?? '') === 'stable') echo 'selected'; ?>>Stable</option>
                                                        <option value="referred" <?php if (strtolower($log['condition_on_release'] ?? '') === 'referred') echo 'selected'; ?>>Referred</option>
                                                        <option value="deceased" <?php if (strtolower($log['condition_on_release'] ?? '') === 'deceased') echo 'selected'; ?>>Deceased</option>
                                                    </select>
                                                </div>

                                                <!-- Blood Pressure -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Blood Pressure</label>
                                                    <input type="text" name="blood_pressure"
                                                        value="<?php echo htmlspecialchars($log['blood_pressure']); ?>"
                                                        class="form-control form-control-lg" placeholder="e.g. 120/80">
                                                </div>

                                                <!-- Temperature -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Temperature (°C)</label>
                                                    <input type="text" name="temperature"
                                                        value="<?php echo htmlspecialchars($log['temperature']); ?>"
                                                        class="form-control form-control-lg" placeholder="e.g. 36.6">
                                                </div>

                                                <!-- Pulse Rate -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Pulse Rate (bpm)</label>
                                                    <input type="text" name="pulse_rate"
                                                        value="<?php echo htmlspecialchars($log['pulse_rate']); ?>"
                                                        class="form-control form-control-lg" placeholder="e.g. 72">
                                                </div>

                                                <!-- Follow Up Required -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Follow Up Required</label>
                                                    <select name="follow_up_required" class="form-select form-control-lg">
                                                        <option value="no" <?php if ($log['follow_up_required'] == 'no') echo 'selected'; ?>>No</option>
                                                        <option value="yes" <?php if ($log['follow_up_required'] == 'yes') echo 'selected'; ?>>Yes</option>
                                                    </select>
                                                </div>

                                                <!-- Follow Up Date -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Follow Up Date</label>
                                                    <input type="date" name="follow_up_date"
                                                        value="<?php echo htmlspecialchars($log['follow_up_date']); ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- Record Status -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Record Status</label>
                                                    <select name="record_status" class="form-select form-control-lg">
                                                        <option value="open" <?php if ($log['record_status'] == 'open') echo 'selected'; ?>>Open</option>
                                                        <option value="under_treatment" <?php if ($log['record_status'] == 'under_treatment') echo 'selected'; ?>>Under Treatment</option>
                                                        <option value="closed" <?php if ($log['record_status'] == 'closed') echo 'selected'; ?>>Closed</option>
                                                    </select>
                                                </div>

                                            </div>

                                            <!-- BUTTON -->
                                            <div class="mt-4">
                                                <button type="submit" name="update_log" class="btn btn-success btn-lg">
                                                    Update Medical Log
                                                </button>
                                                <a href="medical_records.php" class="btn btn-secondary btn-lg ms-2">Cancel</a>
                                            </div>

                                        </form>
                                    </div>
                                </div>

                            </div>

                            <!-- PASSPORT ENLARGEMENT MODAL -->
                            <?php if (!empty($passport_src)) { ?>
                                <div class="modal fade" id="passportModal" tabindex="-1" aria-labelledby="passportModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg">
                                            <div class="modal-header bg-dark text-white">
                                                <h5 class="modal-title" id="passportModalLabel">
                                                    Staff Passport: <?php echo htmlspecialchars($log['staff_name']); ?>
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center p-4 bg-light">
                                                <img src="<?php echo htmlspecialchars($passport_src); ?>"
                                                    alt="<?php echo htmlspecialchars($log['staff_name']); ?>"
                                                    class="img-fluid rounded border shadow-sm"
                                                    style="max-height: 450px; object-fit: contain;" />
                                            </div>
                                            <div class="modal-footer bg-white">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close Preview</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                        </div>
                    </div>
                </section>
                <!-- Edit Staff Section End -->
            </div>

            <?php
            include('./inc/footer.php');
            ?>
        </div>
    </div>

    <script>
        function previewPassport(event) {
            const input = event.target;
            const preview = document.getElementById('passportPreview');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = "block";
                };

                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script src="assets/js/main.js"></script>
</body>

</html>