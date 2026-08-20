<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "
    <script>
        alert('Session Expired! You must log in first.');
        window.location.href='auth-login.php';
    </script>
    ";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Medical Unit</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
</head>

<body>
    <div id="app">
        <?php
        include('./inc/side-nav.php');
        ?>
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="page-heading">
                <div class="page-title">
                    <div class="row align-items-center">
                        <!-- LEFT SIDE -->
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3 class="mb-1">Edit User Record</h3>
                            <p class="text-subtitle text-muted mb-0">
                                Update user account credentials and directory info.
                            </p>
                        </div>

                        <!-- RIGHT SIDE -->
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item">
                                        <a href="index.php">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="users.php">Users</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        Edit User
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div> <br>

                <!-- Edit User Section -->
                <section class="section">
                    <div class="row justify-content-center" id="table-hover-row">
                        <div class="col-12 col-lg-10">

                            <div class="card shadow-sm border-0">

                                <!-- CARD HEADER -->
                                <div class="card-header bg-success text-white py-3">
                                    <h4 class="mb-0">Edit User Details</h4>
                                </div>

                                <br>

                                <?php
                                $msg = $msg ?? '';
                                $msg_type = $msg_type ?? 'success';

                                if (!empty($msg)) {
                                ?>
                                    <div class="alert m-3 alert-<?php echo $msg_type; ?> alert-dismissible fade show">
                                        <?php echo $msg; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php } ?>

                                <!-- CARD BODY -->
                                <div class="card-content">
                                    <div class="card-body p-4">

                                        <?php
                                        /** @var mysqli $conn */
                                        include('./db.php');

                                        // Helper function to decode the encrypted ID token
                                        function decryptId($token)
                                        {
                                            $key = "medical-secret-key";
                                            $decoded = base64_decode(strtr($token, '-_', '+/'));
                                            if ($decoded === false || !str_contains($decoded, '|')) {
                                                return false;
                                            }
                                            list($id, $secret) = explode('|', $decoded, 2);
                                            return ($secret === $key) ? $id : false;
                                        }

                                        // Reversible OpenSSL Decryption Helper for passwords
                                        function decryptPassword($data)
                                        {
                                            if (empty($data)) {
                                                return '';
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

                                        if (!isset($_GET['id'])) {
                                            header("Location: users.php");
                                            exit();
                                        }

                                        // Retrieve and decrypt the token parameter
                                        $encrypted_id = $_GET['id'];
                                        $id = decryptId($encrypted_id);

                                        if ($id === false) {
                                            header("Location: users.php");
                                            exit();
                                        }

                                        // Secure mapping engine via prepared parameterized statements from users table
                                        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
                                        mysqli_stmt_bind_param($stmt, "i", $id);
                                        mysqli_stmt_execute($stmt);
                                        $result = mysqli_stmt_get_result($stmt);

                                        if (mysqli_num_rows($result) == 0) {
                                            header("Location: users.php");
                                            exit();
                                        }

                                        $user = mysqli_fetch_assoc($result);
                                        $stmt->close();

                                        // Decrypt database password for editing view
                                        $plain_password = decryptPassword($user['password'] ?? '');

                                        // Setup passport target verification pathing
                                        $passport = trim($user['passport'] ?? '');
                                        $passport_src = '';
                                        if (!empty($passport) && file_exists("uploads/" . $passport)) {
                                            $passport_src = "uploads/" . $passport;
                                        }
                                        ?>

                                        <!-- Profile Title Bar Area containing Photo Frame Wrapper -->
                                        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                                            <div>
                                                <h4 class="card-title mb-1">Modify User Account Profile</h4>
                                                <p class="text-muted mb-0">Updating records for <strong><?php echo htmlspecialchars($user['fullname']); ?></strong></p>
                                            </div>
                                            <div>
                                                <div class="text-center">
                                                    <img id="passportPreview"
                                                        src="<?php echo !empty($passport_src) ? htmlspecialchars($passport_src) : '#'; ?>"
                                                        alt="Profile Avatar"
                                                        class="rounded-circle img-thumbnail shadow-sm"
                                                        style="width: 85px; height: 85px; object-fit: cover; border: 3px solid #198754; <?php echo empty($passport_src) ? 'display:none;' : ''; ?>" />

                                                    <?php if (empty($passport_src)) { ?>
                                                        <div id="noPhotoPlaceholder" class="bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center border mx-auto" style="width: 85px; height: 85px; font-size: 0.75rem;">
                                                            No Photo
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>

                                        <form action="proc_edit_user.php" method="POST" enctype="multipart/form-data">

                                            <!-- HIDDEN ENCRYPTED TOKEN PASSED FOR POST-BACK ROUTING -->
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($encrypted_id); ?>">

                                            <h5 class="text-success mb-3 border-bottom pb-2">User Credentials & Attributes</h5>
                                            <div class="row mb-4">
                                                <!-- Full Name -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Full Name</label>
                                                    <input type="text" name="fullname"
                                                        value="<?php echo htmlspecialchars($user['fullname']); ?>"
                                                        class="form-control form-control-lg" required>
                                                </div>

                                                <!-- Username -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Username</label>
                                                    <input type="text" name="username"
                                                        value="<?php echo htmlspecialchars($user['username']); ?>"
                                                        class="form-control form-control-lg" required>
                                                </div>

                                                <!-- Password -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Password</label>
                                                    <input type="text" name="password"
                                                        value="<?php echo htmlspecialchars($plain_password); ?>"
                                                        class="form-control form-control-lg font-monospace" required>
                                                </div>

                                                <!-- Email -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Email Address</label>
                                                    <input type="email" name="email"
                                                        value="<?php echo htmlspecialchars($user['email']); ?>"
                                                        class="form-control form-control-lg" required>
                                                </div>

                                                <!-- System Role -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">System Role</label>
                                                    <input type="text" name="role"
                                                        value="<?php echo htmlspecialchars($user['role']); ?>"
                                                        class="form-control form-control-lg" required>
                                                </div>

                                                <!-- Branch Deployment -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Branch</label>
                                                    <input type="text" name="branch"
                                                        value="<?php echo htmlspecialchars($user['branch']); ?>"
                                                        class="form-control form-control-lg" required>
                                                </div>

                                                <!-- Passport File Input -->
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label fw-bold">Update Passport Photo</label>
                                                    <input type="file" name="passport" class="form-control form-control-lg" accept="image/*" onchange="previewPassport(event)">
                                                </div>
                                            </div>

                                            <!-- Form Action Buttons -->
                                            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                                                <a href="users.php" class="btn btn-secondary btn-lg px-4">Cancel</a>
                                                <button type="submit" name="update_user" class="btn btn-success btn-lg px-5">Save Changes</button>
                                            </div>

                                        </form>

                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Passport Image Preview Script -->
    <script>
        function previewPassport(event) {
            const input = event.target;
            const preview = document.getElementById('passportPreview');
            const placeholder = document.getElementById('noPhotoPlaceholder');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = "block";
                    if (placeholder) {
                        placeholder.style.display = "none";
                    }
                };

                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>