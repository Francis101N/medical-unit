<?php
session_start();
/** @var mysqli $conn */
include('db.php');

$conn = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Reversible OpenSSL Decryption Helper for passwords
if (!function_exists('decryptPassword')) {
    function decryptPassword($data)
    {
        if (empty($data)) {
            return '';
        }
        $encryption_key = 'techbyfrancis1972$';
        $cipher = "AES-128-CBC";
        if (strpos($data, '::') === false) {
            return $data;
        }
        list($encrypted_data, $iv) = explode('::', $data, 2);
        $decrypted = openssl_decrypt($encrypted_data, $cipher, $encryption_key, 0, base64_decode($iv));
        return $decrypted !== false ? $decrypted : $data;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize & validate inputs
    $username = trim($_POST['username'] ?? '');
    $input_password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($input_password)) {
        $msg = "All fields are required";
        $msg_type = "danger";
        include 'auth-login.php';
        exit();
    }

    // Prepare statement (secure against SQL injection) - Added passport
    $stmt = $conn->prepare("SELECT id, fullname, username, password, email, role, branch, passport FROM users WHERE username = ? LIMIT 1");

    if (!$stmt) {
        die("Database error");
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {

        $user = $result->fetch_assoc();
        $stored_password = $user['password'];

        $login_successful = false;

        // 1. Check if the stored password uses standard PHP hashing (password_hash)
        if (password_verify($input_password, $stored_password)) {
            $login_successful = true;
        } else {
            // 2. Fallback check: Test against OpenSSL reversible encryption format
            $decrypted_password = decryptPassword($stored_password);
            if ($decrypted_password !== '' && hash_equals($decrypted_password, $input_password)) {
                $login_successful = true;
            }
        }

        if ($login_successful) {

            // Regenerate session ID (prevents session fixation)
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['branch'] = $user['branch'];
            $_SESSION['passport'] = $user['passport']; // Added passport to session

            // Secure "remember me"
            if (!empty($_POST['remember'])) {
                setcookie(
                    "user",
                    $user['username'],
                    [
                        'expires' => time() + (86400 * 30),
                        'path' => '/',
                        'secure' => true,     // only HTTPS
                        'httponly' => true,   // not accessible via JS
                        'samesite' => 'Strict'
                    ]
                );
            }

            header("Location: index.php");
            exit();
        } else {
            $msg = "Invalid login credentials";
            $msg_type = "danger";
            include 'auth-login.php';
            exit();
        }
    } else {
        $msg = "Invalid login credentials";
        $msg_type = "danger";
        include 'auth-login.php';
        exit();
    }

    $stmt->close();
    $conn->close();
}
