<?php
session_start();
/** @var mysqli $conn */
include('db.php');

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

if (isset($_POST['submit'])) {

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

    // Handle multi-select drugs array and convert to a comma-separated string
    $drugs_array = $_POST['drugs_given'] ?? [];
    if (is_array($drugs_array)) {
        $filtered_drugs = array_filter($drugs_array, function ($value) {
            return trim($value) !== '';
        });
        $drugs_given = implode(', ', $filtered_drugs);
    } else {
        $drugs_given = trim($drugs_array);
        $filtered_drugs = !empty($drugs_given) ? [$drugs_given] : [];
    }

    $dosage_instructions = trim($_POST['dosage_instructions'] ?? '');
    $attended_by = trim($_POST['attended_by'] ?? '');
    $follow_up_required = trim($_POST['follow_up_required'] ?? 'no');
    $follow_up_date = trim($_POST['follow_up_date'] ?? '');
    $record_status = trim($_POST['record_status'] ?? 'open');

    // Basic Validation for strictly required fields only
    if (empty($patient_name) || empty($patient_location) || empty($intake_time) || empty($diagnosis)) {
        $_SESSION['msg'] = "Please fill in all required fields (Patient Name, Patient Location, Intake Time, Diagnosis).";
        $_SESSION['msg_type'] = "danger";
        header("Location: add_patient_record.php");
        exit();
    }

    // Convert empty optional strings to null or fallback defaults so SQL handles them cleanly
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

    // Begin Database Transaction to safely handle inventory deduction & record insertion atomically
    mysqli_begin_transaction($conn);

    try {
        // Deduct inventory quantities for each selected drug/item
        foreach ($filtered_drugs as $selected_drug) {
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

        // Prepare SQL Insert statement
        $insert_query = "INSERT INTO patient_medical_records (
            patient_name, patient_location, date_of_birth, gender, blood_group, genotype, 
            phone_number, residential_address, next_of_kin_name, next_of_kin_relationship, 
            next_of_kin_phone, allergies, medical_history, intake_time, release_time, 
            blood_pressure, temperature, pulse_rate, respiratory_rate, oxygen_saturation, 
            weight, height, blood_sugar, condition_on_admission, condition_on_release, 
            symptoms, diagnosis, medical_notes, treatment_given, drugs_given, 
            dosage_instructions, attended_by, follow_up_required, follow_up_date, record_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $insert_query);

        if (!$stmt) {
            throw new Exception("Database query preparation failed: " . mysqli_error($conn));
        }

        $types = str_repeat('s', 35);

        // Bind parameters using the sanitized nullable variables
        mysqli_stmt_bind_param(
            $stmt,
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
            $record_status
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Database error: Could not save patient record. " . mysqli_stmt_error($stmt));
        }

        mysqli_stmt_close($stmt);

        // Commit transaction if all operations succeed
        mysqli_commit($conn);

        $_SESSION['msg'] = "Comprehensive patient record successfully created and inventory updated!";
        $_SESSION['msg_type'] = "success";
        header("Location: patient_medical_records.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on failure
        mysqli_rollback($conn);

        $_SESSION['msg'] = $e->getMessage();
        $_SESSION['msg_type'] = "danger";
        header("Location: add_patient_record.php");
        exit();
    }

    mysqli_close($conn);
} else {
    header("Location: patient_medical_records.php");
    exit();
}
