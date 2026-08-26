<?php
// referral-letter.php
// Standalone web page to generate, preview, download, and print corporate medical referral notes in a compact single-page layout.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Restrict access: Only allow super admins with a JavaScript alert popup
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super-admin') {
    echo "<script>
        alert('Unauthorized access! Only super administrators can access this page.');
        window.location.href = 'index.php';
    </script>";
    exit();
}

/** @var mysqli $conn */
include('./db.php');

// Default fallback values
$staff_data = [
    'note_no' => rand(1000000, 9999999),
    'ref_code' => rand(1000, 9999),
    'staff_name' => '',
    'department' => '',
    'staff_sex' => '',
    'staff_age' => '',
    'form_date' => date('d M Y'),
    'form_time' => '',
    'form_height' => '',
    'form_wt' => '',
    'vital_temp' => '',
    'vital_pulse' => '',
    'vital_res' => '',
    'vital_bp_spo' => '',
    'client_complaint' => 'Nil',
    'complaint_duration' => '',
    'medical_unit' => '',
    'prev_medication' => '',
    'investigation_done' => 'YES',
    'investigation_what' => '',
    'reason_for_referral' => '',
    'referring_officer' => $_SESSION['fullname'] ?? 'Lady Grace Useli (Chief Med.)',
    'officer_contact' => $_SESSION['phone'] ?? ''
];

