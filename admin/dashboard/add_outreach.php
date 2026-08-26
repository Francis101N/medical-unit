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

// Include database connection
/** @var mysqli $conn */
include('db.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Outreach - Medical Unit</title>

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
                            <h3 class="mb-1">Add New Outreach</h3>
                            <p class="text-subtitle text-muted mb-0">
                                Create a new operational outreach project, location, schedule duration, and timeline dates.
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
                                        <a href="outreach.php">Outreach</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        Add Outreach
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div> <br>

                <!-- Add Outreach Section -->
                <section class="section">
                    <div class="row justify-content-center" id="table-hover-row">
                        <div class="col-12 col-lg-8">

                            <div class="card shadow-sm border-0">

                                <!-- CARD HEADER -->
                                <div class="card-header bg-success text-white py-3">
                                    <h4 class="mb-0">Add New Outreach Details</h4>
                                </div>

                                <?php
                                // Retrieve message and message type from session if available, otherwise fallback to local variables
                                $msg = $_SESSION['msg'] ?? ($msg ?? '');
                                $msg_type = $_SESSION['msg_type'] ?? ($msg_type ?? 'success');

                                // Clear session messages so they disappear on page reload
                                if (isset($_SESSION['msg'])) {
                                    unset($_SESSION['msg']);
                                    unset($_SESSION['msg_type']);
                                }

                                // Only show alert if message exists
                                if (!empty($msg)) {
                                ?>
                                    <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible m-3 show fade" role="alert">
                                        <?php echo $msg; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php
                                }
                                ?>

                                <!-- CARD BODY -->
                                <div class="card-content">
                                    <div class="card-body p-4">

                                        <form action="proc_add_outreach.php" method="POST">

                                            <!-- Project Title -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-bold">Project Title</label>
                                                <input type="text" name="project_title"
                                                    class="form-control form-control-lg shadow-sm"
                                                    placeholder="Enter outreach project title" required>
                                            </div>

                                            <!-- Location -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-bold">Location</label>
                                                <input type="text" name="location"
                                                    class="form-control form-control-lg shadow-sm"
                                                    placeholder="Enter outreach location" required>
                                            </div>

                                            <!-- Duration -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-bold">Duration</label>
                                                <input type="text" name="duration"
                                                    class="form-control form-control-lg shadow-sm"
                                                    placeholder="e.g., 3 Days, 1 Week, 2 Months" required>
                                            </div>

                                            <!-- Start Date & End Date Row -->
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-4">
                                                        <label class="form-label fw-bold">Start Date</label>
                                                        <input type="date" name="start_date"
                                                            class="form-control form-control-lg shadow-sm" >
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-4">
                                                        <label class="form-label fw-bold">End Date</label>
                                                        <input type="date" name="end_date"
                                                            class="form-control form-control-lg shadow-sm" >
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- BUTTON -->
                                            <div class="mt-4">
                                                <button type="submit" name="submit"
                                                    class="btn btn-success shadow-sm px-4 py-2">
                                                    Save Outreach
                                                </button>
                                            </div>

                                        </form>

                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </section>
                <!-- Add Outreach Section End -->
            </div>

            <?php
            include('./inc/footer.php');
            ?>
        </div>
    </div>

    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>