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
$total_alerts = $pdo->query("SELECT COUNT(*) FROM Alerts")->fetchColumn();
$resolved_alerts = $pdo->query("SELECT COUNT(*) FROM Alerts WHERE Alerts_status='resolved'")->fetchColumn();
$pending_alerts = $pdo->query("SELECT COUNT(*) FROM Alerts WHERE Alerts_status='pending'")->fetchColumn();
$verified_alerts = $pdo->query("SELECT COUNT(*) FROM Alerts WHERE Alerts_status='verified'")->fetchColumn();
$broadcasted_alerts = $pdo->query("SELECT COUNT(*) FROM Alerts WHERE Alerts_status='broadcasted'")->fetchColumn();

$response_rate = $total_alerts > 0 ? round(($resolved_alerts / $total_alerts) * 100) : 0;
$pending_rate = $total_alerts > 0 ? round(($pending_alerts / $total_alerts) * 100) : 0;

// 2. Response Time Analysis
$avg_response_time = $pdo->query("
    SELECT AVG(TIMESTAMPDIFF(MINUTE, a.Alerts_created_at, ar.AlertResponses_responded_at)) as avg_minutes
    FROM Alerts a
    JOIN AlertResponses ar ON a.Alerts_id = ar.AlertResponses_Alerts_id
    WHERE a.Alerts_status = 'resolved'
")->fetchColumn();

$avg_response_time = $avg_response_time ? round($avg_response_time) : 0;

// 3. Severity Distribution
$severity_stats = $pdo->query("SELECT Alerts_severity, COUNT(*) as count FROM Alerts GROUP BY Alerts_severity")->fetchAll();

// 4. Responder Performance
$responder_stats = $pdo->query("
    SELECT u.Users_full_name as responder, COUNT(ar.AlertResponses_id) as responses,
           AVG(TIMESTAMPDIFF(MINUTE, a.Alerts_created_at, ar.AlertResponses_responded_at)) as avg_response_time
    FROM Users u
    LEFT JOIN AlertResponses ar ON u.Users_id = ar.AlertResponses_Users_id
    LEFT JOIN Alerts a ON ar.AlertResponses_Alerts_id = a.Alerts_id
    WHERE u.Users_Roles_id = 2
    GROUP BY u.Users_id, u.Users_full_name
    ORDER BY responses DESC
")->fetchAll();

// 5. Monthly Trend with more detail
$monthly_stats = $pdo->query("
    SELECT DATE_FORMAT(Alerts_created_at, '%b %Y') as month,
           COUNT(*) as total,
           SUM(CASE WHEN Alerts_status = 'resolved' THEN 1 ELSE 0 END) as resolved,
           AVG(CASE WHEN Alerts_status = 'resolved' THEN TIMESTAMPDIFF(MINUTE, Alerts_created_at,
               (SELECT MIN(AlertResponses_responded_at) FROM AlertResponses WHERE AlertResponses_Alerts_id = Alerts_id))
               ELSE NULL END) as avg_response_time
    FROM Alerts
    GROUP BY DATE_FORMAT(Alerts_created_at, '%Y-%m'), DATE_FORMAT(Alerts_created_at, '%b %Y')
    ORDER BY Alerts_created_at DESC LIMIT 12
")->fetchAll();

// Keep monthly stats chronological left-to-right in the chart
$monthly_stats = array_reverse($monthly_stats);

// 6. Alert Type Distribution
$type_stats = $pdo->query("
    SELECT at.AlertTypes_name as name, COUNT(a.Alerts_id) as count,
           AVG(CASE WHEN a.Alerts_status = 'resolved' THEN TIMESTAMPDIFF(MINUTE, a.Alerts_created_at,
               (SELECT MIN(ar.AlertResponses_responded_at) FROM AlertResponses ar WHERE ar.AlertResponses_Alerts_id = a.Alerts_id))
               ELSE NULL END) as avg_response_time
    FROM AlertTypes at
    LEFT JOIN Alerts a ON at.AlertTypes_id = a.Alerts_AlertTypes_id
    GROUP BY at.AlertTypes_id, at.AlertTypes_name
    ORDER BY count DESC
")->fetchAll();

// 7. Response Time by Severity
$response_time_by_severity = $pdo->query("
    SELECT a.Alerts_severity,
           AVG(TIMESTAMPDIFF(MINUTE, a.Alerts_created_at, ar.AlertResponses_responded_at)) as avg_response_time,
           COUNT(*) as count
    FROM Alerts a
    JOIN AlertResponses ar ON a.Alerts_id = ar.AlertResponses_Alerts_id
    WHERE a.Alerts_status = 'resolved'
    GROUP BY a.Alerts_severity
    ORDER BY FIELD(a.Alerts_severity, 'High', 'Medium', 'Low')
")->fetchAll();

// 8. Status Breakdown
$status_breakdown = $pdo->query("
    SELECT Alerts_status, COUNT(*) as count
    FROM Alerts
    GROUP BY Alerts_status
    ORDER BY count DESC
")->fetchAll();

// 9. Recent Performance Trends (last 30 days vs previous 30 days)
$recent_performance = $pdo->query("
    SELECT
        SUM(CASE WHEN Alerts_created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as recent_alerts,
        SUM(CASE WHEN Alerts_created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND Alerts_status = 'resolved' THEN 1 ELSE 0 END) as recent_resolved,
        SUM(CASE WHEN Alerts_created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as prev_alerts,
        SUM(CASE WHEN Alerts_created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY) AND Alerts_status = 'resolved' THEN 1 ELSE 0 END) as prev_resolved
    FROM Alerts
")->fetch();

// Calculate trends
$alert_trend = 0;
$resolution_trend = 0;
if ($recent_performance['prev_alerts'] > 0) {
    $alert_trend = round((($recent_performance['recent_alerts'] - $recent_performance['prev_alerts']) / $recent_performance['prev_alerts']) * 100);
}
if ($recent_performance['prev_resolved'] > 0) {
    $resolution_trend = round((($recent_performance['recent_resolved'] - $recent_performance['prev_resolved']) / $recent_performance['prev_resolved']) * 100);
}

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
                    <div class="btn-group shadow-sm" role="group">
                        <button class="btn btn btn-white border px-4 py-2 me-2 small fw-bold" id="downloadPdfBtn">
                            <!-- <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2">
                                <path d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231a1.125 1.125 0 0 1-1.12-1.227L6.34 18m11.318-8.318a4.5 4.5 0 1 1-6.364 0m6.364 0a4.5 4.5 0 0 1-6.364 0m6.364 0h.008v.008h-.008V10.5h.008v.008h-.008V10.5Z" />
                            </svg> -->
                            Export PDF
                        </button>
                        <button class="btn btn-white border px-4 py-2 small fw-bold" id="downloadExcelBtn">
                            <!-- <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2">
                                <path d="M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm2 4h6m-6 4h6m-6 4h6" />
                            </svg> -->
                            Export Excel
                        </button>
                    </div>
                </div>

                <!-- Key Performance Indicators -->
                <div class="row g-4 mb-5">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm p-4 bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <small class="text-black text-uppercase fw-bold opacity-75" style="font-size: 0.65rem; letter-spacing: 1px;">Total Incidents</small>
                                    <h2 class="fw-bold text-warning mb-0 mt-1"><?= $total_alerts ?></h2>
                                    <div class="mt-2 small text-black opacity-75">
                                        <span class="badge bg-warning text-dark me-1">
                                            <?php if ($alert_trend > 0): ?>↑<?php elseif ($alert_trend < 0): ?>↓<?php else: ?>→<?php endif; ?>
                                            <?= abs($alert_trend) ?>%
                                        </span>
                                        vs last month
                                    </div>
                                </div>
                                <div class="bg-white bg-opacity-20 rounded-circle p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm p-4 bg-success text-white">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <small class="text-black text-uppercase fw-bold opacity-75" style="font-size: 0.65rem; letter-spacing: 1px;">Resolution Rate</small>
                                    <h2 class="text-success fw-bold mb-0 mt-1"><?= $response_rate ?>%</h2>
                                    <div class="mt-2 small text-black opacity-75">
                                        <span class="badge bg-light text-success me-1">
                                            <?php if ($resolution_trend > 0): ?>↑<?php elseif ($resolution_trend < 0): ?>↓<?php else: ?>→<?php endif; ?>
                                            <?= abs($resolution_trend) ?>%
                                        </span>
                                        vs last month
                                    </div>
                                </div>
                                <div class="bg-white bg-opacity-20 rounded-circle p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm p-4 bg-info text-white">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <small class="text-black text-uppercase fw-bold opacity-75" style="font-size: 0.65rem; letter-spacing: 1px;">Avg Response Time</small>
                                    <h2 class="text-info fw-bold mb-0 mt-1"><?= $avg_response_time ?> <span class="h6">mins</span></h2>
                                    <div class="mt-2 small text-black opacity-75">From alert to response</div>
                                </div>
                                <div class="bg-white bg-opacity-20 rounded-circle p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm p-4 bg-warning text-dark">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <small class="text-dark text-uppercase fw-bold opacity-75" style="font-size: 0.65rem; letter-spacing: 1px;">Pending Alerts</small>
                                    <h2 class="fw-bold mb-0 mt-1"><?= $pending_alerts ?></h2>
                                    <div class="mt-2 small text-dark opacity-75">
                                        <span class="badge bg-danger me-1"><?= $pending_rate ?>%</span>
                                        of total incidents
                                    </div>
                                </div>
                                <div class="bg-dark bg-opacity-10 rounded-circle p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Insights -->
                <div class="row g-4 mb-5">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-4 px-4 border-bottom-0">
                                <h6 class="fw-bold mb-0 text-slate-800">Severity Distribution</h6>
                                <small class="text-muted">Incident priority breakdown</small>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="row g-3">
                                    <?php foreach ($severity_stats as $severity): ?>
                                        <?php
                                        $percentage = $total_alerts > 0 ? round(($severity['count'] / $total_alerts) * 100) : 0;
                                        $color = match($severity['Alerts_severity']) {
                                            'High' => 'danger',
                                            'Medium' => 'warning',
                                            'Low' => 'success',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-semibold text-<?= $color ?>"><?= $severity['Alerts_severity'] ?> Priority</span>
                                                <span class="badge bg-<?= $color ?>-subtle text-<?= $color ?>"><?= $severity['count'] ?> (<?= $percentage ?>%)</span>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-<?= $color ?>" style="width: <?= $percentage ?>%"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-4 px-4 border-bottom-0">
                                <h6 class="fw-bold mb-0 text-slate-800">Top Responders</h6>
                                <small class="text-muted">Most active emergency responders</small>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="row g-3">
                                    <?php foreach (array_slice($responder_stats, 0, 5) as $responder): ?>
                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($responder['responder']) ?></div>
                                                    <small class="text-muted">
                                                        <?= $responder['responses'] ?> responses
                                                        <?php if ($responder['avg_response_time']): ?>
                                                            • Avg: <?= round($responder['avg_response_time']) ?> min
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold text-primary">#<?= $responder['responses'] ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Charts -->
                <div class="row g-4">
                    <!-- Volume Trend -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-4 px-4 border-bottom-0">
                                <h6 class="fw-bold mb-0 text-slate-800">Emergency Incident Trends</h6>
                                <small class="text-muted">Monthly volume analysis showing incident patterns and seasonal variations</small>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div style="height: 300px;">
                                    <canvas id="monthlyReportChart"></canvas>
                                </div>
                                <div class="mt-3 small text-muted">
                                    <strong>Key Insights:</strong>
                                    <?php
                                    $trend = 0;
                                    if (count($monthly_stats) > 1) {
                                        $latest = $monthly_stats[count($monthly_stats) - 1]['total'];
                                        $previous = $monthly_stats[count($monthly_stats) - 2]['total'];
                                        $trend = $previous > 0 ? (($latest - $previous) / $previous) * 100 : 0;
                                    }
                                ?>
                                <span class="badge bg-<?= $trend > 0 ? 'danger' : ($trend < 0 ? 'success' : 'secondary') ?>-subtle text-<?= $trend > 0 ? 'danger' : ($trend < 0 ? 'success' : 'secondary') ?> me-2">
                                    <?= $trend > 0 ? '+' : '' ?><?= round($trend, 1) ?>% vs last month
                                </span>
                                    Peak activity typically indicates system stress points requiring additional resources.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Breakdown -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-4 px-4 border-bottom-0">
                                <h6 class="fw-bold mb-0 text-slate-800">Incident Type Distribution</h6>
                                <small class="text-muted">Breakdown of emergency categories and their frequency</small>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div style="height: 300px;">
                                    <canvas id="typeReportChart"></canvas>
                                </div>
                                <div class="mt-3 small text-muted">
                                    <strong>Most Common:</strong>
                                    <?php
                                    $top_type = $type_stats[0] ?? null;
                                    if ($top_type): ?>
                                        <span class="badge bg-primary-subtle text-primary me-2"><?= $top_type['name'] ?> (<?= $top_type['count'] ?> incidents)</span>
                                        Represents <?= $total_alerts > 0 ? round(($top_type['count'] / $total_alerts) * 100) : 0 ?>% of all emergencies.
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Response Performance Chart -->
                <div class="row g-4 mt-2">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-4 px-4 border-bottom-0">
                                <h6 class="fw-bold mb-0 text-slate-800">Response Time Analysis</h6>
                                <small class="text-muted">Average response times by incident severity</small>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div style="height: 250px;">
                                    <canvas id="responseTimeChart"></canvas>
                                </div>
                                <div class="mt-3 small text-muted">
                                    <strong>Performance Target:</strong> Critical incidents should be addressed within 5 minutes.
                                    <?php if ($avg_response_time > 5): ?>
                                        <span class="badge bg-warning-subtle text-warning ms-2">Above target</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success ms-2">Meeting target</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-4 px-4 border-bottom-0">
                                <h6 class="fw-bold mb-0 text-slate-800">Resolution Status Overview</h6>
                                <small class="text-muted">Current state of all emergency incidents</small>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div style="height: 250px;">
                                    <canvas id="statusChart"></canvas>
                                </div>
                                <div class="mt-3 small text-muted">
                                    <strong>Resolution Rate:</strong>
                                    <span class="badge bg-<?= $response_rate >= 90 ? 'success' : ($response_rate >= 70 ? 'warning' : 'danger') ?>-subtle text-<?= $response_rate >= 90 ? 'success' : ($response_rate >= 70 ? 'warning' : 'danger') ?> me-2">
                                        <?= $response_rate ?>%
                                    </span>
                                    <?php if ($response_rate < 80): ?>
                                        Consider increasing responder capacity or optimizing dispatch processes.
                                    <?php else: ?>
                                        Excellent resolution performance maintained.
                                    <?php endif; ?>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" integrity="sha512-xsrs4p1Z0v9mHJyi5NBHh9s6gTfNT5+y7IuSQU7+icla1m5zXKMqf6gF1br2FQEAbqZzS8/Cd1G8oaxQD8ViHQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
<script>
    window.reportData = {
        summary: {
            total_alerts: <?= json_encode($total_alerts) ?>,
            resolved_alerts: <?= json_encode($resolved_alerts) ?>,
            response_rate: <?= json_encode($response_rate) ?>
        },
        typeStats: <?= json_encode($type_stats) ?>,
        monthlyStats: <?= json_encode($monthly_stats) ?>,
        responseTimeBySeverity: <?= json_encode($response_time_by_severity) ?>,
        statusBreakdown: <?= json_encode($status_breakdown) ?>
    };

    document.addEventListener('DOMContentLoaded', function() {
        const pdfBtn = document.getElementById('downloadPdfBtn');
        const excelBtn = document.getElementById('downloadExcelBtn');
        if (pdfBtn) pdfBtn.addEventListener('click', function() {
            if (window.exportReportPdf) window.exportReportPdf(window.reportData);
        });
        if (excelBtn) excelBtn.addEventListener('click', function() {
            if (window.exportReportExcel) window.exportReportExcel(window.reportData);
        });
    });

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
                data: <?= json_encode(array_column($monthly_stats, 'total')) ?>,
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

    // 3. Response Time by Severity Bar Chart
    new Chart(document.getElementById('responseTimeChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($response_time_by_severity, 'Alerts_severity')) ?>,
            datasets: [{
                label: 'Average Response Time (minutes)',
                data: <?= json_encode(array_map(function($item) { return round($item['avg_response_time']); }, $response_time_by_severity)) ?>,
                backgroundColor: [
                    brandColors.danger,
                    brandColors.warning,
                    brandColors.success
                ],
                borderWidth: 0,
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: '#f1f5f9' },
                    title: {
                        display: true,
                        text: 'Minutes'
                    }
                },
                x: {
                    border: { display: false },
                    grid: { display: false }
                }
            }
        }
    });

    // 4. Status Breakdown Pie Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($status_breakdown, 'Alerts_status')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($status_breakdown, 'count')) ?>,
                backgroundColor: [
                    brandColors.success,  // resolved
                    brandColors.warning,  // pending
                    brandColors.info,     // verified
                    brandColors.primary,  // broadcasted
                    brandColors.slate     // others
                ],
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        font: { size: 11, weight: '600' }
                    }
                }
            }
        }
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>