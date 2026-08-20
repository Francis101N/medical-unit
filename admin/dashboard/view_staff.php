<?php

/** @var mysqli $conn */
include('./db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// Decrypt ID function matching the directory list script
if (!function_exists('decryptId')) {
    function decryptId($encrypted_value)
    {
        $key = "medical-secret-key";
        $decoded = base64_decode(strtr($encrypted_value, '-_', '+/'));
        if ($decoded !== false) {
            $parts = explode('|', $decoded);
            if (count($parts) === 2 && $parts[1] === $key) {
                return $parts[0];
            }
        }
        return null;
    }
}

$encrypted_id = $_GET['id'] ?? '';
$staff_id_pk = decryptId($encrypted_id);

if (!$staff_id_pk) {
    die("Invalid or tampered staff record reference.");
}

// Fetch staff details along with branch info
$stmt = $conn->prepare("SELECT s.*, b.branch_name FROM staffs s LEFT JOIN branches b ON s.branch_id = b.id WHERE s.id = ? LIMIT 1");
$stmt->bind_param("i", $staff_id_pk);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Staff record not found.");
}

$staff = $result->fetch_assoc();
$stmt->close();

// Role and branch access control security check
$user_role = strtolower($_SESSION['role'] ?? '');
$user_branch = $_SESSION['branch'] ?? '';

if ($user_role !== 'super-admin') {
    $staff_branch_name = strtolower(trim($staff['branch_name'] ?? ''));
    $staff_branch_id = $staff['branch_id'];
    if ($staff_branch_name !== strtolower(trim($user_branch)) && $staff_branch_id != $user_branch) {
        die("Access denied: You do not have permission to view records outside your assigned branch.");
    }
}

// Format status classes
$status = strtolower(trim($staff['status'] ?? ''));
$fitness_status = strtolower(trim($staff['fitness_status'] ?? ''));

$status_class = match ($status) {
    'active' => 'badge-soft-success',
    'suspended' => 'badge-soft-warning',
    'inactive' => 'badge-soft-secondary',
    default => 'badge-soft-danger'
};

$fitness_class = match ($fitness_status) {
    'fit' => 'badge-soft-success',
    'under_observation', 'observation' => 'badge-soft-warning',
    default => 'badge-soft-danger'
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Staff Profile - <?php echo htmlspecialchars($staff['fullname']); ?></title>
    <!-- Include your CSS Framework / Bootstrap links here -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .profile-header {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 2rem 0;
        }

        .passport-view {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #dee2e6;
        }

        .badge-soft {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            border-radius: 0.25rem;
        }

        .badge-soft-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .badge-soft-warning {
            background-color: #fff3cd;
            color: #664d03;
        }

        .badge-soft-secondary {
            background-color: #e2e3e5;
            color: #41464b;
        }

        .badge-soft-danger {
            background-color: #f8d7da;
            color: #842029;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <!-- Top Bar Navigation / Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="staffs.php" class="btn btn-outline-secondary btn-sm">&larr; Back to Directory</a>
            <div>
                <a href="edit_staff.php?id=<?php echo urlencode($encrypted_id); ?>" class="btn btn-primary btn-sm">Edit Profile</a>
            </div>
        </div>

        <!-- Profile Overview Card -->
        <div class="card p-4">
            <div class="row align-items-center">
                <div class="col-md-auto text-center mb-3 mb-md-0">
                    <?php if (!empty($staff['passport']) && file_exists("uploads/" . $staff['passport'])) { ?>
                        <img src="uploads/<?php echo htmlspecialchars($staff['passport']); ?>" alt="Passport" class="passport-view shadow-sm">
                    <?php } else { ?>
                        <div class="passport-view d-flex align-items-center justify-content-center bg-light text-muted border mx-auto">No Photo</div>
                    <?php } ?>
                </div>
                <div class="col-md">
                    <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($staff['fullname']); ?></h3>
                    <p class="text-muted mb-2 font-monospace">Staff ID: <?php echo htmlspecialchars($staff['staff_id']); ?></p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($staff['branch_name'] ?? 'Branch ID: ' . $staff['branch_id']); ?></span>
                        <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($staff['department']); ?></span>
                        <span class="badge-soft <?php echo $status_class; ?>"><?php echo ucfirst(htmlspecialchars($status)); ?></span>
                        <span class="badge-soft <?php echo $fitness_class; ?>">Fitness: <?php echo ucfirst(htmlspecialchars(str_replace('_', ' ', $fitness_status))); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Personal & Employment Information -->
            <div class="col-lg-6">
                <div class="card p-4 h-100">
                    <h5 class="fw-bold text-primary mb-3">Employment & Personal Information</h5>
                    <table class="table table-borderless align-middle mb-0">
                        <tr>
                            <th class="text-muted w-40">Email Address:</th>
                            <td><?php echo htmlspecialchars($staff['email']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Phone Number:</th>
                            <td><?php echo htmlspecialchars($staff['phone']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Gender:</th>
                            <td><?php echo ucfirst(htmlspecialchars($staff['gender'])); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Date of Birth:</th>
                            <td><?php echo htmlspecialchars($staff['dob']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Company:</th>
                            <td><?php echo htmlspecialchars($staff['company'] ?: '—'); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Role / Title:</th>
                            <td><?php echo htmlspecialchars($staff['role']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Employment Type:</th>
                            <td><?php echo ucfirst(htmlspecialchars($staff['employment_type'])); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Hire Date:</th>
                            <td><?php echo htmlspecialchars($staff['hire_date']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Residential Address:</th>
                            <td><?php echo htmlspecialchars($staff['address']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Medical & Emergency Profile -->
            <div class="col-lg-6">
                <div class="card p-4 h-100">
                    <h5 class="fw-bold text-danger mb-3">Medical Profile & Emergency Contacts</h5>
                    <table class="table table-borderless align-middle mb-0">
                        <tr>
                            <th class="text-muted w-40">Blood Group:</th>
                            <td><span class="badge bg-danger-subtle text-danger border px-2"><?php echo htmlspecialchars($staff['blood_group']); ?></span></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Genotype:</th>
                            <td><span class="badge bg-info-subtle text-info border px-2"><?php echo htmlspecialchars($staff['genotype']); ?></span></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Allergies:</th>
                            <td class="text-danger fw-medium"><?php echo htmlspecialchars($staff['allergies'] ?: 'None recorded'); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Medical Conditions:</th>
                            <td><?php echo htmlspecialchars($staff['medical_conditions'] ?: 'None recorded'); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Last Checkup:</th>
                            <td><?php echo htmlspecialchars($staff['last_medical_checkup'] ?: '—'); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Next of Kin:</th>
                            <td><?php echo htmlspecialchars($staff['next_of_kin']); ?> (<?php echo htmlspecialchars($staff['next_of_kin_phone']); ?>)</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Emergency Contact:</th>
                            <td><?php echo htmlspecialchars($staff['emergency_contact_name']); ?> (<?php echo htmlspecialchars($staff['emergency_contact_phone']); ?>)</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- System Timestamps Footer -->
        <div class="text-muted text-end mt-3 small">
            Record Created: <?php echo htmlspecialchars($staff['created_at']); ?> | Last Updated: <?php echo htmlspecialchars($staff['updated_at']); ?>
        </div>
    </div>

</body>

</html>