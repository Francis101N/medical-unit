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
    <title>Comprehensive Patient Record - Medical Unit</title>

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
                            <h3 class="mb-1">New Patient Comprehensive Record</h3>
                            <p class="text-subtitle text-muted mb-0">
                                Register detailed patient demographics, emergency contacts, vital signs, and clinical notes.
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
                                        <a href="patient_medical_records.php">Patient Medical Records</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        New Record
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div> <br>

                <!-- Form Section -->
                <section class="section">
                    <div class="row justify-content-center" id="table-hover-row">
                        <div class="col-12 col-lg-11">

                            <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">

                                <!-- CARD HEADER -->
                                <div class="card-header bg-success text-white py-3 px-4">
                                    <h4 class="mb-0 text-white font-semibold"><i class="bi bi-file-medical-fill me-2"></i> Comprehensive Patient File</h4>
                                </div> <br>

                                <?php
                                if (isset($_SESSION['msg'])) {
                                    $msg = $_SESSION['msg'];
                                    $msg_type = $_SESSION['msg_type'] ?? 'info';
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

                                        <form action="proc_add_patient_record.php" method="POST">

                                            <!-- SECTION 1: SYSTEM LINK OR IDENTIFICATION -->
                                            <h5 class="text-success mb-3 border-bottom pb-2"><i class="bi bi-person-badge me-2"></i> Patient Identification & Affiliation</h5>
                                            <div class="row mb-4">
                                                <!-- Patient Full Name Input -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Patient Full Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="patient_name" class="form-control form-control-lg shadow-sm" placeholder="Enter patient's full name" required>
                                                </div>

                                                <!-- Patient Location / Outreach Location Select -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Patient Location <span class="text-danger">*</span></label>
                                                    <select name="patient_location" class="form-select form-control-lg shadow-sm" required>
                                                        <option value="">-- Select Outreach Location --</option>
                                                        <?php
                                                        /** @var mysqli $conn */
                                                        include('./db.php');

                                                        // Fetch distinct locations from the outreach table
                                                        $outreach_query = "SELECT DISTINCT location FROM outreach WHERE location IS NOT NULL AND location != '' ORDER BY location ASC";
                                                        $outreach_result = $conn->query($outreach_query);

                                                        if ($outreach_result && $outreach_result->num_rows > 0) {
                                                            while ($outreach = $outreach_result->fetch_assoc()) {
                                                        ?>
                                                                <option value="<?php echo htmlspecialchars($outreach['location']); ?>">
                                                                    <?php echo htmlspecialchars($outreach['location']); ?>
                                                                </option>
                                                        <?php
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                            </div>
                                            <!-- SECTION 2: PERSONAL & DEMOGRAPHIC DETAILS -->
                                            <h5 class="text-success mb-3 border-bottom pb-2"><i class="bi bi-person-lines-fill me-2"></i> Personal & Demographic Information</h5>
                                            <div class="row mb-4">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Date of Birth</label>
                                                    <input type="date" name="date_of_birth" class="form-control form-control-lg shadow-sm">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Gender</label>
                                                    <select name="gender" class="form-select form-control-lg shadow-sm">
                                                        <option value="">-- Select Gender --</option>
                                                        <option value="Male">Male</option>
                                                        <option value="Female">Female</option>
                                                        <option value="Other">Other</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Blood Group</label>
                                                    <select name="blood_group" class="form-select form-control-lg shadow-sm">
                                                        <option value="">-- Select Blood Type --</option>
                                                        <option value="A+">A+</option>
                                                        <option value="A-">A-</option>
                                                        <option value="B+">B+</option>
                                                        <option value="B-">B-</option>
                                                        <option value="AB+">AB+</option>
                                                        <option value="AB-">AB-</option>
                                                        <option value="O+">O+</option>
                                                        <option value="O-">O-</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Genotype</label>
                                                    <select name="genotype" class="form-select form-control-lg shadow-sm">
                                                        <option value="">-- Select Genotype --</option>
                                                        <option value="AA">AA</option>
                                                        <option value="AS">AS</option>
                                                        <option value="SS">SS</option>
                                                        <option value="AC">AC</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Phone Number</label>
                                                    <input type="text" name="phone_number" class="form-control form-control-lg shadow-sm" placeholder="e.g. 08012345678">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Residential Address</label>
                                                    <input type="text" name="residential_address" class="form-control form-control-lg shadow-sm" placeholder="Home address">
                                                </div>
                                            </div>

                                            <!-- SECTION 3: EMERGENCY CONTACT -->
                                            <h5 class="text-success mb-3 border-bottom pb-2"><i class="bi bi-shield-exclamation me-2"></i> Emergency Contact (Next of Kin)</h5>
                                            <div class="row mb-4">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Next of Kin Full Name</label>
                                                    <input type="text" name="next_of_kin_name" class="form-control form-control-lg shadow-sm" placeholder="Full name">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Relationship</label>
                                                    <input type="text" name="next_of_kin_relationship" class="form-control form-control-lg shadow-sm" placeholder="e.g. Spouse, Parent, Sibling">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Next of Kin Phone</label>
                                                    <input type="text" name="next_of_kin_phone" class="form-control form-control-lg shadow-sm" placeholder="Phone number">
                                                </div>
                                            </div>

                                            <!-- SECTION 4: MEDICAL HISTORY & ALLERGIES -->
                                            <h5 class="text-success mb-3 border-bottom pb-2"><i class="bi bi-heart-pulse me-2"></i> Clinical History & Allergies</h5>
                                            <div class="row mb-4">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Known Medical Allergies</label>
                                                    <textarea name="allergies" class="form-control form-control-lg shadow-sm" rows="2" placeholder="e.g. Penicillin, Sulfa drugs, Peanuts (or None)"></textarea>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Pre-existing Medical Conditions</label>
                                                    <textarea name="medical_history" class="form-control form-control-lg shadow-sm" rows="2" placeholder="e.g. Asthma, Hypertension, Diabetes (or None)"></textarea>
                                                </div>
                                            </div>

                                            <!-- SECTION 5: VISIT & VITAL SIGNS -->
                                            <h5 class="text-success mb-3 border-bottom pb-2"><i class="bi bi-activity me-2"></i> Current Visit & Vital Signs</h5>
                                            <div class="row mb-4">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Intake Time <span class="text-danger">*</span></label>
                                                    <input type="datetime-local" name="intake_time" class="form-control form-control-lg shadow-sm" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Release Time</label>
                                                    <input type="datetime-local" name="release_time" class="form-control form-control-lg shadow-sm">
                                                </div>

                                                <!-- Vitals Row 1 -->
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">Blood Pressure</label>
                                                    <input type="text" name="blood_pressure" class="form-control form-control-lg shadow-sm" placeholder="e.g. 120/80">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">Temperature (°C)</label>
                                                    <input type="text" name="temperature" class="form-control form-control-lg shadow-sm" placeholder="e.g. 36.5">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">Pulse Rate (BPM)</label>
                                                    <input type="text" name="pulse_rate" class="form-control form-control-lg shadow-sm" placeholder="e.g. 72">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">Respiratory Rate</label>
                                                    <input type="text" name="respiratory_rate" class="form-control form-control-lg shadow-sm" placeholder="e.g. 18 cpm">
                                                </div>

                                                <!-- Vitals Row 2 -->
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">Oxygen Saturation</label>
                                                    <input type="text" name="oxygen_saturation" class="form-control form-control-lg shadow-sm" placeholder="e.g. 98% SpO2">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">Weight (kg)</label>
                                                    <input type="text" name="weight" class="form-control form-control-lg shadow-sm" placeholder="e.g. 70 kg">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">Height (cm)</label>
                                                    <input type="text" name="height" class="form-control form-control-lg shadow-sm" placeholder="e.g. 175 cm">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">Blood Sugar / Glucose</label>
                                                    <input type="text" name="blood_sugar" class="form-control form-control-lg shadow-sm" placeholder="e.g. 95 mg/dL">
                                                </div>

                                                <!-- Conditions -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Condition on Admission</label>
                                                    <select name="condition_on_admission" class="form-select form-control-lg shadow-sm">
                                                        <option value="stable" selected>Stable</option>
                                                        <option value="critical">Critical</option>
                                                        <option value="serious">Serious</option>
                                                        <option value="minor">Minor</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Condition on Release</label>
                                                    <select name="condition_on_release" class="form-select form-control-lg shadow-sm">
                                                        <option value="">-- Select Condition --</option>
                                                        <option value="improved">Improved</option>
                                                        <option value="stable">Stable</option>
                                                        <option value="referred">Referred</option>
                                                        <option value="deceased">Deceased</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- SECTION 6: DIAGNOSIS & PRESCRIPTIONS -->
                                            <h5 class="text-success mb-3 border-bottom pb-2"><i class="bi bi-journal-medical me-2"></i> Diagnosis & Treatment</h5>
                                            <div class="row mb-4">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Symptoms</label>
                                                    <textarea name="symptoms" class="form-control form-control-lg shadow-sm" rows="3" placeholder="Describe presenting symptoms..."></textarea>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Diagnosis <span class="text-danger">*</span></label>
                                                    <textarea name="diagnosis" class="form-control form-control-lg shadow-sm" rows="3" placeholder="Enter clinical diagnosis..." required></textarea>
                                                </div>

                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label fw-bold">Medical Notes & Observations</label>
                                                    <textarea name="medical_notes" class="form-control form-control-lg shadow-sm" rows="3" placeholder="Additional observations, clinical examination notes..."></textarea>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Treatment Administered</label>
                                                    <textarea name="treatment_given" class="form-control form-control-lg shadow-sm" rows="2" placeholder="Procedures or immediate treatment given..."></textarea>
                                                </div>


                                                <!-- Drugs Given Catalog Select -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Drugs Prescribed / Administered</label>
                                                    <select name="drugs_given[]" class="form-select form-control-lg shadow-sm" multiple size="4" style="height: auto;">
                                                        <option value="" disabled>-- Select Inventory Asset(s) --</option>
                                                        <?php
                                                        $inventory_query = "SELECT item_name, category, unit, total_quantity FROM outreach_inventory ORDER BY item_name ASC";
                                                        $inventory_result = $conn->query($inventory_query);

                                                        if ($inventory_result && $inventory_result->num_rows > 0) {
                                                            while ($item = $inventory_result->fetch_assoc()) {
                                                                $item_name = $item['item_name'];
                                                                $category = !empty($item['category']) ? ' (' . $item['category'] . ')' : '';
                                                                $unit = !empty($item['unit']) ? ' [' . $item['unit'] . ']' : '';
                                                                $total_qty = intval($item['total_quantity'] ?? 0);

                                                                // Check if quantity is 0 or less to lock/disable
                                                                $is_out_of_stock = ($total_qty <= 0);
                                                                $disabled_attr = $is_out_of_stock ? ' disabled' : '';
                                                                $stock_label = $is_out_of_stock ? ' - [Out of Stock]' : ' [Stock: ' . $total_qty . ']';

                                                                $option_text = $item_name . $category . $unit . $stock_label;
                                                                $option_value = $item_name . (!empty($item['unit']) ? ' (' . $item['unit'] . ')' : '');
                                                        ?>
                                                                <option value="<?php echo htmlspecialchars($option_value); ?>" <?php echo $disabled_attr; ?>>
                                                                    <?php echo htmlspecialchars($option_text); ?>
                                                                </option>
                                                        <?php
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                    <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple medications. Zero-quantity items are locked.</small>
                                                </div>



                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label fw-bold">Dosage & Administration Instructions</label>
                                                    <input type="text" name="dosage_instructions" class="form-control form-control-lg shadow-sm" placeholder="e.g. 2 tablets 3x daily after food for 5 days">
                                                </div>

                                                <!-- Attended By -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Attended By (Medical Officer)</label>
                                                    <?php
                                                    $session_fullname = $_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Medical Personnel';
                                                    ?>
                                                    <input type="text" name="attended_by" class="form-control form-control-lg shadow-sm" value="<?php echo htmlspecialchars($session_fullname); ?>" readonly style="background-color: #f8f9fa;">
                                                </div>

                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Follow-up Required?</label>
                                                    <select name="follow_up_required" class="form-select form-control-lg shadow-sm">
                                                        <option value="no" selected>No</option>
                                                        <option value="yes">Yes</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Follow-up Date</label>
                                                    <input type="date" name="follow_up_date" class="form-control form-control-lg shadow-sm">
                                                </div>

                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Record Case Status</label>
                                                    <select name="record_status" class="form-select form-control-lg shadow-sm">
                                                        <option value="open" selected>Open</option>
                                                        <option value="under_treatment">Under Treatment</option>
                                                        <option value="closed">Closed / Completed</option>
                                                    </select>
                                                </div>

                                            </div>

                                            <!-- SUBMIT BUTTONS BAR -->
                                            <div class="mt-4 d-flex gap-2">
                                                <button type="submit" name="submit" class="btn btn-success shadow-sm px-4 py-2">
                                                    Save Record
                                                </button>
                                                <a href="patient_medical_records.php" class="btn btn-light border px-4 py-2 shadow-sm">
                                                    Cancel
                                                </a>
                                            </div>

                                        </form>

                                    </div>
                                </div>

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

    <script>
        document.getElementById('staffSelect').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var branch = selectedOption.getAttribute('data-branch') || '';
            document.getElementById('locationInput').value = branch;
        });
    </script>

    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>