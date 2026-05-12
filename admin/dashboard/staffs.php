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
    <title>Staffs - Medical Unit</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.css">

    <link rel="stylesheet" href="assets/vendors/simple-datatables/style.css">

    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
</head>
<style>
    #table1 {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        white-space: nowrap;
    }

    #table1 thead th {
        padding: 20px 28px;
        font-size: 15px;
        font-weight: 700;
        background: #f8f9fa;
        vertical-align: middle;
        min-width: 180px;
    }

    #table1 tbody td {
        padding: 22px 28px;
        font-size: 15px;
        line-height: 1.7;
        vertical-align: middle;
        min-width: 180px;
    }

    #table1 tbody tr {
        height: 110px;
    }

    #table1 img {
        width: 75px;
        height: 75px;
        object-fit: cover;
        border-radius: 50%;
    }

    #table1 .btn {
        width: 100%;
        margin-bottom: 8px;
    }

    .table-responsive {
        overflow-x: auto;
        padding-bottom: 15px;
    }

    .wide-field {
        min-width: 320px !important;
        white-space: normal !important;
    }

    .medium-field {
        min-width: 240px !important;
    }

    .small-field {
        min-width: 140px !important;
    }

    #table1 th:last-child,
    #table1 td:last-child {
        min-width: 180px;
        width: 180px;
        white-space: normal !important;
    }

    .action-btns {
        display: flex;
        flex-direction: column;
        gap: 6px;
        align-items: stretch;
    }

    .action-btns .btn {
        width: 100%;
        margin: 0;
    }
