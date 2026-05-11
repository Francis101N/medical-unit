<?php
session_start();

$conn = mysqli_connect("localhost", "medical-unit", "Medical--Unit2026$$", "medical-unit");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

/* =========================
   ID ENCRYPTION SYSTEM
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

/* =========================
   GET & DECRYPT ID
========================= */
$encrypted_id = $_GET['id'] ?? null;

if (!$encrypted_id) {
    die("Invalid request");
}

$id = decryptId($encrypted_id);

if (!$id) {
    die("Invalid or tampered ID");
}

/* =========================
   FETCH DATA
========================= */
$stmt = $conn->prepare("SELECT * FROM branches WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Branch not found");
}

$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Branch - Medical Unit</title>

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
                            <h3 class="mb-1">Edit Branch</h3>
                            <p class="text-subtitle text-muted mb-0">
                                Edit branch and assign a medical head with their details and records.
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
                                        Edit Branch
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
                                    <h4 class="mb-0">Edit Branch</h4>
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

                                        <form action="proc_edit_branch.php" method="POST" enctype="multipart/form-data">

                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                                            <div class="form-group mb-3">
                                                <label>Branch Name</label>
                                                <input type="text" name="branch_name" class="form-control"
                                                    value="<?php echo htmlspecialchars($row['branch_name']); ?>" required>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label>State</label>
                                                <input type="text" name="state" class="form-control"
                                                    value="<?php echo htmlspecialchars($row['state']); ?>" required>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label>Medical Head</label>
                                                <input type="text" name="medical_head" class="form-control"
                                                    value="<?php echo htmlspecialchars($row['medical_head']); ?>" required>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label>Email</label>
                                                <input type="email" name="medical_head_email" class="form-control"
                                                    value="<?php echo htmlspecialchars($row['medical_head_email']); ?>" required>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label>Type</label>
                                                <select name="type" class="form-control" required>
                                                    <option value="onshore" <?php if ($row['type'] == "onshore") echo "selected"; ?>>onshore</option>
                                                    <option value="offshore" <?php if ($row['type'] == "offshore") echo "selected"; ?>>offshore</option>
                                                </select>
                                            </div>

                                            <!-- CURRENT PASSPORT -->
                                            <div class="mb-3">
                                                <label>Current Passport</label><br>
                                                <img src="uploads/<?php echo $row['medical_head_passport']; ?>"
                                                    width="70" height="70"
                                                    style="border-radius:50%; object-fit:cover;">
                                            </div>

                                            <!-- NEW PASSPORT -->
                                            <div class="form-group mb-3">
                                                <label>Change Passport (optional)</label>
                                                <input type="file" name="passport" class="form-control" accept="image/*">
                                            </div>

                                            <button type="submit" class="btn btn-success">
                                                Update Branch
                                            </button>

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