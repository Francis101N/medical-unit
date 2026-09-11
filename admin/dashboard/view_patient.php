<?php

/** @var mysqli $conn */
include('./db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

// Decrypt ID function matching the log directory lists
if (!function_exists('decryptId')) {
    function decryptId($hash)
    {
        $key = "medical-secret-key";
        $decoded = base64_decode(strtr($hash, '-_', '+/'));
        if ($decoded === false) {
            return false;
        }
        $parts = explode('|', $decoded);

        if (count($parts) !== 2 || $parts[1] !== $key) {
            return false;
        }

        return $parts[0];
    }
}

$encrypted_id = $_GET['id'] ?? '';
$log_id_pk = decryptId($encrypted_id);

if (!$log_id_pk) {
    die("Invalid or tampered medical log record reference.");
}

// Fetch patient medical record from patient_medical_records table
$stmt = $conn->prepare("SELECT * FROM patient_medical_records WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $log_id_pk);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Medical log record not found.");
}

$log = $result->fetch_assoc();
$stmt->close();

// Role-based access control check (non-super-admin can only view records matching their assigned location/branch)
$user_role = strtolower($_SESSION['role'] ?? '');
$user_branch = $_SESSION['branch'] ?? '';

if ($user_role !== 'super-admin') {
    if (strtolower(trim($log['patient_location'] ?? '')) !== strtolower(trim($user_branch))) {
        die("Access denied: You do not have permission to view medical records outside your assigned location.");
    }
}

// Helper for displaying empty/null values gracefully
if (!function_exists('displayValue')) {
    function displayValue($val, $suffix = '')
    {
        return (!empty($val) && trim($val) !== '') ? htmlspecialchars($val) . $suffix : '<span class="text-muted">—</span>';
    }
}

