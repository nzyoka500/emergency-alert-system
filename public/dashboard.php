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

// Basic stats - these would normally be pulled from the database
$username = htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES, 'UTF-8');

include __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Dashboard</h1>
        <div>Welcome, <strong><?php echo $username; ?></strong></div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Active Alerts</h5>
                    <p class="card-text display-6">3</p>
                    <p class="text-muted small">Currently active community alerts</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Responses</h5>
                    <p class="card-text display-6">24</p>
                    <p class="text-muted small">Responses received to recent alerts</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Registered Users</h5>
                    <p class="card-text display-6">128</p>
                    <p class="text-muted small">Total users in the system</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <h4 class="mb-3">Recent Alerts</h4>
        <div class="list-group">
            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">Flood warning - Riverbank</h5>
                    <small class="text-muted">2 hours ago</small>
                </div>
                <p class="mb-1">Heavy rains have caused river levels to rise. Stay clear of low-lying areas.</p>
            </div>
            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">Fire reported - East District</h5>
                    <small class="text-muted">1 day ago</small>
                </div>
                <p class="mb-1">Local fire services responding. Avoid the area and follow official instructions.</p>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="alerts.php" class="btn btn-outline-primary">View All Alerts</a>
        <a href="create-alert.php" class="btn btn-primary ms-2">Create New Alert</a>
        <a href="logout.php" class="btn btn-link ms-4 text-danger">Logout</a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

