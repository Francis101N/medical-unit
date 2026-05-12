<?php
session_start();
/** @var mysqli $conn */
include('db.php');

if (isset($_POST['submit'])) {
    // =========================
    // GET ID
    // =========================
    $id = $_POST['id'];

    // =========================
    // COLLECT DATA
    // =========================
    $staff_id                = mysqli_real_escape_string($conn, $_POST['staff_id']);
    $fullname                = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email                   = mysqli_real_escape_string($conn, $_POST['email']);
    $phone                   = mysqli_real_escape_string($conn, $_POST['phone']);
    $gender                  = mysqli_real_escape_string($conn, $_POST['gender']);
    $dob                     = mysqli_real_escape_string($conn, $_POST['dob']);
    $branch_id               = mysqli_real_escape_string($conn, $_POST['branch_id']);
    $department              = mysqli_real_escape_string($conn, $_POST['department']);
    $role                    = mysqli_real_escape_string($conn, $_POST['role']);
    $employment_type         = mysqli_real_escape_string($conn, $_POST['employment_type']);
    $hire_date               = mysqli_real_escape_string($conn, $_POST['hire_date']);
    $status                  = mysqli_real_escape_string($conn, $_POST['status']);

    // =========================
    // GET EXISTING STAFF
    // =========================
    $get_old = mysqli_query($conn, "SELECT passport FROM staffs WHERE id='$id'");
    $old_data = mysqli_fetch_assoc($get_old);
    $old_passport = $old_data['passport'];

    // =========================
    // PASSPORT UPLOAD
    // =========================
    $passport = $old_passport;

    if (!empty($_FILES['passport']['name'])) {
        $file_name = $_FILES['passport']['name'];
        $file_tmp  = $_FILES['passport']['tmp_name'];

        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_name = time() . '_' . rand(1000, 9999) . '.' . $ext;

        move_uploaded_file($file_tmp, "uploads/" . $new_name);

        $passport = $new_name;
    }

    // =========================
    // CHECK EMAIL DUPLICATE
    // =========================
    $check_email = mysqli_query($conn, "SELECT * FROM staffs WHERE email='$email' AND id != '$id'");

    if (mysqli_num_rows($check_email) > 0) {
        $_SESSION['error'] = "Email already exists";
        header("Location: edit_staff.php?id=$id");
        exit();
    }

    // =========================
    // UPDATE QUERY
    // =========================
    $update = mysqli_query($conn, "
        UPDATE staffs SET
            staff_id='$staff_id',
            fullname='$fullname',
            email='$email',
            phone='$phone',
            gender='$gender',
            dob='$dob',
            passport='$passport',
            branch_id='$branch_id',
            department='$department',
            role='$role',
            employment_type='$employment_type',
            hire_date='$hire_date',
            status='$status'
        WHERE id='$id'
    ");

    // =========================
    // RESPONSE
    // =========================
    if ($update) {
        $_SESSION['success'] = "Staff updated successfully";
        header("Location: staffs.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update staff";
        header("Location: edit_staff.php?id=$id");
        exit();
    }
}
