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

// Include database connection to fetch branches
/** @var mysqli $conn */
include('db.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Medical Unit</title>

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
                            <h3 class="mb-1">Add New User</h3>
                            <p class="text-subtitle text-muted mb-0">
                                Create a new user account and assign credentials, role, and branch deployment.
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
                                        Add User
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div> <br>

                <!-- Add User Section -->
                <section class="section">
                    <div class="row justify-content-center" id="table-hover-row">
                        <div class="col-12 col-lg-8">

                            <div class="card shadow-sm border-0">

                                <!-- CARD HEADER -->
                                <div class="card-header bg-success text-white py-3">
                                    <h4 class="mb-0">Add New User Details</h4>
                                </div> <br>

                                <?php
                                // Ensure variables are always defined
                                $msg = $msg ?? '';
                                $msg_type = $msg_type ?? 'success'; // default is success

                                // Only show alert if message exists
                                if (!empty($msg)) {
                                ?>
                                    <div class="alert m-3 alert-<?php echo $msg_type; ?> alert-dismissible show fade">
                                        <?php echo $msg; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                <?php
                                }
                                ?>

                                <!-- CARD BODY -->
                                <div class="card-content">
                                    <div class="card-body p-4">

                                        <form action="proc_add_user.php" method="POST" enctype="multipart/form-data">

                                            <!-- Full Name -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-bold">Full Name</label>
                                                <input type="text" name="fullname"
                                                    class="form-control form-control-lg shadow-sm"
                                                    placeholder="Enter full name" required>
                                            </div>

                                            <!-- Username -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-bold">Username</label>
                                                <input type="text" name="username"
                                                    class="form-control form-control-lg shadow-sm"
                                                    placeholder="Enter username" required>
                                            </div>

                                            <!-- Password -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-bold">Password</label>
                                                <input type="text" name="password"
                                                    class="form-control form-control-lg shadow-sm font-monospace"
                                                    placeholder="Enter password" required>
                                            </div>

                                            <!-- Email -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-bold">Email Address</label>
                                                <input type="email" name="email"
                                                    class="form-control form-control-lg shadow-sm"
                                                    placeholder="Enter email address" required>
                                            </div>

                                            <!-- Role -->

                                            <input type="text" name="role"
                                                class="form-control form-control-lg shadow-sm"
                                                placeholder="Enter system role (e.g. Doctor, Admin)" hidden>


                                            <!-- Branch Deployment (Dynamic Dropdown) -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-bold">Branch Deployment</label>
                                                <select name="branch" class="form-select form-control-lg shadow-sm" required>
                                                    <option value="">-- Select Branch --</option>
                                                    <?php
                                                    // Fetch branches from the database (adjust column name 'branch_name' if your table uses a different column like 'name')
                                                    $branch_query = mysqli_query($conn, "SELECT DISTINCT branch_name FROM branches ORDER BY branch_name ASC");
                                                    if ($branch_query && mysqli_num_rows($branch_query) > 0) {
                                                        while ($b_row = mysqli_fetch_assoc($branch_query)) {
                                                            $b_name = htmlspecialchars($b_row['branch_name']);
                                                            echo '<option value="' . $b_name . '">' . $b_name . '</option>';
                                                        }
                                                    } else {
                                                        echo '<option value="" disabled>No branches available</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>

                                            <!-- Passport -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-bold">Passport Photo</label>

                                                <input type="file" name="passport"
                                                    class="form-control form-control-lg shadow-sm"
                                                    accept="image/*"
                                                    onchange="previewPassport(event)">

                                                <!-- PREVIEW BOX -->
                                                <div class="mt-3">
                                                    <img id="passportPreview"
                                                        src="#"
                                                        alt="Passport Preview"
                                                        style="display:none; width:120px; height:120px; object-fit:cover; border-radius:10px; border:2px solid #ddd;">
                                                </div>
                                            </div>

                                            <!-- BUTTON -->
                                            <div class="mt-4">
                                                <button type="submit" name="submit"
                                                    class="btn btn-success shadow-sm">
                                                    Save User
                                                </button>
                                            </div>

                                        </form>

                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </section>
                <!-- Add User Section End -->
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