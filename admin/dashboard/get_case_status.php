<?php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** @var mysqli $conn */
include('./db.php');

$user_role = strtolower($_SESSION['role'] ?? '');
$user_branch = $_SESSION['branch'] ?? '';

// Build conditional aggregates based on user authority clearance levels
if ($user_role === 'super-admin') {
    $query = "SELECT 
                SUM(CASE WHEN LOWER(TRIM(record_status)) = 'open' THEN 1 ELSE 0 END) as open_count,
                SUM(CASE WHEN LOWER(TRIM(record_status)) = 'under_treatment' THEN 1 ELSE 0 END) as ut_count,
                SUM(CASE WHEN LOWER(TRIM(record_status)) NOT IN ('open', 'under_treatment') THEN 1 ELSE 0 END) as closed_count
              FROM staff_medical_records";
    $stmt = $conn->prepare($query);
} else {
    $query = "SELECT 
                SUM(CASE WHEN LOWER(TRIM(record_status)) = 'open' THEN 1 ELSE 0 END) as open_count,
                SUM(CASE WHEN LOWER(TRIM(record_status)) = 'under_treatment' THEN 1 ELSE 0 END) as ut_count,
                SUM(CASE WHEN LOWER(TRIM(record_status)) NOT IN ('open', 'under_treatment') THEN 1 ELSE 0 END) as closed_count
              FROM staff_medical_records
              WHERE LOWER(TRIM(staff_branch)) = LOWER(TRIM(?))";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user_branch);
}

$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$open = (int)($row['open_count'] ?? 0);
$under_treatment = (int)($row['ut_count'] ?? 0);
$closed = (int)($row['closed_count'] ?? 0);

$stmt->close();
mysqli_close($conn);

// Map outputs clearly down onto array payloads
echo json_encode([
    'success' => true,
    'open' => $open,
    'under_treatment' => $under_treatment,
    'closed' => $closed
]);