$record_status = strtolower($log['record_status'] ?? '');
$status_class = match ($record_status) {
    'open' => 'bg-success-subtle text-success',
    'under_treatment' => 'bg-warning-subtle text-warning',
    default => 'bg-secondary-subtle text-secondary'
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Medical Log - <?php echo htmlspecialchars($log['staff_name']); ?></title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }

        .passport-view {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #dee2e6;
        }

        .vital-badge {
            font-size: 0.9rem;
            padding: 0.5em 0.8em;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <!-- Navigation and Actions Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="patient_medical_records.php" class="btn btn-outline-secondary btn-sm">&larr; Back to Patient Records</a>
            <div class="d-flex gap-2">
                <a href="referral-letter.php?ref_id=<?php echo urlencode($encrypted_id); ?>" class="btn btn-outline-success btn-sm" target="_blank">Generate Referral</a>
                <a href="edit_log.php?id=<?php echo urlencode($encrypted_id); ?>" class="btn btn-primary btn-sm">Edit Log</a>
            </div>
        </div>

        <!-- Patient Header Profile Card -->
        <div class="card p-4 mb-4 border-0 shadow-sm" style="border-radius: 16px;">
            <div class="row align-items-center">
                <div class="col-md">
                    <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($log['patient_name']); ?></h3>
                    <p class="text-muted mb-2">
                        Location: <strong><?php echo htmlspecialchars($log['patient_location']); ?></strong> |
                        Phone: <strong><?php echo displayValue($log['phone_number']); ?></strong>
                    </p>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge bg-light text-dark border">Gender: <?php echo displayValue($log['gender']); ?></span>
                        <span class="badge bg-light text-dark border">DOB: <?php echo displayValue($log['date_of_birth']); ?></span>
                        <span class="badge bg-light text-dark border">Blood Group: <?php echo displayValue($log['blood_group']); ?></span>
                        <span class="badge bg-light text-dark border">Genotype: <?php echo displayValue($log['genotype']); ?></span>
                        <span class="badge <?php echo $status_class; ?> border text-uppercase">Status: <?php echo ucwords(str_replace('_', ' ', $log['record_status'])); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Vitals & Intake Clinical Data -->
            <div class="col-lg-5">
                <div class="card p-4 h-100 border-0 shadow-sm" style="border-radius: 16px;">
                    <h5 class="fw-bold text-primary mb-3">Clinical Vitals & Timing</h5>
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                            <span class="text-muted">Blood Pressure</span>
                            <span class="badge bg-primary vital-badge"><?php echo displayValue($log['blood_pressure']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                            <span class="text-muted">Temperature</span>
                            <span class="badge bg-warning text-dark vital-badge"><?php echo displayValue($log['temperature'], '°C'); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                            <span class="text-muted">Pulse Rate</span>
                            <span class="badge bg-info text-dark vital-badge"><?php echo displayValue($log['pulse_rate'], ' bpm'); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                            <span class="text-muted">Respiratory Rate</span>
                            <span class="badge bg-secondary vital-badge"><?php echo displayValue($log['respiratory_rate']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                            <span class="text-muted">Oxygen Saturation</span>
                            <span class="badge bg-success vital-badge"><?php echo displayValue($log['oxygen_saturation']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                            <span class="text-muted">Blood Sugar</span>
                            <span class="badge bg-dark vital-badge"><?php echo displayValue($log['blood_sugar']); ?></span>
                        </li>
                    </ul>

                    <table class="table table-borderless align-middle mb-0 small">
                        <tr>
                            <th class="text-muted ps-0">Intake Time:</th>
                            <td><?php echo htmlspecialchars($log['intake_time']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted ps-0">Release Time:</th>
                            <td><?php echo displayValue($log['release_time']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted ps-0">Condition on Admission:</th>
                            <td><?php echo displayValue($log['condition_on_admission']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted ps-0">Condition on Release:</th>
                            <td><?php echo displayValue($log['condition_on_release']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted ps-0">Weight / Height:</th>
                            <td><?php echo displayValue($log['weight']); ?> / <?php echo displayValue($log['height']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Medical Evaluation & Treatment Details -->
            <div class="col-lg-7">
                <div class="card p-4 h-100 border-0 shadow-sm" style="border-radius: 16px;">
                    <h5 class="fw-bold text-success mb-3">Diagnosis & Prescription</h5>

                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block mb-1">DIAGNOSIS</label>
                        <div class="p-2 bg-light rounded text-dark"><?php echo nl2br(displayValue($log['diagnosis'])); ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block mb-1">SYMPTOMS</label>
                        <div class="p-2 bg-light rounded text-dark"><?php echo nl2br(displayValue($log['symptoms'])); ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block mb-1">TREATMENT GIVEN</label>
                        <div class="p-2 bg-light rounded text-dark"><?php echo nl2br(displayValue($log['treatment_given'])); ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block mb-1">DRUGS GIVEN</label>
                        <div class="p-2 bg-light rounded text-danger fw-medium"><?php echo nl2br(displayValue($log['drugs_given'])); ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small fw-bold d-block mb-1">DRUG INSTRUCTIONS</label>
                        <div class="p-2 mt-1 bg-light rounded text-muted small"><?php echo nl2br(displayValue($log['dosage_instructions'])); ?></div>
                    </div>

                    <?php if (!empty($log['medical_notes'])) { ?>
                        <div class="mb-3">
                            <label class="text-muted small fw-bold d-block mb-1">MEDICAL NOTES / REMARKS</label>
                            <div class="p-2 bg-light rounded text-dark"><?php echo nl2br(htmlspecialchars($log['medical_notes'])); ?></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- Background, Emergency & Administrative Metadata Row -->
        <div class="row g-4 mt-2">
            <div class="col-lg-6">
                <div class="card p-4 h-100 border-0 shadow-sm" style="border-radius: 16px;">
                    <h5 class="fw-bold text-danger mb-3">Emergency Contact & History</h5>
                    <ul class="list-group list-group-flush mb-0 small">
                        <li class="list-group-item px-0 bg-transparent">
                            <span class="text-muted d-block">Next of Kin Name</span>
                            <strong><?php echo displayValue($log['next_of_kin_name']); ?> (<?php echo displayValue($log['next_of_kin_relationship']); ?>)</strong>
                        </li>
                        <li class="list-group-item px-0 bg-transparent">
                            <span class="text-muted d-block">Next of Kin Phone</span>
                            <strong><?php echo displayValue($log['next_of_kin_phone']); ?></strong>
                        </li>
                        <li class="list-group-item px-0 bg-transparent">
                            <span class="text-muted d-block">Known Allergies</span>
                            <span class="text-danger"><?php echo displayValue($log['allergies']); ?></span>
                        </li>
                        <li class="list-group-item px-0 bg-transparent">
                            <span class="text-muted d-block">Past Medical History</span>
                            <span><?php echo displayValue($log['medical_history']); ?></span>
                        </li>
                        <li class="list-group-item px-0 bg-transparent">
                            <span class="text-muted d-block">Residential Address</span>
                            <span><?php echo displayValue($log['residential_address']); ?></span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card p-4 h-100 border-0 shadow-sm" style="border-radius: 16px;">
                    <h5 class="fw-bold text-secondary mb-3">Administrative Metadata</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <span class="text-muted d-block small">Attended By</span>
                            <strong><?php echo displayValue($log['attended_by']); ?></strong>
                        </div>
                        <div class="col-12">
                            <span class="text-muted d-block small">Follow-Up Required</span>
                            <strong><?php echo ucfirst(displayValue($log['follow_up_required'])); ?> (<?php echo displayValue($log['follow_up_date'], 'None'); ?>)</strong>
                        </div>
                        <div class="col-12">
                            <span class="text-muted d-block small">Log Timestamps</span>
                            <small class="text-muted">Created: <?php echo displayValue($log['created_at']); ?><br>Updated: <?php echo displayValue($log['updated_at']); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>