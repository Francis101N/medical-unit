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
    <title>Dashboard - Medical Unit</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.css">

    <link rel="stylesheet" href="assets/vendors/iconly/bold.css">

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
                <h3>Profile Statistics</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12 col-lg-8">

                        <?php
                        /** @var mysqli $conn */
                        include('./db.php');

                        // Ensure session parameters are active
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }

                        $user_role = strtolower($_SESSION['role'] ?? '');
                        $user_branch = $_SESSION['branch'] ?? '';

                        // GLOBAL COUNT: Total Branches is now ALWAYS pulled system-wide regardless of role
                        $total_branches_query = mysqli_query($conn, "SELECT COUNT(*) AS total_branches FROM branches");
                        $total_branches_fetch = mysqli_fetch_assoc($total_branches_query);
                        $total_branches = $total_branches_fetch['total_branches'] ?? 0;

                        // Role-specific counts for Staffs and Ill Staffs
                        if ($user_role === 'super-admin') {
                            // 1. Count total staffs (Global)
                            $total_staffs_query = mysqli_query($conn, "SELECT COUNT(*) AS total_staffs FROM staffs");
                            $total_staffs_fetch = mysqli_fetch_assoc($total_staffs_query);
                            $total_staffs = $total_staffs_fetch['total_staffs'] ?? 0;

                            // 2. Count ill staffs (Global)
                            $ill_query = "SELECT COUNT(*) AS total_ill FROM staff_medical_records WHERE LOWER(TRIM(record_status)) IN ('open', 'under_treatment')";
                            $ill_stmt = $conn->prepare($ill_query);
                        } else {
                            // 1. Count total staffs (Branch Scoped)
                            $staff_stmt = $conn->prepare("SELECT COUNT(*) AS total_staffs FROM staffs s LEFT JOIN branches b ON s.branch_id = b.id WHERE LOWER(TRIM(b.branch_name)) = LOWER(TRIM(?)) OR s.branch_id = ?");
                            $staff_stmt->bind_param("ss", $user_branch, $user_branch);
                            $staff_stmt->execute();
                            $total_staffs_fetch = $staff_stmt->get_result()->fetch_assoc();
                            $total_staffs = $total_staffs_fetch['total_staffs'] ?? 0;
                            $staff_stmt->close();

                            // 2. Count ill staffs (Branch Scoped)
                            $ill_query = "SELECT COUNT(*) AS total_ill FROM staff_medical_records WHERE LOWER(TRIM(record_status)) IN ('open', 'under_treatment') AND LOWER(TRIM(staff_branch)) = LOWER(TRIM(?))";
                            $ill_stmt = $conn->prepare($ill_query);
                            $ill_stmt->bind_param("s", $user_branch);
                        }

                        $ill_stmt->execute();
                        $ill_staffs_fetch = $ill_stmt->get_result()->fetch_assoc();
                        $ill_staffs = $ill_staffs_fetch['total_ill'] ?? 0;
                        $ill_stmt->close();
                        ?>

                        <div class="row">
                            <!-- Registered Staffs Card -->
                            <div class="col-6 col-lg-4 col-md-6">
                                <a href="staffs.php" class="card-link">
                                    <div class="card">
                                        <div class="card-body px-3 py-4-5">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="stats-icon purple">
                                                        <i class="iconly-boldProfile"></i>
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <h6 class="text-muted font-semibold">Registered Staffs</h6>
                                                    <h6 class="font-extrabold mb-0">
                                                        <?php echo number_format($total_staffs); ?>
                                                    </h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <!-- Total Branches Card (Global Count) -->
                            <div class="col-6 col-lg-4 col-md-6">
                                <a href="branches.php" class="card-link">
                                    <div class="card">
                                        <div class="card-body px-3 py-4-5">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="stats-icon blue">
                                                        <i class="iconly-boldHome"></i>
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <h6 class="text-muted font-semibold">Total Branches</h6>
                                                    <h6 class="font-extrabold mb-0">
                                                        <?php echo number_format($total_branches); ?>
                                                    </h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <!-- Ill Staffs / Medical Logs Card -->
                            <div class="col-6 col-lg-4 col-md-6">
                                <a href="ill_staffs.php" class="card-link">
                                    <div class="card">
                                        <div class="card-body px-3 py-4-5">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="stats-icon red">
                                                        <i class="iconly-boldDanger"></i>
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <h6 class="text-muted font-semibold">Ill Staffs</h6>
                                                    <h6 class="font-extrabold mb-0">
                                                        <?php echo number_format($ill_staffs); ?>
                                                    </h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Chart 1: Volume Column Block -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Medical Records Volume by Branch</h4>
                                    </div>
                                    <div class="card-body">
                                        <div id="chart-medical-volume"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <!-- Profile Identity Metadata component -->
                        <div class="card">
                            <div class="card-body py-4 px-5">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xl">
                                        <?php
                                        $session_passport = trim($_SESSION['passport'] ?? '');
                                        $avatar_src = (!empty($session_passport) && file_exists("uploads/" . $session_passport))
                                            ? "uploads/" . $session_passport
                                            : "assets/images/faces/1.jpg";
                                        ?>
                                        <img src="<?php echo htmlspecialchars($avatar_src); ?>" alt="Profile Image" style="object-fit: cover;">
                                    </div>
                                    <div class="ms-3 name">
                                        <h5 class="font-bold mb-1">
                                            <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Guest User'); ?>
                                        </h5>
                                        <h6 class="text-muted mb-1">
                                            <?php echo htmlspecialchars($_SESSION['email'] ?? 'no-email'); ?>
                                        </h6>
                                        <small class="text-primary d-block">
                                            <b> Role:</b> <?php echo htmlspecialchars($_SESSION['role'] ?? 'Not Assigned'); ?>
                                        </small>
                                        <small class="text-success d-block">
                                            <b> Branch:</b> <?php echo htmlspecialchars($_SESSION['branch'] ?? 'Unknown'); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chart 2: Status Tracker Donut Block -->
                        <div class="card">
                            <div class="card-header">
                                <h4>Case Status Tracker</h4>
                            </div>
                            <div class="card-body">
                                <div id="chart-case-status" class="d-flex justify-content-center"></div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Include ApexCharts Scripts Dependency Once -->
            <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

            <script>
                document.addEventListener("DOMContentLoaded", function() {

                    // =========================================================================
                    // ENGINE 1: MEDICAL RECORDS VOLUME CHART (COLUMN)
                    // =========================================================================
                    const volumeOptions = {
                        series: [{
                            name: 'Total Records',
                            data: []
                        }],
                        chart: {
                            type: 'bar',
                            height: 380,
                            toolbar: {
                                show: false
                            },
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 600
                            }
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                horizontal: false,
                                columnWidth: '45%',
                                distributed: true
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            style: {
                                colors: ['#fff'],
                                fontWeight: '600'
                            },
                            dropShadow: {
                                enabled: false
                            }
                        },
                        colors: ['#435ebe', '#55c39e', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14'],
                        legend: {
                            show: false
                        },
                        xaxis: {
                            categories: [],
                            axisBorder: {
                                show: false
                            },
                            labels: {
                                style: {
                                    colors: '#6c757d',
                                    fontWeight: '500'
                                }
                            }
                        },
                        yaxis: {
                            title: {
                                text: 'Record Count',
                                style: {
                                    color: '#6c757d',
                                    fontWeight: '500'
                                }
                            },
                            labels: {
                                style: {
                                    colors: '#6c757d'
                                }
                            }
                        },
                        grid: {
                            borderColor: '#f1f1f1'
                        },
                        tooltip: {
                            theme: 'dark',
                            y: {
                                formatter: function(val) {
                                    return val + " Intake Records";
                                }
                            }
                        }
                    };

                    const volumeChart = new ApexCharts(document.querySelector("#chart-medical-volume"), volumeOptions);
                    volumeChart.render();

                    function fetchVolumeMetrics() {
                        fetch('get_profile_visits.php')
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    volumeChart.updateSeries([{
                                        name: 'Total Records',
                                        data: data.counts
                                    }]);
                                    volumeChart.updateOptions({
                                        xaxis: {
                                            categories: data.branches
                                        }
                                    });
                                }
                            })
                            .catch(error => console.error('Error fetching medical record metrics:', error));
                    }
                    fetchVolumeMetrics();
                    setInterval(fetchVolumeMetrics, 5000);


                    // =========================================================================
                    // ENGINE 2: CASE STATUS TRACKER CHART (DONUT)
                    // =========================================================================
                    const statusOptions = {
                        series: [], // Populated dynamically: [Open, Under Treatment, Closed/Resolved]
                        chart: {
                            type: 'donut',
                            height: 350,
                            animations: {
                                enabled: true,
                                speed: 500
                            }
                        },
                        labels: ['Open Cases', 'Under Treatment', 'Resolved / Closed'],
                        colors: ['#55c39e', '#ffc107', '#6c757d'],
                        legend: {
                            position: 'bottom',
                            fontFamily: 'inherit',
                            labels: {
                                colors: '#6c757d'
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function(val) {
                                return Math.round(val) + "%";
                            }
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '70%',
                                    labels: {
                                        show: true,
                                        name: {
                                            show: true,
                                            fontSize: '14px',
                                            color: '#6c757d'
                                        },
                                        value: {
                                            show: true,
                                            fontSize: '24px',
                                            fontWeight: '700',
                                            color: '#435ebe'
                                        },
                                        total: {
                                            show: true,
                                            label: 'Total Cases',
                                            color: '#6c757d',
                                            formatter: function(w) {
                                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                            }
                                        }
                                    }
                                }
                            }
                        },
                        tooltip: {
                            theme: 'dark'
                        }
                    };

                    const statusChart = new ApexCharts(document.querySelector("#chart-case-status"), statusOptions);
                    statusChart.render();

                    function fetchStatusMetrics() {
                        fetch('get_case_status.php')
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    statusChart.updateSeries([
                                        data.open,
                                        data.under_treatment,
                                        data.closed
                                    ]);
                                }
                            })
                            .catch(error => console.error('Error fetching case status matrices:', error));
                    }
                    fetchStatusMetrics();
                    setInterval(fetchStatusMetrics, 5000);
                });
            </script>

            <?php
            include('./inc/footer.php');
            ?>
        </div>
    </div>
    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script src="assets/vendors/apexcharts/apexcharts.js"></script>
    <script src="assets/js/pages/dashboard.js"></script>

    <script src="assets/js/main.js"></script>
</body>

</html>