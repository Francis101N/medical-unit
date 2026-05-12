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

                                        if (!isset($_GET['id'])) {
                                            header("Location: staffs.php");
                                            exit();
                                        }

                                        $id = $_GET['id'];

                                        $get_staff = mysqli_query($conn, "SELECT * FROM staffs WHERE id='$id'");

                                        if (mysqli_num_rows($get_staff) == 0) {
                                            header("Location: staffs.php");
                                            exit();
                                        }

                                        $staff = mysqli_fetch_assoc($get_staff);
                                        ?>

                                        <form action="proc_edit_staff.php" method="POST" enctype="multipart/form-data">

                                            <!-- HIDDEN ID -->
                                            <input type="hidden" name="id" value="<?php echo $staff['id']; ?>">

                                            <div class="row">

                                                <!-- Staff ID -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Staff ID</label>
                                                    <input type="text" name="staff_id"
                                                        value="<?php echo $staff['staff_id']; ?>"
                                                        class="form-control form-control-lg" required>
                                                </div>

                                                <!-- Full Name -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Full Name</label>
                                                    <input type="text" name="fullname"
                                                        value="<?php echo $staff['fullname']; ?>"
                                                        class="form-control form-control-lg" required>
                                                </div>

                                                <!-- Email -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Email</label>
                                                    <input type="email" name="email"
                                                        value="<?php echo $staff['email']; ?>"
                                                        class="form-control form-control-lg" required>
                                                </div>

                                                <!-- Phone -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Phone</label>
                                                    <input type="text" name="phone"
                                                        value="<?php echo $staff['phone']; ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- Gender -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Gender</label>

                                                    <select name="gender" class="form-select form-control-lg">

                                                        <option value="male"
                                                            <?php if ($staff['gender'] == 'male') {
                                                                echo 'selected';
                                                            } ?>>
                                                            Male
                                                        </option>

                                                        <option value="female"
                                                            <?php if ($staff['gender'] == 'female') {
                                                                echo 'selected';
                                                            } ?>>
                                                            Female
                                                        </option>

                                                    </select>
                                                </div>

                                                <!-- DOB -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Date of Birth</label>
                                                    <input type="date" name="dob"
                                                        value="<?php echo $staff['dob']; ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- Passport -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Passport</label>

                                                    <input type="file" name="passport"
                                                        class="form-control form-control-lg">

                                                    <br>

                                                    <?php if (!empty($staff['passport'])) { ?>
                                                        <img src="uploads/<?php echo $staff['passport']; ?>"
                                                            width="200" height="200"
                                                            class="rounded">
                                                    <?php } ?>
                                                </div>

                                                <!-- Branch -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Branch</label>

                                                    <select name="branch_id" class="form-select form-control-lg">

                                                        <option value="">-- Select Branch --</option>

                                                        <?php
                                                        $branch_query = mysqli_query($conn, "SELECT * FROM branches ORDER BY branch_name ASC");

                                                        while ($branch = mysqli_fetch_assoc($branch_query)) {
                                                        ?>

                                                            <option value="<?php echo $branch['id']; ?>"
                                                                <?php if ($staff['branch_id'] == $branch['id']) {
                                                                    echo 'selected';
                                                                } ?>>

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

                                                            <option value="<?php echo $value; ?>"
                                                                <?php if ($staff['department'] == $value) {
                                                                    echo 'selected';
                                                                } ?>>

                                                                <?php echo $label; ?>

                                                            </option>

                                                        <?php
                                                        }
                                                        ?>

                                                    </select>
                                                </div>

                                                <!-- Role -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Role</label>
                                                    <input type="text" name="role"
                                                        value="<?php echo $staff['role']; ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- Employment Type -->
                                                <div class="col-md-6 mb-3">
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

                                                            <option value="<?php echo $value; ?>"
                                                                <?php if ($staff['employment_type'] == $value) {
                                                                    echo 'selected';
                                                                } ?>>

                                                                <?php echo $label; ?>

                                                            </option>

                                                        <?php
                                                        }
                                                        ?>

                                                    </select>
                                                </div>

                                                <!-- Hire Date -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Hire Date</label>
                                                    <input type="date" name="hire_date"
                                                        value="<?php echo $staff['hire_date']; ?>"
                                                        class="form-control form-control-lg">
                                                </div>

                                                <!-- Status -->
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Status</label>

                                                    <select name="status" class="form-select form-control-lg">

                                                        <option value="active"
                                                            <?php if ($staff['status'] == 'active') {
                                                                echo 'selected';
                                                            } ?>>
                                                            Active
                                                        </option>

                                                        <option value="inactive"
                                                            <?php if ($staff['status'] == 'inactive') {
                                                                echo 'selected';
                                                            } ?>>
                                                            Inactive
                                                        </option>

                                                        <option value="suspended"
                                                            <?php if ($staff['status'] == 'suspended') {
                                                                echo 'selected';
                                                            } ?>>
                                                            Suspended
                                                        </option>

                                                        <option value="terminated"
                                                            <?php if ($staff['status'] == 'terminated') {
                                                                echo 'selected';
                                                            } ?>>
                                                            Terminated
                                                        </option>

                                                    </select>
                                                </div>

                                            </div>

                                            <!-- BUTTON -->
                                            <div class="mt-4">
                                                <button type="submit" name="submit" class="btn btn-success">
                                                    Update Staff
                                                </button>
                                            </div>

                                        </form>
                                    </div>
                                </div>

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