</style>

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
                    <div class="row">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3>Staff Management</h3>
                            <p class="text-subtitle text-muted">
                                View, manage, and monitor all registered staff records and information.
                            </p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Staffs</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                <section class="section">

                    <div class="card">

                        <!-- HEADER -->
                        <div class="card-header d-flex justify-content-between align-items-center px-4 py-3">

                            <h4 class="card-title mb-0">Staff Records</h4>

                            <a href="add_staff.php" class="btn btn-success btn-sm px-3 py-2">
                                + ADD STAFF
                            </a>

                        </div>

                        <div class="table-responsive">

                            <table class="table table-striped table-bordered" id="table1">

                                <thead>

                                    <tr>

                                        <th class="small-field">ID</th>
                                        <th class="medium-field">Staff ID</th>
                                        <th class="small-field">Passport</th>
                                        <th class="medium-field">Full Name</th>
                                        <th class="wide-field">Email</th>
                                        <th class="medium-field">Phone</th>
                                        <th class="small-field">Gender</th>
                                        <th class="medium-field">Date of Birth</th>

                                        <th class="small-field">Branch ID</th>
                                        <th class="medium-field">Department</th>
                                        <th class="medium-field">Role</th>
                                        <th class="medium-field">Employment Type</th>
                                        <th class="medium-field">Hire Date</th>
                                        <th class="small-field">Status</th>

                                        <th class="wide-field">Address</th>

                                        <th class="medium-field">Next of Kin</th>
                                        <th class="medium-field">Next of Kin Phone</th>

                                        <th class="small-field">Blood Group</th>
                                        <th class="small-field">Genotype</th>

                                        <th class="wide-field">Allergies</th>
                                        <th class="wide-field">Medical Conditions</th>

                                        <th class="medium-field">Emergency Contact Name</th>
                                        <th class="medium-field">Emergency Contact Phone</th>

                                        <th class="medium-field">Last Medical Checkup</th>
                                        <th class="medium-field">Fitness Status</th>

                                        <th class="medium-field">Created At</th>
                                        <th class="medium-field">Updated At</th>

                                        <th class="medium-field">Actions</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <?php

                                    /** @var mysqli $conn */
                                    include('./db.php');

                                    $select_staff = mysqli_query($conn, "SELECT * FROM staffs ORDER BY id DESC");

                                    if (mysqli_num_rows($select_staff) > 0) {

                                        while ($row = mysqli_fetch_assoc($select_staff)) {

                                            $id = $row['id'];
                                            $staff_id = $row['staff_id'];
                                            $fullname = $row['fullname'];
                                            $email = $row['email'];
                                            $phone = $row['phone'];
                                            $gender = $row['gender'];
                                            $dob = $row['dob'];
                                            $passport = $row['passport'];

                                            $branch_id = $row['branch_id'];
                                            $department = $row['department'];
                                            $role = $row['role'];
                                            $employment_type = $row['employment_type'];
                                            $hire_date = $row['hire_date'];
                                            $status = $row['status'];

                                            $address = $row['address'];

                                            $next_of_kin = $row['next_of_kin'];
                                            $next_of_kin_phone = $row['next_of_kin_phone'];

                                            $blood_group = $row['blood_group'];
                                            $genotype = $row['genotype'];
                                            $allergies = $row['allergies'];
                                            $medical_conditions = $row['medical_conditions'];

                                            $emergency_contact_name = $row['emergency_contact_name'];
                                            $emergency_contact_phone = $row['emergency_contact_phone'];

                                            $last_medical_checkup = $row['last_medical_checkup'];
                                            $fitness_status = $row['fitness_status'];

                                            $created_at = $row['created_at'];
                                            $updated_at = $row['updated_at'];

                                            // Staff Status Badge
                                            if ($status == "active") {
                                                $status_badge = "success";
                                            } elseif ($status == "inactive") {
                                                $status_badge = "secondary";
                                            } elseif ($status == "suspended") {
                                                $status_badge = "warning";
                                            } else {
                                                $status_badge = "danger";
                                            }

                                            // Fitness Badge
                                            if ($fitness_status == "fit") {
                                                $fitness_badge = "success";
                                            } elseif ($fitness_status == "under_observation") {
                                                $fitness_badge = "warning";
                                            } else {
                                                $fitness_badge = "danger";
                                            }

                                    ?>

                                            <tr>

                                                <td><?php echo $id; ?></td>

                                                <td><?php echo $staff_id; ?></td>

                                                <td>

                                                    <?php if (!empty($passport)) { ?>

                                                        <img src="uploads/<?php echo $passport; ?>" style="" >

                                                    <?php } else { ?>

                                                        <span class="text-muted">No Image</span>

                                                    <?php } ?>

                                                </td>

                                                <td><?php echo $fullname; ?></td>

                                                <td class="wide-field"><?php echo $email; ?></td>

                                                <td><?php echo $phone; ?></td>

                                                <td><?php echo ucfirst($gender); ?></td>

                                                <td><?php echo $dob; ?></td>

                                                <td><?php echo $branch_id; ?></td>

                                                <td><?php echo $department; ?></td>

                                                <td><?php echo $role; ?></td>

                                                <td><?php echo ucfirst($employment_type); ?></td>

                                                <td><?php echo $hire_date; ?></td>

                                                <td>
                                                    <span class="badge bg-<?php echo $status_badge; ?>">
                                                        <?php echo ucfirst($status); ?>
                                                    </span>
                                                </td>

                                                <td class="wide-field">
                                                    <?php echo $address; ?>
                                                </td>

                                                <td><?php echo $next_of_kin; ?></td>

                                                <td><?php echo $next_of_kin_phone; ?></td>

                                                <td><?php echo $blood_group; ?></td>

                                                <td><?php echo $genotype; ?></td>

                                                <td class="wide-field">
                                                    <?php echo $allergies; ?>
                                                </td>

                                                <td class="wide-field">
                                                    <?php echo $medical_conditions; ?>
                                                </td>

                                                <td><?php echo $emergency_contact_name; ?></td>

                                                <td><?php echo $emergency_contact_phone; ?></td>

                                                <td><?php echo $last_medical_checkup; ?></td>

                                                <td>
                                                    <span class="badge bg-<?php echo $fitness_badge; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $fitness_status)); ?>
                                                    </span>
                                                </td>

                                                <td><?php echo $created_at; ?></td>

                                                <td><?php echo $updated_at; ?></td>

                                                <td>

                                                    <a href="edit_staff.php?id=<?php echo $id; ?>"
                                                        class="btn btn-primary btn-sm">
                                                        Edit
                                                    </a>

                                                    <a href="delete_staff.php?id=<?php echo $id; ?>"
                                                        class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Are you sure you want to delete this staff record?');">
                                                        Delete
                                                    </a>

                                                </td>

                                            </tr>

                                    <?php

                                        }
                                    }

                                    ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </section>
            </div>

            <?php
            include('./inc/footer.php');
            ?>
        </div>
    </div>
    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script src="assets/vendors/simple-datatables/simple-datatables.js"></script>
    <script>
        // Simple Datatable
        let table1 = document.querySelector('#table1');
        let dataTable = new simpleDatatables.DataTable(table1);
    </script>

    <script src="assets/js/main.js"></script>
</body>

</html>