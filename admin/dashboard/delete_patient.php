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

// 3. Fetch Record to verify existence and enforce branch/location access control
$stmt = $conn->prepare("SELECT patient_location FROM patient_medical_records WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $record_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['msg'] = "Patient medical record not found.";
    $_SESSION['msg_type'] = "danger";
    $stmt->close();
    $conn->close();
    header("Location: patient_medical_records.php");
    exit();
}

$record = $result->fetch_assoc();
$stmt->close();

// Role-based access control check (non-super-admin can only delete records within their assigned branch)
$user_role = strtolower($_SESSION['role'] ?? '');
$user_branch = $_SESSION['branch'] ?? '';

if ($user_role !== 'super-admin') {
    if (strtolower(trim($record['patient_location'] ?? '')) !== strtolower(trim($user_branch))) {
        $_SESSION['msg'] = "Access denied: You do not have permission to delete medical records outside your assigned location.";
        $_SESSION['msg_type'] = "danger";
        $conn->close();
        header("Location: patient_medical_records.php");
        exit();
    }
}

// 4. Execute Prepared Deletion Query
$delete_stmt = $conn->prepare("DELETE FROM patient_medical_records WHERE id = ?");
if ($delete_stmt) {
    $delete_stmt->bind_param("i", $record_id);

    if ($delete_stmt->execute()) {
        $_SESSION['msg'] = "Patient record successfully deleted.";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "Database error: Could not delete record. " . $delete_stmt->error;
        $_SESSION['msg_type'] = "danger";
    }
    $delete_stmt->close();
} else {
    $_SESSION['msg'] = "Database query preparation failed: " . $conn->error;
    $_SESSION['msg_type'] = "danger";
}

$conn->close();
header("Location: patient_medical_records.php");
exit();
