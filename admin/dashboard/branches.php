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
<style>
    /* Modern Branches Table Theme */
    .custom-table-container {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        border: 1px solid #eef2f6;
        overflow-x: auto;
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.875rem;
        color: #334155;
    }

    .modern-table thead th {
        background-color: #f8fafc;
        color: #475569;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.725rem;
        letter-spacing: 0.05em;
        padding: 14px 16px;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .modern-table tbody tr {
        transition: all 0.2s ease;
    }

    .modern-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .modern-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    /* Row Counter Badge */
    .sn-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 6px;
        background: #f1f5f9;
        color: #475569;
        font-weight: 600;
        border: 1px solid #e2e8f0;
        font-size: 0.78rem;
    }

    /* Passport Avatar Container */
    .passport-container {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .passport-frame {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        overflow: hidden;
        background: #f1f5f9;
        border: 2px solid #fff;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
        transition: transform 0.2s;
    }

    .passport-frame:hover {
        transform: scale(1.08);
    }

    .passport-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Modern Soft Badges */
    .badge-soft {
        padding: 5px 12px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        text-transform: capitalize;
    }

    .badge-soft-hq {
        background: #e0f2fe;
        color: #0369a1;
    }

    .badge-soft-branch {
        background: #f0fdf4;
        color: #166534;
    }

    .badge-soft-clinic {
        background: #faf5ff;
        color: #6b21a8;
    }

    .badge-soft-secondary {
        background: #f1f5f9;
        color: #475569;
    }

    /* Action Buttons Layout */
    .action-btns {
        display: flex;
        gap: 6px;
    }

    .btn-icon-sm {
        padding: 5px 12px;
        font-size: 0.78rem;
        border-radius: 8px;
        font-weight: 500;
        transition: transform 0.15s ease;
    }

    .btn-icon-sm:hover {
        transform: translateY(-1px);
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
                                        <div class="custom-table-container">
                                            <table class="modern-table mb-0" style="min-width: 1200px;">
                                                <thead>
                                                    <tr>
                                                        <th class="small-field">SN</th>
                                                        <th class="medium-field">BRANCH NAME</th>
                                                        <th class="small-field">STATE</th>
                                                        <th class="medium-field">MEDICAL HEAD</th>
                                                        <th class="medium-field">EMAIL ADDRESS</th>
                                                        <th class="small-field text-center">PASSPORT</th>
                                                        <th class="small-field">TYPE</th>
                                                        <th class="medium-field">DATE CREATED</th>
                                                        <th class="medium-field text-end">ACTION</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    <?php
                                                    /** @var mysqli $conn */
                                                    include('./db.php');

                                                    // Ensure session parameters are active
                                                    if (session_status() === PHP_SESSION_NONE) {
                                                        session_start();
                                                    }

                                                    if (!function_exists('encryptId')) {
                                                        function encryptId($id)
                                                        {
                                                            $key = "medical-secret-key";
                                                            return rtrim(strtr(base64_encode($id . '|' . $key), '+/', '-_'), '=');
                                                        }
                                                    }

                                                    if (!function_exists('decryptId')) {
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
                                                    }

                                                    $sql = "SELECT * FROM branches ORDER BY id DESC";
                                                    $result = mysqli_query($conn, $sql);

                                                    if ($result && mysqli_num_rows($result) > 0) {
                                                        $sn = 1; // Tracks loop records cleanly

                                                        while ($row = mysqli_fetch_assoc($result)) {
                                                            $branch_type = strtolower($row['type']);

                                                            // Dynamically pick stylized color pills depending on branch structural types
                                                            if ($branch_type === 'hq' || $branch_type === 'headquarters') {
                                                                $type_class = 'badge-soft-hq';
                                                            } elseif ($branch_type === 'clinic') {
                                                                $type_class = 'badge-soft-clinic';
                                                            } else {
                                                                $type_class = 'badge-soft-branch';
                                                            }
                                                    ?>
                                                            <tr>
                                                                <!-- Dynamic Row Counter -->
                                                                <td>
                                                                    <span class="sn-badge">#<?php echo sprintf('%02d', $sn++); ?></span>
                                                                </td>

                                                                <!-- Branch Name -->
                                                                <td><strong><?php echo htmlspecialchars($row['branch_name']); ?></strong></td>

                                                                <!-- State -->
                                                                <td><span class="text-muted fw-semibold"><?php echo htmlspecialchars($row['state']); ?></span></td>

                                                                <!-- Medical Head -->
                                                                <td><span class="text-dark fw-medium"><?php echo htmlspecialchars($row['medical_head']); ?></span></td>

                                                                <!-- Email -->
                                                                <td><span class="text-muted"><?php echo htmlspecialchars($row['medical_head_email']); ?></span></td>

                                                                <!-- Passport Frame -->
                                                                <td>
                                                                    <div class="passport-container">
                                                                        <?php if (!empty($row['medical_head_passport']) && file_exists("uploads/" . $row['medical_head_passport'])) { ?>
                                                                            <div class="passport-frame">
                                                                                <img src="uploads/<?php echo htmlspecialchars($row['medical_head_passport']); ?>"
                                                                                    alt="Head Passport"
                                                                                    loading="lazy">
                                                                            </div>
                                                                        <?php } else { ?>
                                                                            <span class="badge-soft badge-soft-secondary">No Photo</span>
                                                                        <?php } ?>
                                                                    </div>
                                                                </td>

                                                                <!-- Styled Type Pill -->
                                                                <td>
                                                                    <span class="badge-soft <?php echo $type_class; ?>">
                                                                        <?php echo htmlspecialchars($row['type']); ?>
                                                                    </span>
                                                                </td>

                                                                <!-- Clean Timestamp Format -->
                                                                <td>
                                                                    <small class="text-muted fw-medium">
                                                                        <?php echo date("Y-m-d H:i", strtotime($row['created_at'])); ?>
                                                                    </small>
                                                                </td>

                                                                <!-- Action Triggers -->
                                                                <td class="text-end">
                                                                    <div class="action-btns justify-content-end">
                                                                        <a href="edit_branch.php?id=<?php echo urlencode(encryptId($row['id'])); ?>"
                                                                            class="btn btn-primary btn-icon-sm">
                                                                            Edit
                                                                        </a>
                                                                        <a href="delete_branch.php?id=<?php echo urlencode(encryptId($row['id'])); ?>"
                                                                            class="btn btn-danger btn-icon-sm"
                                                                            onclick="return confirm('Are you sure you want to delete this branch?');">
                                                                            Delete
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php
                                                        }
                                                    } else {
                                                        ?>
                                                        <tr>
                                                            <td colspan="9" class="text-center text-muted py-5">
                                                                <div class="py-3">No operational branches registered down in system infrastructure.</div>
                                                            </td>
                                                        </tr>
                                                    <?php
                                                    }
                                                    mysqli_close($conn);
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
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