<?php
session_start();
/** @var mysqli $conn */
include('./db.php');

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

// Handle Form Submission for Adding New Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $item_name = trim($_POST['item_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $total_quantity = intval($_POST['total_quantity'] ?? 0);

    if (!empty($item_name) && !empty($category) && !empty($unit) && $total_quantity >= 0) {

        // 1. Check if item already exists (case-insensitive)
        $check_stmt = $conn->prepare("SELECT id, total_quantity FROM outreach_inventory WHERE LOWER(item_name) = LOWER(?) LIMIT 1");
        $check_stmt->bind_param("s", $item_name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Item exists: Update/Add to existing quantity and refresh metadata
            $existing_row = $check_result->fetch_assoc();
            $existing_id = $existing_row['id'];
            $check_stmt->close();

            $update_stmt = $conn->prepare("UPDATE outreach_inventory SET total_quantity = total_quantity + ?, category = ?, unit = ? WHERE id = ?");
            $update_stmt->bind_param("issi", $total_quantity, $category, $unit, $existing_id);

            if ($update_stmt->execute()) {
                $_SESSION['msg'] = "Item already exists! Successfully added " . number_format($total_quantity) . " units to \"" . htmlspecialchars($item_name) . "\".";
                $_SESSION['msg_type'] = "success";
            } else {
                $_SESSION['msg'] = "Error updating item: " . $update_stmt->error;
                $_SESSION['msg_type'] = "danger";
            }
            $update_stmt->close();
        } else {
            // Item does not exist: Insert new row
            $check_stmt->close();

            $insert_stmt = $conn->prepare("INSERT INTO outreach_inventory (item_name, category, unit, total_quantity) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("sssi", $item_name, $category, $unit, $total_quantity);

            if ($insert_stmt->execute()) {
                $_SESSION['msg'] = "Outreach item successfully added to inventory!";
                $_SESSION['msg_type'] = "success";
            } else {
                $_SESSION['msg'] = "Error adding item: " . $insert_stmt->error;
                $_SESSION['msg_type'] = "danger";
            }
            $insert_stmt->close();
        }
    } else {
        $_SESSION['msg'] = "Please fill in all required fields and provide a non-negative quantity.";
        $_SESSION['msg_type'] = "danger";
    }

    header("Location: outreach_inventory.php");
    exit();
} else {
    header("Location: outreach_inventory.php");
    exit();
}
