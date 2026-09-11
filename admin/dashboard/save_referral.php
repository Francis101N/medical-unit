<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Restrict access to super admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super-admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

/** @var mysqli $conn */
include('./db.php');

header('Content-Type: application/json');

// Check if file and form data are received via multipart/form-data
$record_id    = $_POST['record_id'] ?? 0;
$staff_name   = $_POST['staff_name'] ?? '';
$serial_id    = $_POST['serial_id'] ?? '';
$ref_code     = $_POST['ref_code'] ?? '';
$pdf_filename = $_POST['pdf_filename'] ?? '';

if (empty($staff_name) || empty($serial_id) || empty($ref_code)) {
    echo json_encode(['status' => 'error', 'message' => 'Required fields are missing.']);
    exit();
}

// Handle actual physical PDF file upload from the Blob
$saved_filename = $pdf_filename;
if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = './uploads/referrals/';
    
    // Create directory if it does not exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Sanitize and secure filename
    $file_extension = pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION);
    $safe_filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($pdf_filename, PATHINFO_FILENAME)) . '_' . time() . '.' . ($file_extension ?: 'pdf');
    $destination = $upload_dir . $safe_filename;
    
    if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $destination)) {
        $saved_filename = $safe_filename; // Store the safe uploaded filename in database
    }
}

// 1. Log the referral entry into referral_logs
$log_stmt = $conn->prepare("INSERT INTO referral_logs (staff_name, serial_id, ref_code) VALUES (?, ?, ?)");
if ($log_stmt) {
    $log_stmt->bind_param("sss", $staff_name, $serial_id, $ref_code);
    $log_stmt->execute();
    $log_stmt->close();
}

// 2. Update staff_medical_records: set referred to 'yes' and store the PDF filename
if (!empty($record_id) && $record_id > 0) {
    $update_stmt = $conn->prepare("UPDATE staff_medical_records SET referred = 'yes', pdf = ? WHERE id = ?");
    if ($update_stmt) {
        $update_stmt->bind_param("si", $saved_filename, $record_id);
        $update_stmt->execute();
        $update_stmt->close();
    }
}

echo json_encode(['status' => 'success', 'message' => 'Referral record updated and PDF saved successfully.', 'filename' => $saved_filename]);
$conn->close();