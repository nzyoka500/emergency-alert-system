<?php
// sidebar.php - Professional full-height sidebar for dashboard
// Determine active page for highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$role_id = $_SESSION['role_id'] ?? null;

function is_active($page) {
    return basename($_SERVER['PHP_SELF']) === $page ? 'active' : '';
}

function is_active_section($pages) {
    $current = basename($_SERVER['PHP_SELF']);
    return in_array($current, $pages) ? 'active' : '';
}
?>

<!-- Sidebar Column -->
<aside class="col-lg-2 d-none d-lg-block">
    <div class="position-sticky" style="top:20px;">
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(180deg,#3b82f6 0%, #7c3aed 100%); border-radius:12px; height: 100vh; overflow: hidden;">
            <div class="card-body d-flex flex-column p-3 h-100" style="overflow: hidden;">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-2">
                        <div style="width:44px; height:44px; border-radius:8px; background: rgba(255,255,255,0.12);display:flex;align-items:center;justify-content:center;font-weight:700;">R</div>
                    </div>
                    <div>
                        <div class="fw-bold">Responda</div>
                        <small class="d-block text-white-50">Emergency Alerts</small>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="card bg-white-10 border-0 mb-2" style="background: rgba(255,255,255,0.06);">
                        <div class="card-body p-2 d-flex align-items-center">
                            <img src="/assets/images/transparent-logo.png" alt="logo" style="width:36px;height:36px;border-radius:6px;object-fit:cover;opacity:0.95;margin-right:8px;"/>
                            <div>
                                <div class="text-white fw-semibold" style="font-size:0.95rem"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?></div>
                                <small class="text-white-50"><?php echo htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-2">
                        <input type="search" class="form-control form-control-sm" placeholder="Search..." aria-label="Search" />
                        <button class="btn btn-sm btn-light" type="button"><i class="bi bi-search"></i></button>
                    </div>
                </div>

                <nav class="nav flex-column mb-3" style="overflow:auto; max-height: calc(100vh - 220px);">
                    <a href="dashboard.php" class="nav-link text-white py-2 px-2 rounded <?php echo is_active('dashboard.php'); ?>">
                        <i class="bi bi-speedometer2 me-2"></i> <span>Dashboard</span>
                    </a>

                    <button class="btn btn-sm btn-transparent text-white text-start w-100 d-flex justify-content-between align-items-center py-2 px-2 rounded collapsed" data-bs-toggle="collapse" data-bs-target="#alertsMenu" aria-expanded="false">
                        <span><i class="bi bi-exclamation-triangle me-2"></i> Alerts</span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>
                    <div class="collapse <?php echo is_active_section(['alerts.php','alert-details.php','create-alert.php']) ? 'show' : ''; ?> mt-2" id="alertsMenu">
                        <div class="nav flex-column ms-2">
                            <a href="alerts.php" class="nav-link small text-white-50 py-1 <?php echo is_active('alerts.php'); ?>">View All</a>
                            <a href="create-alert.php" class="nav-link small text-white-50 py-1 <?php echo is_active('create-alert.php'); ?>">Create Alert</a>
                        </div>
                    </div>

                    <?php if ($role_id == 1): ?>
                        <a href="users.php" class="nav-link text-white py-2 px-2 rounded <?php echo is_active('users.php'); ?>">
                            <i class="bi bi-people me-2"></i> Users
                        </a>
                    <?php endif; ?>

                    <a href="alerts.php?filter=resolved" class="nav-link text-white py-2 px-2 rounded">
                        <i class="bi bi-archive me-2"></i> Resolved
                    </a>

                    <a href="#" class="nav-link text-white py-2 px-2 rounded disabled">
                        <i class="bi bi-bar-chart me-2"></i> Reports
                    </a>
                </nav>

                <div class="mt-auto">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-white-50">Role: <?php echo ($role_id == 1) ? 'Administrator' : 'Responder'; ?></small>
                        <a href="logout.php" class="btn btn-sm btn-outline-light">Logout</a>
                    </div>
                    <div class="text-white-50 small">v1.0 • <?php echo date('Y'); ?></div>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile fallback: simple top nav for small screens -->
<div class="d-lg-none mb-3">
    <nav class="navbar navbar-light bg-light rounded p-2">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Responda</a>
            <a class="btn btn-outline-secondary btn-sm" href="logout.php">Logout</a>
        </div>
    </nav>
</div>
