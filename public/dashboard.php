<?php
/**
 * dashboard.php - Production Grade Dashboard
 * Professional Layout for Admins & Responders
 */

require_once __DIR__ . '/../includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not authenticated
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$username = htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES, 'UTF-8');
$user_id = $_SESSION['user_id'] ?? null;
$role_id = $_SESSION['role_id'] ?? null;

// Initialize stats
$stats = [
    'active_alerts' => 0,
    'pending_alerts' => 0,
    'verified_alerts' => 0,
    'resolved_alerts' => 0,
    'total_responses' => 0,
    'total_users' => 0,
    'active_users' => 0,
    'personal_responses' => 0,
    'pending_responses' => 0
];

$recent_alerts = [];
$status_breakdown = ['pending' => 0, 'verified' => 0, 'broadcasted' => 0, 'resolved' => 0];

try {
    $pdo = getPDO();

    if ($role_id == 1) {
        // ADMIN DATA
        $stmt = $pdo->query("
            SELECT
            SUM(status='pending') as pending,
            SUM(status='verified') as verified,
            SUM(status='broadcasted') as broadcasted,
            SUM(status='resolved') as resolved
            FROM alerts
        ");
        $row = $stmt->fetch();
        $stats['active_alerts'] = (int)$row['pending'] + (int)$row['verified'] + (int)$row['broadcasted'];
        $stats['pending_alerts'] = (int)$row['pending'];
        $stats['verified_alerts'] = (int)$row['verified'];
        $stats['resolved_alerts'] = (int)$row['resolved'];

        $userStats = $pdo->query("SELECT COUNT(*) as total, SUM(status='active') as active FROM users")->fetch();
        $stats['total_users'] = $userStats['total'];
        $stats['active_users'] = $userStats['active'];

        $stats['total_responses'] = $pdo->query("SELECT COUNT(*) FROM alert_responses")->fetchColumn();

        $stmt = $pdo->query("
            SELECT a.id, a.title, a.status, a.created_at, at.name as alert_type, u.full_name as creator
            FROM alerts a 
            LEFT JOIN alert_types at ON a.alert_type_id = at.id
            LEFT JOIN users u ON a.created_by = u.id
            ORDER BY a.created_at DESC LIMIT 6
        ");
        $recent_alerts = $stmt->fetchAll();
    } else {
        // RESPONDER DATA
        $stats['personal_responses'] = $pdo->query("SELECT COUNT(*) FROM alert_responses WHERE responder_id = $user_id")->fetchColumn();
        $stats['pending_responses'] = $pdo->query("SELECT COUNT(*) FROM alert_responses WHERE responder_id = $user_id AND status = 'pending'")->fetchColumn();
        $stats['active_alerts'] = $pdo->query("SELECT COUNT(*) FROM alerts WHERE status IN ('pending', 'verified')")->fetchColumn();

        $stmt = $pdo->query("
            SELECT a.id, a.title, a.status, a.created_at, at.name as alert_type
            FROM alerts a 
            LEFT JOIN alert_types at ON a.alert_type_id = at.id
            WHERE a.status IN ('pending', 'verified')
            ORDER BY a.created_at DESC LIMIT 6
        ");
        $recent_alerts = $stmt->fetchAll();
    }

    // Breakdown for Charts
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM alerts GROUP BY status");
    while($row = $stmt->fetch()) {
        if(isset($status_breakdown[$row['status']])) $status_breakdown[$row['status']] = (int)$row['count'];
    }

} catch (Exception $e) {
    error_log($e->getMessage());
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-lg-10 bg-light min-vh-100">
            <div class="p-4 p-lg-5">
                
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-end mb-5">
                    <div>
                        <h1 class="h3 fw-bold mb-1">Dashboard</h1>
                        <p class="text-muted mb-0">Overview of system activity and emergency alerts.</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-white text-dark border shadow-sm px-3 py-2">
                            <i class="status-pulse-pending"></i> Live Monitoring Active
                        </span>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="row g-4 mb-5">
                    <?php if ($role_id == 1): ?>
                        <!-- Admin Stats -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm p-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-subtle text-primary p-3 rounded-3 me-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                                    </div>
                                    <div>
                                        <h3 class="fw-bold mb-0" id="stat-active"><?= $stats['active_alerts'] ?></h3>
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Active Alerts</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm p-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning-subtle text-warning p-3 rounded-3 me-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    </div>
                                    <div>
                                        <h3 class="fw-bold mb-0" id="stat-pending"><?= $stats['pending_alerts'] ?></h3>
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Pending Review</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm p-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success-subtle text-success p-3 rounded-3 me-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    </div>
                                    <div>
                                        <h3 class="fw-bold mb-0"><?= $stats['total_responses'] ?></h3>
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Total Responses</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm p-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-info-subtle text-info p-3 rounded-3 me-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                                    </div>
                                    <div>
                                        <h3 class="fw-bold mb-0"><?= $stats['active_users'] ?>/<?= $stats['total_users'] ?></h3>
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Users Active</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Responder Stats -->
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm p-4">
                                <h3 class="fw-bold text-primary mb-1"><?= $stats['personal_responses'] ?></h3>
                                <small class="text-muted fw-bold">My Responses</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm p-4">
                                <h3 class="fw-bold text-warning mb-1"><?= $stats['pending_responses'] ?></h3>
                                <small class="text-muted fw-bold">Pending Actions</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm p-4">
                                <h3 class="fw-bold text-danger mb-1"><?= $stats['active_alerts'] ?></h3>
                                <small class="text-muted fw-bold">System Alerts</small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="row g-4">
                    <!-- Chart -->
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0">Alert Status Distribution</h6>
                                <button class="btn btn-sm btn-light border" onclick="location.reload()"><i class="bi bi-arrow-clockwise"></i></button>
                            </div>
                            <div class="card-body">
                                <canvas id="statusChart" style="max-height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Alerts -->
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0">Recent Alerts</h6>
                                <a href="alerts.php" class="text-primary text-decoration-none small fw-bold">View All</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recent_alerts as $alert): ?>
                                        <div class="list-group-item border-0 px-4 py-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p class="mb-0 fw-bold" style="font-size: 0.9rem;"><?= htmlspecialchars($alert['title']) ?></p>
                                                    <small class="text-muted"><?= htmlspecialchars($alert['alert_type']) ?> • <?= date('H:i', strtotime($alert['created_at'])) ?></small>
                                                </div>
                                                <?php
                                                    $statusClass = match($alert['status']) {
                                                        'pending' => 'bg-warning-subtle text-warning',
                                                        'verified' => 'bg-info-subtle text-info',
                                                        'resolved' => 'bg-success-subtle text-success',
                                                        default => 'bg-secondary-subtle text-secondary'
                                                    };
                                                ?>
                                                <span class="badge <?= $statusClass ?>"><?= ucfirst($alert['status']) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if(empty($recent_alerts)): ?>
                                        <div class="p-5 text-center text-muted small">No recent activity.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-5">
                    <h6 class="fw-bold mb-3">Quick Actions</h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAlertModal">
                            Create Alert
                        </button>
                        <a href="alerts.php" class="btn btn-white border shadow-sm">
                            Manage All Alerts
                        </a>
                        <?php if($role_id == 1): ?>
                            <a href="users.php" class="btn btn-white border shadow-sm">
                                System Users
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Chart Initialization
    const statusData = {
        labels: ['Pending', 'Verified', 'Broadcasted', 'Resolved'],
        datasets: [{
            data: [
                <?= $status_breakdown['pending'] ?>,
                <?= $status_breakdown['verified'] ?>,
                <?= $status_breakdown['broadcasted'] ?>,
                <?= $status_breakdown['resolved'] ?>
            ],
            backgroundColor: ['#f59e0b', '#0ea5e9', '#4f46e5', '#10b981'],
            hoverOffset: 10,
            borderRadius: 5,
            spacing: 5
        }]
    };

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: statusData,
        options: {
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
            }
        }
    });

    // Stats Auto-Refresh
    function updateStats() {
        fetch('get-dashboard-stats.php')
            .then(res => res.json())
            .then(data => {
                if (document.getElementById('stat-active')) {
                    document.getElementById('stat-active').innerText = data.alerts;
                    document.getElementById('stat-pending').innerText = data.pending;
                }
            });
    }
    setInterval(updateStats, 10000); // 10 seconds
</script>

<?php 
include __DIR__ . '/../includes/modals/create-alert-modal.html';
include __DIR__ . '/../includes/footer.php'; 
?>