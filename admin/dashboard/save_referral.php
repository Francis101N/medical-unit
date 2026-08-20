<?php
/** @var mysqli $conn */
include('./db.php');

header('Content-Type: application/json');

// Read incoming JSON payload
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload.']);
    exit();
}

$staff_name = $data['staff_name'] ?? '';
$serial_id  = $data['serial_id'] ?? '';
$ref_code   = $data['ref_code'] ?? '';

if (empty($staff_name) || empty($serial_id) || empty($ref_code)) {
    echo json_encode(['status' => 'error', 'message' => 'Required fields are missing.']);
    exit();
}

// Insert query into referral table (inserting staff_name, serial_id, and ref_code)
$stmt = $conn->prepare("INSERT INTO referral_logs (staff_name, serial_id, ref_code) VALUES (?, ?, ?)");
if ($stmt) {
    $stmt->bind_param("sss", $staff_name, $serial_id, $ref_code);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Referral record saved successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Execute failed: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
}

$conn->close();