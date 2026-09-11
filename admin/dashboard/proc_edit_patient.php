<?php
session_start();
/** @var mysqli $conn */
include('./db.php');

// 1. Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

// 2. Ensure request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: patient_medical_records.php");
    exit();
}

// 3. ID Decryption Helper Function
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
        return intval($parts[0]);
    }
}

$encrypted_id = $_GET['id'] ?? '';
$record_id = decryptId($encrypted_id);

if (!$record_id) {
    $_SESSION['msg'] = "Invalid or tampered medical log record reference.";
    $_SESSION['msg_type'] = "danger";
    header("Location: patient_medical_records.php");
    exit();
}

// 4. Capture and Sanitize Form Inputs
// Identification & Affiliation
$patient_name = trim($_POST['patient_name'] ?? '');
$patient_location = trim($_POST['patient_location'] ?? '');

// Personal & Demographic Details
$date_of_birth = trim($_POST['date_of_birth'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$blood_group = trim($_POST['blood_group'] ?? '');
$genotype = trim($_POST['genotype'] ?? '');
$phone_number = trim($_POST['phone_number'] ?? '');
$residential_address = trim($_POST['residential_address'] ?? '');

// Emergency Contact
$next_of_kin_name = trim($_POST['next_of_kin_name'] ?? '');
$next_of_kin_relationship = trim($_POST['next_of_kin_relationship'] ?? '');
$next_of_kin_phone = trim($_POST['next_of_kin_phone'] ?? '');

// Clinical History & Allergies
$allergies = trim($_POST['allergies'] ?? '');
$medical_history = trim($_POST['medical_history'] ?? '');

// Current Visit & Vital Signs
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

// Diagnosis & Treatment
$symptoms = trim($_POST['symptoms'] ?? '');
$diagnosis = trim($_POST['diagnosis'] ?? '');
$medical_notes = trim($_POST['medical_notes'] ?? '');
$treatment_given = trim($_POST['treatment_given'] ?? '');

// Handle drugs given & quantity accumulation logic
$existing_drugs = trim($_POST['drugs_given'] ?? '');
$additional_drugs_array = $_POST['additional_drugs_given'] ?? [];

$filtered_additional_drugs = [];
if (is_array($additional_drugs_array)) {
    $filtered_additional_drugs = array_filter($additional_drugs_array, function ($value) {
        return trim($value) !== '';
    });
}

// Parse existing drugs text and accumulate quantities (e.g., "Paracetamol (2), Aspirin" -> counts array)
$current_drug_counts = [];
if (!empty($existing_drugs)) {
    $parts = explode(',', $existing_drugs);
    foreach ($parts as $part) {
        $part = trim($part);
        if (empty($part)) continue;

        if (preg_match('/^(.*?)\s*\((\d+)\)$/', $part, $matches)) {
            $name = trim($matches[1]);
            $qty = intval($matches[2]);
            $current_drug_counts[$name] = ($current_drug_counts[$name] ?? 0) + $qty;
        } else {
            $current_drug_counts[$part] = ($current_drug_counts[$part] ?? 0) + 1;
        }
    }
}

// Count frequencies of newly selected items from the multi-select dropdown
$additional_counts = array_count_values($filtered_additional_drugs);
foreach ($additional_counts as $drug => $count) {
    $current_drug_counts[$drug] = ($current_drug_counts[$drug] ?? 0) + $count;
}

// Rebuild the final accumulated string with updated quantities
$final_drug_strings = [];
foreach ($current_drug_counts as $drug => $qty) {
    if ($qty > 1) {
        $final_drug_strings[] = $drug . ' (' . $qty . ')';
    } else {
        $final_drug_strings[] = $drug;
    }
}
$drugs_given = implode(', ', $final_drug_strings);

$dosage_instructions = trim($_POST['dosage_instructions'] ?? '');
$attended_by = trim($_POST['attended_by'] ?? '');
$follow_up_required = trim($_POST['follow_up_required'] ?? 'no');
$follow_up_date = trim($_POST['follow_up_date'] ?? '');
$record_status = trim($_POST['record_status'] ?? 'open');

// 5. Validation for Required Fields
if (empty($patient_name) || empty($patient_location) || empty($intake_time) || empty($diagnosis)) {
    $_SESSION['msg'] = "Please fill in all required fields (Patient Name, Patient Location, Intake Time, Diagnosis).";
    $_SESSION['msg_type'] = "danger";
    header("Location: edit_patient.php?id=" . urlencode($encrypted_id));
    exit();
}

// 6. Normalize Optional Fields to NULL if Empty
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

// 7. Begin Database Transaction for atomic inventory adjustments and record updating
mysqli_begin_transaction($conn);

try {
    // Deduct stock accurately based on total quantity added per item in this submission
    foreach ($additional_counts as $selected_drug => $qty_to_deduct) {
        $item_name_to_check = trim(preg_replace('/\s*\(.*?\)\s*$/', '', $selected_drug));

        $inv_check_stmt = $conn->prepare("SELECT id, total_quantity FROM outreach_inventory WHERE item_name = ? OR CONCAT(item_name, ' (', unit, ')') = ? FOR UPDATE");
        $inv_check_stmt->bind_param("ss", $item_name_to_check, $selected_drug);
        $inv_check_stmt->execute();
        $inv_res = $inv_check_stmt->get_result();

        if ($inv_row = $inv_res->fetch_assoc()) {
            if (intval($inv_row['total_quantity']) < $qty_to_deduct) {
                throw new Exception("Insufficient stock for '" . htmlspecialchars($selected_drug) . "'. Requested: " . $qty_to_deduct . ", Available: " . $inv_row['total_quantity']);
            }

            $inv_update_stmt = $conn->prepare("UPDATE outreach_inventory SET total_quantity = total_quantity - ? WHERE id = ?");
            $inv_update_stmt->bind_param("ii", $qty_to_deduct, $inv_row['id']);
            $inv_update_stmt->execute();
            $inv_update_stmt->close();
        } else {
            throw new Exception("Inventory item '" . htmlspecialchars($selected_drug) . "' could not be found.");
        }
        $inv_check_stmt->close();
    }

    // Execute Prepared Update Query
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

    // 35 string parameters + 1 integer parameter (id)
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

    $_SESSION['msg'] = "Patient record successfully updated and inventory quantity adjusted!";
    $_SESSION['msg_type'] = "success";
    $conn->close();
    header("Location: edit_patient.php?id=" . urlencode($encrypted_id));
    exit();
} catch (Exception $e) {
    // Rollback transaction on failure
    mysqli_rollback($conn);

    $_SESSION['msg'] = $e->getMessage();
    $_SESSION['msg_type'] = "danger";
    $conn->close();
    header("Location: edit_patient.php?id=" . urlencode($encrypted_id));
    exit();
}