// Check if a referral record ID is passed via URL
if (isset($_GET['ref_id'])) {
    $encrypted_id = $_GET['ref_id'];

    // Decryption helper function matching your workspace table encryption key
    function decryptId($data)
    {
        $key = "medical-secret-key";
        $decoded = base64_decode(strtr($data, '-_', '+/'));
        $parts = explode('|', $decoded);
        return (isset($parts[1]) && $parts[1] === $key) ? $parts[0] : false;
    }

    $id = decryptId($encrypted_id);

    if ($id) {
        $query = "SELECT smr.*, s.passport, s.gender, s.department as staff_dept 
                  FROM staff_medical_records smr 
                  LEFT JOIN staffs s ON smr.staff_name = s.fullname 
                  WHERE smr.id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $staff_data['note_no'] = $row['id'] + 1000000;
            $staff_data['ref_code'] = rand(1000, 9999);
            $staff_data['staff_name'] = $row['staff_name'] ?? '';
            $staff_data['department'] = $row['department'] ?? $row['staff_dept'] ?? '';
            $staff_data['staff_sex'] = $row['gender'] ?? 'Male';
            $staff_data['form_date'] = !empty($row['created_at']) ? date('d M Y', strtotime($row['created_at'])) : date('d M Y');
            $staff_data['form_time'] = !empty($row['intake_time']) ? date('H:i', strtotime($row['intake_time'])) : '';

            // Populate clinical values directly from taken medical records
            $staff_data['client_complaint'] = $row['symptoms'] ?? $row['diagnosis'] ?? 'Nil';
            $staff_data['prev_medication'] = $row['treatment_given'] ?? '';
            $staff_data['investigation_done'] = (!empty($row['drugs_given']) || !empty($row['diagnosis'])) ? 'YES' : 'NO';

            // Combine Diagnosis and Drugs Given into the investigation details field
            $diagnosis_text = !empty($row['diagnosis']) ? "Diagnosis: " . $row['diagnosis'] : "";
            $drugs_text = !empty($row['drugs_given']) ? "Drugs given: " . $row['drugs_given'] : "";

            $staff_data['investigation_what'] = trim($diagnosis_text . (!empty($diagnosis_text) && !empty($drugs_text) ? " | " : "") . $drugs_text);

            $staff_data['reason_for_referral'] = 'Follow-up review for: ' . ($row['diagnosis'] ?? 'General medical assessment');

            // Vital Signs mapping
            $staff_data['vital_temp'] = $row['temperature'] ?? '';
            $staff_data['vital_pulse'] = $row['pulse_rate'] ?? '';
            $staff_data['vital_bp_spo'] = $row['blood_pressure'] ?? '';

            if (!empty($row['attended_by'])) {
                $staff_data['referring_officer'] = $row['attended_by'];
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equal Logistics - Medical Referral Note Generator</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Include html2pdf.js CDN library for true PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            color: #222;
        }

        .form-card,
        .preview-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }

        /* Full-Page High Visibility Referral Note Styling */
        .referral-note-document {
            background: #ffffff;
            border: 2.5px solid #111;
            padding: 28px;
            font-family: Arial, sans-serif;
            font-size: 13.5px;
            line-height: 1.5;
            color: #000;
            max-width: 800px;
            margin: 0 auto;
        }

        .note-header-box {
            border-bottom: 2.5px solid #dc3545;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .section-banner {
            background-color: #fce8e6;
            color: #b02a37;
            font-weight: bold;
            font-size: 0.75rem;
            text-transform: uppercase;
            padding: 4px 8px;
            margin-top: 14px;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
            border-left: 3px solid #dc3545;
        }

        .field-line {
            border-bottom: 1px dotted #495057;
            min-height: 24px;
            display: inline-block;
            width: 100%;
            font-weight: 700;
            color: #000;
        }

        .field-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #333;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .stamp-box {
            border: 2px dashed #495057;
            padding: 12px;
            text-align: center;
            background: #fafafa;
            border-radius: 6px;
        }

        .letterhead-logo-preview {
            max-height: 55px;
            max-width: 150px;
            object-fit: contain;
        }

        .signature-preview {
            max-height: 40px;
            max-width: 110px;
            object-fit: contain;
            display: block;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 5mm;
            }

            body * {
                visibility: hidden;
            }

            #printableLetter,
            #printableLetter * {
                visibility: visible;
            }

            #printableLetter {
                position: absolute;
                left: 0;
                top: 0;
                width: 100vw;
                height: 100vh;
                max-width: none;
                border: none;
                box-shadow: none;
                padding: 15mm;
                margin: 0;
                font-size: 14px;
                line-height: 1.6;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="container py-4">
        <!-- Page Header -->
        <div class="row mb-3 no-print align-items-center">
            <div class="col-12 col-md-8">
                <h2 class="fw-bold text-dark">Medical Referral Note Generator</h2>
                <p class="text-muted small mb-0">Record data has been auto-populated from the selected medical log. Optimized to scale and fill the full printed page clearly.</p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                <a href="referrals.php" class="btn btn-outline-primary px-3 py-2 fw-semibold shadow-sm" style="border-radius: 10px;">
                    <span>View Referrals</span>
                    <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Input Form Column -->
            <div class="col-12 col-lg-5 no-print">
                <div class="form-card">
                    <h5 class="fw-bold mb-3 text-primary">Referral Form Fields</h5>
                    <form id="referralForm">
                        <!-- Company Logo Selection -->
                        <div class="mb-2">
                            <label class="form-label fw-semibold small">Company Logo</label>
                            <select class="form-select form-select-sm" id="logoSelect">
                                <option value="" selected disabled>-- Select Company Logo --</option>
                                <option value="./assets/images/Equal-logo.png">Equal Logistics Limited</option>
                                <option value="./assets/images/visco.jpeg">Viscosupport</option>
                                <option value="./assets/images/upstream.jpeg">Upstream DC</option>
                                <option value="./assets/images/cannax.jpeg">Cannax</option>
                                <option value="./assets/images/idiaa.jpeg">Idiaa</option>
                            </select>
                        </div>

                        <!-- Signature Upload -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Officer Signature Image</label>
                            <input type="file" class="form-control form-control-sm" id="signatureInput" accept="image/*">
                            <div class="form-text text-muted" style="font-size: 0.7rem;">Upload signature image to replace placeholder stamp signature.</div>
                        </div>

                        <!-- Header Control Info -->
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label fw-semibold small">No. / Serial ID</label>
                                <input type="text" class="form-control form-control-sm" id="noteNo" value="<?php echo htmlspecialchars($staff_data['note_no']); ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Ref Code</label>
                                <input type="text" class="form-control form-control-sm" id="refCode" value="<?php echo htmlspecialchars($staff_data['ref_code']); ?>" required>
                            </div>
                        </div>

                        <!-- Staff Demographics -->
                        <div class="mb-2">
                            <label class="form-label fw-semibold small">Name of Staff</label>
                            <input type="text" class="form-control form-control-sm" id="staffName" value="<?php echo htmlspecialchars($staff_data['staff_name']); ?>" required>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-4">
                                <label class="form-label fw-semibold small">Department</label>
                                <input type="text" class="form-control form-control-sm" id="department" value="<?php echo htmlspecialchars($staff_data['department']); ?>" required>
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold small">Sex</label>
                                <input type="text" class="form-control form-control-sm" id="staffSex" value="<?php echo htmlspecialchars($staff_data['staff_sex']); ?>" required>
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold small">Age</label>
                                <input type="text" class="form-control form-control-sm" id="staffAge" value="<?php echo htmlspecialchars($staff_data['staff_age']); ?>" placeholder="Age">
                            </div>
                        </div>

                        <!-- Date, Time, Height, WT -->
                        <div class="row g-2 mb-2">
                            <div class="col-3">
                                <label class="form-label fw-semibold small">Date</label>
                                <input type="text" class="form-control form-control-sm" id="formDate" value="<?php echo htmlspecialchars($staff_data['form_date']); ?>" required>
                            </div>
                            <div class="col-3">
                                <label class="form-label fw-semibold small">Time</label>
                                <input type="text" class="form-control form-control-sm" id="formTime" value="<?php echo htmlspecialchars($staff_data['form_time']); ?>" placeholder="Time">
                            </div>
                            <div class="col-3">
                                <label class="form-label fw-semibold small">Height</label>
                                <input type="text" class="form-control form-control-sm" id="formHeight" value="<?php echo htmlspecialchars($staff_data['form_height']); ?>" placeholder="Height">
                            </div>
                            <div class="col-3">
                                <label class="form-label fw-semibold small">WT</label>
                                <input type="text" class="form-control form-control-sm" id="formWt" value="<?php echo htmlspecialchars($staff_data['form_wt']); ?>" placeholder="WT">
                            </div>
                        </div>

                        <!-- Vital Signs -->
                        <div class="p-2 border rounded bg-light mb-2">
                            <label class="form-label fw-bold text-danger small mb-1">VITAL SIGNS</label>
                            <div class="row g-2">
                                <div class="col-3">
                                    <label class="form-label text-muted" style="font-size:0.6rem">TEMP</label>
                                    <input type="text" class="form-control form-control-sm" id="vitalTemp" value="<?php echo htmlspecialchars($staff_data['vital_temp']); ?>">
                                </div>
                                <div class="col-3">
                                    <label class="form-label text-muted" style="font-size:0.6rem">PULSE</label>
                                    <input type="text" class="form-control form-control-sm" id="vitalPulse" value="<?php echo htmlspecialchars($staff_data['vital_pulse']); ?>">
                                </div>
                                <div class="col-3">
                                    <label class="form-label text-muted" style="font-size:0.6rem">RES</label>
                                    <input type="text" class="form-control form-control-sm" id="vitalRes" value="<?php echo htmlspecialchars($staff_data['vital_res']); ?>">
                                </div>
                                <div class="col-3">
                                    <label class="form-label text-muted" style="font-size:0.6rem">BP / SPO₂</label>
                                    <input type="text" class="form-control form-control-sm" id="vitalBpSpo" value="<?php echo htmlspecialchars($staff_data['vital_bp_spo']); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Clinical Presentation -->
                        <div class="mb-2">
                            <label class="form-label fw-semibold small">Clients Complaint on Presentation</label>
                            <input type="text" class="form-control form-control-sm" id="clientComplaint" value="<?php echo htmlspecialchars($staff_data['client_complaint']); ?>" required>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Duration of Symptoms</label>
                                <input type="text" class="form-control form-control-sm" id="complaintDuration" value="<?php echo htmlspecialchars($staff_data['complaint_duration']); ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Medical Unit</label>
                                <input type="text" class="form-control form-control-sm" id="medicalUnit" value="<?php echo htmlspecialchars($staff_data['medical_unit']); ?>">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold small">Previous Medication / Intervention</label>
                            <input type="text" class="form-control form-control-sm" id="prevMedication" value="<?php echo htmlspecialchars($staff_data['prev_medication']); ?>">
                        </div>

                        <!-- Investigation Done Section -->
                        <div class="mb-2">
                            <label class="form-label fw-semibold small">Any Investigation Done?</label>
                            <select class="form-select form-select-sm mb-1" id="investigationDone">
                                <option value="YES" <?php echo ($staff_data['investigation_done'] === 'YES') ? 'selected' : ''; ?>>YES</option>
                                <option value="NO" <?php echo ($staff_data['investigation_done'] === 'NO') ? 'selected' : ''; ?>>NO</option>
                            </select>

                            <!-- Controllable container holding diagnosis & drugs details -->
                            <div id="investigationWhatContainer" class="mt-2">
                                <label class="form-label fw-semibold small">If Yes What (Diagnosis & Medications)</label>
                                <input type="text" class="form-control form-control-sm" id="investigationWhat" value="<?php echo htmlspecialchars($staff_data['investigation_what']); ?>">
                            </div>
                        </div>

                        <!-- Reason for Referral -->
                        <div class="mb-2">
                            <label class="form-label fw-semibold small">Reason(s) for Referral</label>
                            <textarea class="form-control form-control-sm" id="reasonForReferral" rows="2" required><?php echo htmlspecialchars($staff_data['reason_for_referral']); ?></textarea>
                        </div>

                        <!-- Referring Officer -->
                        <div class="row g-2 mb-3">
                            <div class="col-7">
                                <label class="form-label fw-semibold small">Referring Officer</label>
                                <input type="text" class="form-control form-control-sm" id="referringOfficer" value="<?php echo htmlspecialchars($staff_data['referring_officer']); ?>" required>
                            </div>
                            <div class="col-5">
                                <label class="form-label fw-semibold small">Phone Contact</label>
                                <input type="text" class="form-control form-control-sm" id="officerContact" value="<?php echo htmlspecialchars($staff_data['officer_contact']); ?>" required>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 mt-3">
                            <button type="button" class="btn btn-primary btn-sm fw-bold" id="printBtn">Print / Save as PDF</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="downloadTxtBtn">Download as Text File</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Live Note Preview Column -->
            <div class="col-12 col-lg-7">
                <div class="preview-card">
                    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
                        <h5 class="fw-bold text-secondary mb-0">Live Referral Note Preview (Full Page View)</h5>
                        <span class="badge bg-success">Ready</span>
                    </div>

                    <!-- Printable Referral Note Container -->
                    <div id="printableLetter" class="referral-note-document">
                        <!-- Top Header Row -->
                        <div class="row align-items-center note-header-box">
                            <div class="col-7">
                                <div class="text-secondary fw-bold" style="font-size: 0.9rem;">MEDICAL SERVICES & REFERRAL UNIT</div>
                            </div>
                            <div class="col-5 text-end">
                                <div class="fw-bold text-dark" style="font-size: 0.8rem;">REF: <span id="prevRefCode"><?php echo htmlspecialchars($staff_data['ref_code']); ?></span></div>
                                <div class="fw-bold text-danger" style="font-size: 0.9rem;">No: <span id="prevNoteNo"><?php echo htmlspecialchars($staff_data['note_no']); ?></span></div>
                                <div id="logoContainer" class="mt-1">
                                    <div id="logoPlaceholder" class="border border-secondary border-dashed p-1 text-muted rounded d-inline-block" style="font-size: 0.65rem; background: #fafafa;">
                                        [EQUAL Logo]
                                    </div>
                                    <img id="logoImageDisplay" class="letterhead-logo-preview d-none" alt="Company Logo">
                                </div>
                            </div>
                        </div>

                        <!-- Staff Info Grid -->
                        <div class="row g-2 mb-2">
                            <div class="col-7">
                                <span class="field-label">Name of Staff:</span>
                                <div class="field-line px-1" id="prevStaffName"><?php echo htmlspecialchars($staff_data['staff_name']); ?></div>
                            </div>
                            <div class="col-3">
                                <span class="field-label">Department:</span>
                                <div class="field-line px-1" id="prevDepartment"><?php echo htmlspecialchars($staff_data['department']); ?></div>
                            </div>
                            <div class="col-2">
                                <span class="field-label">Sex:</span>
                                <div class="field-line px-1" id="prevStaffSex"><?php echo htmlspecialchars($staff_data['staff_sex']); ?></div>
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-3">
                                <span class="field-label">Date:</span>
                                <div class="field-line px-1" id="prevFormDate"><?php echo htmlspecialchars($staff_data['form_date']); ?></div>
                            </div>
                            <div class="col-3">
                                <span class="field-label">Time:</span>
                                <div class="field-line px-1" id="prevFormTime"><?php echo htmlspecialchars($staff_data['form_time'] ?: '&nbsp;'); ?></div>
                            </div>
                            <div class="col-3">
                                <span class="field-label">Height:</span>
                                <div class="field-line px-1" id="prevFormHeight">&nbsp;</div>
                            </div>
                            <div class="col-3">
                                <span class="field-label">WT:</span>
                                <div class="field-line px-1" id="prevFormWt">&nbsp;</div>
                            </div>
                        </div>

                        <!-- Vital Signs Section -->
                        <div class="section-banner">Vital Signs:</div>
                        <div class="row g-2 mb-2">
                            <div class="col-3">
                                <span class="field-label">Temp:</span>
                                <div class="field-line px-1" id="prevVitalTemp"><?php echo htmlspecialchars($staff_data['vital_temp'] ?: '&nbsp;'); ?></div>
                            </div>
                            <div class="col-3">
                                <span class="field-label">Pulse:</span>
                                <div class="field-line px-1" id="prevVitalPulse"><?php echo htmlspecialchars($staff_data['vital_pulse'] ?: '&nbsp;'); ?></div>
                            </div>
                            <div class="col-3">
                                <span class="field-label">Res:</span>
                                <div class="field-line px-1" id="prevVitalRes">&nbsp;</div>
                            </div>
                            <div class="col-3">
                                <span class="field-label">BP / SpO₂:</span>
                                <div class="field-line px-1" id="prevVitalBpSpo"><?php echo htmlspecialchars($staff_data['vital_bp_spo'] ?: '&nbsp;'); ?></div>
                            </div>
                        </div>

                        <!-- Clients Complaint Section -->
                        <div class="section-banner">Clients Complaint on Presentation</div>
                        <div class="mb-2">
                            <div class="field-line px-1 py-1" id="prevClientComplaint"><?php echo htmlspecialchars($staff_data['client_complaint']); ?></div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <span class="field-label">Duration of Complaints / Symptoms:</span>
                                <div class="field-line px-1" id="prevComplaintDuration">&nbsp;</div>
                            </div>
                            <div class="col-6">
                                <span class="field-label">Medical Unit:</span>
                                <div class="field-line px-1" id="prevMedicalUnit">&nbsp;</div>
                            </div>
                        </div>

                        <!-- Previous Medication Section -->
                        <div class="section-banner">Previous Medication Received at the Clinic or Intervention on the Condition</div>
                        <div class="mb-2">
                            <div class="field-line px-1 py-1" id="prevPrevMedication"><?php echo htmlspecialchars($staff_data['prev_medication'] ?: '&nbsp;'); ?></div>
                        </div>

                        <!-- Investigation Done Section -->
                        <div class="section-banner">Any Investigation Done?</div>
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-3">
                                <span class="fw-bold" style="font-size:0.8rem;">Status: <span id="prevInvestigationDone" class="text-danger"><?php echo htmlspecialchars($staff_data['investigation_done']); ?></span></span>
                            </div>
                            <div class="col-9">
                                <span class="field-label">If Yes What:</span>
                                <div class="field-line px-1" id="prevInvestigationWhat"><?php echo htmlspecialchars($staff_data['investigation_what'] ?: '&nbsp;'); ?></div>
                            </div>
                        </div>

                        <!-- Reason for Referral Section -->
                        <div class="section-banner">Reason(s) for Referral</div>
                        <div class="mb-3">
                            <div class="field-line px-1 py-2 fw-bold text-dark" style="min-height: 45px;" id="prevReasonForReferral"><?php echo htmlspecialchars($staff_data['reason_for_referral']); ?></div>
                        </div>

                        <!-- Footer Sign-off & Stamps -->
                        <div class="row mt-4 pt-2 align-items-end">
                            <div class="col-7">
                                <span class="field-label">Name of Medical Officer Referring:</span>
                                <div class="field-line px-1 mt-1 fw-bold" id="prevReferringOfficer"><?php echo htmlspecialchars($staff_data['referring_officer']); ?></div>
                                <div class="text-secondary mt-1 fw-semibold" style="font-size: 0.75rem;">Contact: <span id="prevOfficerContact"><?php echo htmlspecialchars($staff_data['officer_contact']); ?></span></div>
                            </div>
                            <div class="col-5 text-end">
                                <div class="stamp-box">
                                    <div class="fw-bold text-uppercase text-secondary" style="font-size: 0.65rem;">Chief Medical Director Stamp</div>
                                    <div class="my-2 d-flex justify-content-center align-items-center" style="min-height: 40px;">
                                        <div id="signaturePlaceholder" class="text-muted" style="font-size: 0.65rem;">[ Signature / Stamp ]</div>
                                        <img id="signatureImageDisplay" class="signature-preview d-none" alt="Officer Signature">
                                    </div>
                                    <div class="fw-bold text-dark" id="prevStampDate" style="font-size: 0.75rem;"><?php echo date('d / m / Y'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include html2pdf.js CDN library for direct PDF file download -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Company Logo Dropdown Selection Handler
            const logoSelect = document.getElementById('logoSelect');
            const logoPlaceholder = document.getElementById('logoPlaceholder');
            const logoImageDisplay = document.getElementById('logoImageDisplay');

            logoSelect.addEventListener('change', function() {
                const selectedLogoUrl = this.value;
                if (selectedLogoUrl) {
                    logoImageDisplay.src = selectedLogoUrl;
                    logoImageDisplay.classList.remove('d-none');
                    logoPlaceholder.classList.add('d-none');
                } else {
                    logoImageDisplay.classList.add('d-none');
                    logoPlaceholder.classList.remove('d-none');
                }
            });
            // Signature Upload Handler
            const signatureInput = document.getElementById('signatureInput');
            const signaturePlaceholder = document.getElementById('signaturePlaceholder');
            const signatureImageDisplay = document.getElementById('signatureImageDisplay');

            signatureInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        signatureImageDisplay.src = event.target.result;
                        signatureImageDisplay.classList.remove('d-none');
                        signaturePlaceholder.classList.add('d-none');
                    };
                    reader.readAsDataURL(file);
                } else {
                    signatureImageDisplay.classList.add('d-none');
                    signaturePlaceholder.classList.remove('d-none');
                }
            });

            // Investigation Field Visibility Toggle
            const investigationDoneSelect = document.getElementById('investigationDone');
            const investigationWhatContainer = document.getElementById('investigationWhatContainer');
            const investigationWhatInput = document.getElementById('investigationWhat');
            const prevInvestigationWhat = document.getElementById('prevInvestigationWhat');

            function toggleInvestigationInput() {
                if (investigationDoneSelect.value === 'NO') {
                    investigationWhatContainer.classList.add('d-none');
                    investigationWhatInput.value = ''; // Clear text if No
                    prevInvestigationWhat.textContent = '—'; // Update preview
                } else {
                    investigationWhatContainer.classList.remove('d-none');
                }
            }

            // Run on page load to handle pre-selected states
            toggleInvestigationInput();

            // Run on dropdown change
            investigationDoneSelect.addEventListener('change', toggleInvestigationInput);

            // Form Fields Live Binding Elements
            const fields = {
                noteNo: document.getElementById('noteNo'),
                refCode: document.getElementById('refCode'),
                staffName: document.getElementById('staffName'),
                department: document.getElementById('department'),
                staffSex: document.getElementById('staffSex'),
                formDate: document.getElementById('formDate'),
                formTime: document.getElementById('formTime'),
                formHeight: document.getElementById('formHeight'),
                formWt: document.getElementById('formWt'),
                vitalTemp: document.getElementById('vitalTemp'),
                vitalPulse: document.getElementById('vitalPulse'),
                vitalRes: document.getElementById('vitalRes'),
                vitalBpSpo: document.getElementById('vitalBpSpo'),
                clientComplaint: document.getElementById('clientComplaint'),
                complaintDuration: document.getElementById('complaintDuration'),
                medicalUnit: document.getElementById('medicalUnit'),
                prevMedication: document.getElementById('prevMedication'),
                investigationDone: document.getElementById('investigationDone'),
                investigationWhat: document.getElementById('investigationWhat'),
                reasonForReferral: document.getElementById('reasonForReferral'),
                referringOfficer: document.getElementById('referringOfficer'),
                officerContact: document.getElementById('officerContact')
            };

            const previews = {
                noteNo: document.getElementById('prevNoteNo'),
                refCode: document.getElementById('prevRefCode'),
                staffName: document.getElementById('prevStaffName'),
                department: document.getElementById('prevDepartment'),
                staffSex: document.getElementById('prevStaffSex'),
                formDate: document.getElementById('prevFormDate'),
                formTime: document.getElementById('prevFormTime'),
                formHeight: document.getElementById('prevFormHeight'),
                formWt: document.getElementById('prevFormWt'),
                vitalTemp: document.getElementById('prevVitalTemp'),
                vitalPulse: document.getElementById('prevVitalPulse'),
                vitalRes: document.getElementById('prevVitalRes'),
                vitalBpSpo: document.getElementById('prevVitalBpSpo'),
                clientComplaint: document.getElementById('prevClientComplaint'),
                complaintDuration: document.getElementById('prevComplaintDuration'),
                medicalUnit: document.getElementById('prevMedicalUnit'),
                prevMedication: document.getElementById('prevPrevMedication'),
                investigationDone: document.getElementById('prevInvestigationDone'),
                investigationWhat: document.getElementById('prevInvestigationWhat'),
                reasonForReferral: document.getElementById('prevReasonForReferral'),
                referringOfficer: document.getElementById('prevReferringOfficer'),
                officerContact: document.getElementById('prevOfficerContact')
            };

            // Bind input events to live preview
            Object.keys(fields).forEach(key => {
                if (fields[key] && previews[key]) {
                    fields[key].addEventListener('input', function() {
                        previews[key].textContent = this.value || (key.includes('Time') || key.includes('Temp') || key.includes('Pulse') || key.includes('Res') || key.includes('BpSpo') || key.includes('What') || key.includes('Medication') || key.includes('Duration') || key.includes('Unit') ? '' : '—');
                    });
                }
            });

            // Function to log/save the referral record via AJAX before action execution
            function saveReferralRecord(callback) {
                const payload = {
                    staff_name: fields.staffName.value,
                    serial_id: fields.noteNo.value,
                    ref_code: fields.refCode.value
                };

                fetch('save_referral.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Referral logged successfully:', data);
                        if (callback) callback();
                    })
                    .catch(error => {
                        console.error('Error logging referral record:', error);
                        if (callback) callback();
                    });
            }

            // Direct PDF Download Button Handler (Bypasses Print Preview)
            document.getElementById('printBtn').addEventListener('click', function() {
                const printBtn = document.getElementById('printBtn');
                printBtn.disabled = true;
                printBtn.textContent = 'Downloading PDF...';

                saveReferralRecord(function() {
                    const element = document.getElementById('printableLetter');
                    const opt = {
                        margin: [8, 8, 8, 8],
                        filename: `Medical_Referral_${fields.refCode.value}.pdf`,
                        image: {
                            type: 'jpeg',
                            quality: 0.98
                        },
                        html2canvas: {
                            scale: 2,
                            useCORS: true,
                            letterRendering: true,
                            scrollY: 0
                        },
                        jsPDF: {
                            unit: 'mm',
                            format: 'a4',
                            orientation: 'portrait',
                            compress: true
                        }
                    };

                    html2pdf().from(element).set(opt).save().then(() => {
                        printBtn.disabled = false;
                        printBtn.textContent = 'Print / Save as PDF';
                    }).catch(err => {
                        console.error('PDF generation error:', err);
                        printBtn.disabled = false;
                        printBtn.textContent = 'Print / Save as PDF';
                    });
                });
            });

            // Download as Text File Button Handler
            document.getElementById('downloadTxtBtn').addEventListener('click', function() {
                saveReferralRecord(function() {
                    const textContent = `========================================\n` +
                        `EQUAL LOGISTICS - MEDICAL REFERRAL NOTE\n` +
                        `========================================\n` +
                        `Ref Code: ${fields.refCode.value}\n` +
                        `Serial ID / Note No: ${fields.noteNo.value}\n` +
                        `Date: ${fields.formDate.value} ${fields.formTime.value}\n\n` +
                        `STAFF DETAILS:\n` +
                        `- Name: ${fields.staffName.value}\n` +
                        `- Department: ${fields.department.value}\n` +
                        `- Sex: ${fields.staffSex.value}\n\n` +
                        `CLINICAL PRESENTATION:\n` +
                        `- Complaint: ${fields.clientComplaint.value}\n` +
                        `- Vital Signs - Temp: ${fields.vitalTemp.value}, Pulse: ${fields.vitalPulse.value}, BP: ${fields.vitalBpSpo.value}\n` +
                        `- Previous Medication: ${fields.prevMedication.value}\n` +
                        `- Investigation Done: ${fields.investigationDone.value} (${fields.investigationWhat.value})\n\n` +
                        `REFERRAL DETAILS:\n` +
                        `- Reason: ${fields.reasonForReferral.value}\n` +
                        `- Referring Officer: ${fields.referringOfficer.value} (${fields.officerContact.value})\n` +
                        `========================================`;

                    const blob = new Blob([textContent], {
                        type: 'text/plain;charset=utf-8'
                    });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `Referral_Note_${fields.refCode.value}.txt`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                });
            });
        });
    </script>
</body>

</html>