<?php
// 1. Temporary Error Debugger (Turn on to catch the exact fatal error)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/** @var mysqli $conn */
include('./db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_log'])) {

    // 2. Sanitize Primary Key
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        header("Location: medical-records.php?error=invalid_id");
        exit();
    }

    // 3. Extract & Sanitize Form Inputs (Using empty string fallback to avoid type issues)
    $staff_name             = trim($_POST['staff_name'] ?? '');
    $staff_branch           = trim($_POST['staff_branch'] ?? '');
    $department             = trim($_POST['department'] ?? '');
    $attended_by            = trim($_POST['attended_by'] ?? '');

    $diagnosis              = trim($_POST['diagnosis'] ?? '');
    $symptoms               = trim($_POST['symptoms'] ?? '');

    // Convert empty fields to explicit variables (Passing direct nulls by reference can trigger PHP 500s)
    $medical_notes          = !empty($_POST['medical_notes']) ? trim($_POST['medical_notes']) : "";
    $treatment_given        = !empty($_POST['treatment_given']) ? trim($_POST['treatment_given']) : "";
    $drugs_given            = !empty($_POST['drugs_given']) ? trim($_POST['drugs_given']) : "";
    $dosage_instructions    = !empty($_POST['dosage_instructions']) ? trim($_POST['dosage_instructions']) : "";

    $condition_on_admission = !empty($_POST['condition_on_admission']) ? trim($_POST['condition_on_admission']) : "";
    $condition_on_release   = !empty($_POST['condition_on_release']) ? trim($_POST['condition_on_release']) : "";

    $blood_pressure         = !empty($_POST['blood_pressure']) ? trim($_POST['blood_pressure']) : "";
    $temperature            = !empty($_POST['temperature']) ? trim($_POST['temperature']) : "";
    $pulse_rate             = !empty($_POST['pulse_rate']) ? trim($_POST['pulse_rate']) : "";

    $follow_up_required     = trim($_POST['follow_up_required'] ?? 'no');
    $record_status          = trim($_POST['record_status'] ?? 'active');

    // 4. Date Formatting (Ensure empty fields don't corrupt into 1970-01-01)
    $intake_time            = !empty($_POST['intake_time']) ? date('Y-m-d H:i:s', strtotime($_POST['intake_time'])) : date('Y-m-d H:i:s');
    $release_time           = !empty($_POST['release_time']) ? date('Y-m-d H:i:s', strtotime($_POST['release_time'])) : null;
    $follow_up_date         = !empty($_POST['follow_up_date']) ? date('Y-m-d', strtotime($_POST['follow_up_date'])) : null;

    // 5. Prepared Statement Update Query
    $query = "UPDATE staff_medical_records SET 
                staff_name = ?, 
                staff_branch = ?, 
                department = ?, 
                attended_by = ?, 
                intake_time = ?, 
                release_time = ?, 
                diagnosis = ?, 
                symptoms = ?, 
                medical_notes = ?, 
                treatment_given = ?, 
                drugs_given = ?, 
                dosage_instructions = ?, 
                condition_on_admission = ?, 
                condition_on_release = ?, 
                blood_pressure = ?, 
                temperature = ?, 
                pulse_rate = ?, 
                follow_up_required = ?, 
                follow_up_date = ?, 
                record_status = ?, 
                updated_at = NOW()
              WHERE id = ?";

    $stmt = $conn->prepare($query);

    if ($stmt) {
        // 20 string/null fields ('s') + 1 integer ID ('i') = 21 parameters total
        $stmt->bind_param(
            "ssssssssssssssssssssi",
            $staff_name,
            $staff_branch,
            $department,
            $attended_by,
            $intake_time,
            $release_time,
            $diagnosis,
            $symptoms,
            $medical_notes,
            $treatment_given,
            $drugs_given,
            $dosage_instructions,
            $condition_on_admission,
            $condition_on_release,
            $blood_pressure,
            $temperature,
            $pulse_rate,
            $follow_up_required,
            $follow_up_date,
            $record_status,
            $id
        );

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: medical-records.php?status=success&msg=" . urlencode("Medical record updated successfully."));
            exit();
        } else {
            // If execution fails, print out why instead of redirecting blankly
            die("Execution Failed: " . $stmt->error);
        }
    } else {
        // If SQL syntax has a column mismatch with your table scheme, it prints here
        die("Statement Preparation Failed: " . $conn->error);
    }
} else {
    header("Location: medical-records.php?error=invalid_request");
    exit();
}
