<?php
session_start();

$conn = mysqli_connect("localhost", "medical-unit", "Medical--Unit2026$$", "medical-unit");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Ensure request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method");
}

// Get ID
$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    die("Invalid ID");
}

// Sanitize inputs
$branch_name = trim($_POST['branch_name'] ?? '');
$state = trim($_POST['state'] ?? '');
$medical_head = trim($_POST['medical_head'] ?? '');
$medical_head_email = trim($_POST['medical_head_email'] ?? '');
$type = trim($_POST['type'] ?? '');

if (empty($branch_name) || empty($state) || empty($medical_head) || empty($medical_head_email) || empty($type)) {
    die("All fields are required");
}

// --------------------------------------------------
// GET CURRENT PASSPORT (for fallback)
// --------------------------------------------------
$stmt = $conn->prepare("SELECT medical_head_passport FROM branches WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Branch not found");
}

$current = $result->fetch_assoc();
$current_passport = $current['medical_head_passport'];

// --------------------------------------------------
// HANDLE PASSPORT UPLOAD (OPTIONAL)
// --------------------------------------------------
$new_passport_name = $current_passport;

if (!empty($_FILES['passport']['name'])) {

    $passport = $_FILES['passport']['name'];
    $passport_tmp = $_FILES['passport']['tmp_name'];
    $passport_size = $_FILES['passport']['size'];

    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
    $file_ext = strtolower(pathinfo($passport, PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_ext)) {
        die("Invalid file type. Only JPG, JPEG, PNG, WEBP allowed.");
    }

    if ($passport_size > 2 * 1024 * 1024) {
        die("File too large. Max 2MB allowed.");
    }

    // Unique name
    $new_passport_name = uniqid("passport_", true) . "." . $file_ext;

    // IMPORTANT PATH FIX (your structure)
    $upload_dir = "uploads/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $upload_path = $upload_dir . $new_passport_name;

    if (!move_uploaded_file($passport_tmp, $upload_path)) {
        die("Failed to upload passport image");
    }

    // Optional: delete old file
    if (!empty($current_passport) && file_exists($upload_dir . $current_passport)) {
        unlink($upload_dir . $current_passport);
    }
}

// --------------------------------------------------
// UPDATE DATABASE
// --------------------------------------------------
$stmt = $conn->prepare("
    UPDATE branches 
    SET branch_name = ?, 
        state = ?, 
        medical_head = ?, 
        medical_head_email = ?, 
        type = ?, 
        medical_head_passport = ? 
    WHERE id = ?
");

$stmt->bind_param(
    "ssssssi",
    $branch_name,
    $state,
    $medical_head,
    $medical_head_email,
    $type,
    $new_passport_name,
    $id
);

if ($stmt->execute()) {

    echo "<script>
        alert('Branch updated successfully');
        window.location.href = 'branches.php';
    </script>";
} else {
    echo "Error updating branch: " . $stmt->error;
}

$stmt->close();
$conn->close();
