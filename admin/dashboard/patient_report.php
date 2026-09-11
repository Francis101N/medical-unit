<?php

/** @var mysqli $conn */
include('./db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_role = strtolower($_SESSION['role'] ?? '');
$user_branch = trim($_SESSION['branch'] ?? '');

// Decrypt ID helper function
function decryptId($encryptedId)
{
    $key = "medical-secret-key";
    $decoded = base64_decode(strtr($encryptedId, '-_', '+/'));
    $parts = explode('|', $decoded);
    return ($parts[1] === $key) ? $parts[0] : false;
}

$encrypted_id = $_GET['id'] ?? '';
$id = decryptId($encrypted_id);

if (!$id) {
    die("Invalid or tampered record identifier.");
}

// Fetch record securely with branch scoping for adhoc users
if ($user_role === 'adhoc-user' && !empty($user_branch)) {
    $stmt = $conn->prepare("SELECT * FROM patient_medical_records WHERE id = ? AND LOWER(TRIM(patient_location)) = LOWER(TRIM(?))");
    $stmt->bind_param("is", $id, $user_branch);
} else {
    $stmt = $conn->prepare("SELECT * FROM patient_medical_records WHERE id = ?");
    $stmt->bind_param("i", $id);
}

$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    die("Medical record not found or access denied.");
}

