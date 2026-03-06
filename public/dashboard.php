<?php
// dashboard.php
// Protected dashboard page for logged-in users
// Role-based dashboard (Admin: System Overview, Responder: Personal Stats)

require_once __DIR__ . '/../includes/config.php';

// Ensure session is started (config.php may start it)
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
$role_name = ($role_id == 1) ? 'Administrator' : 'Responder';

// Initialize stats arrays
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
        // ADMIN DASHBOARD - System Overview
        // Count active alerts (status: pending, verified, broadcasted)
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM alerts WHERE status IN ('pending', 'verified', 'broadcasted')");
        $stats['active_alerts'] = $stmt->fetch()['count'] ?? 0;

        // Count pending review
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM alerts WHERE status = 'pending'");
        $stats['pending_alerts'] = $stmt->fetch()['count'] ?? 0;

        // Count verified
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM alerts WHERE status = 'verified'");
        $stats['verified_alerts'] = $stmt->fetch()['count'] ?? 0;

        // Count resolved
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM alerts WHERE status = 'resolved'");
        $stats['resolved_alerts'] = $stmt->fetch()['count'] ?? 0;

        // Count total responses
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM alert_responses");
        $stats['total_responses'] = $stmt->fetch()['count'] ?? 0;

        // Count total users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch()['count'] ?? 0;

        // Count active users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
        $stats['active_users'] = $stmt->fetch()['count'] ?? 0;

        // Get status breakdown for chart
        $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM alerts GROUP BY status");
        $breakdown = $stmt->fetchAll();
        foreach ($breakdown as $row) {
            if (isset($status_breakdown[$row['status']])) {
                $status_breakdown[$row['status']] = $row['count'];
            }
        }

        // Fetch recent alerts (all)
        $stmt = $pdo->query("
            SELECT a.id, a.title, a.description, a.status, a.created_at, at.name as alert_type, u.full_name as created_by
            FROM alerts a 
            LEFT JOIN alert_types at ON a.alert_type_id = at.id
            LEFT JOIN users u ON a.created_by = u.id
            ORDER BY a.created_at DESC 
            LIMIT 8
        ");
        $recent_alerts = $stmt->fetchAll() ?? [];
    } else {
        // RESPONDER DASHBOARD - Personal Stats
        // Count alerts they've responded to
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM alert_responses WHERE responder_id = ?");
        $stmt->execute([$user_id]);
        $stats['personal_responses'] = $stmt->fetch()['count'] ?? 0;

        // Count pending responses for this responder
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM alert_responses 
            WHERE responder_id = ? AND status = 'pending'
        ");
        $stmt->execute([$user_id]);
        $stats['pending_responses'] = $stmt->fetch()['count'] ?? 0;

        // Count active alerts system-wide
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM alerts WHERE status IN ('pending', 'verified', 'broadcasted')");
        $stats['active_alerts'] = $stmt->fetch()['count'] ?? 0;

        // Count total system responses
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM alert_responses");
        $stats['total_responses'] = $stmt->fetch()['count'] ?? 0;

        // Get alerts they can respond to
        $stmt = $pdo->prepare("
            SELECT a.id, a.title, a.description, a.status, a.created_at, at.name as alert_type
            FROM alerts a 
            LEFT JOIN alert_types at ON a.alert_type_id = at.id
            WHERE a.status IN ('pending', 'verified', 'broadcasted')
            ORDER BY a.created_at DESC 
            LIMIT 8
        ");
        $stmt->execute();
        $recent_alerts = $stmt->fetchAll() ?? [];

        // Get status breakdown for this responder's responses
        $stmt = $pdo->prepare("
            SELECT ar.status, COUNT(*) as count 
            FROM alert_responses ar
            WHERE ar.responder_id = ?
            GROUP BY ar.status
        ");
        $stmt->execute([$user_id]);
        $responder_breakdown = $stmt->fetchAll();
        foreach ($responder_breakdown as $row) {
            if (isset($status_breakdown[$row['status']])) {
                $status_breakdown[$row['status']] = $row['count'];
            }
        }
    }
} catch (Exception $e) {
    error_log('Dashboard stats error: ' . $e->getMessage());
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid mt-2 mb-0">
    <div class="row">
        <!-- Sidebar Navigation -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col-lg-10" style="min-height: calc(100vh - 40px); overflow:auto; padding: 24px 32px;">
            
            <!-- Header Section -->
            <div class="row mb-3 align-items-center" style="padding-bottom: 16px; border-bottom: 1px solid #e8ebf2;">
                <div class="col-md-8">
                    <h2 class="fw-bold mb-1" style="font-size: 28px; color: #2d3748;">Dashboard</h2>
                    <small class="text-muted" style="font-size: 13px;">
                        <?php 
                        if ($role_id == 1) {
                            echo 'System Overview & Management';
                        } else {
                            echo 'Your Alert Response Center';
                        }
                        ?>
                    </small>
                </div>
                <div class="col-md-4 text-end">
                    <div style="background: linear-gradient(135deg, #667eea 0%, #a36ed8 100%); border-radius: 8px; padding: 12px 16px; color: white;">
                        <small style="display: block; opacity: 0.9; font-size: 12px;">Welcome back,</small>
                        <h6 class="mb-1 fw-bold" style="font-size: 16px; margin: 4px 0;"><?php echo $username; ?></h6>
                        <small style="display: block; opacity: 0.95; font-size: 12px;">
                            <?php echo ($role_id == 1) ? '👤 Administrator' : '📋 Responder'; ?>
                        </small>
                    </div>
                </div>
            </div>

            <?php if ($role_id == 1): ?>
                <!-- ADMIN DASHBOARD -->
                
                <!-- Stats Cards Row -->
                <div class="row g-3 mt-3 mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #667eea;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="text-muted small mb-1" style="font-size: 12px;">Active Alerts</p>
                                        <h3 class="fw-bold mb-0" style="color: #667eea; font-size: 24px;"><?php echo $stats['active_alerts']; ?></h3>
                                    </div>
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" style="opacity: 0.2;">
                                        <circle cx="20" cy="20" r="18" stroke="#667eea" stroke-width="2"/>
                                        <path d="M20 10v20M10 20h20" stroke="#667eea" stroke-width="2"/>
                                    </svg>
                                </div>
                                <small class="text-muted">Pending, Verified, Broadcasted</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="text-muted small mb-1" style="font-size: 12px;">Pending Review</p>
                                        <h3 class="fw-bold mb-0" style="color: #ffc107; font-size: 24px;"><?php echo $stats['pending_alerts']; ?></h3>
                                    </div>
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" style="opacity: 0.2;">
                                        <rect x="8" y="10" width="24" height="20" stroke="#ffc107" stroke-width="2" rx="2"/>
                                    </svg>
                                </div>
                                <small class="text-muted">Awaiting verification</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #28a745;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="text-muted small mb-1" style="font-size: 12px;">Total Responses</p>
                                        <h3 class="fw-bold mb-0" style="color: #28a745; font-size: 24px;"><?php echo $stats['total_responses']; ?></h3>
                                    </div>
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" style="opacity: 0.2;">
                                        <path d="M12 20h16M20 12v16" stroke="#28a745" stroke-width="2"/>
                                    </svg>
                                </div>
                                <small class="text-muted">Responder submissions</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0dcaf0;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="text-muted small mb-1" style="font-size: 12px;">Active Users</p>
                                        <h3 class="fw-bold mb-0" style="color: #0dcaf0; font-size: 24px;"><?php echo $stats['active_users']; ?>/<?php echo $stats['total_users']; ?></h3>
                                    </div>
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" style="opacity: 0.2;">
                                        <circle cx="14" cy="14" r="4" stroke="#0dcaf0" stroke-width="2"/>
                                        <path d="M10 22c0-2 2-3 4-3s4 1 4 3" stroke="#0dcaf0" stroke-width="2"/>
                                    </svg>
                                </div>
                                <small class="text-muted">Online / Total</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Content Row -->
                <div class="row g-4 mb-4">
                    <!-- Chart Section -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0">Alert Status Distribution</h5>
                            </div>
                            <div class="card-body p-4" style="height: 350px;">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats Section -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0">Alert Breakdown</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Pending</span>
                                        <div>
                                            <span class="badge bg-warning"><?php echo $status_breakdown['pending']; ?></span>
                                            <small class="text-muted ms-2"><?php echo $stats['active_alerts'] > 0 ? round(($status_breakdown['pending'] / $stats['active_alerts']) * 100) : 0; ?>%</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Verified</span>
                                        <div>
                                            <span class="badge bg-info"><?php echo $stats['verified_alerts']; ?></span>
                                            <small class="text-muted ms-2"><?php echo $stats['active_alerts'] > 0 ? round(($stats['verified_alerts'] / $stats['active_alerts']) * 100) : 0; ?>%</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Broadcasted</span>
                                        <div>
                                            <span class="badge bg-primary"><?php echo $status_breakdown['broadcasted']; ?></span>
                                            <small class="text-muted ms-2"><?php echo $stats['active_alerts'] > 0 ? round(($status_breakdown['broadcasted'] / $stats['active_alerts']) * 100) : 0; ?>%</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Resolved</span>
                                    <div>
                                        <span class="badge bg-success"><?php echo $stats['resolved_alerts']; ?></span>
                                        <small class="text-muted ms-2"><?php echo ($stats['pending_alerts'] + $stats['verified_alerts'] + $stats['resolved_alerts'] + $status_breakdown['broadcasted']) > 0 ? round(($stats['resolved_alerts'] / ($stats['pending_alerts'] + $stats['verified_alerts'] + $stats['resolved_alerts'] + $status_breakdown['broadcasted'])) * 100) : 0; ?>%</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm bg-light">
                            <div class="card-body">
                                <h6 class="mb-3">Quick Actions</h6>
                                <div class="d-flex flex-row gap-2">
                                    <a type="button" data-bs-toggle="modal" data-bs-target="#createAlertModal" class="btn btn-primary btn-sm" style="text-align:center; width: 140px;">New Alert</a>
                                    <a href="alerts.php" class="btn btn-outline-primary btn-sm" style="width: 140px;">View Alerts</a>
                                    <a href="users.php" class="btn btn-outline-secondary btn-sm" style="width: 140px;">Manage Users</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Alerts Section -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent System Alerts</h5>
                            <a href="alerts.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (count($recent_alerts) > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_alerts as $alert): ?>
                                    <?php
                                        $status_color = match($alert['status']) {
                                            'pending' => 'warning',
                                            'verified' => 'info',
                                            'broadcasted' => 'primary',
                                            'resolved' => 'success',
                                            default => 'secondary'
                                        };
                                        $time_ago = date('M d, Y H:i', strtotime($alert['created_at']));
                                    ?>
                                    <div class="list-group-item p-4 border-bottom">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1">
                                                    <a href="alert-details.php?id=<?php echo $alert['id']; ?>" class="text-decoration-none" style="color: #2d3748;">
                                                        <?php echo htmlspecialchars($alert['title'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                </h6>
                                                <span class="badge bg-<?php echo $status_color; ?>"><?php echo ucfirst($alert['status']); ?></span>
                                                <span class="badge bg-light text-dark ms-2">
                                                    <?php echo htmlspecialchars($alert['alert_type'], ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                                <small class="text-muted ms-2">by <?php echo htmlspecialchars($alert['created_by'] ?? 'System', ENT_QUOTES, 'UTF-8'); ?></small>
                                            </div>
                                            <small class="text-muted"><?php echo $time_ago; ?></small>
                                        </div>
                                        <p class="text-muted small mb-0"><?php echo htmlspecialchars(substr($alert['description'], 0, 120), ENT_QUOTES, 'UTF-8'); ?>...</p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="p-5 text-center text-muted">
                                <p>No alerts in the system yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else: ?>
                <!-- RESPONDER DASHBOARD -->
                
                <!-- Personal Stats Cards -->
                <div class="row g-3 mt-3 mb-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #667eea;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="text-muted small mb-1" style="font-size: 12px;">My Responses</p>
                                        <h3 class="fw-bold mb-0" style="color: #667eea; font-size: 24px;"><?php echo $stats['personal_responses']; ?></h3>
                                    </div>
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" style="opacity: 0.2;">
                                        <path d="M12 20h16M20 12v16" stroke="#667eea" stroke-width="2"/>
                                    </svg>
                                </div>
                                <small class="text-muted">Total submitted</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="text-muted small mb-1" style="font-size: 12px;">Pending Actions</p>
                                        <h3 class="fw-bold mb-0" style="color: #ffc107; font-size: 24px;"><?php echo $stats['pending_responses']; ?></h3>
                                    </div>
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" style="opacity: 0.2;">
                                        <rect x="8" y="10" width="24" height="20" stroke="#ffc107" stroke-width="2" rx="2"/>
                                    </svg>
                                </div>
                                <small class="text-muted">Review needed</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #28a745;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="text-muted small mb-1" style="font-size: 12px;">Active Alerts</p>
                                        <h3 class="fw-bold mb-0" style="color: #28a745; font-size: 24px;"><?php echo $stats['active_alerts']; ?></h3>
                                    </div>
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" style="opacity: 0.2;">
                                        <circle cx="20" cy="20" r="18" stroke="#28a745" stroke-width="2"/>
                                        <path d="M20 10v20M10 20h20" stroke="#28a745" stroke-width="2"/>
                                    </svg>
                                </div>
                                <small class="text-muted">Available to respond</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Response Status Chart -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0">Your Response History</h5>
                            </div>
                            <div class="card-body p-4" style="height: 350px;">
                                <canvas id="responsesChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0">Response Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Pending Review</span>
                                        <span class="badge bg-warning"><?php echo $status_breakdown['pending']; ?></span>
                                    </div>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Verified</span>
                                        <span class="badge bg-info"><?php echo $status_breakdown['verified']; ?></span>
                                    </div>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Broadcasted</span>
                                        <span class="badge bg-primary"><?php echo $status_breakdown['broadcasted']; ?></span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Resolved</span>
                                    <span class="badge bg-success"><?php echo $status_breakdown['resolved']; ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm bg-light">
                            <div class="card-body">
                                <h6 class="mb-3">Quick Actions</h6>
                                <div class="d-flex flex-column gap-2">
                                    <a href="alerts.php" class="btn btn-primary btn-sm" style="width: 140px;">View Alerts</a>
                                    <a href="alerts.php?filter=pending" class="btn btn-warning btn-sm" style="width: 140px;">See Pending</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alerts Available for Response -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Alerts Awaiting Your Response</h5>
                            <a href="alerts.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (count($recent_alerts) > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_alerts as $alert): ?>
                                    <?php
                                        $status_color = match($alert['status']) {
                                            'pending' => 'warning',
                                            'verified' => 'info',
                                            'broadcasted' => 'primary',
                                            'resolved' => 'success',
                                            default => 'secondary'
                                        };
                                        $time_ago = date('M d, Y H:i', strtotime($alert['created_at']));
                                    ?>
                                    <div class="list-group-item p-4 border-bottom">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <a href="alert-details.php?id=<?php echo $alert['id']; ?>" class="text-decoration-none" style="color: #2d3748;">
                                                        <?php echo htmlspecialchars($alert['title'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                </h6>
                                                <span class="badge bg-<?php echo $status_color; ?>"><?php echo ucfirst($alert['status']); ?></span>
                                                <span class="badge bg-light text-dark ms-2"><?php echo htmlspecialchars($alert['alert_type'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            </div>
                                            <small class="text-muted"><?php echo $time_ago; ?></small>
                                        </div>
                                        <p class="text-muted small"><?php echo htmlspecialchars(substr($alert['description'], 0, 120), ENT_QUOTES, 'UTF-8'); ?>...</p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="p-5 text-center text-muted">
                                <p>No active alerts to respond to at the moment.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Chart.js and inline chart initialization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.2/dist/chart.umd.js" integrity="sha384-eI7PSr3L1XLISH8JdDII5YN/njoSsxfbrkCTnJrzXt+ENP5MOVBxD+l6sEG4zoLp" crossorigin="anonymous"></script>
<script>
    (function(){
        const roleId = <?php echo json_encode($role_id); ?>;
        const statusBreakdown = <?php echo json_encode($status_breakdown); ?>;

        if (roleId == 1) {
            // ADMIN CHART - Alert Status Distribution
            const adminLabels = ['Pending', 'Verified', 'Broadcasted', 'Resolved'];
            const adminData = [
                statusBreakdown.pending,
                statusBreakdown.verified,
                statusBreakdown.broadcasted,
                statusBreakdown.resolved
            ];
            
            const ctx = document.getElementById('statusChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: adminLabels,
                        datasets: [{
                            data: adminData,
                            backgroundColor: ['#ffc107', '#0dcaf0', '#0d6efd', '#28a745'],
                            borderColor: '#fff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { padding: 15, font: { size: 13 } }
                            }
                        }
                    }
                });
            }
        } else {
            // RESPONDER CHART - Response History
            const responderLabels = ['Pending', 'Verified', 'Broadcasted', 'Resolved'];
            const responderData = [
                statusBreakdown.pending,
                statusBreakdown.verified,
                statusBreakdown.broadcasted,
                statusBreakdown.resolved
            ];

            const ctx = document.getElementById('responsesChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: responderLabels,
                        datasets: [{
                            label: 'My Responses',
                            data: responderData,
                            backgroundColor: ['#ffc107', '#0dcaf0', '#0d6efd', '#28a745'],
                            borderRadius: 6,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1 }
                            }
                        }
                    }
                });
            }
        }
    })();
</script>
<?php include __DIR__ . '/../includes/modals/create-alert-modal.html'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>


