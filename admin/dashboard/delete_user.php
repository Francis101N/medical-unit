<?php
session_start();
/** @var mysqli $conn */
include('db.php');

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// 1. Security Check: Ensure user is logged in and is a super-admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'super-admin') {
    echo "
    <script>
        alert('Access Denied! Unauthorized action.');
        window.location.href='auth-login.php';
    </script>
    ";
    exit();
}

// 2. Check if the ID parameter exists in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {

    $encoded_param = trim($_GET['id']);

    // Reverse the URL-safe Base64 encoding used in encryptId()
    $base64_decoded = base64_decode(strtr($encoded_param, '-_', '+/'));

    // Validate the token structure (format: ID|medical-secret-key)
    if ($base64_decoded !== false && strpos($base64_decoded, '|') !== false) {
        list($target_id, $secret_key) = explode('|', $base64_decoded, 2);

        // Verify the secret key matches and the target ID is numeric
        if ($secret_key === 'medical-secret-key' && is_numeric($target_id)) {

            // Safeguard: Prevent super-admin from deleting their own currently logged-in account
            if ((int)$target_id === (int)$_SESSION['user_id']) {
                $_SESSION['msg'] = "Action forbidden: You cannot delete your own active administrator account.";
                $_SESSION['msg_type'] = "danger";
                header("Location: users.php");
                exit();
            }

            // Fetch the user's passport filename first to clean up the uploads folder
            $fetch_query = mysqli_prepare($conn, "SELECT passport FROM users WHERE id = ? LIMIT 1");
            mysqli_stmt_bind_param($fetch_query, "i", $target_id);
            mysqli_stmt_execute($fetch_query);
            $fetch_result = mysqli_stmt_get_result($fetch_query);

            if ($fetch_result && mysqli_num_rows($fetch_result) === 1) {
                $user_data = mysqli_fetch_assoc($fetch_result);
                $passport_file = $user_data['passport'];

                // Delete physical passport file if it exists in uploads/
                if (!empty($passport_file) && file_exists("uploads/" . $passport_file)) {
                    @unlink("uploads/" . $passport_file);
                }
            }
            mysqli_stmt_close($fetch_query);

            // Proceed to delete the user record from the database using a prepared statement
            $delete_stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ? LIMIT 1");

            if ($delete_stmt) {
                mysqli_stmt_bind_param($delete_stmt, "i", $target_id);

                if (mysqli_stmt_execute($delete_stmt)) {
                    $_SESSION['msg'] = "User account successfully deleted.";
                    $_SESSION['msg_type'] = "success";
                } else {
                    $_SESSION['msg'] = "Database error: Could not delete the user record.";
                    $_SESSION['msg_type'] = "danger";
                }
                mysqli_stmt_close($delete_stmt);
            } else {
                $_SESSION['msg'] = "Query preparation failed.";
                $_SESSION['msg_type'] = "danger";
            }
        } else {
            $_SESSION['msg'] = "Invalid security token signature.";
            $_SESSION['msg_type'] = "danger";
        }
    } else {
        $_SESSION['msg'] = "Malformed parameter format.";
        $_SESSION['msg_type'] = "danger";
    }
} else {
    $_SESSION['msg'] = "No user ID specified.";
    $_SESSION['msg_type'] = "danger";
}

mysqli_close($conn);
header("Location: users.php");
exit();
