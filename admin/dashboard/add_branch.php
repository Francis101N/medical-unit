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
    <title>Add Branch - Medical Unit</title>

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
                            <h3 class="mb-1">Add New Branch</h3>
                            <p class="text-subtitle text-muted mb-0">
                                Create a new branch and assign a medical head with their details and records.
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
                                        <a href="branches.php">Branches</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        Add Branch
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div> <br>

                <!-- Add Branch Section -->
                <section class="section">
                    <div class="row justify-content-center" id="table-hover-row">
                        <div class="col-12 col-lg-8">

                            <div class="card shadow-sm border-0">

                                <!-- CARD HEADER -->
                                <div class="card-header bg-success text-white py-3">
                                    <h4 class="mb-0">Add New Branch</h4>
                                </div>

                                <!-- CARD BODY -->
                                <div class="card-content">
                                    <div class="card-body p-4">

                                        <form action="proc_add_branch.php" method="POST" enctype="multipart/form-data">

                                            <!-- Branch Name -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-bold">Branch Name</label>
                                                <input type="text" name="branch_name"
                                                    class="form-control form-control-lg shadow-sm"
                                                    placeholder="Enter branch name" required>
                                            </div>

                                            <!-- State -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-bold">State</label>
                                                <input type="text" name="state"
                                                    class="form-control form-control-lg shadow-sm"
                                                    placeholder="Enter state" required>
                                            </div>

                                            <!-- Medical Head -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-bold">Medical Head</label>
                                                <input type="text" name="medical_head"
                                                    class="form-control form-control-lg shadow-sm"
                                                    placeholder="Enter medical head name" required>
                                            </div>

                                            <!-- Email -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-bold">Medical Head Email</label>
                                                <input type="email" name="medical_head_email"
                                                    class="form-control form-control-lg shadow-sm"
                                                    placeholder="Enter email" required>
                                            </div>

                                            <!-- Type -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-bold">Type</label>
                                                <select name="type"
                                                    class="form-select form-control-lg shadow-sm"
                                                    required>
                                                    <option value="">-- Select Type --</option>
                                                    <option value="onshore">onshore</option>
                                                    <option value="offshore">offshore</option>
                                                </select>
                                            </div>

                                            <!-- Passport -->
                                            <div class="form-group mb-4">
                                                <label class="form-label fw-bold">Passport Image</label>

                                                <input type="file" name="passport"
                                                    class="form-control form-control-lg shadow-sm"
                                                    accept="image/*"
                                                    onchange="previewPassport(event)" required>

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
                                                    Save Branch
                                                </button>
                                            </div>

                                        </form>

                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </section>
                <!-- Add Branch Section End -->
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