<?php
// sidebar.php - Navigation sidebar for dashboard pages
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

<div class="col-lg-2 mb-4 mb-lg-0">
    <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
        <div class="card-body p-0">
            <nav class="nav flex-column">
                <!-- Dashboard Link -->
                <a href="dashboard.php" class="nav-link <?php echo is_active('dashboard.php'); ?> border-bottom">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>

                <!-- Alerts Section -->
                <div class="nav-item">
                    <a class="nav-link <?php echo is_active_section(['alerts.php', 'alert-details.php', 'create-alert.php']); ?> dropdown-toggle border-bottom" href="#alertsMenu" data-bs-toggle="collapse">
                        <i class="bi bi-exclamation-triangle"></i> Alerts
                    </a>
                    <div class="collapse <?php echo is_active_section(['alerts.php', 'alert-details.php', 'create-alert.php']) ? 'show' : ''; ?>" id="alertsMenu">
                        <div class="nav flex-column ms-3 border-start">
                            <a href="alerts.php" class="nav-link small <?php echo is_active('alerts.php'); ?>">
                                <i class="bi bi-list-ul"></i> View All
                            </a>
                            <a href="create-alert.php" class="nav-link small <?php echo is_active('create-alert.php'); ?>">
                                <i class="bi bi-plus-circle"></i> Create Alert
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Users Section (Admin Only) -->
                <?php if ($role_id == 1): ?>
                    <a href="users.php" class="nav-link <?php echo is_active('users.php'); ?> border-bottom">
                        <i class="bi bi-people"></i> Users
                    </a>
                <?php endif; ?>

                <!-- Reports -->
                <a href="#" class="nav-link border-bottom disabled">
                    <i class="bi bi-bar-chart"></i> Reports
                </a>

                <!-- Settings -->
                <a href="#" class="nav-link border-bottom disabled">
                    <i class="bi bi-gear"></i> Settings
                </a>

                <!-- Divider -->
                <hr class="my-2">

                <!-- Help & Logout -->
                <a href="#" class="nav-link small disabled">
                    <i class="bi bi-question-circle"></i> Help
                </a>
                <a href="logout.php" class="nav-link small text-danger">
                    <i class="bi bi-box-arrow-left"></i> Logout
                </a>
            </nav>
        </div>
    </div>
</div>
