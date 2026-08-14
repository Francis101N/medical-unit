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
    <title>Edit Staff - Medical Unit</title>

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
                            <h3 class="mb-1">Edit Staff</h3>
                            <p class="text-subtitle text-muted mb-0">
                                Update staff member details and records.
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
                                        <a href="staffs.php">Staff</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        Edit Staff
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div> <br>

                <!-- Edit Staff Section -->
                <section class="section">
                    <div class="row justify-content-center" id="table-hover-row">
                        <div class="col-12 col-lg-10">

                            <div class="card shadow-sm border-0">

                                <!-- CARD HEADER -->
                                <div class="card-header bg-success text-white py-3">
                                    <h4 class="mb-0">Edit Staff</h4>
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

                                        if (!isset($_GET['id'])) {
                                            header("Location: staffs.php");
                                            exit();
                                        }

                                        // Retrieve and decrypt the token parameter
                                        $encrypted_id = $_GET['id'];
                                        $id = decryptId($encrypted_id);

                                        if ($id === false) {
                                            header("Location: staffs.php");
                                            exit();
                                        }

                                        // Secure mapping engine via prepared parameterized statements
                                        $stmt = mysqli_prepare($conn, "SELECT * FROM staffs WHERE id = ?");
                                        mysqli_stmt_bind_param($stmt, "s", $id);
                                        mysqli_stmt_execute($stmt);
                                        $result = mysqli_stmt_get_result($stmt);

                                        if (mysqli_num_rows($result) == 0) {
                                            header("Location: staffs.php");
                                            exit();
                                        }

                                        $staff = mysqli_fetch_assoc($result);

                                        // Setup passport target verification pathing
                                        $passport = trim($staff['passport'] ?? '');
                                        $passport_src = '';
                                        if (!empty($passport) && file_exists("uploads/" . $passport)) {
                                            $passport_src = "uploads/" . $passport;
                                        }
                                        ?>

                                        <!-- Profile Title Bar Area containing Expandable Photo Frame Wrapper -->
                                        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                                            <div>
                                                <h4 class="card-title mb-1">Modify Staff Directory Profile</h4>
                                                <p class="text-muted mb-0">Reviewing clinical metrics and account records for <strong><?php echo htmlspecialchars($staff['fullname']); ?></strong></p>
                                            </div>
                                            <div>
                                                <?php if (!empty($passport_src)) { ?>
                                                    <div class="text-center">
                                                        <a href="#" data-bs-toggle="modal" data-bs-target="#staffPassportModal" title="Click to enlarge passport photo">
                                                            <img src="<?php echo htmlspecialchars($passport_src); ?>"
                                                                alt="Profile Avatar"
                                                                class="rounded-circle img-thumbnail shadow-sm"
                                                                style="width: 85px; height: 85px; object-fit: cover; cursor: pointer; border: 3px solid #198754; transition: transform 0.2s;"
                                                                onmouseover="this.style.transform='scale(1.05)';"
                                                                onmouseout="this.style.transform='scale(1)';" />
                                                        </a>
                                                        <div class="small text-muted mt-1" style="font-size: 0.72rem;">Click to expand</div>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center border" style="width: 85px; height: 85px; font-size: 0.75rem;">
                                                        No Photo
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>

                                        <form action="proc_edit_staff.php" method="POST" enctype="multipart/form-data">

                                            <!-- HIDDEN ENCRYPTED TOKEN PASSED FOR POST-BACK ROUTING -->
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($encrypted_id); ?>">

                                            <h5 class="text-success mb-3 border-bottom pb-2">Primary & Identification Details</h5>
                                            <div class="row mb-4">
                                                <!-- Staff ID -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Staff ID</label>
                                                    <input type="text" name="staff_id"
                                                        value="<?php echo htmlspecialchars($staff['staff_id']); ?>"
                                                        class="form-control form-control-lg" required>
                                                </div>

                                                <!-- Full Name -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Full Name</label>
                                                    <input type="text" name="fullname"
                                                        value="<?php echo htmlspecialchars($staff['fullname']); ?>"
                                                        class="form-control form-control-lg" required>
                                                </div>

                                                <!-- Gender -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Gender</label>
                                                    <select name="gender" class="form-select form-control-lg">
                                                        <option value="male" <?php if (strtolower($staff['gender'] ?? '') == 'male') echo 'selected'; ?>>Male</option>
                                                        <option value="female" <?php if (strtolower($staff['gender'] ?? '') == 'female') echo 'selected'; ?>>Female</option>
                                                    </select>
                                                </div>

                                                <!-- Email -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Email Address</label>
                                                    <input type="email" name="email"
                                                        value="<?php echo htmlspecialchars($staff['email']); ?>"
                                                        class="form-control form-control-lg" required>
                                                </div>

                                                <!-- Phone -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Phone Number</label>
                                                    <input type="text" name="phone"
                                                        value="<?php echo htmlspecialchars($staff['phone']); ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- DOB -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Date of Birth</label>
                                                    <input type="date" name="dob"
                                                        value="<?php echo htmlspecialchars($staff['dob']); ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- Residential Address -->
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label fw-bold">Residential Address</label>
                                                    <textarea name="address" rows="2" class="form-control form-control-lg"><?php echo htmlspecialchars($staff['address'] ?? ''); ?></textarea>
                                                </div>
                                            </div>

                                            <h5 class="text-success mb-3 border-bottom pb-2">Organizational & Placement Metrics</h5>
                                            <div class="row mb-4">
                                                <!-- Branch Deployment -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Branch</label>
                                                    <select name="branch_id" class="form-select form-control-lg">
                                                        <option value="">-- Select Branch --</option>
                                                        <?php
                                                        $branch_query = mysqli_query($conn, "SELECT * FROM branches ORDER BY branch_name ASC");
                                                        while ($branch = mysqli_fetch_assoc($branch_query)) {
                                                        ?>
                                                            <option value="<?php echo $branch['id']; ?>" <?php if ($staff['branch_id'] == $branch['id']) echo 'selected'; ?>>
                                                                <?php echo htmlspecialchars($branch['branch_name']); ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                                <!-- Company Selection Dropdown -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Company</label>
                                                    <select name="company" class="form-select form-control-lg">
                                                        <option value="">-- Select Company --</option>
                                                        <option value="Equal Logistics" <?php if (($staff['company'] ?? '') == 'Equal Logistics') echo 'selected'; ?>>Equal Logistics</option>
                                                        <option value="Upstream DC" <?php if (($staff['company'] ?? '') == 'Upstream DC') echo 'selected'; ?>>Upstream DC</option>
                                                        <option value="Viscosupport" <?php if (($staff['company'] ?? '') == 'Viscosupport') echo 'selected'; ?>>Viscosupport</option>
                                                    </select>
                                                </div>

                                                <!-- Department Selection Dropdown -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Department</label>
                                                    <select name="department" class="form-select form-control-lg">
                                                        <?php
                                                        $departments = [
                                                            'human_resources' => 'Human Resources',
                                                            'information_technology' => 'Information Technology',
                                                            'finance' => 'Finance',
                                                            'accounts' => 'Accounts',
                                                            'administration' => 'Administration',
                                                            'medical_unit' => 'Medical Unit',
                                                            'operations' => 'Operations',
                                                            'customer_service' => 'Customer Service',
                                                            'security' => 'Security',
                                                            'legal' => 'Legal',
                                                            'marketing' => 'Marketing',
                                                            'sales' => 'Sales',
                                                            'procurement' => 'Procurement',
                                                            'logistics' => 'Logistics',
                                                            'maintenance' => 'Maintenance',
                                                            'engineering' => 'Engineering'
                                                        ];
                                                        foreach ($departments as $value => $label) {
                                                        ?>
                                                            <option value="<?php echo $value; ?>" <?php if ($staff['department'] == $value) echo 'selected'; ?>>
                                                                <?php echo $label; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                                <!-- Corporate Role -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Role Assignment</label>
                                                    <input type="text" name="role"
                                                        value="<?php echo htmlspecialchars($staff['role']); ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- Employment Status Configuration matrix -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Employment Type</label>
                                                    <select name="employment_type" class="form-select form-control-lg">
                                                        <?php
                                                        $employment_types = [
                                                            'full_time' => 'Full Time',
                                                            'part_time' => 'Part Time',
                                                            'contract' => 'Contract',
                                                            'temporary' => 'Temporary',
                                                            'intern' => 'Intern',
                                                            'casual' => 'Casual',
                                                            'remote' => 'Remote',
                                                            'hybrid' => 'Hybrid',
                                                            'freelance' => 'Freelance',
                                                            'probation' => 'Probation'
                                                        ];
                                                        foreach ($employment_types as $value => $label) {
                                                        ?>
                                                            <option value="<?php echo $value; ?>" <?php if ($staff['employment_type'] == $value) echo 'selected'; ?>>
                                                                <?php echo $label; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                                <!-- Hire Date -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Hire Date</label>
                                                    <input type="date" name="hire_date"
                                                        value="<?php echo htmlspecialchars($staff['hire_date']); ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- System Operational Profile Status -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Account Profile Status</label>
                                                    <select name="status" class="form-select form-control-lg">
                                                        <option value="active" <?php if (strtolower($staff['status'] ?? '') == 'active') echo 'selected'; ?>>Active</option>
                                                        <option value="inactive" <?php if (strtolower($staff['status'] ?? '') == 'inactive') echo 'selected'; ?>>Inactive</option>
                                                        <option value="suspended" <?php if (strtolower($staff['status'] ?? '') == 'suspended') echo 'selected'; ?>>Suspended</option>
                                                        <option value="terminated" <?php if (strtolower($staff['status'] ?? '') == 'terminated') echo 'selected'; ?>>Terminated</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <h5 class="text-success mb-3 border-bottom pb-2">Clinical Diagnostics & Medical Attributes</h5>
                                            <div class="row mb-4">
                                                <!-- Blood Group Input -->
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">Blood Group</label>
                                                    <input type="text" name="blood_group" placeholder="e.g. O+"
                                                        value="<?php echo htmlspecialchars($staff['blood_group'] ?? ''); ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- Genotype Input -->
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">Genotype</label>
                                                    <input type="text" name="genotype" placeholder="e.g. AA"
                                                        value="<?php echo htmlspecialchars($staff['genotype'] ?? ''); ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- Fitness Evaluation Status -->
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">Fitness Status</label>
                                                    <select name="fitness_status" class="form-select form-control-lg">
                                                        <option value="fit" <?php if (strtolower($staff['fitness_status'] ?? '') == 'fit') echo 'selected'; ?>>Fit</option>
                                                        <option value="observation" <?php if (strtolower($staff['fitness_status'] ?? '') == 'observation' || strtolower($staff['fitness_status'] ?? '') == 'under_observation') echo 'selected'; ?>>Under Observation</option>
                                                        <option value="unfit" <?php if (strtolower($staff['fitness_status'] ?? '') == 'unfit') echo 'selected'; ?>>Unfit</option>
                                                    </select>
                                                </div>

                                                <!-- Last Checkup Log Tracking -->
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label fw-bold">Last Clinical Checkup</label>
                                                    <input type="date" name="last_medical_checkup"
                                                        value="<?php echo htmlspecialchars($staff['last_medical_checkup'] ?? ''); ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- Allergies Tracking -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Allergies</label>
                                                    <textarea name="allergies" rows="2" placeholder="List any known food or chemical reactions..." class="form-control form-control-lg"><?php echo htmlspecialchars($staff['allergies'] ?? ''); ?></textarea>
                                                </div>

                                                <!-- Medical Conditions Tracking -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Pre-existing Medical Conditions</label>
                                                    <textarea name="medical_conditions" rows="2" placeholder="List ongoing challenges or chronic profiles..." class="form-control form-control-lg"><?php echo htmlspecialchars($staff['medical_conditions'] ?? ''); ?></textarea>
                                                </div>
                                            </div>

                                            <h5 class="text-success mb-3 border-bottom pb-2">Emergency Networks & Next of Kin</h5>
                                            <div class="row mb-4">
                                                <!-- Next of Kin Designation -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Next of Kin Name</label>
                                                    <input type="text" name="next_of_kin"
                                                        value="<?php echo htmlspecialchars($staff['next_of_kin'] ?? ''); ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- Next of Kin contact phone -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Next of Kin Phone</label>
                                                    <input type="text" name="next_of_kin_phone"
                                                        value="<?php echo htmlspecialchars($staff['next_of_kin_phone'] ?? ''); ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- Primary Emergency Contact name -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Emergency Contact Representative</label>
                                                    <input type="text" name="emergency_contact_name"
                                                        value="<?php echo htmlspecialchars($staff['emergency_contact_name'] ?? ''); ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- Primary Emergency Phone -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Emergency Contact Phone Number</label>
                                                    <input type="text" name="emergency_contact_phone"
                                                        value="<?php echo htmlspecialchars($staff['emergency_contact_phone'] ?? ''); ?>"
                                                        class="form-control form-control-lg">
                                                </div>
                                            </div>

                                            <h5 class="text-success mb-3 border-bottom pb-2">Profile Assets Management</h5>
                                            <div class="row mb-4">
                                                <!-- New Passport Upload Stream -->
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label fw-bold">Upload New Passport Image (Optional)</label>
                                                    <input type="file" name="passport" class="form-control form-control-lg" accept="image/*">
                                                    <div class="form-text text-muted">Uploading a profile picture here replaces any currently active thumbnail assets.</div>
                                                </div>
                                            </div>

                                            <!-- BUTTON MATRIX INTERFACING CONTAINER -->
                                            <div class="mt-4 pt-2 border-top">
                                                <button type="submit" name="update_staff" class="btn btn-success btn-lg px-5">
                                                    Update Profile Record
                                                </button>
                                                <a href="staffs.php" class="btn btn-secondary btn-lg ms-2">Cancel Changes</a>
                                            </div>

                                        </form>
                                    </div>
                                </div>

                                <!-- STAFF PASSPORT LIGHTBOX MODAL COMPONENT -->
                                <?php if (!empty($passport_src)) { ?>
                                    <div class="modal fade" id="staffPassportModal" tabindex="-1" aria-labelledby="staffPassportModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg">
                                                <div class="modal-header bg-dark text-white">
                                                    <h5 class="modal-title" id="staffPassportModalLabel">
                                                        Staff Passport: <?php echo htmlspecialchars($staff['fullname']); ?>
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-center p-4 bg-light">
                                                    <img src="<?php echo htmlspecialchars($passport_src); ?>"
                                                        alt="<?php echo htmlspecialchars($staff['fullname']); ?>"
                                                        class="img-fluid rounded border shadow-sm"
                                                        style="max-height: 460px; object-fit: contain;" />
                                                </div>
                                                <div class="modal-footer bg-white">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close View</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>

                            </div>

                        </div>
                    </div>
                </section>
                <!-- Edit Staff Section End -->
            </div>

            <?php
            include('./inc/footer.php');
            ?>
        </div>
    </div>

    <script>
        function previewPassport(event) {
            const input = event.target;
            const preview = document.getElementById('passportPreview');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = "block";
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