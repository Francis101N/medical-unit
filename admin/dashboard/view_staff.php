<?php

/** @var mysqli $conn */
include('./db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// Decrypt ID function matching the directory list script
if (!function_exists('decryptId')) {
    function decryptId($encrypted_value)
    {
        $key = "medical-secret-key";
        $decoded = base64_decode(strtr($encrypted_value, '-_', '+/'));
        if ($decoded !== false) {
            $parts = explode('|', $decoded);
            if (count($parts) === 2 && $parts[1] === $key) {
                return $parts[0];
            }
        }
        return null;
    }
}

$encrypted_id = $_GET['id'] ?? '';
$staff_id_pk = decryptId($encrypted_id);

if (!$staff_id_pk) {
    die("Invalid or tampered staff record reference.");
}

// Fetch staff details along with branch info
$stmt = $conn->prepare("SELECT s.*, b.branch_name FROM staffs s LEFT JOIN branches b ON s.branch_id = b.id WHERE s.id = ? LIMIT 1");
$stmt->bind_param("i", $staff_id_pk);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Staff record not found.");
}

$staff = $result->fetch_assoc();
$stmt->close();

// Role and branch access control security check
$user_role = strtolower($_SESSION['role'] ?? '');
$user_branch = $_SESSION['branch'] ?? '';

if ($user_role !== 'super-admin') {
    $staff_branch_name = strtolower(trim($staff['branch_name'] ?? ''));
    $staff_branch_id = $staff['branch_id'];
    if ($staff_branch_name !== strtolower(trim($user_branch)) && $staff_branch_id != $user_branch) {
        die("Access denied: You do not have permission to view records outside your assigned branch.");
    }
}

// Capture filter parameters
$filter_status = trim($_GET['record_status'] ?? '');
$filter_search = trim($_GET['search_term'] ?? '');
$filter_start_date = trim($_GET['start_date'] ?? '');
$filter_end_date = trim($_GET['end_date'] ?? '');

// Build dynamic query for medical records matching staff_name with filters
$query = "SELECT * FROM staff_medical_records WHERE staff_name = ?";
$params = [$staff['fullname']];
$types = "s";

if (!empty($filter_status)) {
    $query .= " AND record_status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if (!empty($filter_search)) {
    $query .= " AND (diagnosis LIKE ? OR symptoms LIKE ? OR treatment_given LIKE ? OR drugs_given LIKE ? OR attended_by LIKE ?)";
    $search_wildcard = "%" . $filter_search . "%";
    array_push($params, $search_wildcard, $search_wildcard, $search_wildcard, $search_wildcard, $search_wildcard);
    $types .= "sssss";
}

if (!empty($filter_start_date)) {
    $query .= " AND intake_time >= ?";
    $params[] = $filter_start_date . " 00:00:00";
    $types .= "s";
}

if (!empty($filter_end_date)) {
    $query .= " AND intake_time <= ?";
    $params[] = $filter_end_date . " 23:59:59";
    $types .= "s";
}

$query .= " ORDER BY intake_time DESC";

$med_stmt = $conn->prepare($query);
$med_stmt->bind_param($types, ...$params);
$med_stmt->execute();
$medical_records_result = $med_stmt->get_result();
$med_stmt->close();

// Format status classes
$status = strtolower(trim($staff['status'] ?? ''));
$fitness_status = strtolower(trim($staff['fitness_status'] ?? ''));

$status_class = match ($status) {
    'active' => 'badge-soft-success',
    'suspended' => 'badge-soft-warning',
    'inactive' => 'badge-soft-secondary',
    default => 'badge-soft-danger'
};

