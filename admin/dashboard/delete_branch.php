<?php
session_start();

$conn = mysqli_connect("localhost", "medical-unit", "Medical--Unit2026$$", "medical-unit");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

/* -----------------------------
   YOUR DECRYPT FUNCTION
------------------------------ */
function decryptId($hash)
{
    $key = "medical-secret-key";

    $decoded = base64_decode(strtr($hash, '-_', '+/'));

    $parts = explode('|', $decoded);

    if (count($parts) !== 2 || $parts[1] !== $key) {
        return false;
    }

    return $parts[0];
}

/* -----------------------------
   GET ENCRYPTED ID
------------------------------ */
$encrypted_id = $_GET['id'] ?? '';

$id = decryptId($encrypted_id);

if (!$id || !is_numeric($id)) {
    die("Invalid request");
}

$id = (int)$id;

/* -----------------------------
   GET PASSPORT BEFORE DELETE
------------------------------ */
$stmt = $conn->prepare("SELECT medical_head_passport FROM branches WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Branch not found");
}

$row = $result->fetch_assoc();
$passport = $row['medical_head_passport'];

/* -----------------------------
   DELETE RECORD
------------------------------ */
$stmt = $conn->prepare("DELETE FROM branches WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    // delete image file if exists
    if (!empty($passport)) {
        $file_path = "uploads/" . $passport;

        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    echo "<script>
        alert('Branch deleted successfully ..');
        window.location.href = 'branches.php';
    </script>";
} else {
    echo "Error deleting branch: " . $stmt->error;
}

$stmt->close();
$conn->close();
