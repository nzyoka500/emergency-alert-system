<?php
/**
 * reports.php - Professional System Insights
 * Updated with Indigo/Slate analytical layout
 */

require_once __DIR__ . '/../includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not authenticated
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$pdo = getPDO();

// 1. Overview Stats for the Top Row
// UPDATED: Table 'Alerts'
$total_alerts = $pdo->query("SELECT COUNT(*) FROM Alerts")->fetchColumn();

// UPDATED: Column 'Alerts_status'
$resolved_alerts = $pdo->query("SELECT COUNT(*) FROM Alerts WHERE Alerts_status='resolved'")->fetchColumn();
$response_rate = $total_alerts > 0 ? round(($resolved_alerts / $total_alerts) * 100) : 0;

// 2. Data for Category Pie Chart
// UPDATED: Table names and prefixed column names
$type_stats = $pdo->query("SELECT at.AlertTypes_name as name, COUNT(a.Alerts_id) as count 
                           FROM AlertTypes at 
                           LEFT JOIN Alerts a ON at.AlertTypes_id = a.Alerts_AlertTypes_id 
                           GROUP BY at.AlertTypes_name")->fetchAll();

// 3. Data for Monthly Trend Line Chart
// UPDATED: Column 'Alerts_created_at' and 'Alerts_id'
$monthly_stats = $pdo->query("SELECT DATE_FORMAT(Alerts_created_at, '%b') as month, COUNT(Alerts_id) as count 
                              FROM Alerts 
                              GROUP BY month 
                              ORDER BY Alerts_created_at ASC LIMIT 6")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Sidebar Navigation -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-lg-10 bg-light min-vh-100">
            <div class="p-4 p-lg-5">

                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="h3 fw-bold mb-1">Alert Insights</h1>
                        <p class="text-muted mb-0">Statistical analysis of emergency trends and response efficiency.</p>
                    </div>
                    <div class="btn-group shadow-sm">
                        <button class="btn btn-white border px-4 py-2 small fw-bold" onclick="window.print()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231a1.125 1.125 0 0 1-1.12-1.227L6.34 18m11.318-8.318a4.5 4.5 0 1 1-6.364 0m6.364 0a4.5 4.5 0 0 1-6.364 0m6.364 0h.008v.008h-.008V10.5h.008v.008h-.008V10.5Z" />
                            </svg>
                            Export Report
                        </button>
                    </div>
                </div>

                <!-- Report Summary Grid -->
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm p-4 bg-primary text-white">
                            <small class="text-white text-uppercase fw-bold opacity-75" style="font-size: 0.65rem; letter-spacing: 1px;">Lifetime Incidents</small>
                            <h2 class="fw-bold text-warning mb-0 mt-1"><?= $total_alerts ?></h2>
                            <div class="mt-3 small text-white opacity-75">Total emergencies logged</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm p-4">
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Resolution Rate</small>
                            <h2 class="fw-bold text-success mb-0 mt-1"><?= $response_rate ?>%</h2>
                            <div class="mt-3 small text-muted">Percentage of alerts resolved</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm p-4">
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Avg. Response Time</small>
                            <h2 class="fw-bold text-dark mb-0 mt-1">12.4 <span class="h6 text-muted">mins</span></h2>
                            <div class="mt-3 small text-muted">From pending to verified</div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Charts -->
                <div class="row g-4">
                    <!-- Volume Trend -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-4 px-4 border-bottom-0">
                                <h6 class="fw-bold mb-0 text-slate-800">Monthly Incident Volume</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div style="height: 300px;">
                                    <canvas id="monthlyReportChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Breakdown -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-4 px-4 border-bottom-0">
                                <h6 class="fw-bold mb-0 text-slate-800">Alert Distribution</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div style="height: 300px;">
                                    <canvas id="typeReportChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const brandColors = {
        primary: '#4f46e5',
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#f43f5e',
        info: '#0ea5e9',
        slate: '#64748b'
    };

    // 1. Monthly Trend Line Chart
    new Chart(document.getElementById('monthlyReportChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($monthly_stats, 'month')) ?>,
            datasets: [{
                label: 'New Alerts',
                data: <?= json_encode(array_column($monthly_stats, 'count')) ?>,
                borderColor: brandColors.primary,
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: brandColors.primary,
                borderWidth: 3
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, border: { display: false }, grid: { color: '#f1f5f9' } },
                x: { border: { display: false }, grid: { display: false } }
            }
        }
    });

    // 2. Category Pie Chart
    new Chart(document.getElementById('typeReportChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($type_stats, 'name')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($type_stats, 'count')) ?>,
                backgroundColor: [brandColors.danger, brandColors.info, brandColors.warning, brandColors.success, brandColors.slate],
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: { size: 12, weight: '600' }
                    }
                }
            }
        }
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>