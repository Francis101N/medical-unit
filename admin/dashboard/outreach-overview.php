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
    <title>Medical Operations & Outreach Dashboard - Medical Unit</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="icon" type="image/x-icon" href="./assets/images/favicon.png">
    <!-- ApexCharts CSS/Library -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
</head>

<style>
    :root {
        --primary-color: #435ebe;
        --primary-gradient: linear-gradient(135deg, #435ebe, #2c4294);
        --border-color: #e2e8f0;
        --text-main: #334155;
        --text-muted: #64748b;
        --bg-light-pane: #f8fafc;
    }

    body {
        font-family: 'Nunito', sans-serif;
        background-color: #f2f7ff;
    }

    .stat-card {
        border: none;
        border-radius: 16px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(67, 94, 190, 0.08) !important;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    @keyframes pulse {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
        }

        70% {
            transform: scale(1);
            box-shadow: 0 0 0 6px rgba(25, 135, 84, 0);
        }

        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(25, 135, 84, 0);
        }
    }
</style>

<body>
    <div id="app">
        <?php include('././inc/side-nav.php'); ?>
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="page-heading">
                <div class="page-title mb-4">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-6">
                            <h3>Outreach & Operations Dashboard</h3>
                            <p class="text-subtitle text-muted mb-0">
                                Comprehensive real-time monitoring of medical volumes, location telemetry, and patient registries.
                            </p>
                        </div>
                        <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
                            <a href="add_patient_record.php" class="btn btn-success shadow-sm px-4 py-2 fw-semibold" style="border-radius: 10px;">
                                <i class="bi bi-plus-lg me-1"></i> New Patient Record
                            </a>
                        </div>
                    </div>
                </div>

                <?php
                /** @var mysqli $conn */
                include('./db.php');

                $user_role = strtolower($_SESSION['role'] ?? '');
                $user_branch = $_SESSION['branch'] ?? '';

                if ($user_role === 'super-admin') {
                    $query = "SELECT * FROM patient_medical_records ORDER BY id DESC";
                    $stmt = $conn->prepare($query);
                } else {
                    $query = "SELECT * FROM patient_medical_records WHERE LOWER(TRIM(patient_location)) = LOWER(TRIM(?)) ORDER BY id DESC";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("s", $user_branch);
                }

                $stmt->execute();
                $select_logs = $stmt->get_result();

                $count_open = 0;
                $count_treatment = 0;
                $count_closed = 0;
                $total_records = 0;
                $unique_locations = [];

                if ($select_logs && $select_logs->num_rows > 0) {
                    while ($row = $select_logs->fetch_assoc()) {
                        $total_records++;
                        $record_status = strtolower($row['record_status']);
                        if ($record_status === 'open') $count_open++;
                        elseif ($record_status === 'under_treatment') $count_treatment++;
                        else $count_closed++;

                        $locName = trim($row['patient_location'] ?? '');
                        if (!empty($locName)) {
                            $unique_locations[strtolower($locName)] = true;
                        }
                    }
                }
                $total_loc_count = count($unique_locations);
                ?>

                <!-- Section 1: Telemetry Stat Cards (Clickable Links) -->
                <section class="row mb-4">
                    <div class="col-6 col-lg-3 col-md-6 mb-3">
                        <a href="outreach.php" class="text-decoration-none">
                            <div class="card stat-card shadow-sm h-100">
                                <div class="card-body px-4 py-3-custom py-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <div class="stat-icon bg-primary text-white">
                                                <i class="bi bi-geo-alt"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-8 text-md-end">
                                            <h6 class="text-muted font-semibold mb-0">Total Outreach Locations</h6>
                                            <h4 class="font-bold mb-0 text-dark mt-1" id="metricTotalLocations"><?php echo $total_loc_count; ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-lg-3 col-md-6 mb-3">
                        <a href="patient_medical_records.php" class="text-decoration-none">
                            <div class="card stat-card shadow-sm h-100">
                                <div class="card-body px-4 py-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <div class="stat-icon bg-info text-white">
                                                <i class="bi bi-people"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-8 text-md-end">
                                            <h6 class="text-muted font-semibold mb-0">Total Patient Records</h6>
                                            <h4 class="font-bold mb-0 text-dark mt-1" id="metricTotalLogs"><?php echo $total_records; ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-lg-3 col-md-6 mb-3">
                        <a href="patient_medical_records.php?status=open" class="text-decoration-none">
                            <div class="card stat-card shadow-sm h-100">
                                <div class="card-body px-4 py-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <div class="stat-icon bg-success text-white">
                                                <i class="bi bi-check2-circle"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-8 text-md-end">
                                            <h6 class="text-muted font-semibold mb-0">Open Cases</h6>
                                            <h4 class="font-bold mb-0 text-dark mt-1" id="metricOpenCases"><?php echo $count_open; ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-lg-3 col-md-6 mb-3">
                        <a href="patient_medical_records.php?status=closed" class="text-decoration-none">
                            <div class="card stat-card shadow-sm h-100">
                                <div class="card-body px-4 py-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <div class="stat-icon bg-secondary text-white">
                                                <i class="bi bi-archive"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-8 text-md-end">
                                            <h6 class="text-muted font-semibold mb-0">Closed Cases</h6>
                                            <h4 class="font-bold mb-0 text-dark mt-1" id="metricClosedCases"><?php echo $count_closed; ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Section 2: Interactive Location/Branch Volume Chart -->
                    <section class="row mb-4">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                                    <h4 class="card-title mb-0" style="color: #1e293b; font-weight: 600;">Medical Records Volume by Location</h4>
                                    <span class="badge bg-light text-secondary border">Live Polling Active (5s)</span>
                                </div>
                                <div class="card-body p-4">
                                    <div id="chart-medical-volume" style="min-height: 320px;"></div>
                                </div>
                            </div>
                        </div>
                    </section>
            </div>

            <!-- Page Script for ApexCharts -->
            <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.min.js"></script>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const volumeOptions = {
                        chart: {
                            type: 'bar',
                            height: 320,
                            toolbar: {
                                show: false
                            },
                            events: {
                                dataPointSelection: function(event, chartContext, config) {
                                    const seriesIndex = config.seriesIndex;
                                    const dataPointIndex = config.dataPointIndex;
                                    const chartConfig = volumeChart.w.config.series[seriesIndex];

                                    if (chartConfig && chartConfig.branchIds && chartConfig.branchIds[dataPointIndex]) {
                                        window.location.href = 'patient_medical_records.php?branch=' + encodeURIComponent(chartConfig.branchIds[dataPointIndex]);
                                    } else {
                                        const categoryName = volumeChart.w.globals.categoryLabels[dataPointIndex];
                                        if (categoryName) {
                                            window.location.href = 'patient_medical_records.php?branch=' + encodeURIComponent(categoryName);
                                        }
                                    }
                                }
                            }
                        },
                        series: [{
                            name: 'Total Records',
                            data: [],
                            branchIds: []
                        }],
                        xaxis: {
                            categories: [],
                            labels: {
                                style: {
                                    colors: '#64748b',
                                    fontSize: '12px'
                                }
                            }
                        },
                        colors: ['#435ebe'],
                        plotOptions: {
                            bar: {
                                borderRadius: 8,
                                columnWidth: '45%'
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        grid: {
                            borderColor: '#f1f5f9'
                        }
                    };

                    const volumeChart = new ApexCharts(document.querySelector("#chart-medical-volume"), volumeOptions);
                    volumeChart.render();

                    function fetchVolumeMetrics() {
                        fetch('get_patient_medical_volume.php')
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    volumeChart.updateSeries([{
                                        name: 'Total Records',
                                        data: data.counts,
                                        branchIds: data.branch_ids || []
                                    }]);
                                    volumeChart.updateOptions({
                                        xaxis: {
                                            categories: data.branches
                                        }
                                    });
                                }
                            })
                            .catch(error => console.error('Error fetching metrics:', error));
                    }

                    fetchVolumeMetrics();
                    x
                    setInterval(fetchVolumeMetrics, 5000);
                });
            </script>

            <?php include('./inc/footer.php'); ?>
        </div>
    </div>
    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>