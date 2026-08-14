<?php

/**
 * Master Drug Update Processor
 * File: proc_edit_drug.php
 */

/** @var mysqli $conn */
include('db.php');

// 1. Ensure Session Initialization
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Access Control: Super-Admin authorization check
$user_role = strtolower($_SESSION['role'] ?? '');
if ($user_role !== 'super-admin') {
    header("Location: manage_drugs.php?error=" . urlencode("Unauthorized access clearance required."));
    exit();
}

// 3. Verify Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage_drugs.php?error=" . urlencode("Invalid request method."));
    exit();
}

// 4. Extract and Sanitize POST Data
$original_code = trim($_POST['original_code'] ?? '');
$drug_code     = trim($_POST['drug_code'] ?? '');
$drug_name     = trim($_POST['drug_name'] ?? '');
$generic_name  = trim($_POST['generic_name'] ?? '');
$category      = trim($_POST['category'] ?? '');
$strength      = trim($_POST['strength'] ?? '');
$quantity_raw  = trim($_POST['quantity'] ?? '');
$dosage_form   = trim($_POST['dosage_form'] ?? '');

// Encrypted code fallback for redirecting back to edit page on validation error
if (!function_exists('encryptId')) {
    function encryptId($id)
    {
        $key = "drug-catalog-secret-key";
        return strtr(base64_encode($id . '|' . $key), '+/', '-_');
    }
}
$redirect_code_param = urlencode(encryptId($original_code));

// 5. Validation Check
if (
    empty($original_code) ||
    empty($drug_code) ||
    empty($drug_name) ||
    empty($generic_name) ||
    empty($category) ||
    empty($strength) ||
    $quantity_raw === '' ||
    empty($dosage_form)
) {
    header("Location: edit_drug.php?code={$redirect_code_param}&error=" . urlencode("All fields are required to update the asset."));
    exit();
}

// Data Type Validation for Quantity
if (!is_numeric($quantity_raw) || (int)$quantity_raw < 0) {
    header("Location: edit_drug.php?code={$redirect_code_param}&error=" . urlencode("Quantity must be a valid non-negative integer."));
    exit();
}
$quantity = (int)$quantity_raw;

try {
    // 6. Check for duplicate drug_code if the SKU/code is being changed
    if ($original_code !== $drug_code) {
        $check_stmt = $conn->prepare("SELECT drug_code FROM drugs_master WHERE drug_code = ? LIMIT 1");
        if (!$check_stmt) {
            header("Location: edit_drug.php?code={$redirect_code_param}&error=stmt_compilation_failed");
            exit();
        }

        $check_stmt->bind_param("s", $drug_code);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result && $check_result->num_rows > 0) {
            $check_stmt->close();
            header("Location: edit_drug.php?code={$redirect_code_param}&error=" . urlencode("The Item Code/SKU '{$drug_code}' is already assigned to another drug."));
            exit();
        }
        $check_stmt->close();
    }

    // 7. Update Master Drug Definition
    $update_stmt = $conn->prepare("UPDATE drugs_master SET drug_code = ?, drug_name = ?, generic_name = ?, category = ?, strength = ?, quantity = ?, dosage_form = ? WHERE drug_code = ?");
    if (!$update_stmt) {
        header("Location: edit_drug.php?code={$redirect_code_param}&error=stmt_compilation_failed");
        exit();
    }

    // Bind parameters match: s = string, i = integer
    $update_stmt->bind_param("sssssiss", $drug_code, $drug_name, $generic_name, $category, $strength, $quantity, $dosage_form, $original_code);

    if ($update_stmt->execute()) {
        $update_stmt->close();
        $success_msg = "Successfully updated drug definition for <strong>" . htmlspecialchars($drug_name) . "</strong>.";
        header("Location: manage_drugs.php?status=success&msg=" . urlencode($success_msg));
        exit();
    } else {
        $update_stmt->close();
        header("Location: edit_drug.php?code={$redirect_code_param}&error=" . urlencode("Failed to update database record."));
        exit();
    }
} catch (Exception $e) {
    header("Location: edit_drug.php?code={$redirect_code_param}&error=" . urlencode("System Exception: " . $e->getMessage()));
    exit();
}