$fitness_class = match ($fitness_status) {
    'fit' => 'badge-soft-success',
    'under_observation', 'observation' => 'badge-soft-warning',
    default => 'badge-soft-danger'
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Staff Profile - <?php echo htmlspecialchars($staff['fullname']); ?></title>
    <!-- Include your CSS Framework / Bootstrap links here -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .profile-header {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 2rem 0;
        }

        .passport-view {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #dee2e6;
        }

        .badge-soft {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            border-radius: 0.25rem;
        }

        .badge-soft-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .badge-soft-warning {
            background-color: #fff3cd;
            color: #664d03;
        }

        .badge-soft-secondary {
            background-color: #e2e3e5;
            color: #41464b;
        }

        .badge-soft-danger {
            background-color: #f8d7da;
            color: #842029;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }

        .table-med {
            font-size: 0.875rem;
        }

        .clickable-row {
            cursor: pointer;
            transition: background-color 0.15s ease-in-out;
        }

        .clickable-row:hover {
            background-color: rgba(0, 123, 255, 0.075) !important;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <!-- Top Bar Navigation / Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="staffs.php" class="btn btn-outline-secondary btn-sm">&larr; Back to Directory</a>
            <div>
                <a href="edit_staff.php?id=<?php echo urlencode($encrypted_id); ?>" class="btn btn-primary btn-sm">Edit Profile</a>
            </div>
        </div>

        <!-- Profile Overview Card -->
        <div class="card p-4">
            <div class="row align-items-center">
                <div class="col-md-auto text-center mb-3 mb-md-0">
                    <?php if (!empty($staff['passport']) && file_exists("uploads/" . $staff['passport'])) { ?>
                        <img src="uploads/<?php echo htmlspecialchars($staff['passport']); ?>" alt="Passport" class="passport-view shadow-sm">
                    <?php } else { ?>
                        <div class="passport-view d-flex align-items-center justify-content-center bg-light text-muted border mx-auto">No Photo</div>
                    <?php } ?>
                </div>
                <div class="col-md">
                    <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($staff['fullname']); ?></h3>
                    <p class="text-muted mb-2 font-monospace">Staff ID: <?php echo htmlspecialchars($staff['staff_id']); ?></p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($staff['branch_name'] ?? 'Branch ID: ' . $staff['branch_id']); ?></span>
                        <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($staff['department']); ?></span>
                        <span class="badge-soft <?php echo $status_class; ?>"><?php echo ucfirst(htmlspecialchars($status)); ?></span>
                        <span class="badge-soft <?php echo $fitness_class; ?>">Fitness: <?php echo ucfirst(htmlspecialchars(str_replace('_', ' ', $fitness_status))); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Personal & Employment Information -->
            <div class="col-lg-6">
                <div class="card p-4 h-100">
                    <h5 class="fw-bold text-primary mb-3">Employment & Personal Information</h5>
                    <table class="table table-borderless align-middle mb-0">
                        <tr>
                            <th class="text-muted w-40">Email Address:</th>
                            <td><?php echo htmlspecialchars($staff['email']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Phone Number:</th>
                            <td><?php echo htmlspecialchars($staff['phone']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Gender:</th>
                            <td><?php echo ucfirst(htmlspecialchars($staff['gender'])); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Date of Birth:</th>
                            <td><?php echo htmlspecialchars($staff['dob']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Company:</th>
                            <td><?php echo htmlspecialchars($staff['company'] ?: '—'); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Role / Title:</th>
                            <td><?php echo htmlspecialchars($staff['role']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Employment Type:</th>
                            <td><?php echo ucfirst(htmlspecialchars($staff['employment_type'])); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Hire Date:</th>
                            <td><?php echo htmlspecialchars($staff['hire_date']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Residential Address:</th>
                            <td><?php echo htmlspecialchars($staff['address']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Medical & Emergency Profile -->
            <div class="col-lg-6">
                <div class="card p-4 h-100">
                    <h5 class="fw-bold text-danger mb-3">Medical Profile & Emergency Contacts</h5>
                    <table class="table table-borderless align-middle mb-0">
                        <tr>
                            <th class="text-muted w-40">Blood Group:</th>
                            <td><span class="badge bg-danger-subtle text-danger border px-2"><?php echo htmlspecialchars($staff['blood_group']); ?></span></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Genotype:</th>
                            <td><span class="badge bg-info-subtle text-info border px-2"><?php echo htmlspecialchars($staff['genotype']); ?></span></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Allergies:</th>
                            <td class="text-danger fw-medium"><?php echo htmlspecialchars($staff['allergies'] ?: 'None recorded'); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Medical Conditions:</th>
                            <td><?php echo htmlspecialchars($staff['medical_conditions'] ?: 'None recorded'); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Last Checkup:</th>
                            <td><?php echo htmlspecialchars($staff['last_medical_checkup'] ?: '—'); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Next of Kin:</th>
                            <td><?php echo htmlspecialchars($staff['next_of_kin']); ?> (<?php echo htmlspecialchars($staff['next_of_kin_phone']); ?>)</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Emergency Contact:</th>
                            <td><?php echo htmlspecialchars($staff['emergency_contact_name']); ?> (<?php echo htmlspecialchars($staff['emergency_contact_phone']); ?>)</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Comprehensive Medical Encounters & History Section -->
        <div class="card p-4 mt-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-journal-medical text-danger me-2"></i>Staff Medical Records & Encounters</h5><br><br>
                <span class="badge bg-secondary"><?php echo $medical_records_result->num_rows; ?> Total Record(s)</span>
            </div>

            <!-- Generic Filter Form -->
            <form method="GET" action="" class="row g-3 mb-4 bg-light p-3 rounded border">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($encrypted_id); ?>">

                <div class="col-md-3">
                    <label for="search_term" class="form-label small fw-bold">Search Keywords</label>
                    <input type="text" class="form-control form-control-sm" id="search_term" name="search_term" value="<?php echo htmlspecialchars($filter_search); ?>" placeholder="Diagnosis, symptoms, drugs...">
                </div>

                <div class="col-md-2">
                    <label for="record_status" class="form-label small fw-bold">Record Status</label>
                    <select class="form-select form-select-sm" id="record_status" name="record_status">
                        <option value="">All Statuses</option>
                        <option value="open" <?php echo ($filter_status === 'open') ? 'selected' : ''; ?>>Open</option>
                        <option value="closed" <?php echo ($filter_status === 'closed') ? 'selected' : ''; ?>>Closed</option>
                        <option value="under_treatment" <?php echo ($filter_status === 'under_treatment') ? 'selected' : ''; ?>>Under Treatment</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="start_date" class="form-label small fw-bold">Start Date</label>
                    <input type="date" class="form-control form-control-sm" id="start_date" name="start_date" value="<?php echo htmlspecialchars($filter_start_date); ?>">
                </div>

                <div class="col-md-2">
                    <label for="end_date" class="form-label small fw-bold">End Date</label>
                    <input type="date" class="form-control form-control-sm" id="end_date" name="end_date" value="<?php echo htmlspecialchars($filter_end_date); ?>">
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-dark btn-sm w-100"><i class="bi bi-filter"></i> Filter</button>
                    <a href="?id=<?php echo urlencode($encrypted_id); ?>" class="btn btn-outline-secondary btn-sm" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle table-med mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>S/N</th>
                            <th>Intake Date & Time</th>
                            <th>Release Time</th>
                            <th>Branch</th>
                            <th>Diagnosis & Symptoms</th>
                            <th>Vitals (BP / Temp / Pulse)</th>
                            <th>Treatment & Drugs</th>
                            <th>Attended By</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($medical_records_result->num_rows > 0) {
                            $sn = 1;
                            while ($med = $medical_records_result->fetch_assoc()) {
                                $rec_status = strtolower(trim($med['record_status'] ?? 'open'));
                                $badge_bg = match ($rec_status) {
                                    'closed' => 'bg-success',
                                    'under_treatment' => 'bg-warning text-dark',
                                    default => 'bg-info text-dark'
                                };
                        ?>
                                <tr class="clickable-row"
                                    data-bs-toggle="modal"
                                    data-bs-target="#medicalRecordModal"
                                    data-intake="<?php echo htmlspecialchars($med['intake_time']); ?>"
                                    data-release="<?php echo htmlspecialchars($med['release_time'] ?? ''); ?>"
                                    data-company="<?php echo htmlspecialchars($med['company']); ?>"
                                    data-branch="<?php echo htmlspecialchars($med['staff_branch'] ?? ''); ?>"
                                    data-department="<?php echo htmlspecialchars($med['department'] ?? ''); ?>"
                                    data-diagnosis="<?php echo htmlspecialchars($med['diagnosis']); ?>"
                                    data-symptoms="<?php echo htmlspecialchars($med['symptoms'] ?? ''); ?>"
                                    data-notes="<?php echo htmlspecialchars($med['medical_notes'] ?? ''); ?>"
                                    data-treatment="<?php echo htmlspecialchars($med['treatment_given'] ?? ''); ?>"
                                    data-drugs="<?php echo htmlspecialchars($med['drugs_given'] ?? ''); ?>"
                                    data-dosage="<?php echo htmlspecialchars($med['dosage_instructions'] ?? ''); ?>"
                                    data-attended="<?php echo htmlspecialchars($med['attended_by'] ?? ''); ?>"
                                    data-admission-condition="<?php echo htmlspecialchars($med['condition_on_admission'] ?? ''); ?>"
                                    data-release-condition="<?php echo htmlspecialchars($med['condition_on_release'] ?? ''); ?>"
                                    data-bp="<?php echo htmlspecialchars($med['blood_pressure'] ?? ''); ?>"
                                    data-temp="<?php echo htmlspecialchars($med['temperature'] ?? ''); ?>"
                                    data-pulse="<?php echo htmlspecialchars($med['pulse_rate'] ?? ''); ?>"
                                    data-followup-required="<?php echo htmlspecialchars($med['follow_up_required'] ?? ''); ?>"
                                    data-followup-date="<?php echo htmlspecialchars($med['follow_up_date'] ?? ''); ?>"
                                    data-status="<?php echo htmlspecialchars($med['record_status']); ?>"
                                    data-created="<?php echo htmlspecialchars($med['created_at']); ?>"
                                    data-updated="<?php echo htmlspecialchars($med['updated_at']); ?>">
                                    <td><?php echo $sn++; ?></td>
                                    <td>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($med['intake_time']); ?></div>
                                        <small class="text-muted"><?php echo date('l, M j, Y', strtotime($med['intake_time'])); ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($med['release_time'])) { ?>
                                            <div><?php echo htmlspecialchars($med['release_time']); ?></div>
                                            <small class="text-muted"><?php echo date('l, M j, Y', strtotime($med['release_time'])); ?></small>
                                        <?php } else { ?>
                                            <span class="text-muted">—</span>
                                        <?php } ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($med['staff_branch'] ?? '—'); ?></span></td>
                                    <td>
                                        <div class="fw-medium text-danger"><?php echo htmlspecialchars($med['diagnosis']); ?></div>
                                        <?php if (!empty($med['symptoms'])) { ?>
                                            <small class="text-muted">Symptoms: <?php echo htmlspecialchars($med['symptoms']); ?></small>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <small class="d-block">BP: <?php echo htmlspecialchars($med['blood_pressure'] ?: 'N/A'); ?></small>
                                        <small class="d-block">Temp: <?php echo htmlspecialchars($med['temperature'] ?: 'N/A'); ?></small>
                                        <small class="d-block">Pulse: <?php echo htmlspecialchars($med['pulse_rate'] ?: 'N/A'); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($med['drugs_given'] ?: 'None'); ?></div>
                                        <?php if (!empty($med['dosage_instructions'])) { ?>
                                            <small class="text-muted">Dosage: <?php echo htmlspecialchars($med['dosage_instructions']); ?></small>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($med['attended_by'] ?? '—'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $badge_bg; ?>">
                                            <?php echo ucwords(str_replace('_', ' ', $med['record_status'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    No medical records or clinical encounters found matching your criteria.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i> Click on any record row to view complete detailed medical information.</div>
        </div>

        <!-- System Timestamps Footer -->
        <div class="text-muted text-end mt-3 small">
            Record Created: <?php echo htmlspecialchars($staff['created_at']); ?> | Last Updated: <?php echo htmlspecialchars($staff['updated_at']); ?>
        </div>
    </div>

    <!-- Medical Record Detail Modal -->
    <div class="modal fade" id="medicalRecordModal" tabindex="-1" aria-labelledby="medicalRecordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="medicalRecordModalLabel"><i class="bi bi-journal-medical me-2"></i>Medical Record Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Intake Time</label>
                            <div id="modal-intake" class="fw-semibold"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Release Time</label>
                            <div id="modal-release" class="fw-semibold">—</div>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Company / Branch / Department</label>
                            <div id="modal-location" class="fw-semibold"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Record Status & Conditions</label>
                            <div><span id="modal-status" class="badge bg-secondary"></span> | Admission: <span id="modal-admission-condition" class="fw-semibold"></span></div>
                        </div>

                        <hr class="my-2">

                        <div class="col-12">
                            <label class="text-muted small fw-bold">Diagnosis</label>
                            <div id="modal-diagnosis" class="text-danger fw-bold fs-6"></div>
                        </div>

                        <div class="col-12">
                            <label class="text-muted small fw-bold">Symptoms</label>
                            <div id="modal-symptoms" class="p-2 bg-light rounded border"></div>
                        </div>

                        <div class="col-12">
                            <label class="text-muted small fw-bold">Medical Notes</label>
                            <div id="modal-notes" class="p-2 bg-light rounded border"></div>
                        </div>

                        <hr class="my-2">

                        <div class="col-md-4">
                            <label class="text-muted small fw-bold">Blood Pressure</label>
                            <div id="modal-bp" class="fw-semibold"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-bold">Temperature</label>
                            <div id="modal-temp" class="fw-semibold"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-bold">Pulse Rate</label>
                            <div id="modal-pulse" class="fw-semibold"></div>
                        </div>

                        <hr class="my-2">

                        <div class="col-12">
                            <label class="text-muted small fw-bold">Treatment Given</label>
                            <div id="modal-treatment" class="p-2 bg-light rounded border"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Drugs Given</label>
                            <div id="modal-drugs" class="fw-semibold"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Dosage Instructions</label>
                            <div id="modal-dosage" class="fw-semibold"></div>
                        </div>

                        <hr class="my-2">

                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Attended By / Medical Officer</label>
                            <div id="modal-attended" class="fw-semibold"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Condition on Release</label>
                            <div id="modal-release-condition" class="fw-semibold"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Follow-up Required?</label>
                            <div id="modal-followup-req" class="fw-semibold text-uppercase"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Follow-up Date</label>
                            <div id="modal-followup-date" class="fw-semibold"></div>
                        </div>

                        <div class="col-12 text-muted small mt-3 pt-2 border-top">
                            System Entry Created: <span id="modal-created"></span> | Last Updated: <span id="modal-updated"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const medicalRecordModal = document.getElementById('medicalRecordModal');
            if (medicalRecordModal) {
                medicalRecordModal.addEventListener('show.bs.modal', function(event) {
                    const row = event.relatedTarget;

                    // Extract data attributes
                    const intake = row.getAttribute('data-intake');
                    const release = row.getAttribute('data-release');
                    const company = row.getAttribute('data-company');
                    const branch = row.getAttribute('data-branch');
                    const department = row.getAttribute('data-department');
                    const diagnosis = row.getAttribute('data-diagnosis');
                    const symptoms = row.getAttribute('data-symptoms');
                    const notes = row.getAttribute('data-notes');
                    const treatment = row.getAttribute('data-treatment');
                    const drugs = row.getAttribute('data-drugs');
                    const dosage = row.getAttribute('data-dosage');
                    const attended = row.getAttribute('data-attended');
                    const admissionCondition = row.getAttribute('data-admission-condition');
                    const releaseCondition = row.getAttribute('data-release-condition');
                    const bp = row.getAttribute('data-bp');
                    const temp = row.getAttribute('data-temp');
                    const pulse = row.getAttribute('data-pulse');
                    const followupReq = row.getAttribute('data-followup-required');
                    const followupDate = row.getAttribute('data-followup-date');
                    const status = row.getAttribute('data-status');
                    const created = row.getAttribute('data-created');
                    const updated = row.getAttribute('data-updated');

                    // Populate modal elements
                    document.getElementById('modal-intake').textContent = intake || '—';
                    document.getElementById('modal-release').textContent = release || '—';
                    document.getElementById('modal-location').textContent = `${company || '—'} / ${branch || '—'} / ${department || '—'}`;

                    const statusBadge = document.getElementById('modal-status');
                    statusBadge.textContent = status ? status.replace('_', ' ').toUpperCase() : 'OPEN';
                    statusBadge.className = 'badge ' + (status === 'closed' ? 'bg-success' : (status === 'under_treatment' ? 'bg-warning text-dark' : 'bg-info text-dark'));

                    document.getElementById('modal-admission-condition').textContent = admissionCondition || '—';
                    document.getElementById('modal-diagnosis').textContent = diagnosis || '—';
                    document.getElementById('modal-symptoms').textContent = symptoms || 'No symptoms recorded.';
                    document.getElementById('modal-notes').textContent = notes || 'No medical notes recorded.';
                    document.getElementById('modal-bp').textContent = bp || 'N/A';
                    document.getElementById('modal-temp').textContent = temp || 'N/A';
                    document.getElementById('modal-pulse').textContent = pulse || 'N/A';
                    document.getElementById('modal-treatment').textContent = treatment || 'No treatment details recorded.';
                    document.getElementById('modal-drugs').textContent = drugs || 'None';
                    document.getElementById('modal-dosage').textContent = dosage || '—';
                    document.getElementById('modal-attended').textContent = attended || '—';
                    document.getElementById('modal-release-condition').textContent = releaseCondition || '—';
                    document.getElementById('modal-followup-req').textContent = followupReq || 'no';
                    document.getElementById('modal-followup-date').textContent = followupDate || '—';
                    document.getElementById('modal-created').textContent = created || '—';
                    document.getElementById('modal-updated').textContent = updated || '—';
                });
            }
        });
    </script>
</body>

</html>