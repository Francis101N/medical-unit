<?php
session_start();
$host = "localhost";
$user = "medical-unit";
$password = "Medical--Unit2026$$";
$database = "medical-unit";

$conn = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize & validate inputs
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');


    if (empty($username) || empty($password)) {
        $msg = "All fields are required";
        $msg_type = "danger";
        include 'auth-login.php';
        exit();
    }

    // Prepare statement (secure against SQL injection)
    $stmt = $conn->prepare("SELECT id,fullname, username, password , email, role, branch FROM users WHERE username = ? LIMIT 1");

    if (!$stmt) {
        die("Database error");
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {

        $user = $result->fetch_assoc();

        // Verify hashed password
        if (password_verify($password, $user['password'])) {

            // Regenerate session ID (prevents session fixation)
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['branch'] = $user['branch'];

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
