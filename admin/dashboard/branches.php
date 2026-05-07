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
    <title>Branches - Medical Unit</title>

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
                    <div class="row">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3>Branches</h3>
                            <p class="text-subtitle text-muted">
                                Manage and view all branches and their assigned medical heads..
                            </p>
                        </div>

                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Branches</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Hoverable rows start -->
                <section class="section">
                    <div class="row" id="table-hover-row">
                        <div class="col-12">
                            <div class="card">

                                <!-- HEADER -->
                                <div class="card-header d-flex justify-content-between align-items-center px-4 py-3">

                                    <h4 class="card-title mb-0">Branches List</h4>

                                    <a href="add_branch.php" class="btn btn-success btn-sm px-3 py-2">
                                        + ADD BRANCH
                                    </a>

                                </div>

                                <div class="card-content">
                                    <div class="card-body px-4 py-2">
                                        <p class="mb-0">List of all branches with assigned medical heads and details.</p>
                                    </div>

                                    <!-- TABLE WRAPPER -->
                                    <div class="table-responsive px-3 pb-3">

                                        <table class="table table-hover align-middle mb-0"
                                            style="min-width: 1200px;">

                                            <thead class="table-light">
                                                <tr>
                                                    <th class="py-3 px-3">SN</th>
                                                    <th class="py-3 px-3">BRANCH NAME</th>
                                                    <th class="py-3 px-3">STATE</th>
                                                    <th class="py-3 px-3">MEDICAL HEAD</th>
                                                    <th class="py-3 px-3">EMAIL</th>
                                                    <th class="py-3 px-3">PASSPORT</th>
                                                    <th class="py-3 px-3">TYPE</th>
                                                    <th class="py-3 px-3">DATE CREATED</th>
                                                    <th class="py-3 px-3">ACTION</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php
                                                $conn = mysqli_connect(
                                                    "localhost",
                                                    "medical-unit",
                                                    "Medical--Unit2026$$",
                                                    "medical-unit"
                                                );

                                                if (!$conn) {
                                                    die("Connection failed: " . mysqli_connect_error());
                                                }

                                                /* =========================
   ID ENCRYPTION FUNCTIONS
========================= */
                                                function encryptId($id)
                                                {
                                                    $key = "medical-secret-key";
                                                    return rtrim(strtr(base64_encode($id . '|' . $key), '+/', '-_'), '=');
                                                }

                                                function decryptId($hash)
                                                {
                                                    $key = "medical-secret-key";
                                                    $decoded = base64_decode(strtr($hash, '-_', '+/'));
                                                    $parts = explode('|', $decoded);

                                                    if (count($parts) !== 2 || $parts[1] !== $key) {
                                                        return false;
                                                    }

                                                    return $parts[0];
                                                }

                                                $sql = "SELECT * FROM branches ORDER BY id DESC";
                                                $result = mysqli_query($conn, $sql);

                                                if ($result && mysqli_num_rows($result) > 0) {

                                                    $sn = 1;

                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                ?>
                                                        <tr>

                                                            <!-- SN -->
                                                            <td class="px-3 py-3"><?php echo $sn++; ?></td>

                                                            <!-- BRANCH -->
                                                            <td class="px-3 py-3">
                                                                <?php echo htmlspecialchars($row['branch_name']); ?>
                                                            </td>

                                                            <!-- STATE -->
                                                            <td class="px-3 py-3">
                                                                <?php echo htmlspecialchars($row['state']); ?>
                                                            </td>

                                                            <!-- MEDICAL HEAD -->
                                                            <td class="px-3 py-3">
                                                                <?php echo htmlspecialchars($row['medical_head']); ?>
                                                            </td>

                                                            <!-- EMAIL -->
                                                            <td class="px-3 py-3">
                                                                <?php echo htmlspecialchars($row['medical_head_email']); ?>
                                                            </td>

                                                            <!-- PASSPORT -->
                                                            <td class="px-3 py-3 text-center align-middle">

                                                                <?php if (!empty($row['medical_head_passport'])) { ?>

                                                                    <img src="uploads/<?php echo htmlspecialchars($row['medical_head_passport']); ?>"
                                                                        alt="Passport"
                                                                        width="100"
                                                                        height="100"
                                                                        loading="lazy"
                                                                        style="border-radius:10px; object-fit:cover;">

                                                                <?php } else { ?>

                                                                    <span class="badge bg-secondary">No Image</span>

                                                                <?php } ?>

                                                            </td>

                                                            <!-- TYPE -->
                                                            <td class="px-3 py-3">
                                                                <?php echo htmlspecialchars($row['type']); ?>
                                                            </td>

                                                            <!-- DATE -->
                                                            <td class="px-3 py-3 text-nowrap">
                                                                <?php echo date("Y-m-d H:i:s", strtotime($row['created_at'])); ?>
                                                            </td>

                                                            <!-- ACTION (ENCRYPTED ID) -->
                                                            <td class="px-3 py-3 text-nowrap">

                                                                <a href="edit_branch.php?id=<?php echo urlencode(encryptId($row['id'])); ?>"
                                                                    class="btn btn-primary btn-sm me-1 px-3">
                                                                    Edit
                                                                </a>

                                                                <a href="delete_branch.php?id=<?php echo urlencode(encryptId($row['id'])); ?>"
                                                                    class="btn btn-danger btn-sm px-3"
                                                                    onclick="return confirm('Are you sure you want to delete this branch?');">
                                                                    Delete
                                                                </a>

                                                            </td>

                                                        </tr>
                                                    <?php
                                                    }
                                                } else {
                                                    ?>
                                                    <tr>
                                                        <td colspan="9" class="text-center text-muted py-4">
                                                            No branches found
                                                        </td>
                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>

                                        </table>

                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                </section>
                <!-- Hoverable rows end -->
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