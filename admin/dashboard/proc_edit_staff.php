<?php
session_start();
/** @var mysqli $conn */
include('db.php');

// Helper function to decode the encrypted ID token
function decryptId($token)
{
    $key = "medical-secret-key";
    $decoded = base64_decode(strtr($token, '-_', '+/'));
    if ($decoded === false || !str_contains($decoded, '|')) {
        return false;
    }
    list($id, $secret) = explode('|', $decoded, 2);
    return ($secret === $key) ? $id : false;
}

if (isset($_POST['update_staff'])) {
    // =========================
    // GET & DECRYPT ID TOKEN
    // =========================
    $encrypted_id = $_POST['id'];
    $id = decryptId($encrypted_id);

    // Bounce if token manipulation is detected
    if ($id === false) {
        $_SESSION['error'] = "Invalid or expired tracking token identification details.";
        header("Location: staffs.php");
        exit();
    }

    // =========================
    // COLLECT DATA
    // =========================
    $staff_id        = $_POST['staff_id'];
    $fullname        = trim($_POST['fullname']); // Trim spaces to prevent join mismatches
    $email           = $_POST['email'];
    $phone           = $_POST['phone'];
    $gender          = $_POST['gender'];
    $dob             = $_POST['dob'];
    $branch_id       = $_POST['branch_id'];
    $department      = $_POST['department'];
    $role            = $_POST['role'];
    $employment_type = $_POST['employment_type'];
    $hire_date       = $_POST['hire_date'];
    $status          = $_POST['status'];

    // =========================
    // GET EXISTING STAFF DETAILS
    // =========================
    // We must pull both passport AND the old fullname to check for shifts
    $stmt_old = mysqli_prepare($conn, "SELECT fullname, passport FROM staffs WHERE id = ?");
    mysqli_stmt_bind_param($stmt_old, "s", $id);
    mysqli_stmt_execute($stmt_old);
    $result_old = mysqli_stmt_get_result($stmt_old);
    $old_data = mysqli_fetch_assoc($result_old);

    $old_fullname = $old_data['fullname'] ?? '';
    $old_passport = $old_data['passport'] ?? '';

    // =========================
    // PASSPORT UPLOAD
    // =========================
    $passport = $old_passport;

    if (!empty($_FILES['passport']['name'])) {
        $file_name = $_FILES['passport']['name'];
        $file_tmp  = $_FILES['passport']['tmp_name'];

        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_name = time() . '_' . rand(1000, 9999) . '.' . $ext;

        if (move_uploaded_file($file_tmp, "uploads/" . $new_name)) {
            $passport = $new_name;
        }
    }

    // =========================
    // CHECK EMAIL DUPLICATE
    // =========================
    $stmt_email = mysqli_prepare($conn, "SELECT id FROM staffs WHERE email = ? AND id != ?");
    mysqli_stmt_bind_param($stmt_email, "ss", $email, $id);
    mysqli_stmt_execute($stmt_email);
    $result_email = mysqli_stmt_get_result($stmt_email);

    if (mysqli_num_rows($result_email) > 0) {
        $_SESSION['error'] = "Email already exists";
        header("Location: edit_staff.php?id=" . urlencode($encrypted_id));
        exit();
    }

    // =========================
    // TRANSACTION CONTROL
    // =========================
    // Turn off autocommit to ensure both tables update together cleanly
    mysqli_begin_transaction($conn);

    try {
        // 1. Update the core Staff record
        $stmt_update = mysqli_prepare($conn, "
            UPDATE staffs SET
                staff_id = ?,
                fullname = ?,
                email = ?,
                phone = ?,
                gender = ?,
                dob = ?,
                passport = ?,
                branch_id = ?,
                department = ?,
                role = ?,
                employment_type = ?,
                hire_date = ?,
                status = ?
            WHERE id = ?
        ");

        mysqli_stmt_bind_param(
            $stmt_update,
            "ssssssssssssss",
            $staff_id,
            $fullname,
            $email,
            $phone,
            $gender,
            $dob,
            $passport,
            $branch_id,
            $department,
            $role,
            $employment_type,
            $hire_date,
            $status,
            $id
        );
        mysqli_stmt_execute($stmt_update);

        // 2. Cascade changes to medical records if the name was altered
        if (!empty($old_fullname) && $old_fullname !== $fullname) {
            $stmt_cascade = mysqli_prepare($conn, "
                UPDATE staff_medical_records 
                SET staff_name = ? 
                WHERE staff_name = ?
            ");
            mysqli_stmt_bind_param($stmt_cascade, "ss", $fullname, $old_fullname);
            mysqli_stmt_execute($stmt_cascade);
        }

        // Commit transaction blocks safely
        mysqli_commit($conn);
        $update_success = true;
    } catch (Exception $e) {
        // Roll back changes if database constraints break
        mysqli_rollback($conn);
        $update_success = false;
    }

    // =========================
    // RESPONSE
    // =========================
    if ($update_success) {
        $_SESSION['success'] = "Staff updated successfully";
        header("Location: staffs.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update staff record details.";
        header("Location: edit_staff.php?id=" . urlencode($encrypted_id));
        exit();
    }
}
