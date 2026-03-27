<?php
/**
 * sidebar.php - Professional Sidebar for Responda
 * Fixes icon scaling and adds modern indigo/slate styling
 */

$current_page = basename($_SERVER['PHP_SELF']);
$role_id = $_SESSION['role_id'] ?? null;
$current_filter = $_GET['filter'] ?? null;

/**
 * Enhanced active state checker
 */
function is_active($pages, $required_filter = null) {
    $current = basename($_SERVER['PHP_SELF']);
    $active_filter = $_GET['filter'] ?? null;

    if (is_array($pages)) {
        if (in_array($current, $pages) && $active_filter === $required_filter) {
            return 'active';
        }
    } else {
        if ($current === $pages && $active_filter === $required_filter) {
            return 'active';
        }
    }
    return '';
}
?>

<!-- Sidebar Column -->
<aside class="col-lg-2 d-none d-lg-block p-0">
    <div class="position-sticky vh-100 d-flex flex-column" style="top: 0; background: #0f172a; border-right: 1px solid rgba(255,255,255,0.05); width: 100%;">
        
        <!-- Brand Header -->
        <div class="p-4 mb-2">
            <div class="d-flex align-items-center">
                <div class="bg-primary rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px; background-color: #4e46e515 !important;">
                    <img class="img-fluid" src="./assets/images/logo-white.png" alt="Logo" style="width: 22px; height: 22px; border-radius: 50%;">
                </div>
                <div class="ms-3">
                    <h5 class="text-white fw-bold mb-0" style="letter-spacing: -0.2px; font-size: 1.1rem;">Responda</h5>
                    <!-- <span class="text-muted" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px; color: #95aac7 !important;">Account Portal</span> -->
                </div>
            </div>
        </div>


        <!-- Navigation Menu -->
        <nav class="nav flex-column px-3 flex-grow-1">
            <small class="text-muted px-2 mb-2" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: #64748b !important;">Main Menu</small>
            
            <a href="dashboard.php" class="nav-link-custom <?php echo is_active('dashboard.php'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z" />
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="alerts.php" class="nav-link-custom <?php echo is_active(['alerts.php', 'alert-details.php', 'create-alert.php'], null); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
                <span>Alerts</span>
            </a>

            <?php if ($role_id == 1): ?>
            <a href="users.php" class="nav-link-custom <?php echo is_active('users.php'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                <span>Users</span>
            </a>
            <?php endif; ?>

            <a href="alerts.php?filter=resolved" class="nav-link-custom <?php echo is_active('alerts.php', 'resolved'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Resolved</span>
            </a>

            <div class="my-3"></div>
            <small class="text-muted px-2 mb-2" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: #64748b !important;">Insights</small>

            <a href="reports.php" class="nav-link-custom <?php echo is_active('reports.php'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V19.875c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
                <span>Reports</span>
            </a>

            <!-- System Logout -->
            <div class="mt-auto mb-4 px-2">
                <a href="logout.php" class="nav-link-custom text-danger-hover" style="color: #fca5a5 !important;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                    <span>Logout</span>
                </a>
            </div>
        </nav>
    </div>
</aside>

<!-- Mobile Navigation Fix -->
<div class="d-lg-none sticky-top bg-white border-bottom p-3" style="z-index: 1050;">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <div class="rounded p-1" style="background-color: #4f46e5;">
                <img src="assets/images/logo-white.png" style="width: 20px; filter: brightness(0) invert(1);">
            </div>
            <span class="ms-2 fw-bold text-dark">Responda</span>
        </div>
        <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu">
             <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
    </div>
    <div class="collapse mt-3" id="mobileMenu">
        <div class="list-group list-group-flush shadow-sm rounded border">
            <a href="dashboard.php" class="list-group-item list-group-item-action border-0">Dashboard</a>
            <a href="alerts.php" class="list-group-item list-group-item-action border-0">Alerts</a>
            <?php if ($role_id == 1): ?>
                <a href="users.php" class="list-group-item list-group-item-action border-0">Users</a>
            <?php endif; ?>
            <a href="logout.php" class="list-group-item list-group-item-action border-0 text-danger">Logout</a>
        </div>
    </div>
</div>