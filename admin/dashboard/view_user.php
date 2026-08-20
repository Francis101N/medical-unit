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

// Reversible OpenSSL Decryption Helper for passwords
if (!function_exists('decryptPassword')) {
    function decryptPassword($data)
    {
        if (empty($data)) {
            return 'N/A';
        }
        $encryption_key = 'techbyfrancis1972$';
        $cipher = "AES-128-CBC";
        if (strpos($data, '::') === false) {
            return $data;
        }
        list($encrypted_data, $iv) = explode('::', $data, 2);
        $decrypted = openssl_decrypt($encrypted_data, $cipher, $encryption_key, 0, base64_decode($iv));
        return $decrypted !== false ? $decrypted : $data;
    }
}

$encrypted_id = $_GET['id'] ?? '';
$user_id_pk = decryptId($encrypted_id);

if (!$user_id_pk) {
    die("Invalid or tampered user record reference.");
}

// Fetch user details from the users table
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id_pk);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User record not found.");
}

$user = $result->fetch_assoc();
$stmt->close();

// Decrypt password for viewing interface
$plain_password = decryptPassword($user['password'] ?? '');

// Role and branch access control security check
$session_role = strtolower($_SESSION['role'] ?? '');
$session_branch = $_SESSION['branch'] ?? '';

if ($session_role !== 'super-admin') {
    $user_branch = strtolower(trim($user['branch'] ?? ''));
    if ($user_branch !== strtolower(trim($session_branch))) {
        die("Access denied: You do not have permission to view records outside your assigned branch.");
    }
}

// Format status classes if status exists in your table structure
$status = strtolower(trim($user['status'] ?? 'active'));
$status_class = match ($status) {
    'active' => 'badge-soft-success',
    'suspended' => 'badge-soft-warning',
    'inactive' => 'badge-soft-secondary',
    default => 'badge-soft-danger'
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User Profile - <?php echo htmlspecialchars($user['fullname']); ?></title>
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
            <a href="users.php" class="btn btn-outline-secondary btn-sm">&larr; Back to Directory</a>
            <div>
                <a href="edit_user.php?id=<?php echo urlencode($encrypted_id); ?>" class="btn btn-primary btn-sm">Edit Profile</a>
            </div>
        </div>

        <!-- Profile Overview Card -->
        <div class="card p-4">
            <div class="row align-items-center">
                <div class="col-md-auto text-center mb-3 mb-md-0">
                    <?php if (!empty($user['passport']) && file_exists("uploads/" . $user['passport'])) { ?>
                        <img src="uploads/<?php echo htmlspecialchars($user['passport']); ?>" alt="Passport" class="passport-view shadow-sm">
                    <?php } else { ?>
                        <div class="passport-view d-flex align-items-center justify-content-center bg-light text-muted border mx-auto">No Photo</div>
                    <?php } ?>
                </div>
                <div class="col-md">
                    <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($user['fullname']); ?></h3>
                    <p class="text-muted mb-2 font-monospace">Username: @<?php echo htmlspecialchars($user['username']); ?></p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark border">Branch: <?php echo htmlspecialchars($user['branch']); ?></span>
                        <span class="badge bg-light text-dark border">Role: <?php echo htmlspecialchars($user['role']); ?></span>
                        <span class="badge-soft <?php echo $status_class; ?>"><?php echo ucfirst(htmlspecialchars($status)); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Information Breakdown Card -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card p-4 h-100">
                    <h5 class="fw-bold text-primary mb-3">Account Information Overview</h5>
                    <table class="table table-borderless align-middle mb-0">
                        <tr>
                            <th class="text-muted w-25">Full Name:</th>
                            <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Username:</th>
                            <td><span class="font-monospace"><?php echo htmlspecialchars($user['username']); ?></span></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Password (Decrypted):</th>
                            <td><span class="font-monospace text-dark bg-light px-2 py-1 rounded border"><?php echo htmlspecialchars($plain_password); ?></span></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Email Address:</th>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">System Role:</th>
                            <td><span class="badge bg-secondary-subtle text-secondary border px-2"><?php echo htmlspecialchars($user['role']); ?></span></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Assigned Branch:</th>
                            <td><span class="badge bg-light text-dark border px-2"><?php echo htmlspecialchars($user['branch']); ?></span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- System Timestamps Footer -->
        <div class="text-muted text-end mt-3 small">
            Record Created: <?php echo htmlspecialchars($user['date_created'] ?? ($user['created_at'] ?? 'N/A')); ?>
        </div>
    </div>

</body>

</html>