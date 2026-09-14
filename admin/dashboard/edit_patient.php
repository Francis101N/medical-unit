<?php
session_start();
/** @var mysqli $conn */
include('./db.php');

// 1. Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

// 2. ID Decryption Helper Function
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
$record_id = decryptId($encrypted_id);

if (!$record_id) {
    die("Invalid or tampered medical log record reference.");
}

// 3. Handle Form Submission for Updating
if (isset($_POST['update_record'])) {
    // 1. Identification & Affiliation
    $patient_name = trim($_POST['patient_name'] ?? '');
    $patient_location = trim($_POST['patient_location'] ?? '');

    // 2. Personal & Demographic Details
    $date_of_birth = trim($_POST['date_of_birth'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $blood_group = trim($_POST['blood_group'] ?? '');
    $genotype = trim($_POST['genotype'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $residential_address = trim($_POST['residential_address'] ?? '');

    // 3. Emergency Contact
    $next_of_kin_name = trim($_POST['next_of_kin_name'] ?? '');
    $next_of_kin_relationship = trim($_POST['next_of_kin_relationship'] ?? '');
    $next_of_kin_phone = trim($_POST['next_of_kin_phone'] ?? '');

    // 4. Clinical History & Allergies
    $allergies = trim($_POST['allergies'] ?? '');
    $medical_history = trim($_POST['medical_history'] ?? '');

    // 5. Current Visit & Vital Signs
    $intake_time = trim($_POST['intake_time'] ?? '');
    $release_time = trim($_POST['release_time'] ?? '');
    $blood_pressure = trim($_POST['blood_pressure'] ?? '');
    $temperature = trim($_POST['temperature'] ?? '');
    $pulse_rate = trim($_POST['pulse_rate'] ?? '');
    $respiratory_rate = trim($_POST['respiratory_rate'] ?? '');
    $oxygen_saturation = trim($_POST['oxygen_saturation'] ?? '');
    $weight = trim($_POST['weight'] ?? '');
    $height = trim($_POST['height'] ?? '');
    $blood_sugar = trim($_POST['blood_sugar'] ?? '');
    $condition_on_admission = trim($_POST['condition_on_admission'] ?? 'stable');
    $condition_on_release = trim($_POST['condition_on_release'] ?? '');

    // 6. Diagnosis & Treatment
    $symptoms = trim($_POST['symptoms'] ?? '');
    $diagnosis = trim($_POST['diagnosis'] ?? '');
    $medical_notes = trim($_POST['medical_notes'] ?? '');
    $treatment_given = trim($_POST['treatment_given'] ?? '');

    // Combine existing drugs with newly selected additional drugs from multi-select inventory dropdown
    $existing_drugs = trim($_POST['drugs_given'] ?? '');
    $additional_drugs_array = $_POST['additional_drugs_given'] ?? [];

    $filtered_additional_drugs = [];
    if (is_array($additional_drugs_array)) {
        $filtered_additional_drugs = array_filter($additional_drugs_array, function ($value) {
            return trim($value) !== '';
        });
    }

    // Merge them into a single unique comma-separated string list
    $combined_drugs_list = array_filter(array_merge(
        array_map('trim', explode(',', $existing_drugs)),
        $filtered_additional_drugs
    ));
    $drugs_given = implode(', ', array_unique($combined_drugs_list));

    $dosage_instructions = trim($_POST['dosage_instructions'] ?? '');
    $attended_by = trim($_POST['attended_by'] ?? '');
    $follow_up_required = trim($_POST['follow_up_required'] ?? 'no');
    $follow_up_date = trim($_POST['follow_up_date'] ?? '');
    $record_status = trim($_POST['record_status'] ?? 'open');

    // Validation for strictly required fields only
    if (empty($patient_name) || empty($patient_location) || empty($intake_time) || empty($diagnosis)) {
        $_SESSION['msg'] = "Please fill in all required fields (Patient Name, Patient Location, Intake Time, Diagnosis).";
        $_SESSION['msg_type'] = "danger";
        header("Location: edit_patient.php?id=" . urlencode($encrypted_id));
        exit();
    }

    // Convert empty optional strings to null for clean database storage
    $date_of_birth_val         = !empty($date_of_birth) ? $date_of_birth : null;
    $release_time_val          = !empty($release_time) ? $release_time : null;
    $follow_up_date_val        = !empty($follow_up_date) ? $follow_up_date : null;
    $condition_on_release_val  = !empty($condition_on_release) ? $condition_on_release : null;

    $gender_val                = !empty($gender) ? $gender : null;
    $blood_group_val           = !empty($blood_group) ? $blood_group : null;
    $genotype_val              = !empty($genotype) ? $genotype : null;
    $phone_number_val          = !empty($phone_number) ? $phone_number : null;
    $residential_address_val   = !empty($residential_address) ? $residential_address : null;

    $next_of_kin_name_val      = !empty($next_of_kin_name) ? $next_of_kin_name : null;
    $next_of_kin_rel_val       = !empty($next_of_kin_relationship) ? $next_of_kin_relationship : null;
    $next_of_kin_phone_val     = !empty($next_of_kin_phone) ? $next_of_kin_phone : null;

    $allergies_val             = !empty($allergies) ? $allergies : null;
    $medical_history_val       = !empty($medical_history) ? $medical_history : null;

    $blood_pressure_val        = !empty($blood_pressure) ? $blood_pressure : null;
    $temperature_val           = !empty($temperature) ? $temperature : null;
    $pulse_rate_val            = !empty($pulse_rate) ? $pulse_rate : null;
    $respiratory_rate_val      = !empty($respiratory_rate) ? $respiratory_rate : null;
    $oxygen_saturation_val     = !empty($oxygen_saturation) ? $oxygen_saturation : null;
    $weight_val                = !empty($weight) ? $weight : null;
    $height_val                = !empty($height) ? $height : null;
    $blood_sugar_val           = !empty($blood_sugar) ? $blood_sugar : null;

    $symptoms_val              = !empty($symptoms) ? $symptoms : null;
    $medical_notes_val         = !empty($medical_notes) ? $medical_notes : null;
    $treatment_given_val       = !empty($treatment_given) ? $treatment_given : null;
    $drugs_given_val           = !empty($drugs_given) ? $drugs_given : null;
    $dosage_instructions_val   = !empty($dosage_instructions) ? $dosage_instructions : null;
    $attended_by_val           = !empty($attended_by) ? $attended_by : null;

    // Begin Database Transaction for atomic inventory updates and record saving
    mysqli_begin_transaction($conn);

    try {
        // Deduct inventory stock only for newly appended additional drugs
        foreach ($filtered_additional_drugs as $selected_drug) {
            $item_name_to_check = trim(preg_replace('/\s*\(.*?\)\s*$/', '', $selected_drug));

            $inv_check_stmt = $conn->prepare("SELECT id, total_quantity FROM outreach_inventory WHERE item_name = ? OR CONCAT(item_name, ' (', unit, ')') = ? FOR UPDATE");
            $inv_check_stmt->bind_param("ss", $item_name_to_check, $selected_drug);
            $inv_check_stmt->execute();
            $inv_res = $inv_check_stmt->get_result();

            if ($inv_row = $inv_res->fetch_assoc()) {
                if (intval($inv_row['total_quantity']) <= 0) {
                    throw new Exception("Selected inventory item '" . htmlspecialchars($selected_drug) . "' is out of stock and cannot be dispensed.");
                }

                $inv_update_stmt = $conn->prepare("UPDATE outreach_inventory SET total_quantity = total_quantity - 1 WHERE id = ?");
                $inv_update_stmt->bind_param("i", $inv_row['id']);
                $inv_update_stmt->execute();
                $inv_update_stmt->close();
            }
            $inv_check_stmt->close();
        }

        // Prepare Update Query
        $update_sql = "UPDATE patient_medical_records SET 
            patient_name = ?, patient_location = ?, date_of_birth = ?, gender = ?, blood_group = ?, genotype = ?, 
            phone_number = ?, residential_address = ?, next_of_kin_name = ?, next_of_kin_relationship = ?, 
            next_of_kin_phone = ?, allergies = ?, medical_history = ?, intake_time = ?, release_time = ?, 
            blood_pressure = ?, temperature = ?, pulse_rate = ?, respiratory_rate = ?, oxygen_saturation = ?, 
            weight = ?, height = ?, blood_sugar = ?, condition_on_admission = ?, condition_on_release = ?, 
            symptoms = ?, diagnosis = ?, medical_notes = ?, treatment_given = ?, drugs_given = ?, 
            dosage_instructions = ?, attended_by = ?, follow_up_required = ?, follow_up_date = ?, record_status = ? 
            WHERE id = ?";

        $stmt = $conn->prepare($update_sql);

        if (!$stmt) {
            throw new Exception("Database query preparation failed: " . $conn->error);
        }

        $types = str_repeat('s', 35) . 'i';

        $stmt->bind_param(
            $types,
            $patient_name,
            $patient_location,
            $date_of_birth_val,
            $gender_val,
            $blood_group_val,
            $genotype_val,
            $phone_number_val,
            $residential_address_val,
            $next_of_kin_name_val,
            $next_of_kin_rel_val,
            $next_of_kin_phone_val,
            $allergies_val,
            $medical_history_val,
            $intake_time,
            $release_time_val,
            $blood_pressure_val,
            $temperature_val,
            $pulse_rate_val,
            $respiratory_rate_val,
            $oxygen_saturation_val,
            $weight_val,
            $height_val,
            $blood_sugar_val,
            $condition_on_admission,
            $condition_on_release_val,
            $symptoms_val,
            $diagnosis,
            $medical_notes_val,
            $treatment_given_val,
            $drugs_given_val,
            $dosage_instructions_val,
            $attended_by_val,
            $follow_up_required,
            $follow_up_date_val,
            $record_status,
            $record_id
        );

        if (!$stmt->execute()) {
            throw new Exception("Database error: Could not update record. " . $stmt->error);
        }
        $stmt->close();

        // Commit transaction
        mysqli_commit($conn);

        $_SESSION['msg'] = "Patient record successfully updated and inventory adjusted!";
        $_SESSION['msg_type'] = "success";
        header("Location: view_patient.php?id=" . urlencode($encrypted_id));
        exit();
    } catch (Exception $e) {
        // Rollback transaction on failure
        mysqli_rollback($conn);

        $_SESSION['msg'] = $e->getMessage();
        $_SESSION['msg_type'] = "danger";
        header("Location: edit_patient.php?id=" . urlencode($encrypted_id));
        exit();
    }
}

// 4. Fetch Existing Record Data for Form Population
$stmt = $conn->prepare("SELECT * FROM patient_medical_records WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $record_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Patient record not found.");
}

$record = $result->fetch_assoc();
$stmt->close();
// NOTE: Do NOT call $conn->close() here because the inventory dropdown query runs further down inside the HTML!
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Patient - Medical Unit</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="icon" type="image/x-icon" href="./assets/images/favicon.png">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
</head>

<body>
    <div id="app">
        <?php include('./inc/side-nav.php'); ?>
        <div id="main">
            <div class="page-heading">
                <div class="page-title mb-4">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-6">
                            <h3>Edit Patient Medical Record</h3>
                            <p class="text-subtitle text-muted">Update clinical information, vitals, and diagnostic details.</p>
                        </div>
                        <div class="col-12 col-md-6 text-md-end">
                            <a href="view_patient.php?id=<?php echo urlencode($encrypted_id); ?>" class="btn btn-secondary btn-sm px-3 py-2">
                                <i class="bi bi-arrow-left me-1"></i> Back to Profile
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (isset($_SESSION['msg'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['msg_type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['msg'];
                        unset($_SESSION['msg']);
                        unset($_SESSION['msg_type']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <section class="section">
                    <form action="proc_edit_patient.php?id=<?php echo urlencode($encrypted_id); ?>" method="POST">
                        <div class="row g-4">

                            <!-- 1. Identification & Demographics -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                                    <h5 class="fw-bold text-primary mb-3">1. Patient Identification & Demographics</h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Patient Name <span class="text-danger">*</span></label>
                                            <input type="text" name="patient_name" class="form-control" value="<?php echo htmlspecialchars($record['patient_name']); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Patient Location / Branch <span class="text-danger">*</span></label>
                                            <input type="text" name="patient_location" class="form-control" value="<?php echo htmlspecialchars($record['patient_location']); ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Phone Number</label>
                                            <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($record['phone_number'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Date of Birth</label>
                                            <input type="date" name="date_of_birth" class="form-control" value="<?php echo htmlspecialchars($record['date_of_birth'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Gender</label>
                                            <select name="gender" class="form-select">
                                                <option value="">Select Gender</option>
                                                <option value="Male" <?php echo ($record['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                                <option value="Female" <?php echo ($record['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                                <option value="Other" <?php echo ($record['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Blood Group</label>
                                            <select name="blood_group" class="form-select">
                                                <option value="">Select Blood Group</option>
                                                <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg): ?>
                                                    <option value="<?php echo $bg; ?>" <?php echo ($record['blood_group'] === $bg) ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Genotype</label>
                                            <select name="genotype" class="form-select">
                                                <option value="">Select Genotype</option>
                                                <?php foreach (['AA', 'AS', 'SS', 'AC'] as $gt): ?>
                                                    <option value="<?php echo $gt; ?>" <?php echo ($record['genotype'] === $gt) ? 'selected' : ''; ?>><?php echo $gt; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Residential Address</label>
                                            <textarea name="residential_address" class="form-control" rows="2"><?php echo htmlspecialchars($record['residential_address'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Emergency Contacts & Medical History -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                                    <h5 class="fw-bold text-danger mb-3">2. Emergency Contact & Medical Background</h5>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Next of Kin Name</label>
                                            <input type="text" name="next_of_kin_name" class="form-control" value="<?php echo htmlspecialchars($record['next_of_kin_name'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Relationship</label>
                                            <input type="text" name="next_of_kin_relationship" class="form-control" value="<?php echo htmlspecialchars($record['next_of_kin_relationship'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Next of Kin Phone</label>
                                            <input type="text" name="next_of_kin_phone" class="form-control" value="<?php echo htmlspecialchars($record['next_of_kin_phone'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Known Allergies</label>
                                            <textarea name="allergies" class="form-control" rows="2"><?php echo htmlspecialchars($record['allergies'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Past Medical History</label>
                                            <textarea name="medical_history" class="form-control" rows="2"><?php echo htmlspecialchars($record['medical_history'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Vitals & Visit Timelines -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                                    <h5 class="fw-bold text-success mb-3">3. Vital Signs & Visit Timelines</h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Intake Time <span class="text-danger">*</span></label>
                                            <input type="text" name="intake_time" class="form-control" value="<?php echo htmlspecialchars($record['intake_time']); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Release Time</label>
                                            <input type="text" name="release_time" class="form-control" value="<?php echo htmlspecialchars($record['release_time'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Blood Pressure</label>
                                            <input type="text" name="blood_pressure" class="form-control" value="<?php echo htmlspecialchars($record['blood_pressure'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Temperature (°C)</label>
                                            <input type="text" name="temperature" class="form-control" value="<?php echo htmlspecialchars($record['temperature'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Pulse Rate (bpm)</label>
                                            <input type="text" name="pulse_rate" class="form-control" value="<?php echo htmlspecialchars($record['pulse_rate'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Respiratory Rate</label>
                                            <input type="text" name="respiratory_rate" class="form-control" value="<?php echo htmlspecialchars($record['respiratory_rate'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Oxygen Saturation</label>
                                            <input type="text" name="oxygen_saturation" class="form-control" value="<?php echo htmlspecialchars($record['oxygen_saturation'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Weight</label>
                                            <input type="text" name="weight" class="form-control" value="<?php echo htmlspecialchars($record['weight'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Height</label>
                                            <input type="text" name="height" class="form-control" value="<?php echo htmlspecialchars($record['height'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Blood Sugar</label>
                                            <input type="text" name="blood_sugar" class="form-control" value="<?php echo htmlspecialchars($record['blood_sugar'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Condition on Admission</label>
                                            <input type="text" name="condition_on_admission" class="form-control" value="<?php echo htmlspecialchars($record['condition_on_admission'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Condition on Release</label>
                                            <input type="text" name="condition_on_release" class="form-control" value="<?php echo htmlspecialchars($record['condition_on_release'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Diagnosis & Treatment -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                                    <h5 class="fw-bold text-warning mb-3">4. Diagnosis & Treatment Details</h5>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Symptoms Presented</label>
                                            <textarea name="symptoms" class="form-control" rows="2"><?php echo htmlspecialchars($record['symptoms'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Diagnosis <span class="text-danger">*</span></label>
                                            <textarea name="diagnosis" class="form-control" rows="2" required><?php echo htmlspecialchars($record['diagnosis']); ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Medical Notes / Remarks</label>
                                            <textarea name="medical_notes" class="form-control" rows="2"><?php echo htmlspecialchars($record['medical_notes'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Treatment Given</label>
                                            <textarea name="treatment_given" class="form-control" rows="2"><?php echo htmlspecialchars($record['treatment_given'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Drugs Administered</label>

                                            <!-- Editable text area for existing or manual drug entries -->
                                            <div class="mb-2">
                                                <textarea name="drugs_given" class="form-control" rows="2" placeholder="Previously recorded drugs..."><?php echo htmlspecialchars($record['drugs_given'] ?? ''); ?></textarea>
                                                <small class="text-muted">You can edit the text above or use the dropdown below to append additional inventory items.</small>
                                            </div>

                                            <!-- Inventory Multi-select dropdown to add more drugs -->
                                            <label class="form-label fw-semibold text-primary mt-1">Add Additional Inventory Asset(s)</label>
                                            <select name="additional_drugs_given[]" class="form-select form-control-lg shadow-sm" multiple size="3" style="height: auto;">
                                                <option value="" disabled>-- Select Additional Inventory Asset(s) --</option>
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
                                            <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple. Zero-quantity items are locked.</small>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Dosage Instructions</label>
                                            <textarea name="dosage_instructions" class="form-control" rows="2"><?php echo htmlspecialchars($record['dosage_instructions'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Administrative Controls -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                                    <h5 class="fw-bold text-secondary mb-3">5. Administrative Controls</h5>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Attended By</label>
                                            <input type="text" name="attended_by" class="form-control" value="<?php echo htmlspecialchars($record['attended_by'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Follow-Up Required</label>
                                            <select name="follow_up_required" class="form-select">
                                                <option value="no" <?php echo ($record['follow_up_required'] === 'no') ? 'selected' : ''; ?>>No</option>
                                                <option value="yes" <?php echo ($record['follow_up_required'] === 'yes') ? 'selected' : ''; ?>>Yes</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Follow-Up Date</label>
                                            <input type="date" name="follow_up_date" class="form-control" value="<?php echo htmlspecialchars($record['follow_up_date'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Record Status</label>
                                            <select name="record_status" class="form-select">
                                                <option value="open" <?php echo ($record['record_status'] === 'open') ? 'selected' : ''; ?>>Open</option>
                                                <option value="under_treatment" <?php echo ($record['record_status'] === 'under_treatment') ? 'selected' : ''; ?>>Under Treatment</option>
                                                <option value="closed" <?php echo ($record['record_status'] === 'closed') ? 'selected' : ''; ?>>Closed</option>
                                            </select>
                                        </div>
                                        <div class="col-12 text-end mt-4">
                                            <button type="submit" name="update_record" class="btn btn-primary px-5 py-2">
                                                <i class="bi bi-check-circle me-1"></i> Update Patient Record
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>
<?php
// Close connection safely at the very end of page lifecycle if needed
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>