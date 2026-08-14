<?php

/**
 * Safe Stock Autodepletion Engine
 * Deducts specified units from the local branch balance safely.
 */
function consumeBranchStock($conn, $branch_id, $drug_id, $quantity, $staff_id, $log_notes = '')
{
    // 1. Check availability status first to prevent underflow balance loops
    $check = $conn->prepare("SELECT current_balance FROM drugs_allocations WHERE branch_id = ? AND drug_id = ?");
    $check->bind_param("ii", $branch_id, $drug_id);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();
    $check->close();

    if (!$res || $res['current_balance'] < $quantity) {
        return false; // Insufficient items configuration state
    }

    // 2. Process physical balance reduction matrix adjustments
    $update = $conn->prepare("
        UPDATE drugs_allocations 
        SET current_balance = current_balance - ? 
        WHERE branch_id = ? AND drug_id = ? AND current_balance >= ?
    ");
    $update->bind_param("iiii", $quantity, $branch_id, $drug_id, $quantity);
    $update->execute();
    $affected = $update->affected_rows;
    $update->close();

    if ($affected > 0) {
        // 3. Document operations execution footprint onto audit trails
        $final_notes = !empty($log_notes) ? $log_notes : "Auto-deduction for clinical consumption.";
        $neg_qty = -$quantity;

        $log = $conn->prepare("
            INSERT INTO drugs_stock_logs (branch_id, drug_id, transaction_type, quantity, processed_by, notes)
            VALUES (?, ?, 'dispensed', ?, ?, ?)
        ");
        $log->bind_param("iiiis", $branch_id, $drug_id, $neg_qty, $staff_id, $final_notes);
        $log->execute();
        $log->close();

        return true;
    }

    return false;
}
