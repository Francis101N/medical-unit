<?php
session_start();
/** @var mysqli $conn */
include('db.php');

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

if (isset($_POST['submit'])) {

    // Sanitize and trim inputs
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $raw_password = trim($_POST['password'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Handle role
    $role = trim($_POST['role'] ?? '');
    $allowed_roles = ['super-admin', 'staff', 'adhoc-staff', 'adhoc-user'];
    if (!in_array($role, $allowed_roles)) {
        $role = 'staff'; // Fallback default role
    }

    // Determine deployment value (saves to the single 'branch' field for both standard and ad-hoc roles)
    $branch = '';

    if ($role === 'adhoc-staff' || $role === 'adhoc-user') {
        $branch = trim($_POST['outreach_location'] ?? '');
    } else {
        $branch = trim($_POST['branch'] ?? '');
    }

    // Validate required fields
    if (empty($fullname) || empty($username) || empty($raw_password) || empty($email) || empty($role)) {
        $_SESSION['msg'] = "All required fields must be filled out.";
        $_SESSION['msg_type'] = "danger";
        header("Location: add_user.php");
        exit();
    }

    if (empty($branch)) {
        $deployment_type = ($role === 'adhoc-staff' || $role === 'adhoc-user') ? "outreach location" : "branch deployment";
        $_SESSION['msg'] = "Please select a valid " . $deployment_type . ".";
        $_SESSION['msg_type'] = "danger";
        header("Location: add_user.php");
        exit();
    }

    // Check if username already exists
    $check_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? LIMIT 1");
    mysqli_stmt_bind_param($check_stmt, "s", $username);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        mysqli_stmt_close($check_stmt);
        $_SESSION['msg'] = "The username '<strong>" . htmlspecialchars($username) . "</strong>' is already taken. Please choose another.";
        $_SESSION['msg_type'] = "danger";
        header("Location: add_user.php");
        exit();
    }
    mysqli_stmt_close($check_stmt);

    // Encrypt password using your OpenSSL reversible cipher
    $encryption_key = 'techbyfrancis1972$';
    $cipher = "AES-128-CBC";
    $iv_length = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($iv_length);
    $encrypted_password = openssl_encrypt($raw_password, $cipher, $encryption_key, 0, $iv);
    $final_password = $encrypted_password . '::' . base64_encode($iv);

    // Handle Passport Image Upload (Optional)
    $passport_filename = '';
    if (isset($_FILES['passport']) && $_FILES['passport']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['passport']['tmp_name'];
        $file_name = $_FILES['passport']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_ext, $allowed_extensions)) {
            $passport_filename = 'user_' . time() . '_' . mt_rand(1000, 9999) . '.' . $file_ext;
            $upload_dir = 'uploads/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            move_uploaded_file($file_tmp, $upload_dir . $passport_filename);
        }
    }

    // Insert user into database mapping branch/outreach to the `branch` column
    $insert_stmt = mysqli_prepare($conn, "INSERT INTO users (fullname, username, password, email, role, branch, passport) VALUES (?, ?, ?, ?, ?, ?, ?)");

    if ($insert_stmt) {
        mysqli_stmt_bind_param($insert_stmt, "sssssss", $fullname, $username, $final_password, $email, $role, $branch, $passport_filename);

        if (mysqli_stmt_execute($insert_stmt)) {
            $_SESSION['msg'] = "New user account successfully created!";
            $_SESSION['msg_type'] = "success";
            header("Location: users.php");
            exit();
        } else {
            $_SESSION['msg'] = "Database error: Could not save user record.";
            $_SESSION['msg_type'] = "danger";
            header("Location: add_user.php");
            exit();
        }
        mysqli_stmt_close($insert_stmt);
    } else {
        $_SESSION['msg'] = "Database query preparation failed.";
        $_SESSION['msg_type'] = "danger";
        header("Location: add_user.php");
        exit();
    }

    mysqli_close($conn);
} else {
    header("Location: add_user.php");
    exit();
}
