<?php
session_start();
/** @var mysqli $conn */
include('./db.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

// Helper function to decode the encrypted ID token
function decryptId($token)
{
    $key = "medical-secret-key";
    $decoded = base64_decode(strtr($token, '-_', '+/'));
    if ($decoded === false || !str_contains($decoded, '|')) {
        return false;
    }
    list($id, $secret) = explode('|', $decoded, 2);
    return ($secret === $key) ? $id : false;
}

// Reversible OpenSSL Encryption Helper for passwords
function encryptPassword($data)
{
    if (empty($data)) {
        return '';
    }
    $encryption_key = 'techbyfrancis1972$'; 
    $cipher = "AES-128-CBC";
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $encrypted = openssl_encrypt($data, $cipher, $encryption_key, 0, $iv);
    return $encrypted . '::' . base64_encode($iv);
}

if (isset($_POST['update_user'])) {
    $encrypted_id = $_POST['id'] ?? '';
    $id = decryptId($encrypted_id);

    if ($id === false) {
        $_SESSION['msg'] = "Invalid or tampered record reference.";
        $_SESSION['msg_type'] = "danger";
        header("Location: users.php");
        exit();
    }

    // Sanitize and collect form inputs
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $raw_password = $_POST['password'];
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $branch = trim($_POST['branch']);

    // Encrypt password reversibly
    $encrypted_password = encryptPassword($raw_password);

    // Fetch existing user record to check for old passport file management
    $stmt_old = mysqli_prepare($conn, "SELECT passport FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt_old, "i", $id);
    mysqli_stmt_execute($stmt_old);
    $old_result = mysqli_stmt_get_result($stmt_old);
    $old_data = mysqli_fetch_assoc($old_result);
    $stmt_old->close();

    $passport_filename = $old_data['passport'] ?? '';

    // Handle Passport Image Upload if a new file was provided
    if (isset($_FILES['passport']) && $_FILES['passport']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['passport']['tmp_name'];
        $file_name = $_FILES['passport']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($file_ext, $allowed_extensions)) {
            $new_filename = "user_" . $id . "_" . time() . "." . $file_ext;
            $upload_dir = "uploads/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($file_tmp, $upload_dir . $new_filename)) {
                // Delete old passport file if it exists
                if (!empty($old_data['passport']) && file_exists($upload_dir . $old_data['passport'])) {
                    @unlink($upload_dir . $old_data['passport']);
                }
                $passport_filename = $new_filename;
            }
        }
    }

    // Update database record using prepared statement
    $sql = "UPDATE users SET fullname = ?, username = ?, password = ?, email = ?, role = ?, branch = ?, passport = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssssi", $fullname, $username, $encrypted_password, $email, $role, $branch, $passport_filename, $id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['msg'] = "User profile updated successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "Error updating record: " . mysqli_error($conn);
        $_SESSION['msg_type'] = "danger";
    }

    mysqli_stmt_close($stmt);
    
    // Redirect back to the edit page with the encrypted token
    header("Location: users.php?id=" . urlencode($encrypted_id));
    exit();
} else {
    header("Location: users.php");
    exit();
}