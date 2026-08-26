<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

// Include database connection
/** @var mysqli $conn */
include('db.php');

// Decrypt ID function
if (!function_exists('decryptId')) {
    function decryptId($hash)
    {
        $key = "medical-secret-key";
        $decoded = base64_decode(strtr($hash, '-_', '+/'));
        $parts = explode('|', $decoded);

        if (count($parts) !== 2 || $parts[1] !== $key) {
            return false;
        }

        return $parts[0];
    }
}

// Get and decrypt ID from query string
$encrypted_id = $_GET['id'] ?? '';
$outreach_id = decryptId($encrypted_id);

if (!$outreach_id || !is_numeric($outreach_id)) {
    $_SESSION['msg'] = "Invalid or tampered outreach record identifier.";
    $_SESSION['msg_type'] = "danger";
    header("Location: outreach.php");
    exit();
}

// Delete record using a prepared statement
$stmt = mysqli_prepare($conn, "DELETE FROM outreach WHERE id = ? LIMIT 1");

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $outreach_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['msg'] = "Outreach record successfully deleted.";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "Database error: Could not delete the outreach record.";
        $_SESSION['msg_type'] = "danger";
    }

    mysqli_stmt_close($stmt);
} else {
    $_SESSION['msg'] = "Database query preparation failed.";
    $_SESSION['msg_type'] = "danger";
}

mysqli_close($conn);

// Redirect back to the main outreach listing page
header("Location: outreach.php");
exit();
