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

// Decrypt ID function matching the branch directory list script
if (!function_exists('decryptId')) {
    function decryptId($hash)
    {
        $key = "medical-secret-key";
        $decoded = base64_decode(strtr($hash, '-_', '+/'));
        if ($decoded === false) {
            return false;
        }
        $parts = explode('|', $decoded);

        if (count($parts) !== 2 || $parts[1] !== $key) {
            return false;
        }

        return $parts[0];
    }
}

$encrypted_id = $_GET['id'] ?? '';
$branch_id_pk = decryptId($encrypted_id);

if (!$branch_id_pk) {
    die("Invalid or tampered branch record reference.");
}

// Fetch branch details
$stmt = $conn->prepare("SELECT * FROM branches WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $branch_id_pk);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Branch record not found.");
}

$branch = $result->fetch_assoc();
$stmt->close();

// Role-based access control check (non-super-admin can only view their own branch)
$user_role = strtolower($_SESSION['role'] ?? '');
$user_branch = $_SESSION['branch'] ?? '';

if ($user_role !== 'super-admin') {
    $branch_name_val = strtolower(trim($branch['branch_name'] ?? ''));
    if ($branch_name_val !== strtolower(trim($user_branch)) && $branch['id'] != $user_branch) {
        die("Access denied: You do not have permission to view branches outside your assigned location.");
    }
}

// Fetch total staff count for this branch
$staff_count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM staffs WHERE branch_id = ?");
$staff_count_stmt->bind_param("i", $branch['id']);
$staff_count_stmt->execute();
$staff_count_res = $staff_count_stmt->get_result()->fetch_assoc();
$total_staff = $staff_count_res['total'] ?? 0;
$staff_count_stmt->close();

// Map branch type to soft badge class
$branch_type = strtolower(trim($branch['type'] ?? ''));
$type_class = match ($branch_type) {
    'hq', 'headquarters' => 'badge-soft-hq bg-danger-subtle text-danger',
    'clinic' => 'badge-soft-clinic bg-success-subtle text-success',
    default => 'badge-soft-branch bg-primary-subtle text-primary'
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Branch - <?php echo htmlspecialchars($branch['branch_name']); ?></title>
    <!-- Include Bootstrap CSS -->
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
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #dee2e6;
        }

        .badge-soft {
            padding: 0.4em 0.75em;
            font-size: 0.75em;
            font-weight: 700;
            border-radius: 0.35rem;
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
        <!-- Navigation and Actions Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="branches.php" class="btn btn-outline-secondary btn-sm">&larr; Back to Branches</a>
            <div>
                <a href="edit_branch.php?id=<?php echo urlencode($encrypted_id); ?>" class="btn btn-primary btn-sm">Edit Branch</a>
            </div>
        </div>

        <!-- Branch Overview Header Card -->
        <div class="card p-4">
            <div class="row align-items-center">
                <div class="col-md-auto text-center mb-3 mb-md-0">
                    <?php if (!empty($branch['medical_head_passport']) && file_exists("uploads/" . $branch['medical_head_passport'])) { ?>
                        <img src="uploads/<?php echo htmlspecialchars($branch['medical_head_passport']); ?>" alt="Medical Head Passport" class="passport-view shadow-sm">
                    <?php } else { ?>
                        <div class="passport-view d-flex align-items-center justify-content-center bg-light text-muted border mx-auto">No Photo</div>
                    <?php } ?>
                </div>
                <div class="col-md">
                    <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($branch['branch_name']); ?></h3>
                    <p class="text-muted mb-2">State Location: <strong><?php echo htmlspecialchars($branch['state']); ?></strong></p>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge badge-soft <?php echo $type_class; ?> border"><?php echo htmlspecialchars($branch['type']); ?></span>
                        <span class="badge bg-light text-dark border">Registered Staff: <strong><?php echo $total_staff; ?></strong></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Branch & Leadership Details -->
            <div class="col-lg-7">
                <div class="card p-4 h-100">
                    <h5 class="fw-bold text-primary mb-3">Branch & Medical Head Details</h5>
                    <table class="table table-borderless align-middle mb-0">
                        <tr>
                            <th class="text-muted w-40">Branch Name:</th>
                            <td><strong><?php echo htmlspecialchars($branch['branch_name']); ?></strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted">State:</th>
                            <td><?php echo htmlspecialchars($branch['state']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Branch Type:</th>
                            <td><?php echo ucfirst(htmlspecialchars($branch['type'])); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Medical Head:</th>
                            <td><strong><?php echo htmlspecialchars($branch['medical_head']); ?></strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Medical Head Email:</th>
                            <td><?php echo htmlspecialchars($branch['medical_head_email']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- System Activity Timestamps -->
            <div class="col-lg-5">
                <div class="card p-4 h-100">
                    <h5 class="fw-bold text-secondary mb-3">System Metadata</h5>
                    <table class="table table-borderless align-middle mb-0">
                        <tr>
                            <th class="text-muted w-50">Branch Database ID:</th>
                            <td>#<?php echo htmlspecialchars($branch['id']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Created At:</th>
                            <td><small><?php echo htmlspecialchars($branch['date_created']); ?></small></td>
                        </tr>

                    </table>
                </div>
            </div>
        </div>
    </div>

</body>

</html>