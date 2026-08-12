<?php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** @var mysqli $conn */
include('./db.php');

$user_role = strtolower($_SESSION['role'] ?? '');
$user_branch = $_SESSION['branch'] ?? '';

// Aggregates total records linked to each branch context cleanly
if ($user_role === 'super-admin') {
    // Admins see all operational branches compared together
    $query = "SELECT b.branch_name, COUNT(smr.id) as total_records 
              FROM branches b
              LEFT JOIN staff_medical_records smr 
                ON LOWER(TRIM(smr.staff_branch)) = LOWER(TRIM(b.branch_name))
              GROUP BY b.id, b.branch_name
              ORDER BY total_records DESC, b.branch_name ASC";
    $stmt = $conn->prepare($query);
} else {
    // Staff/Managers only see their specific branch context totals
    $query = "SELECT b.branch_name, COUNT(smr.id) as total_records 
              FROM branches b
              LEFT JOIN staff_medical_records smr 
                ON LOWER(TRIM(smr.staff_branch)) = LOWER(TRIM(b.branch_name))
              WHERE LOWER(TRIM(b.branch_name)) = LOWER(TRIM(?)) OR b.id = ?
              GROUP BY b.id, b.branch_name";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $user_branch, $user_branch);
}

$stmt->execute();
$result = $stmt->get_result();

$branches = [];
$counts = [];

while ($row = $result->fetch_assoc()) {
    $branches[] = $row['branch_name'];
    $counts[] = (int)$row['total_records'];
}

$stmt->close();
mysqli_close($conn);

// Pass clean parallel data payloads down to chart arrays
echo json_encode([
    'success' => true,
    'branches' => $branches,
    'counts' => $counts
]);
