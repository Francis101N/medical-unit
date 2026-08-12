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
    <title>Add Medical Record - Medical Unit</title>

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
                            <h3 class="mb-1">Add New Medical Record</h3>
                            <p class="text-subtitle text-muted mb-0">
                                Create a new medical record and assign their details and records.
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
                                        Add Medical Record
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div> <br>

                <!-- Add Medical Record Section -->
                <section class="section">
                    <div class="row justify-content-center" id="table-hover-row">
                        <div class="col-12 col-lg-10">

                            <div class="card shadow-sm border-0">

                                <!-- CARD HEADER -->
                                <div class="card-header bg-success text-white py-3">
                                    <h4 class="mb-0">Add New Medical Record</h4>
                                </div>

                                <br>

                                <?php
                                if (isset($_SESSION['msg'])) {
                                    $msg = $_SESSION['msg'];
                                    $msg_type = $_SESSION['msg_type'] ?? 'info';

                                    // Clear message after reading
                                    unset($_SESSION['msg']);
                                    unset($_SESSION['msg_type']);
                                ?>
                                    <div class="alert m-3 alert-<?php echo $msg_type; ?> alert-dismissible fade show">
                                        <?php echo htmlspecialchars($msg); ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php } ?>

                                <!-- CARD BODY -->
                                <div class="card-content">
                                    <div class="card-body p-4">

                                        <form action="proc_add_medical_record.php" method="POST">
                                            <div class="row">

                                                <!-- Select Staff Member -->
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label fw-bold">Select Staff <span class="text-danger">*</span></label>
                                                    <select name="staff_name" id="staffSelect" class="form-select form-control-lg" required>
                                                        <option value="">-- Select Staff Member --</option>
                                                        <?php
                                                        /** @var mysqli $conn */
                                                        include('./db.php');

                                                        // UPDATED QUERY: Added LEFT JOIN to fetch the branch name using branch_id
                                                        $query = "SELECT s.id, s.staff_id, s.fullname, b.branch_name FROM staffs s LEFT JOIN branches b ON s.branch_id = b.id ORDER BY s.fullname ASC";

                                                        $staff_query = mysqli_query($conn, $query);

                                                        while ($staff = mysqli_fetch_assoc($staff_query)) {
                                                            // Fallback message if a staff member isn't assigned to any branch yet
                                                            $branch_display = !empty($staff['branch_name']) ? $staff['branch_name'] : 'No Branch Assigned';
                                                        ?>
                                                            <!-- FIX: Passing staff_id instead of the primary key id to satisfy foreign key constraints -->
                                                            <!-- ADDED: data-branch attribute to store the fetched branch name securely -->
                                                            <option value="<?php echo htmlspecialchars($staff['staff_id']); ?>"
                                                                data-branch="<?php echo htmlspecialchars($branch_display); ?>">
                                                                <?php echo htmlspecialchars($staff['fullname']) . " (" . htmlspecialchars($staff['staff_id']) . ") - " . htmlspecialchars($branch_display); ?>
                                                            </option>
                                                        <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <!-- Intake Time -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Intake Time <span class="text-danger">*</span></label>
                                                    <input type="datetime-local" name="intake_time" class="form-control form-control-lg" required>
                                                </div>

                                                <!-- Release Time -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Release Time</label>
                                                    <input type="datetime-local" name="release_time" class="form-control form-control-lg">
                                                </div>

                                                <!-- Symptoms -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Symptoms</label>
                                                    <textarea name="symptoms" class="form-control form-control-lg" rows="3" placeholder="Describe symptoms..."></textarea>
                                                </div>

                                                <!-- Diagnosis -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Diagnosis</label>
                                                    <textarea name="diagnosis" class="form-control form-control-lg" rows="3" placeholder="Enter diagnosis..."></textarea>
                                                </div>

                                                <!-- Medical Notes -->
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label fw-bold">Medical Notes</label>
                                                    <textarea name="medical_notes" class="form-control form-control-lg" rows="3" placeholder="Additional observations or medical history notes..."></textarea>
                                                </div>

                                                <!-- Treatment Given -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Treatment Given</label>
                                                    <textarea name="treatment_given" class="form-control form-control-lg" rows="2" placeholder="Treatments administered..."></textarea>
                                                </div>

                                                <!-- Drugs Given -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Drugs Given</label>
                                                    <textarea name="drugs_given" class="form-control form-control-lg" rows="2" placeholder="List medications prescribed/administered..."></textarea>
                                                </div>

                                                <!-- Dosage Instructions -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Dosage Instructions</label>
                                                    <input type="text" name="dosage_instructions" class="form-control form-control-lg" placeholder="e.g. 2 tablets 3x daily after food">
                                                </div>

                                                <!-- Attended By -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Attended By (Doctor/Nurse)</label>
                                                    <input type="text" name="attended_by" class="form-control form-control-lg" placeholder="Name of medical personnel">
                                                </div>

                                                <!-- Condition on Admission Dropdown -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Condition on Admission</label>
                                                    <select name="condition_on_admission" class="form-select form-control-lg">
                                                        <option value="stable" selected>Stable</option>
                                                        <option value="critical">Critical</option>
                                                        <option value="serious">Serious</option>
                                                        <option value="minor">Minor</option>
                                                    </select>
                                                </div>

                                                <!-- Condition on Release Dropdown -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Condition on Release</label>
                                                    <select name="condition_on_release" class="form-select form-control-lg">
                                                        <option value="">-- Select Condition --</option>
                                                        <option value="improved">Improved</option>
                                                        <option value="stable">Stable</option>
                                                        <option value="referred">Referred</option>
                                                        <option value="deceased">Deceased</option>
                                                    </select>
                                                </div>

                                                <!-- Vitals: Blood Pressure -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Blood Pressure</label>
                                                    <input type="text" name="blood_pressure" class="form-control form-control-lg" placeholder="e.g. 120/80 mmHg">
                                                </div>

                                                <!-- Vitals: Temperature -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Temperature (°C)</label>
                                                    <input type="text" name="temperature" class="form-control form-control-lg" placeholder="e.g. 36.5">
                                                </div>

                                                <!-- Vitals: Pulse Rate -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Pulse Rate (BPM)</label>
                                                    <input type="text" name="pulse_rate" class="form-control form-control-lg" placeholder="e.g. 72">
                                                </div>

                                                <!-- Follow-up Required -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Follow-up Required?</label>
                                                    <select name="follow_up_required" class="form-select form-control-lg">
                                                        <option value="no">No</option>
                                                        <option value="yes">Yes</option>
                                                    </select>
                                                </div>

                                                <!-- Follow-up Date -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Follow-up Date</label>
                                                    <input type="date" name="follow_up_date" class="form-control form-control-lg">
                                                </div>

                                                <!-- Record Status Dropdown aligned with DB ENUM options -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Record Status</label>
                                                    <select name="record_status" class="form-select form-control-lg">
                                                        <option value="open" selected>Open</option>
                                                        <option value="under_treatment">Under Treatment</option>
                                                        <option value="closed">Closed / Completed</option>
                                                    </select>
                                                </div>

                                            </div>

                                            <!-- BUTTON -->
                                            <div class="mt-4">
                                                <button type="submit" name="submit" class="btn btn-success shadow-sm">
                                                    Save Medical Record
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </section>
                <!-- Add Staff Section End -->
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