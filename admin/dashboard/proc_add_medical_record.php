<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
/** @var mysqli $conn */
include('./db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    // 1. Sanitize Basic Inputs
    $submitted_staff_id     = !empty($_POST['staff_name']) ? trim($_POST['staff_name']) : null;
    $intake_time            = !empty($_POST['intake_time']) ? $_POST['intake_time'] : null;
    $release_time           = !empty($_POST['release_time']) ? $_POST['release_time'] : null;
    $follow_up_date         = !empty($_POST['follow_up_date']) ? $_POST['follow_up_date'] : null;

    $symptoms               = trim($_POST['symptoms'] ?? '');
    $diagnosis              = trim($_POST['diagnosis'] ?? '');
    $medical_notes          = trim($_POST['medical_notes'] ?? '');
    $treatment_given        = trim($_POST['treatment_given'] ?? '');
    
    // Handle multiple selected drugs array safely
    $raw_drugs_array        = $_POST['drugs_given'] ?? [];
    if (!is_array($raw_drugs_array)) {
        $raw_drugs_array = [$raw_drugs_array];
    }
    // Filter out empty options if any are accidentally selected
    $drugs_given_array      = array_filter(array_map('trim', $raw_drugs_array));

    $dosage_instructions    = trim($_POST['dosage_instructions'] ?? '');
    $attended_by            = trim($_POST['attended_by'] ?? '');
    $blood_pressure         = trim($_POST['blood_pressure'] ?? '');
    $temperature            = trim($_POST['temperature'] ?? '');
    $pulse_rate             = trim($_POST['pulse_rate'] ?? '');
    $follow_up_required     = trim($_POST['follow_up_required'] ?? 'no');
    $company                = trim($_POST['company'] ?? '');

    // Strict ENUM Validation for Dropdowns
    $allowed_admission = ['stable', 'critical', 'serious', 'minor'];
    $allowed_release   = ['improved', 'stable', 'referred', 'deceased'];
    $allowed_statuses  = ['open', 'closed', 'under_treatment'];

    $raw_admission = strtolower(trim($_POST['condition_on_admission'] ?? ''));
    $raw_release   = strtolower(trim($_POST['condition_on_release'] ?? ''));
    $raw_status    = strtolower(trim($_POST['record_status'] ?? 'open'));

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

    if (empty($drugs_given_array)) {
        $_SESSION['msg'] = "Please select at least one prescription drug.";
        $_SESSION['msg_type'] = "danger";
        header("Location: add_medical_record.php");
        exit();
    }

    // 3. Look up Staff Profile details (including branch ID, branch name, and company)
    $staff_lookup_sql = "SELECT s.id as staff_pk, s.fullname, s.branch_id, b.branch_name, s.department, s.company 
                         FROM staffs s
                         LEFT JOIN branches b ON s.branch_id = b.id 
                         WHERE s.staff_id = ? 
                         LIMIT 1";

    $staff_actual_name = '';
    $staff_branch      = '';
    $branch_id         = 0;
    $department        = '';
    $staff_company     = '';

    if ($lookup_stmt = mysqli_prepare($conn, $staff_lookup_sql)) {
        mysqli_stmt_bind_param($lookup_stmt, "s", $submitted_staff_id);
        mysqli_stmt_execute($lookup_stmt);
        mysqli_stmt_bind_result($lookup_stmt, $staff_pk, $fetched_name, $fetched_branch_id, $fetched_branch_name, $fetched_dept, $fetched_company);

        if (mysqli_stmt_fetch($lookup_stmt)) {
            $staff_actual_name = $fetched_name ?? 'Unknown Staff';
            $branch_id         = intval($fetched_branch_id ?? 0);
            $staff_branch      = $fetched_branch_name ?? 'Unknown Branch';
            $department        = $fetched_dept ?? 'Unassigned';

            // Fallback to looked-up company if the posted form value is empty
            if (empty($company)) {
                $company = $fetched_company ?? '';
            }
        } else {
            $_SESSION['msg'] = "Error: Selected staff profile record could not be found.";
            $_SESSION['msg_type'] = "danger";
            mysqli_stmt_close($lookup_stmt);
            header("Location: add_medical_record.php");
            exit();
        }
        mysqli_stmt_close($lookup_stmt);
    }

    // 4. Begin ACID Transaction Sequence
    $conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    try {
        // Prepare string representation for database storage
        $drugs_given_string = implode(', ', $drugs_given_array);

        // A. Loop through each selected drug, verify stock, and deduct inventory
        foreach ($drugs_given_array as $single_drug_title) {
            if ($branch_id <= 0) {
                throw new Exception("Dispensing Error: The selected staff member is not assigned to a valid branch node vault.");
            }

            // Find matching master drug ID based on the title string selection
            $drug_match_stmt = $conn->prepare("
                SELECT id, drug_name, strength 
                FROM drugs_master 
                WHERE ? LIKE CONCAT('%', drug_name, '%')
                LIMIT 1
            ");
            if (!$drug_match_stmt) {
                throw new Exception("Failed to compile pharmaceutical asset tracking mapping.");
            }
            $drug_match_stmt->bind_param("s", $single_drug_title);
            $drug_match_stmt->execute();
            $drug_res = $drug_match_stmt->get_result()->fetch_assoc();
            $drug_match_stmt->close();

            if (!$drug_res) {
                throw new Exception("Dispensing Error: Prescription drug asset (" . htmlspecialchars($single_drug_title) . ") could not be verified in the master catalog.");
            }

            $drug_id = intval($drug_res['id']);

            // Check branch vault allocation balance and lock row (FOR UPDATE)
            $vault_check = $conn->prepare("
                SELECT current_balance 
                FROM drugs_allocations 
                WHERE branch_id = ? AND drug_id = ? 
                LIMIT 1 FOR UPDATE
            ");
            if (!$vault_check) {
                throw new Exception("Failed to establish secure localized branch vault mapping.");
            }
            $vault_check->bind_param("ii", $branch_id, $drug_id);
            $vault_check->execute();
            $vault_res = $vault_check->get_result()->fetch_assoc();
            $vault_check->close();

            if (!$vault_res || intval($vault_res['current_balance']) < 1) {
                throw new Exception("Vault Out Of Stock: The branch vault (" . htmlspecialchars($staff_branch) . ") has insufficient stock balance for: " . htmlspecialchars($single_drug_title));
            }

            // Deduct 1 unit from the branch local vault current_balance
            $vault_update = $conn->prepare("
                UPDATE drugs_allocations 
                SET current_balance = current_balance - 1 
                WHERE branch_id = ? AND drug_id = ?
            ");
            if (!$vault_update) {
                throw new Exception("Failed to update branch vault balance parameters.");
            }
            $vault_update->bind_param("ii", $branch_id, $drug_id);
            $vault_update->execute();
            $vault_update->close();

            // Append a log entry into drugs_stock_logs for tracking transparency
            $processed_by_user = intval($_SESSION['user_id'] ?? 1);
            $audit_note = "Dispensed 1 unit of " . $single_drug_title . " to staff member " . $staff_actual_name . " (" . $submitted_staff_id . ").";

            $log_stmt = $conn->prepare("
                INSERT INTO drugs_stock_logs (drug_id, branch_id, transaction_type, quantity, processed_by, notes, created_at) 
                VALUES (?, ?, 'dispense', 1, ?, ?, NOW())
            ");
            if ($log_stmt) {
                $log_stmt->bind_param("iiis", $drug_id, $branch_id, $processed_by_user, $audit_note);
                $log_stmt->execute();
                $log_stmt->close();
            }
        }

        // B. Insert data into staff_medical_records (storing combined comma-separated string)
        $sql = "INSERT INTO staff_medical_records (
                    staff_name, 
                    staff_branch, 
                    company,
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
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param(
                $stmt,
                "sssssssssssssssssssss",
                $staff_actual_name,
                $staff_branch,
                $company,
                $department,
                $intake_time,
                $release_time,
                $symptoms,
                $diagnosis,
                $medical_notes,
                $treatment_given,
                $drugs_given_string,
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

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Execution Error: " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
        } else {
            throw new Exception("SQL Prepare Error: " . mysqli_error($conn));
        }

        // Commit transaction safely
        $conn->commit();

        $_SESSION['msg'] = "Medical record successfully recorded and branch inventory updated for all selected drugs!";
        $_SESSION['msg_type'] = "success";
    } catch (Exception $e) {
        // Rollback transaction on failure to ensure data parity
        $conn->rollback();

        $_SESSION['msg'] = "Transaction Aborted: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
    }

    header("Location: add_medical_record.php");
    exit();
} else {
    header("Location: add_medical_record.php");
    exit();
}