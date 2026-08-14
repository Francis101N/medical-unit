<?php

/**
 * Master Drug Registration Processor
 * File: process_master_drug.php
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

// 4. Extract and Sanitize POST Data matching updated drugs_master schema
$drug_code    = trim($_POST['drug_code'] ?? '');
$drug_name    = trim($_POST['drug_name'] ?? '');
$generic_name = trim($_POST['generic_name'] ?? '');
$category     = trim($_POST['category'] ?? '');
$strength     = trim($_POST['strength'] ?? '');
$quantity_raw = trim($_POST['quantity'] ?? '');
$dosage_form  = trim($_POST['dosage_form'] ?? '');

// 5. Validation Check
if (
    empty($drug_code) || 
    empty($drug_name) || 
    empty($generic_name) || 
    empty($category) || 
    empty($strength) || 
    $quantity_raw === '' || 
    empty($dosage_form)
) {
    header("Location: manage_drugs.php?error=" . urlencode("All drug definition fields are required."));
    exit();
}

// Validate quantity as a non-negative integer
if (!is_numeric($quantity_raw) || (int)$quantity_raw < 0) {
    header("Location: manage_drugs.php?error=" . urlencode("Initial quantity must be a valid non-negative number."));
    exit();
}
$quantity = (int)$quantity_raw;

try {
    // 6. Check for Duplicate Item Code / SKU
    $check_stmt = $conn->prepare("SELECT drug_code FROM drugs_master WHERE drug_code = ? LIMIT 1");
    if (!$check_stmt) {
        header("Location: manage_drugs.php?error=stmt_compilation_failed");
        exit();
    }

    $check_stmt->bind_param("s", $drug_code);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result && $check_result->num_rows > 0) {
        $check_stmt->close();
        header("Location: manage_drugs.php?error=duplicate_code");
        exit();
    }
    $check_stmt->close();

    // 7. Insert New Master Drug Definition (7 Fields)
    $insert_stmt = $conn->prepare("INSERT INTO drugs_master (drug_code, drug_name, generic_name, category, strength, quantity, dosage_form) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$insert_stmt) {
        header("Location: manage_drugs.php?error=stmt_compilation_failed");
        exit();
    }

    // Types: s (drug_code), s (drug_name), s (generic_name), s (category), s (strength), i (quantity), s (dosage_form)
    $insert_stmt->bind_param("sssssis", $drug_code, $drug_name, $generic_name, $category, $strength, $quantity, $dosage_form);

    if ($insert_stmt->execute()) {
        $insert_stmt->close();
        $success_msg = "Successfully registered <strong>" . htmlspecialchars($drug_name) . "</strong> (" . htmlspecialchars($drug_code) . ") into the master catalog.";
        header("Location: manage_drugs.php?status=success&msg=" . urlencode($success_msg));
        exit();
    } else {
        $insert_stmt->close();
        header("Location: manage_drugs.php?error=update_failed");
        exit();
    }
} catch (Exception $e) {
    header("Location: manage_drugs.php?error=" . urlencode("System Exception: " . $e->getMessage()));
    exit();
}