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

                        // Count total staffs
                        $total_staffs_query = mysqli_query($conn, "SELECT COUNT(*) AS total_staffs FROM staffs");
                        $total_staffs_fetch = mysqli_fetch_assoc($total_staffs_query);
                        $total_staffs = $total_staffs_fetch['total_staffs'] ?? 0;

                        // Count total branches
                        $total_branches_query = mysqli_query($conn, "SELECT COUNT(*) AS total_branches FROM branches");
                        $total_branches_fetch = mysqli_fetch_assoc($total_branches_query);
                        $total_branches = $total_branches_fetch['total_branches'] ?? 0;

                        // Dynamic ill staffs query (adjust column/status according to your table schema)
                        $ill_staffs_query = mysqli_query($conn, "SELECT COUNT(*) AS total_ill FROM staff_medical_records WHERE record_status = 'open' OR record_status = 'under_treatment'");
                        $ill_staffs_fetch = mysqli_fetch_assoc($ill_staffs_query);
                        $ill_staffs = $ill_staffs_fetch['total_ill'] ?? 15;
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

                            <!-- Total Branches Card -->
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
                                <a href="medical-records.php" class="card-link">
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
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Medical Records Volume by Branch</h4>
                                    </div>
                                    <div class="card-body">
                                        <div id="chart-profile-visit"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Include ApexCharts Dependencies -->
                        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                // 1. Initial configuration for an elegant Column Chart shell
                                const options = {
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
                                            distributed: true // Gives individual colors to different branches
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
                                    // Modern, corporate healthcare color palette
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

                                // 2. Render the empty chart shell instance
                                const chart = new ApexCharts(document.querySelector("#chart-profile-visit"), options);
                                chart.render();

                                // 3. Runtime data stream routine
                                function fetchLiveMetrics() {
                                    fetch('get_profile_visits.php')
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                chart.updateSeries([{
                                                    name: 'Total Records',
                                                    data: data.counts
                                                }]);

                                                chart.updateOptions({
                                                    xaxis: {
                                                        categories: data.branches
                                                    }
                                                });
                                            }
                                        })
                                        .catch(error => console.error('Error fetching medical record metrics:', error));
                                }

                                // 4. Initial load + Poll for infrastructure updates every 5 seconds
                                fetchLiveMetrics();
                                setInterval(fetchLiveMetrics, 5000);
                            });
                        </script>

                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="card">
                            <div class="card-body py-4 px-5">
                                <div class="d-flex align-items-center">

                                    <div class="avatar avatar-xl">
                                        <img src="assets/images/faces/1.jpg" alt="Profile Image">
                                    </div>

                                    <div class="ms-3 name">

                                        <h5 class="font-bold mb-1">
                                            <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Guest User'); ?>
                                        </h5>

                                        <h6 class="text-muted mb-1">
                                            <?php echo htmlspecialchars($_SESSION['email'] ?? 'no-email'); ?>
                                        </h6>

                                        <small class="text-primary d-block">
                                            <b> Role:</b>
                                            <?php echo htmlspecialchars($_SESSION['role'] ?? 'Not Assigned'); ?>
                                        </small>

                                        <small class="text-success d-block">
                                            <b> Branch:</b>
                                            <?php echo htmlspecialchars($_SESSION['branch'] ?? 'Unknown'); ?>
                                        </small>

                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4>Case Status Tracker</h4>
                            </div>
                            <div class="card-body">
                                <div id="chart-visitors-profile" class="d-flex justify-content-center"></div>
                            </div>
                        </div>

                        <!-- Ensure ApexCharts is available (Only keep one instance if already loaded on the page) -->
                        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                // 1. Initial Donut chart options matching your data schema ENUMs
                                const options = {
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
                                    // Colors mapping directly to badge-soft styling tokens
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
                                            return Math.round(val) + "%"
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
                                                        color: '#435ebe',
                                                        formatter: function(val) {
                                                            return val;
                                                        }
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

                                // 2. Instantiate and render the chart
                                const statusChart = new ApexCharts(document.querySelector("#chart-visitors-profile"), options);
                                statusChart.render();

                                // 3. Runtime asynchronous background fetch routine
                                function fetchStatusMetrics() {
                                    fetch('get_case_status.php')
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                // Update series arrays directly cleanly without resetting options
                                                statusChart.updateSeries([
                                                    data.open,
                                                    data.under_treatment,
                                                    data.closed
                                                ]);
                                            }
                                        })
                                        .catch(error => console.error('Error fetching medical case status matrices:', error));
                                }

                                // 4. Run loop immediately and schedule a pulse stream every 5 seconds
                                fetchStatusMetrics();
                                setInterval(fetchStatusMetrics, 5000);
                            });
                        </script>
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

    <script src="assets/vendors/apexcharts/apexcharts.js"></script>
    <script src="assets/js/pages/dashboard.js"></script>

    <script src="assets/js/main.js"></script>
</body>

</html>