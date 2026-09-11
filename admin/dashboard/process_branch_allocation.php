<?php

/**
 * Process Branch Stock Allocation Script
 */
ini_set('display_errors', 1);
/** @var mysqli $conn */
include('db.php');
session_start();

// Enforce Role-Based Privilege Authentication Controls
$user_role = strtolower($_SESSION['role'] ?? '');
if ($user_role !== 'super-admin') {
    $_SESSION['msg'] = "Access denied: Administrative clearance parameters missing.";
    $_SESSION['msg_type'] = "danger";
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['allocate_stock'])) {
    // Retrieve and sanitize inputs
    $item_id = intval($_POST['item_id'] ?? 0);
    $branch_name = trim($_POST['branch_name'] ?? '');
    $allocated_quantity = intval($_POST['allocated_quantity'] ?? 0);
    $allocated_by = $_SESSION['username'] ?? $_SESSION['user_name'] ?? 'Super Admin';

    // Basic validation checks
    if ($item_id <= 0 || empty($branch_name) || $allocated_quantity <= 0) {
        $_SESSION['msg'] = "Invalid input parameters provided for allocation.";
        $_SESSION['msg_type'] = "danger";
        header("Location: outreach_allocate_stock.php");
        exit();
    }

    // Begin database transaction to ensure atomicity
    $conn->begin_transaction();

    try {
        // 1. Lock and check current inventory levels
        $stmt_check = $conn->prepare("SELECT total_quantity, item_name FROM outreach_inventory WHERE id = ? FOR UPDATE");
        $stmt_check->bind_param("i", $item_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Selected inventory asset could not be located in the central database.");
        }

        $row = $result->fetch_assoc();
        $current_total = intval($row['total_quantity']);
        $item_name = $row['item_name'];
        $stmt_check->close();

        // Verify sufficient stock availability
        if ($allocated_quantity > $current_total) {
            throw new Exception("Allocation volume exceeds available central stock for {$item_name} (Available: {$current_total}).");
        }

        // 2. Deduct quantity from central outreach_inventory
        $new_total = $current_total - $allocated_quantity;
        $stmt_update = $conn->prepare("UPDATE outreach_inventory SET total_quantity = ? WHERE id = ?");
        $stmt_update->bind_param("ii", $new_total, $item_id);
        if (!$stmt_update->execute()) {
            throw new Exception("Failed to update central inventory levels.");
        }
        $stmt_update->close();

        // 3. Record log inside branch_allocations audit table
        $stmt_log = $conn->prepare("INSERT INTO branch_allocations (item_id, branch_name, allocated_quantity, allocated_by, allocation_date) VALUES (?, ?, ?, ?, NOW())");
        $stmt_log->bind_param("isis", $item_id, $branch_name, $allocated_quantity, $allocated_by);
        if (!$stmt_log->execute()) {
            throw new Exception("Failed to record transaction log in the audit trail.");
        }
        $stmt_log->close();

        // 4. Update or Insert into branch_stock_balance for live location tracking
        $stmt_balance = $conn->prepare("SELECT id, 	quantity_remaining FROM branch_stock_balance WHERE item_id = ? AND branch_name = ? FOR UPDATE");
        $stmt_balance->bind_param("is", $item_id, $branch_name);
        $stmt_balance->execute();
        $balance_res = $stmt_balance->get_result();

        if ($balance_res->num_rows > 0) {
            $bal_row = $balance_res->fetch_assoc();
            $bal_id = $bal_row['id'];
            $stmt_balance->close();

            $stmt_bal_update = $conn->prepare("UPDATE branch_stock_balance SET quantity_remaining = quantity_remaining + ? WHERE id = ?");
            $stmt_bal_update->bind_param("ii", $allocated_quantity, $bal_id);
            if (!$stmt_bal_update->execute()) {
                throw new Exception("Failed to update branch stock balance ledger.");
            }
            $stmt_bal_update->close();
        } else {
            $stmt_balance->close();

            $stmt_bal_insert = $conn->prepare("INSERT INTO branch_stock_balance (item_id, branch_name, quantity_remaining) VALUES (?, ?, ?)");
            $stmt_bal_insert->bind_param("isi", $item_id, $branch_name, $allocated_quantity);
            if (!$stmt_bal_insert->execute()) {
                throw new Exception("Failed to initialize branch stock balance entry.");
            }
            $stmt_bal_insert->close();
        }

        // Commit transaction
        $conn->commit();

        $_SESSION['msg'] = "Successfully allocated " . number_format($allocated_quantity) . " units of " . htmlspecialchars($item_name) . " to " . htmlspecialchars($branch_name) . ".";
        $_SESSION['msg_type'] = "success";
        header("Location: outreach_allocate_stock.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();

        $_SESSION['msg'] = "Allocation transaction failed: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
        header("Location: outreach_allocate_stock.php");
        exit();
    }
} else {
    header("Location: outreach_allocate_stock.php");
    exit();
}
