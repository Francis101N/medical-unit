<?php
session_start();
/** @var mysqli $conn */
include('./db.php');

if (isset($_POST['submit'])) {

    // =========================
    // COLLECT FORM DATA
    // =========================

    $staff_id                  = mysqli_real_escape_string($conn, $_POST['staff_id']);
    $fullname                  = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email                     = mysqli_real_escape_string($conn, $_POST['email']);
    $phone                     = mysqli_real_escape_string($conn, $_POST['phone']);
    $gender                    = mysqli_real_escape_string($conn, $_POST['gender']);
    $dob                       = mysqli_real_escape_string($conn, $_POST['dob']);
    $branch_id                 = mysqli_real_escape_string($conn, $_POST['branch_id']);
    $company                   = mysqli_real_escape_string($conn, $_POST['company']);
    $department                = mysqli_real_escape_string($conn, $_POST['department']);
    $role                      = mysqli_real_escape_string($conn, $_POST['role']);
    $employment_type           = mysqli_real_escape_string($conn, $_POST['employment_type']);
    $hire_date                 = mysqli_real_escape_string($conn, $_POST['hire_date']);
    $status                    = mysqli_real_escape_string($conn, $_POST['status']);
    $address                   = mysqli_real_escape_string($conn, $_POST['address']);
    $next_of_kin               = mysqli_real_escape_string($conn, $_POST['next_of_kin']);
    $next_of_kin_phone         = mysqli_real_escape_string($conn, $_POST['next_of_kin_phone']);
    $blood_group               = mysqli_real_escape_string($conn, $_POST['blood_group']);
    $genotype                  = mysqli_real_escape_string($conn, $_POST['genotype']);
    $last_medical_checkup      = mysqli_real_escape_string($conn, $_POST['last_medical_checkup']);
    $allergies                 = mysqli_real_escape_string($conn, $_POST['allergies']);
    $medical_conditions        = mysqli_real_escape_string($conn, $_POST['medical_conditions']);
    $emergency_contact_name    = mysqli_real_escape_string($conn, $_POST['emergency_contact_name']);
    $emergency_contact_phone   = mysqli_real_escape_string($conn, $_POST['emergency_contact_phone']);
    $fitness_status            = mysqli_real_escape_string($conn, $_POST['fitness_status']);



    // =========================
    // PASSPORT UPLOAD
    // =========================

    $passport = '';

    if (!empty($_FILES['passport']['name'])) {

        $passport_name = $_FILES['passport']['name'];
        $passport_tmp  = $_FILES['passport']['tmp_name'];

        $extension = pathinfo($passport_name, PATHINFO_EXTENSION);

        $new_passport_name = time() . '_' . rand(1000, 9999) . '.' . $extension;

        $upload_path = "uploads/" . $new_passport_name;

        move_uploaded_file($passport_tmp, $upload_path);

        $passport = $new_passport_name;
    }



    // =========================
    // CHECK STAFF ID
    // =========================

    $check_staff_id = mysqli_query($conn, "SELECT * FROM staffs WHERE staff_id='$staff_id'");

    if (mysqli_num_rows($check_staff_id) > 0) {
        $msg = "Staff ID already exists";
        $msg_type = "danger";
        include 'add_staff.php';
        exit();
    }



    // =========================
    // CHECK EMAIL
    // =========================

    $check_email = mysqli_query($conn, "SELECT * FROM staffs WHERE email='$email'");

    if (mysqli_num_rows($check_email) > 0) {
        $msg = "Email already exists";
        $msg_type = "danger";
        include 'add_staff.php';
        exit();
    }



    // =========================
    // INSERT INTO DATABASE
    // =========================

    $insert = mysqli_query($conn, "INSERT INTO staffs
    (
        staff_id,
        fullname,
        email,
        phone,
        gender,
        dob,
        passport,
        branch_id,
        company,
        department,
        role,
        employment_type,
        hire_date,
        status,
        address,
        next_of_kin,
        next_of_kin_phone,
        blood_group,
        genotype,
        last_medical_checkup,
        allergies,
        medical_conditions,
        emergency_contact_name,
        emergency_contact_phone,
        fitness_status
    )

    VALUES
    (
        '$staff_id',
        '$fullname',
        '$email',
        '$phone',
        '$gender',
        '$dob',
        '$passport',
        '$branch_id',
        '$company',
        '$department',
        '$role',
        '$employment_type',
        '$hire_date',
        '$status',
        '$address',
        '$next_of_kin',
        '$next_of_kin_phone',
        '$blood_group',
        '$genotype',
        '$last_medical_checkup',
        '$allergies',
        '$medical_conditions',
        '$emergency_contact_name',
        '$emergency_contact_phone',
        '$fitness_status'
    )");



    // =========================
    // SUCCESS / ERROR
    // =========================

    if ($insert) {
        $msg = "Staff added successfully";
        $msg_type = "success";
        include 'add_staff.php';
        exit();
    } else {
        $msg = "Something went wrong";
        $msg_type = "danger";
        include 'add_staff.php';
        exit();
    }
}
