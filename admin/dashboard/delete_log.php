<?php
// 1. Temporary Error Debugger (Turn off in final production environment)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/** @var mysqli $conn */
include('./db.php');

// Secure ID decryption helper function matching the layout engine
function decryptId($encoded_id)
{
    $key = "medical-secret-key";
    $decoded = base64_decode(strtr($encoded_id, '-_', '+/'));
    if ($decoded !== false && strpos($decoded, '|' . $key) !== false) {
        return str_replace('|' . $key, '', $decoded);
    }
    return false;
}

// 2. Query String Verification
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: medical-records.php?error=invalid_id");
    exit();
}

$encrypted_id = $_GET['id'];
$id = decryptId($encrypted_id);

// If token decryption fails, stop execution and route error back
if ($id === false || intval($id) <= 0) {
    header("Location: medical-records.php?error=invalid_id");
    exit();
}

$id = intval($id);

// 3. Prepared Statement Deletion Strategy
$query = "DELETE FROM staff_medical_records WHERE id = ?";
$stmt = $conn->prepare($query);

if ($stmt) {
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Track whether an actual record row was found and removed
        if ($stmt->affected_rows > 0) {
            $stmt->close();
            header("Location: medical-records.php?status=success&msg=" . urlencode("Medical log record dropped successfully."));
            exit();
        } else {
            $stmt->close();
            header("Location: medical-records.php?status=error&error=invalid_id");
            exit();
        }
    } else {
        $error_msg = $stmt->error;
        $stmt->close();
        header("Location: medical-records.php?status=error&msg=" . urlencode("Execution failed: " . $error_msg));
        exit();
    }
} else {
    header("Location: medical-records.php?status=error&error=stmt_compilation_failed");
    exit();
}
