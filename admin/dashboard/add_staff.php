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
    <title>Add Staff - Medical Unit</title>

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
                            <h3 class="mb-1">Add New Staff</h3>
                            <p class="text-subtitle text-muted mb-0">
                                Create a new staff member and assign their details and records.
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
                                        Add Staff
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div> <br>

                <!-- Add Staff Section -->
                <section class="section">
                    <div class="row justify-content-center" id="table-hover-row">
                        <div class="col-12 col-lg-10">

                            <div class="card shadow-sm border-0">

                                <!-- CARD HEADER -->
                                <div class="card-header bg-success text-white py-3">
                                    <h4 class="mb-0">Add New Staff</h4>
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

                                        <form action="proc_add_staff.php" method="POST" enctype="multipart/form-data">

                                            <div class="row">

                                                <!-- Staff ID -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Staff ID</label>
                                                    <input type="text" name="staff_id" class="form-control form-control-lg" required>
                                                </div>

                                                <!-- Full Name -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Full Name</label>
                                                    <input type="text" name="fullname" class="form-control form-control-lg" required>
                                                </div>

                                                <!-- Email -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Email</label>
                                                    <input type="email" name="email" class="form-control form-control-lg" required>
                                                </div>

                                                <!-- Phone -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Phone</label>
                                                    <input type="text" name="phone" class="form-control form-control-lg">
                                                </div>

                                                <!-- Gender -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Gender</label>
                                                    <select name="gender" class="form-select form-control-lg">
                                                        <option value="">-- Select --</option>
                                                        <option value="male">Male</option>
                                                        <option value="female">Female</option>
                                                    </select>
                                                </div>

                                                <!-- DOB -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Date of Birth</label>
                                                    <input type="date" name="dob" class="form-control form-control-lg">
                                                </div>

                                                <!-- Passport -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Passport</label>
                                                    <input type="file" name="passport" class="form-control form-control-lg" accept="image/*">
                                                </div>

                                                <!-- Branch -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Branch</label>

                                                    <select name="branch_id" class="form-select form-control-lg" required>
                                                        <option value="">-- Select Branch --</option>

                                                        <?php
                                                        /** @var mysqli $conn */
                                                        include('./db.php');
                                                        $branch_query = mysqli_query($conn, "SELECT * FROM branches ORDER BY branch_name ASC");

                                                        while ($branch = mysqli_fetch_assoc($branch_query)) {
                                                        ?>
                                                            <option value="<?php echo $branch['id']; ?>">
                                                                <?php echo $branch['branch_name']; ?>
                                                            </option>
                                                        <?php
                                                        }
                                                        ?>

                                                    </select>
                                                </div>

                                                <!-- Department -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Department</label>

                                                    <select name="department" class="form-select form-control-lg">
                                                        <option value="">-- Select Department --</option>

                                                        <option value="human_resources">Human Resources</option>
                                                        <option value="information_technology">Information Technology</option>
                                                        <option value="finance">Finance</option>
                                                        <option value="accounts">Accounts</option>
                                                        <option value="administration">Administration</option>
                                                        <option value="medical_unit">Medical Unit</option>
                                                        <option value="operations">Operations</option>
                                                        <option value="customer_service">Customer Service</option>
                                                        <option value="security">Security</option>
                                                        <option value="legal">Legal</option>
                                                        <option value="marketing">Marketing</option>
                                                        <option value="sales">Sales</option>
                                                        <option value="procurement">Procurement</option>
                                                        <option value="logistics">Logistics</option>
                                                        <option value="maintenance">Maintenance</option>
                                                        <option value="engineering">Engineering</option>
                                                        <option value="audit">Audit</option>
                                                        <option value="compliance">Compliance</option>
                                                        <option value="research_and_development">Research & Development</option>
                                                        <option value="training">Training</option>

                                                    </select>
                                                </div>

                                                <!-- Role -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Role</label>
                                                    <input type="text" name="role" class="form-control form-control-lg">
                                                </div>

                                                <!-- Employment Type -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Employment Type</label>

                                                    <select name="employment_type" class="form-select form-control-lg">
                                                        <option value="">-- Select Employment Type --</option>

                                                        <option value="full_time">Full Time</option>
                                                        <option value="part_time">Part Time</option>
                                                        <option value="contract">Contract</option>
                                                        <option value="temporary">Temporary</option>
                                                        <option value="intern">Intern</option>
                                                        <option value="casual">Casual</option>
                                                        <option value="remote">Remote</option>
                                                        <option value="hybrid">Hybrid</option>
                                                        <option value="freelance">Freelance</option>
                                                        <option value="probation">Probation</option>

                                                    </select>
                                                </div>

                                                <!-- Hire Date -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Hire Date</label>
                                                    <input type="date" name="hire_date" class="form-control form-control-lg">
                                                </div>

                                                <!-- Status -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Status</label>
                                                    <select name="status" class="form-select form-control-lg">
                                                        <option value="active">Active</option>
                                                        <option value="inactive">Inactive</option>
                                                        <option value="suspended">Suspended</option>
                                                        <option value="terminated">Terminated</option>
                                                    </select>
                                                </div>

                                                <!-- Address -->
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label fw-bold">Address</label>
                                                    <textarea name="address" class="form-control form-control-lg" rows="2"></textarea>
                                                </div>

                                                <!-- Next of Kin -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Next of Kin</label>
                                                    <input type="text" name="next_of_kin" class="form-control form-control-lg">
                                                </div>

                                                <!-- Next of Kin Phone -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Next of Kin Phone</label>
                                                    <input type="text" name="next_of_kin_phone" class="form-control form-control-lg">
                                                </div>

                                                <!-- Blood Group -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Blood Group</label>
                                                    <input type="text" name="blood_group" class="form-control form-control-lg">
                                                </div>

                                                <!-- Genotype -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Genotype</label>
                                                    <input type="text" name="genotype" class="form-control form-control-lg">
                                                </div>

                                                <!-- Last Medical Checkup -->
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-bold">Last Medical Checkup</label>
                                                    <input type="date" name="last_medical_checkup" class="form-control form-control-lg">
                                                </div>

                                                <!-- Allergies -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Allergies</label>
                                                    <textarea name="allergies" class="form-control form-control-lg" rows="2"></textarea>
                                                </div>

                                                <!-- Medical Conditions -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Medical Conditions</label>
                                                    <textarea name="medical_conditions" class="form-control form-control-lg" rows="2"></textarea>
                                                </div>

                                                <!-- Emergency Contact Name -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Emergency Contact Name</label>
                                                    <input type="text" name="emergency_contact_name" class="form-control form-control-lg">
                                                </div>

                                                <!-- Emergency Contact Phone -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Emergency Contact Phone</label>
                                                    <input type="text" name="emergency_contact_phone" class="form-control form-control-lg">
                                                </div>

                                                <!-- Fitness Status -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Fitness Status</label>
                                                    <select name="fitness_status" class="form-select form-control-lg">
                                                        <option value="fit">Fit</option>
                                                        <option value="unfit">Unfit</option>
                                                        <option value="under_observation">Under Observation</option>
                                                    </select>
                                                </div>

                                            </div>

                                            <!-- BUTTON -->
                                            <div class="mt-4">
                                                <button type="submit" name="submit" class="btn btn-success shadow-sm">
                                                    Save Staff
                                                </button>
                                            </div>

                                        </form>

                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </section>
                <!-- Add Staff Section End -->
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