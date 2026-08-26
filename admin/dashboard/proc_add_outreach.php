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
    $project_title = trim($_POST['project_title'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');

    // Validate required fields (Only project title and location are strictly required)
    if (empty($project_title) || empty($location)) {
        $_SESSION['msg'] = "Project Title and Location are required fields.";
        $_SESSION['msg_type'] = "danger";
        header("Location: add_outreach.php");
        exit();
    }

    // Insert outreach record into database via prepared statement
    $insert_stmt = mysqli_prepare($conn, "INSERT INTO outreach (project_title, location, duration, start_date, end_date) VALUES (?, ?, ?, ?, ?)");

    if ($insert_stmt) {
        mysqli_stmt_bind_param($insert_stmt, "sssss", $project_title, $location, $duration, $start_date, $end_date);

        if (mysqli_stmt_execute($insert_stmt)) {
            $_SESSION['msg'] = "New outreach project successfully created!";
            $_SESSION['msg_type'] = "success";
            header("Location: outreach.php");
            exit();
        } else {
            $_SESSION['msg'] = "Database error: Could not save outreach record.";
            $_SESSION['msg_type'] = "danger";
            header("Location: add_outreach.php");
            exit();
        }
        mysqli_stmt_close($insert_stmt);
    } else {
        $_SESSION['msg'] = "Database query preparation failed: " . mysqli_error($conn);
        $_SESSION['msg_type'] = "danger";
        header("Location: add_outreach.php");
        exit();
    }

    mysqli_close($conn);
} else {
    header("Location: add_outreach.php");
    exit();
}
