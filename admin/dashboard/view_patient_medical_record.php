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

$record_id = $_GET['id'] ?? 0;
if (!$record_id || !is_numeric($record_id)) {
    echo "<script>alert('Invalid medical record identifier.'); window.location.href='outreach_locations_overview.php';</script>";
    exit();
}

// Fetch the patient medical record
$stmt = $conn->prepare("SELECT * FROM patient_medical_records WHERE id = ?");
$stmt->bind_param("i", $record_id);
$stmt->execute();
$record = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$record) {
    echo "<script>alert('Medical record not found.'); window.location.href='outreach_locations_overview.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patient Medical Record - Medical Unit</title>

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
        --border-color: #e2e8f0;
    }

    .record-card {
        border: none;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(226, 232, 240, 0.4);
    }

    .patient-avatar-lg {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .patient-avatar-placeholder-lg {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background-color: #f1f5f9;
        color: #94a3b8;
        font-size: 1.2rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px dashed var(--border-color);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .info-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .info-value {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-main);
    }

    .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-main);
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 0.5rem;
        margin-bottom: 1.25rem;
    }

    .vital-box {
        background: #f8fafc;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 0.85rem;
        text-align: center;
        height: 100%;
    }

    .badge-status {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.35rem 0.75rem;
        border-radius: 50rem;
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
                <div class="page-title mb-4">
                    <div class="row align-items-center g-3">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3 class="fw-bold text-dark mb-1">Patient Medical Details</h3>
                            <p class="text-subtitle text-muted mb-0">Comprehensive clinical profile for registered individual.</p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first text-start text-md-end">
                            <a href="outreach_locations_overview.php" class="btn btn-secondary btn-sm fw-bold">
                                <i class="bi bi-arrow-left"></i> Back to Locations
                            </a>
                        </div>
                    </div>
                </div>

                <section class="section">
                    <div class="row">
                        <!-- Left Sidebar: Basic Info & Profile -->
                        <div class="col-12 col-lg-4 mb-4">
                            <div class="card record-card p-4 text-center mb-4">
                                <div class="d-flex justify-content-center mb-3">
                                    <div class="patient-avatar-placeholder-lg">
                                        <?php 
                                            $name_parts = explode(' ', trim($record['patient_name'] ?? 'U'));
                                            $initials = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
                                            echo $initials;
                                        ?>
                                    </div>
                                </div>
                                <h4 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($record['patient_name'] ?? 'N/A'); ?></h4>
                                <p class="text-primary fw-semibold small mb-2"><i class="bi bi-geo-alt-fill"></i> <?php echo htmlspecialchars($record['patient_location'] ?? 'N/A'); ?></p>
                                
                                <div class="mb-3">
                                    <span class="badge bg-<?php echo ($record['record_status'] == 'open' ? 'success' : 'secondary'); ?> badge-status text-uppercase">
                                        Status: <?php echo htmlspecialchars($record['record_status'] ?? 'open'); ?>
                                    </span>
                                </div>

                                <div class="border-top pt-3 text-start">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="info-label">Gender</div>
                                            <div class="info-value"><?php echo htmlspecialchars($record['gender'] ?? 'N/A'); ?></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-label">Date of Birth</div>
                                            <div class="info-value"><?php echo htmlspecialchars($record['date_of_birth'] ?? 'N/A'); ?></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-label">Blood Group</div>
                                            <div class="info-value text-danger font-monospace"><?php echo htmlspecialchars($record['blood_group'] ?? 'N/A'); ?></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-label">Genotype</div>
                                            <div class="info-value font-monospace"><?php echo htmlspecialchars($record['genotype'] ?? 'N/A'); ?></div>
                                        </div>
                                        <div class="col-12">
                                            <div class="info-label">Phone Number</div>
                                            <div class="info-value"><?php echo htmlspecialchars($record['phone_number'] ?? 'N/A'); ?></div>
                                        </div>
                                        <div class="col-12">
                                            <div class="info-label">Residential Address</div>
                                            <div class="info-value"><?php echo htmlspecialchars($record['residential_address'] ?? 'N/A'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Next of Kin Card -->
                            <div class="card record-card p-4">
                                <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom">Next of Kin Details</h6>
                                <div class="mb-3">
                                    <div class="info-label">Name</div>
                                    <div class="info-value"><?php echo htmlspecialchars($record['next_of_kin_name'] ?? 'N/A'); ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="info-label">Relationship</div>
                                    <div class="info-value"><?php echo htmlspecialchars($record['next_of_kin_relationship'] ?? 'N/A'); ?></div>
                                </div>
                                <div class="mb-0">
                                    <div class="info-label">Phone Number</div>
                                    <div class="info-value"><?php echo htmlspecialchars($record['next_of_kin_phone'] ?? 'N/A'); ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Main Content: Vitals, Clinical Findings & Treatment -->
                        <div class="col-12 col-lg-8">
                            <!-- Vitals Signs Grid -->
                            <div class="card record-card p-4 mb-4">
                                <h5 class="section-title"><i class="bi bi-heart-pulse text-danger"></i> Vital Signs</h5>
                                <div class="row g-3">
                                    <div class="col-6 col-md-4">
                                        <div class="vital-box">
                                            <div class="info-label">Blood Pressure</div>
                                            <div class="info-value text-dark"><?php echo htmlspecialchars($record['blood_pressure'] ?? 'N/A'); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="vital-box">
                                            <div class="info-label">Temperature</div>
                                            <div class="info-value text-dark"><?php echo htmlspecialchars($record['temperature'] ?? 'N/A'); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="vital-box">
                                            <div class="info-label">Pulse Rate</div>
                                            <div class="info-value text-dark"><?php echo htmlspecialchars($record['pulse_rate'] ?? 'N/A'); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="vital-box">
                                            <div class="info-label">Respiratory Rate</div>
                                            <div class="info-value text-dark"><?php echo htmlspecialchars($record['respiratory_rate'] ?? 'N/A'); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="vital-box">
                                            <div class="info-label">Oxygen Saturation</div>
                                            <div class="info-value text-dark"><?php echo htmlspecialchars($record['oxygen_saturation'] ?? 'N/A'); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="vital-box">
                                            <div class="info-label">Blood Sugar</div>
                                            <div class="info-value text-dark"><?php echo htmlspecialchars($record['blood_sugar'] ?? 'N/A'); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-6">
                                        <div class="vital-box">
                                            <div class="info-label">Weight</div>
                                            <div class="info-value text-dark"><?php echo htmlspecialchars($record['weight'] ?? 'N/A'); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-6">
                                        <div class="vital-box">
                                            <div class="info-label">Height</div>
                                            <div class="info-value text-dark"><?php echo htmlspecialchars($record['height'] ?? 'N/A'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Clinical Information & History -->
                            <div class="card record-card p-4 mb-4">
                                <h5 class="section-title"><i class="bi bi-clipboard2-pulse text-primary"></i> Clinical Profile & Findings</h5>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <div class="info-label">Allergies</div>
                                        <div class="info-value bg-light p-2 rounded border"><?php echo nl2br(htmlspecialchars($record['allergies'] ?? 'None recorded')); ?></div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="info-label">Medical History</div>
                                        <div class="info-value bg-light p-2 rounded border"><?php echo nl2br(htmlspecialchars($record['medical_history'] ?? 'None recorded')); ?></div>
                                    </div>
                                    <div class="col-12">
                                        <div class="info-label">Symptoms</div>
                                        <div class="info-value bg-light p-3 rounded border"><?php echo nl2br(htmlspecialchars($record['symptoms'] ?? 'No symptoms recorded.')); ?></div>
                                    </div>
                                    <div class="col-12">
                                        <div class="info-label">Diagnosis</div>
                                        <div class="info-value bg-light p-3 rounded border text-danger fw-bold"><?php echo nl2br(htmlspecialchars($record['diagnosis'] ?? 'No diagnosis recorded.')); ?></div>
                                    </div>
                                    <div class="col-12">
                                        <div class="info-label">Medical Notes</div>
                                        <div class="info-value bg-light p-3 rounded border"><?php echo nl2br(htmlspecialchars($record['medical_notes'] ?? 'No additional medical notes.')); ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Treatment & Prescriptions -->
                            <div class="card record-card p-4 mb-4">
                                <h5 class="section-title"><i class="bi bi-capsule text-success"></i> Treatment & Prescriptions</h5>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="info-label">Treatment Given</div>
                                        <div class="info-value bg-light p-3 rounded border"><?php echo nl2br(htmlspecialchars($record['treatment_given'] ?? 'None recorded.')); ?></div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="info-label">Drugs Given</div>
                                        <div class="info-value bg-light p-3 rounded border"><?php echo nl2br(htmlspecialchars($record['drugs_given'] ?? 'None recorded.')); ?></div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="info-label">Dosage Instructions</div>
                                        <div class="info-value bg-light p-3 rounded border"><?php echo nl2br(htmlspecialchars($record['dosage_instructions'] ?? 'None recorded.')); ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Administrative & Timeline Metadata -->
                            <div class="card record-card p-4">
                                <h5 class="section-title"><i class="bi bi-info-circle text-secondary"></i> Administrative & Visit Metadata</h5>
                                <div class="row g-3">
                                    <div class="col-6 col-md-3">
                                        <div class="info-label">Condition on Admission</div>
                                        <div class="info-value text-capitalize"><?php echo htmlspecialchars($record['condition_on_admission'] ?? 'Stable'); ?></div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="info-label">Condition on Release</div>
                                        <div class="info-value text-capitalize"><?php echo htmlspecialchars($record['condition_on_release'] ?? 'N/A'); ?></div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="info-label">Intake Time</div>
                                        <div class="info-value"><?php echo htmlspecialchars($record['intake_time'] ?? 'N/A'); ?></div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="info-label">Release Time</div>
                                        <div class="info-value"><?php echo htmlspecialchars($record['release_time'] ?? 'N/A'); ?></div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="info-label">Attended By</div>
                                        <div class="info-value"><?php echo htmlspecialchars($record['attended_by'] ?? 'N/A'); ?></div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="info-label">Follow-up Required</div>
                                        <div class="info-value text-uppercase"><?php echo htmlspecialchars($record['follow_up_required'] ?? 'No'); ?></div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="info-label">Follow-up Date</div>
                                        <div class="info-value"><?php echo htmlspecialchars($record['follow_up_date'] ?? 'N/A'); ?></div>
                                    </div>
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
    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>