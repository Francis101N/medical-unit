<?php
session_start();

$conn = mysqli_connect("localhost", "medical-unit", "Medical--Unit2026$$", "medical-unit");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['submit'])) {

    // Sanitize inputs
    $branch_name = trim($_POST['branch_name']);
    $state = trim($_POST['state']);
    $medical_head = trim($_POST['medical_head']);
    $medical_head_email = trim($_POST['medical_head_email']);
    $type = trim($_POST['type']);

    // Validate empty fields
    if (empty($branch_name) || empty($state) || empty($medical_head) || empty($medical_head_email)) {
        $msg = "All fields are required";
        $msg_type = "danger";
        include 'add_branch.php';
        exit();
    }

    // =========================
    // HANDLE PASSPORT UPLOAD
    // =========================
    $passport = $_FILES['passport']['name'];
    $passport_tmp = $_FILES['passport']['tmp_name'];
    $passport_size = $_FILES['passport']['size'];

    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

    $file_ext = strtolower(pathinfo($passport, PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_ext)) {
        $msg = "Invalid file type. Only JPG, JPEG, PNG, WEBP allowed.";
        $msg_type = "danger";
        include 'add_branch.php';
        exit();
    }

    if ($passport_size > 2 * 1024 * 1024) {
        $msg = "File too large. Max 2MB allowed.";
        $msg_type = "danger";
        include 'add_branch.php';
        exit();
    }

    // Create unique file name
    $new_passport_name = uniqid("passport_", true) . "." . $file_ext;

    $upload_dir = "uploads/";
    $upload_path = $upload_dir . $new_passport_name;

    // Move file
    if (!move_uploaded_file($passport_tmp, $upload_path)) {
        $msg = "Failed to upload passport image";
        $msg_type = "danger";
        include 'add_branch.php';
        exit();
    }

    // =========================
    // INSERT INTO DATABASE
    // =========================
    $stmt = $conn->prepare("INSERT INTO branches 
        (branch_name, state, medical_head, medical_head_email, type, medical_head_passport, date_created) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())");

    $stmt->bind_param(
        "ssssss",
        $branch_name,
        $state,
        $medical_head,
        $medical_head_email,
        $type,
        $new_passport_name
    );

    if ($stmt->execute()) {
        echo "<script>
            alert('Branch added successfully');
            window.location.href = 'branches.php';
        </script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