$row = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Report - <?php echo htmlspecialchars($row['patient_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
            font-family: Arial, sans-serif;
        }

        .report-container {
            max-width: 850px;
            margin: 40px auto;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }

        .report-header {
            border-bottom: 2px solid #435ebe;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: bold;
            color: #435ebe;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
            margin-top: 25px;
            margin-bottom: 15px;
        }

        .vitals-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }

        .vitals-label {
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: bold;
        }

        .vitals-value {
            font-size: 1.1rem;
            font-weight: bold;
            color: #212529;
        }

        .btn-theme {
            background-color: #435ebe;
            border-color: #435ebe;
            color: #fff;
        }

        .btn-theme:hover {
            background-color: #384ea8;
            border-color: #384ea8;
            color: #fff;
        }

        .report-logo {
            max-height: 60px;
            max-width: 180px;
            object-fit: contain;
        }

        @media print {
            body {
                background: #fff;
            }

            .report-container {
                box-shadow: none;
                margin: 0;
                padding: 15px;
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="container report-container">
        <!-- Action Toolbar & Logo Selector Form Control -->
        <div class="row align-items-center mb-4 no-print g-3 bg-light p-3 rounded border">
            <div class="col-md-5">
                <div class="mb-0">
                    <label class="form-label fw-semibold small mb-1">Company Logo</label>
                    <select class="form-select form-select-sm" id="logoSelect">
                        <option value="" selected disabled>-- Select Company Logo --</option>
                        <option value="./assets/images/umunna-logo.jpeg">Umunna Foundation</option>
                        <option value="./assets/images/Equal-logo.png">Equal Logistics Limited</option>
                        <option value="./assets/images/visco.jpeg">Viscosupport</option>
                        <option value="./assets/images/upstream.jpeg">Upstream DC</option>
                        <option value="./assets/images/cannax.jpeg">Cannax</option>
                        <option value="./assets/images/idiaa.jpeg">Idiaa</option>
                    </select>
                </div>
            </div>
            <div class="col-md-7 text-end d-flex justify-content-end align-items-center gap-2">
                <a href="patient_medical_records.php" class="btn btn-outline-secondary btn-sm">&larr; Back</a>
                <button onclick="window.print();" class="btn btn-theme btn-sm">Print / Save as PDF</button>
            </div>
        </div>

        <!-- Header -->
        <div class="report-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div id="logoContainer" style="display: none;">
                    <img id="dynamicLogoImg" src="" alt="Company Logo" class="report-logo">
                </div>
                <div>
                    <h3 class="mb-1 fw-bold" style="color: #435ebe;">OUTREACH MEDICAL REPORT</h3>
                    <p class="text-muted mb-0">Official Patient Clinical Summary & Treatment Record</p>
                </div>
            </div>
            <div class="text-end">
                <h5 class="mb-0">Location: <?php echo htmlspecialchars($row['patient_location']); ?></h5>
                <small class="text-muted">Generated: <?php echo date('Y-m-d H:i'); ?></small>
            </div>
        </div>

        <!-- Patient Demographics -->
        <div class="section-title">1. Patient Information</div>
        <div class="row g-3">
            <div class="col-md-6">
                <strong>Full Name:</strong> <?php echo htmlspecialchars($row['patient_name']); ?>
            </div>
            <div class="col-md-3">
                <strong>Gender:</strong> <?php echo htmlspecialchars($row['gender'] ?? '—'); ?>
            </div>
            <div class="col-md-3">
                <strong>Date of Birth:</strong> <?php echo htmlspecialchars($row['date_of_birth'] ?? '—'); ?>
            </div>
            <div class="col-md-6">
                <strong>Phone Number:</strong> <?php echo htmlspecialchars($row['phone_number'] ?? '—'); ?>
            </div>
            <div class="col-md-3">
                <strong>Blood Group:</strong> <?php echo htmlspecialchars($row['blood_group'] ?? '—'); ?>
            </div>
            <div class="col-md-3">
                <strong>Genotype:</strong> <?php echo htmlspecialchars($row['genotype'] ?? '—'); ?>
            </div>
        </div>

        <!-- Clinical Vitals -->
        <div class="section-title">2. Clinical Vitals At Assessment</div>
        <div class="row g-2">
            <div class="col-md-3 col-6">
                <div class="vitals-box">
                    <div class="vitals-label">Blood Pressure</div>
                    <div class="vitals-value"><?php echo htmlspecialchars($row['blood_pressure'] ?: '—'); ?></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="vitals-box">
                    <div class="vitals-label">Temperature</div>
                    <div class="vitals-value"><?php echo htmlspecialchars($row['temperature'] ? $row['temperature'] . '°C' : '—'); ?></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="vitals-box">
                    <div class="vitals-label">Pulse Rate</div>
                    <div class="vitals-value"><?php echo htmlspecialchars($row['pulse_rate'] ? $row['pulse_rate'] . ' bpm' : '—'); ?></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="vitals-box">
                    <div class="vitals-label">Blood Sugar</div>
                    <div class="vitals-value"><?php echo htmlspecialchars($row['blood_sugar'] ?: '—'); ?></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="vitals-box">
                    <div class="vitals-label">Resp. Rate</div>
                    <div class="vitals-value"><?php echo htmlspecialchars($row['respiratory_rate'] ?: '—'); ?></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="vitals-box">
                    <div class="vitals-label">Oxygen Sat.</div>
                    <div class="vitals-value"><?php echo htmlspecialchars($row['oxygen_saturation'] ?: '—'); ?></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="vitals-box">
                    <div class="vitals-label">Weight</div>
                    <div class="vitals-value"><?php echo htmlspecialchars($row['weight'] ?: '—'); ?></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="vitals-box">
                    <div class="vitals-label">Height</div>
                    <div class="vitals-value"><?php echo htmlspecialchars($row['height'] ?: '—'); ?></div>
                </div>
            </div>
        </div>

        <!-- Medical Evaluation -->
        <div class="section-title">3. Evaluation & Clinical Notes</div>
        <div class="row g-3">
            <div class="col-12">
                <strong>Presenting Symptoms:</strong>
                <p class="bg-light p-2 rounded mb-0"><?php echo nl2br(htmlspecialchars($row['symptoms'] ?: '—')); ?></p>
            </div>
            <div class="col-12">
                <strong>Clinical Diagnosis:</strong>
                <p class="bg-light p-2 rounded mb-0 text-danger fw-medium"><?php echo nl2br(htmlspecialchars($row['diagnosis'] ?: '—')); ?></p>
            </div>
            <div class="col-12">
                <strong>Medical Notes:</strong>
                <p class="bg-light p-2 rounded mb-0"><?php echo nl2br(htmlspecialchars($row['medical_notes'] ?: '—')); ?></p>
            </div>
        </div>

        <!-- Treatment & Prescription -->
        <div class="section-title">4. Treatment & Prescription Given</div>
        <div class="row g-3">
            <div class="col-12">
                <strong>Treatment Administered:</strong>
                <p class="bg-light p-2 rounded mb-0"><?php echo nl2br(htmlspecialchars($row['treatment_given'] ?: '—')); ?></p>
            </div>
            <div class="col-md-6">
                <strong>Drugs Dispensed:</strong>
                <p class="bg-light p-2 rounded mb-0 text-success fw-medium"><?php echo nl2br(htmlspecialchars($row['drugs_given'] ?: '—')); ?></p>
            </div>
            <div class="col-md-6">
                <strong>Dosage Instructions:</strong>
                <p class="bg-light p-2 rounded mb-0"><?php echo nl2br(htmlspecialchars($row['dosage_instructions'] ?: '—')); ?></p>
            </div>
        </div>

        <!-- Status & Follow up -->
        <div class="section-title">5. Status & Disposition</div>
        <div class="row g-3">
            <div class="col-md-4">
                <strong>Condition on Intake:</strong><br>
                <span><?php echo htmlspecialchars($row['condition_on_admission'] ?: '—'); ?></span>
            </div>
            <div class="col-md-4">
                <strong>Condition on Release:</strong><br>
                <span><?php echo htmlspecialchars($row['condition_on_release'] ?: '—'); ?></span>
            </div>
            <div class="col-md-4">
                <strong>Record Status:</strong><br>
                <span class="badge bg-secondary"><?php echo ucwords(str_replace('_', ' ', $row['record_status'])); ?></span>
            </div>
            <div class="col-md-6">
                <strong>Follow-up Required:</strong>
                <span><?php echo ucfirst(htmlspecialchars($row['follow_up_required'])); ?></span>
                <?php if (!empty($row['follow_up_date'])): ?>
                    (Date: <?php echo htmlspecialchars($row['follow_up_date']); ?>)
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <strong>Attending Medical Personnel:</strong>
                <span class="fw-bold"><?php echo htmlspecialchars($row['attended_by']); ?></span>
            </div>
        </div>

        <!-- Footer Signature Note -->
        <div class="mt-5 pt-4 border-top text-muted small d-flex justify-content-between">
            <div>Intake Time: <?php echo htmlspecialchars($row['intake_time']); ?> | Release Time: <?php echo htmlspecialchars($row['release_time'] ?: '—'); ?></div>
            <div>Authorized Medical Documentation</div>
        </div>
    </div>

    <script>
        document.getElementById('logoSelect').addEventListener('change', function() {
            const selectedUrl = this.value;
            const logoContainer = document.getElementById('logoContainer');
            const logoImg = document.getElementById('dynamicLogoImg');

            if (selectedUrl) {
                logoImg.src = selectedUrl;
                logoContainer.style.display = 'block';
            } else {
                logoImg.src = '';
                logoContainer.style.display = 'none';
            }
        });
    </script>
</body>

</html>