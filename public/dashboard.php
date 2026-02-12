<?php
// dashboard.php
// Protected dashboard page for logged-in users

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
$role_id = $_SESSION['role_id'] ?? null;

// Fetch dashboard statistics from database
$stats = [
    'active_alerts' => 0,
    'pending_alerts' => 0,
    'responses' => 0,
    'total_users' => 0,
    'active_users' => 0
];

$recent_alerts = [];

try {
    $pdo = getPDO();

    // Count active alerts (status: pending, verified, broadcasted)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM alerts WHERE status IN ('pending', 'verified', 'broadcasted')");
    $stats['active_alerts'] = $stmt->fetch()['count'] ?? 0;

    // Count pending alerts
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM alerts WHERE status = 'pending'");
    $stats['pending_alerts'] = $stmt->fetch()['count'] ?? 0;

    // Count total responses
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM alert_responses");
    $stats['responses'] = $stmt->fetch()['count'] ?? 0;

    // Count total users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $stmt->fetch()['count'] ?? 0;

    // Count active users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
    $stats['active_users'] = $stmt->fetch()['count'] ?? 0;

    // Fetch recent alerts
    $stmt = $pdo->query("
        SELECT a.id, a.title, a.description, a.status, a.created_at, at.name as alert_type 
        FROM alerts a 
        LEFT JOIN alert_types at ON a.alert_type_id = at.id 
        ORDER BY a.created_at DESC 
        LIMIT 5
    ");
    $recent_alerts = $stmt->fetchAll() ?? [];
} catch (Exception $e) {
    error_log('Dashboard stats error: ' . $e->getMessage());
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid mt-5 mb-5">
    <div class="row">
        <!-- Sidebar Navigation -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col-lg-10" style="min-height: calc(100vh - 40px); overflow:auto; padding: 24px 32px;">
            <!-- Header Section -->
            <div class="row mb-5 align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold">Dashboard</h1>
                    <p class="text-muted">Emergency Alert & Response System</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <p class="mb-0"><small class="text-muted">Welcome back,</small></p>
                            <h5 class="mb-0"><?php echo $username; ?></h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards Row -->
            <div class="row g-4 mb-5">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #667eea;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Active Alerts</p>
                            <h2 class="fw-bold mb-0" style="color: #667eea;"><?php echo $stats['active_alerts']; ?></h2>
                        </div>
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" style="opacity: 0.2;">
                            <circle cx="20" cy="20" r="18" stroke="#667eea" stroke-width="2"/>
                            <path d="M20 10v20M10 20h20" stroke="#667eea" stroke-width="2"/>
                        </svg>
                    </div>
                    <small class="text-muted">Currently active</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #764ba2;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Pending Review</p>
                            <h2 class="fw-bold mb-0" style="color: #764ba2;"><?php echo $stats['pending_alerts']; ?></h2>
                        </div>
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" style="opacity: 0.2;">
                            <rect x="8" y="10" width="24" height="20" stroke="#764ba2" stroke-width="2" rx="2"/>
                        </svg>
                    </div>
                    <small class="text-muted">Need attention</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #28a745;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Responses</p>
                            <h2 class="fw-bold mb-0" style="color: #28a745;"><?php echo $stats['responses']; ?></h2>
                        </div>
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" style="opacity: 0.2;">
                            <path d="M12 20h16M20 12v16" stroke="#28a745" stroke-width="2"/>
                        </svg>
                    </div>
                    <small class="text-muted">Total in system</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Active Users</p>
                            <h2 class="fw-bold mb-0" style="color: #ffc107;"><?php echo $stats['active_users']; ?>/<?php echo $stats['total_users']; ?></h2>
                        </div>
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" style="opacity: 0.2;">
                            <circle cx="14" cy="14" r="4" stroke="#ffc107" stroke-width="2"/>
                            <path d="M10 22c0-2 2-3 4-3s4 1 4 3" stroke="#ffc107" stroke-width="2"/>
                        </svg>
                    </div>
                    <small class="text-muted">Online users</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Alerts Section -->
    <div class="row mb-5">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Recent Alerts</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($recent_alerts) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_alerts as $alert): ?>
                                <?php
                                    $status_color = match($alert['status']) {
                                        'pending' => 'warning',
                                        'verified' => 'info',
                                        'broadcasted' => 'success',
                                        'resolved' => 'secondary',
                                        default => 'light'
                                    };
                                    $time_ago = date('M d, Y H:i', strtotime($alert['created_at']));
                                ?>
                                <div class="list-group-item p-4 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($alert['title'], ENT_QUOTES, 'UTF-8'); ?></h6>
                                            <span class="badge bg-<?php echo $status_color; ?>"><?php echo ucfirst($alert['status']); ?></span>
                                            <span class="badge bg-light text-dark ms-2"><?php echo htmlspecialchars($alert['alert_type'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                        <small class="text-muted"><?php echo $time_ago; ?></small>
                                    </div>
                                    <p class="text-muted small mb-0"><?php echo htmlspecialchars(substr($alert['description'], 0, 100), ENT_QUOTES, 'UTF-8'); ?>...</p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted">
                            <p>No alerts yet. Create one to get started.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="create-alert.php" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-plus-circle"></i> Create New Alert
                    </a>
                    <a href="alerts.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-list-ul"></i> View All Alerts
                    </a>
                    <a href="users.php" class="btn btn-outline-secondary w-100 mb-2">
                        <i class="bi bi-people"></i> Manage Users
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="mb-2">System Info</h6>
                    <small class="text-muted d-block mb-1"><strong>Role:</strong> <?php echo ($role_id == 1) ? 'Administrator' : 'Responder'; ?></small>
                    <small class="text-muted d-block mb-1"><strong>Status:</strong> <span class="badge bg-success">Active</span></small>
                    <small class="text-muted"><strong>Last Login:</strong> Today</small>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>


