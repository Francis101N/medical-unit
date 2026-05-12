<?php
session_start();
/** @var mysqli $conn */
include('db.php');

// Check if ID is passed
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid request";
    header("Location: staffs.php");
    exit();
}

$id = $_GET['id'];

// =========================
// GET STAFF (for passport cleanup)
// =========================
$get_staff = mysqli_query($conn, "SELECT passport FROM staffs WHERE id='$id'");

if (mysqli_num_rows($get_staff) == 0) {
    $_SESSION['error'] = "Staff not found";
    header("Location: staffs.php");
    exit();
}

$staff = mysqli_fetch_assoc($get_staff);
$passport = $staff['passport'];


// =========================
// DELETE STAFF
// =========================
$delete = mysqli_query($conn, "DELETE FROM staffs WHERE id='$id'");

if ($delete) {
    // =========================
    // DELETE PASSPORT FILE
    // =========================
    if (!empty($passport) && file_exists("uploads/" . $passport)) {
        unlink("uploads/" . $passport);
    }

    $_SESSION['success'] = "Staff deleted successfully";
    header("Location: staffs.php");
    exit();
} else {
    $_SESSION['error'] = "Failed to delete staff";
    header("Location: staffs.php");
    exit();
}
