<?php

/**
 * Premium Stock Allocation Transaction Processor
 * File: process_allocation.php
 */

// TEMPORARY DEBUGGING
error_reporting(E_ALL);
ini_set('display_errors', 1);

/** @var mysqli $conn */
include('db.php');

// 1. Session and Authorization Security Check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_role = strtolower($_SESSION['role'] ?? '');
if ($user_role !== 'super-admin') {
    header("Location: allocate_stock.php?error=" . urlencode("Unauthorized access clearance required."));
    exit();
}

// 2. Validate Request Entry Point
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: allocate_stock.php?error=" . urlencode("Invalid request method strategy."));
    exit();
}

// 3. Extract and Parse Payload Inputs
$drug_id    = isset($_POST['drug_id']) ? intval($_POST['drug_id']) : 0;
$branch_id  = isset($_POST['branch_id']) ? intval($_POST['branch_id']) : 0;
$quantity   = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

// Basic completeness check
if ($drug_id <= 0 || $branch_id <= 0 || $quantity <= 0) {
    header("Location: allocate_stock.php?error=" . urlencode("All allocation spec fields are required and must be valid numeric values."));
    exit();
}

// Capture Admin Full Name String from Session
$processed_by = $_SESSION['fullname'] ?? $_SESSION['name'] ?? $_SESSION['username'] ?? 'Super Admin';

// 4. Begin ACID Transaction Sequence
$conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

try {
    // A. Fetch current Master inventory state and lock row (FOR UPDATE) to prevent race conditions
    $master_stmt = $conn->prepare("SELECT drug_code, drug_name, strength, quantity FROM drugs_master WHERE id = ? FOR UPDATE");
    if (!$master_stmt) {
        throw new Exception("Master compilation state failed.");
    }

    $master_stmt->bind_param("i", $drug_id);
    $master_stmt->execute();
    $master_res = $master_stmt->get_result()->fetch_assoc();
    $master_stmt->close();

    if (!$master_res) {
        throw new Exception("The requested pharmaceutical asset does not exist inside the master catalog.");
    }

    $drug_name       = $master_res['drug_name'];
    $strength        = !empty($master_res['strength']) ? ' ' . $master_res['strength'] : '';
    $full_drug_title = $drug_name . $strength;
    $available_stock = intval($master_res['quantity']);

    // B. Business Rule Logic Validation: Guard against overallocation
    if ($quantity > $available_stock) {
        throw new Exception("Overallocation Error: Requested {$quantity} units, but only {$available_stock} are available in the master inventory.");
    }

    // C. Validate destination node/branch presence
    $branch_stmt = $conn->prepare("SELECT branch_name FROM branches WHERE id = ? LIMIT 1");
    if (!$branch_stmt) {
        throw new Exception("Branch validation tracking failure.");
    }
    $branch_stmt->bind_param("i", $branch_id);
    $branch_stmt->execute();
    $branch_res = $branch_stmt->get_result()->fetch_assoc();
    $branch_stmt->close();

    if (!$branch_res) {
        throw new Exception("Targeted node destination branch ID is invalid.");
    }
    $branch_name = $branch_res['branch_name'];

    // D. Deduct volume from Master Catalog Warehouse balance
    $deduct_stmt = $conn->prepare("UPDATE drugs_master SET quantity = quantity - ? WHERE id = ?");
    if (!$deduct_stmt) {
        throw new Exception("Failed to configure asset deduction mapping.");
    }
    $deduct_stmt->bind_param("ii", $quantity, $drug_id);
    $deduct_stmt->execute();
    $deduct_stmt->close();

    // E. Credit local destination vault balance using your custom drugs_allocations table
    $alloc_check = $conn->prepare("SELECT branch_id FROM drugs_allocations WHERE branch_id = ? AND drug_id = ? LIMIT 1 FOR UPDATE");
    if (!$alloc_check) {
        throw new Exception("Failed to establish secure localized ledger mapping.");
    }
    $alloc_check->bind_param("ii", $branch_id, $drug_id);
    $alloc_check->execute();
    $alloc_record = $alloc_check->get_result()->fetch_assoc();
    $alloc_check->close();

    if ($alloc_record) {
        // Row exists: Accumulate both allocated total historical tracking + current operational balance
        $alloc_update = $conn->prepare("UPDATE drugs_allocations SET allocated_qty = allocated_qty + ?, current_balance = current_balance + ?, last_allocated_at = NOW() WHERE branch_id = ? AND drug_id = ?");
        if (!$alloc_update) {
            throw new Exception("Failed to configure structural update parameters.");
        }
        $alloc_update->bind_param("iiii", $quantity, $quantity, $branch_id, $drug_id);
    } else {
        // Row does not exist: Initialize values from absolute zeros
        $alloc_update = $conn->prepare("INSERT INTO drugs_allocations (branch_id, drug_id, allocated_qty, current_balance, last_allocated_at) VALUES (?, ?, ?, ?, NOW())");
        if (!$alloc_update) {
            throw new Exception("Failed to configure structural insert parameters.");
        }
        $alloc_update->bind_param("iiii", $branch_id, $drug_id, $quantity, $quantity);
    }

    $alloc_update->execute();
    $alloc_update->close();

    // F. Append System Systemic Audit Footprint Log Trace with string processed_by
    $audit_note = "Dispatched " . number_format($quantity) . " units from Master Warehouse to " . $branch_name . ".";
    $log_stmt = $conn->prepare("INSERT INTO drugs_stock_logs (drug_id, branch_id, transaction_type, quantity, processed_by, notes, created_at) VALUES (?, ?, 'allocation', ?, ?, ?, NOW())");
    if (!$log_stmt) {
        throw new Exception("Failed to compile internal operational logger payload.");
    }

    // Bind parameters: 3 integers (drug_id, branch_id, quantity), 2 strings (processed_by, notes) -> "iiiss"
    $log_stmt->bind_param("iiiss", $drug_id, $branch_id, $quantity, $processed_by, $audit_note);
    $log_stmt->execute();
    $log_stmt->close();

    // Commit changes safely to permanent storage state
    $conn->commit();

    // Redirect with systemic success confirmation toast metadata
    $success_msg = "Successfully allocated <strong>" . number_format($quantity) . " units</strong> of <strong>" . htmlspecialchars($full_drug_title) . "</strong> to the <strong>" . htmlspecialchars($branch_name) . "</strong> node vault.";
    header("Location: allocate_stock.php?status=success&msg=" . urlencode($success_msg));
    exit();
} catch (Exception $e) {
    // Rollback changes to maintain data parity on error
    $conn->rollback();

    header("Location: allocate_stock.php?error=" . urlencode("Transaction Aborted: " . $e->getMessage()));
    exit();
}
