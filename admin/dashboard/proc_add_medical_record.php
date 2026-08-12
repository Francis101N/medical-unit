<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('./db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    // 1. Sanitize Basic Inputs
    // $submitted_staff_id stores the alphanumeric staff_id card string passed from the form
    $submitted_staff_id     = !empty($_POST['staff_name']) ? trim($_POST['staff_name']) : null;
    $intake_time            = !empty($_POST['intake_time']) ? $_POST['intake_time'] : null;
    $release_time           = !empty($_POST['release_time']) ? $_POST['release_time'] : null;
    $follow_up_date         = !empty($_POST['follow_up_date']) ? $_POST['follow_up_date'] : null;

    $symptoms               = trim($_POST['symptoms'] ?? '');
    $diagnosis              = trim($_POST['diagnosis'] ?? '');
    $medical_notes          = trim($_POST['medical_notes'] ?? '');
    $treatment_given        = trim($_POST['treatment_given'] ?? '');
    $drugs_given            = trim($_POST['drugs_given'] ?? '');
    $dosage_instructions    = trim($_POST['dosage_instructions'] ?? '');
    $attended_by            = trim($_POST['attended_by'] ?? '');
    $blood_pressure         = trim($_POST['blood_pressure'] ?? '');
    $temperature            = trim($_POST['temperature'] ?? '');
    $pulse_rate             = trim($_POST['pulse_rate'] ?? '');
    $follow_up_required     = trim($_POST['follow_up_required'] ?? 'no');

    // Strict ENUM Validation for Dropdowns
    $allowed_admission = ['stable', 'critical', 'serious', 'minor'];
    $allowed_release   = ['improved', 'stable', 'referred', 'deceased'];
    $allowed_statuses  = ['open', 'closed', 'under_treatment'];

    $raw_admission = strtolower(trim($_POST['condition_on_admission'] ?? ''));
    $raw_release   = strtolower(trim($_POST['condition_on_release'] ?? ''));
    $raw_status    = strtolower(trim($_POST['record_status'] ?? 'open'));

    // Apply strict validation fallbacks to prevent data truncation errors
    $condition_on_admission = in_array($raw_admission, $allowed_admission, true) ? $raw_admission : 'stable';
    $condition_on_release   = in_array($raw_release, $allowed_release, true) ? $raw_release : null;
    $record_status          = in_array($raw_status, $allowed_statuses, true) ? $raw_status : 'open';

    // 2. Initial Validation
    if (!$submitted_staff_id || empty($intake_time)) {
        $_SESSION['msg'] = "Please fill in all required fields (Staff Selection and Intake Time).";
        $_SESSION['msg_type'] = "danger";
        header("Location: add_medical_record.php");
        exit();
    }

    /** @var mysqli $conn */

    // 3. Look up Staff Profile details (including fullname) using the staff_id string
    $staff_lookup_sql = "SELECT s.fullname, b.branch_name, s.department 
                         FROM staffs s
                         LEFT JOIN branches b ON s.branch_id = b.id 
                         WHERE s.staff_id = ? 
                         LIMIT 1";

    $staff_actual_name = '';
    $staff_branch      = '';
    $department        = '';

    if ($lookup_stmt = mysqli_prepare($conn, $staff_lookup_sql)) {
        mysqli_stmt_bind_param($lookup_stmt, "s", $submitted_staff_id);
        mysqli_stmt_execute($lookup_stmt);
        mysqli_stmt_bind_result($lookup_stmt, $fetched_name, $fetched_branch, $fetched_dept);

        if (mysqli_stmt_fetch($lookup_stmt)) {
            $staff_actual_name = $fetched_name ?? 'Unknown Staff';
            $staff_branch      = $fetched_branch ?? 'Unknown Branch';
            $department        = $fetched_dept ?? 'Unassigned';
        } else {
            // Fallback case: if the lookup somehow returns absolutely nothing, fail cleanly
            $_SESSION['msg'] = "Error: Selected staff profile record could not be found.";
            $_SESSION['msg_type'] = "danger";
            mysqli_stmt_close($lookup_stmt);
            header("Location: add_medical_record.php");
            exit();
        }
        mysqli_stmt_close($lookup_stmt);
    }

    // 4. Prepared Statement to insert data into staff_medical_records
    $sql = "INSERT INTO staff_medical_records (
                staff_name, 
                staff_branch, 
                department, 
                intake_time, 
                release_time, 
                symptoms, 
                diagnosis, 
                medical_notes, 
                treatment_given, 
                drugs_given, 
                dosage_instructions, 
                attended_by, 
                condition_on_admission, 
                condition_on_release, 
                blood_pressure, 
                temperature, 
                pulse_rate, 
                follow_up_required, 
                follow_up_date, 
                record_status, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    if ($stmt = mysqli_prepare($conn, $sql)) {

        // Swapped in $staff_actual_name as the first bound parameter string instead of the ID
        mysqli_stmt_bind_param(
            $stmt,
            "ssssssssssssssssssss",
            $staff_actual_name,
            $staff_branch,
            $department,
            $intake_time,
            $release_time,
            $symptoms,
            $diagnosis,
            $medical_notes,
            $treatment_given,
            $drugs_given,
            $dosage_instructions,
            $attended_by,
            $condition_on_admission,
            $condition_on_release,
            $blood_pressure,
            $temperature,
            $pulse_rate,
            $follow_up_required,
            $follow_up_date,
            $record_status
        );

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['msg'] = "Medical record successfully recorded!";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['msg'] = "Execution Error: " . mysqli_stmt_error($stmt);
            $_SESSION['msg_type'] = "danger";
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['msg'] = "SQL Prepare Error: " . mysqli_error($conn);
        $_SESSION['msg_type'] = "danger";
    }

    header("Location: add_medical_record.php");
    exit();
} else {
    header("Location: add_medical_record.php");
    exit();
}
