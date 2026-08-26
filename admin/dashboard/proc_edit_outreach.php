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
    $outreach_id = intval($_POST['outreach_id'] ?? 0);
    $project_title = trim($_POST['project_title'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');

    // Validate required fields (Only project title and location are strictly required)
    if ($outreach_id <= 0 || empty($project_title) || empty($location)) {
        $_SESSION['msg'] = "Project Title and Location are required fields.";
        $_SESSION['msg_type'] = "danger";

        // Re-encrypt ID for safe redirect back to edit form
        $key = "medical-secret-key";
        $encrypted_id = rtrim(strtr(base64_encode($outreach_id . '|' . $key), '+/', '-_'), '=');
        header("Location: edit_outreach.php?id=" . urlencode($encrypted_id));
        exit();
    }

    // Update outreach record in database via prepared statement
    $update_stmt = mysqli_prepare($conn, "UPDATE outreach SET project_title = ?, location = ?, duration = ?, start_date = ?, end_date = ? WHERE id = ?");

    if ($update_stmt) {
        mysqli_stmt_bind_param($update_stmt, "sssssi", $project_title, $location, $duration, $start_date, $end_date, $outreach_id);

        if (mysqli_stmt_execute($update_stmt)) {
            $_SESSION['msg'] = "Outreach record successfully updated!";
            $_SESSION['msg_type'] = "success";
            header("Location: outreach.php");
            exit();
        } else {
            $_SESSION['msg'] = "Database error: Could not update outreach record.";
            $_SESSION['msg_type'] = "danger";

            // Re-encrypt ID for safe redirect back to edit form
            $key = "medical-secret-key";
            $encrypted_id = rtrim(strtr(base64_encode($outreach_id . '|' . $key), '+/', '-_'), '=');
            header("Location: edit_outreach.php?id=" . urlencode($encrypted_id));
            exit();
        }
        mysqli_stmt_close($update_stmt);
    } else {
        $_SESSION['msg'] = "Database query preparation failed: " . mysqli_error($conn);
        $_SESSION['msg_type'] = "danger";
        header("Location: outreach.php");
        exit();
    }

    mysqli_close($conn);
} else {
    header("Location: outreach.php");
    exit();
}
