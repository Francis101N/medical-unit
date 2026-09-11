<?php
session_start();
header('Content-Type: application/json');
/** @var mysqli $conn */
include('./db.php');


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_role = strtolower($_SESSION['role'] ?? '');
$user_branch = $_SESSION['branch'] ?? '';

if ($user_role === 'super-admin') {
    $query = "SELECT patient_location as location, COUNT(*) as total FROM patient_medical_records WHERE patient_location IS NOT NULL AND patient_location != '' GROUP BY patient_location ORDER BY total DESC";
    $stmt = $conn->prepare($query);
} else {
    $query = "SELECT patient_location as location, COUNT(*) as total FROM patient_medical_records WHERE LOWER(TRIM(patient_location)) = LOWER(TRIM(?)) GROUP BY patient_location";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user_branch);
}

$stmt->execute();
$result = $stmt->get_result();

$branches = [];
$counts = [];
$branch_ids = [];

while ($row = $result->fetch_assoc()) {
    $branches[] = $row['location'];
    $counts[] = (int)$row['total'];
    $branch_ids[] = $row['location'];
}

echo json_encode([
    'success' => true,
    'branches' => $branches,
    'counts' => $counts,
    'branch_ids' => $branch_ids
]);
