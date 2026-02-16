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
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(180deg,#3b82f6 0%, #9155fa 100%); border-radius:12px; height: 100vh; overflow: hidden;">
            <div class="card-body d-flex flex-column p-3 h-100" style="overflow: hidden;">
                <div class="d-flex align-items-center mb-3">
                    
                    <!-- Logo -->
                    <div class="me-2">
                        <div style="width:44px; height:44px; border-radius:50%; background: rgba(255,255,255,0.12);display:flex;align-items:center;justify-content:center;font-weight:700;">
                            <img src="assets/images/logo.svg" alt="Responda Logo" style="width:32px; height:32px; border-radius:6px; object-fit:cover; opacity:0.95;">
                        </div>
                    </div>
                    <div>
                        <strong class="h5 mb-0" style="color:#fff;">Responda</strong>
                        <small class="d-block text-white-50">Emergency Alerts</small>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="card bg-white-10 border-0 mb-2" style="background: rgba(255,255,255,0.06);">
                        <div class="card-body p-2 d-flex align-items-center">
                            
                            <div>
                                <div class="text-white fw-semibold" style="font-size:0.95rem"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?></div>
                                <small class="text-white-50"><?php echo htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-2">
                        <input type="search" class="form-control form-control-sm rounded-3" placeholder="Search..." aria-label="Search" />
                        <button class="btn btn-sm btn-light" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <nav class="nav flex-column mb-3" style="overflow:auto; max-height: calc(100vh - 220px);">
                    <a href="dashboard.php" class="nav-link text-white py-2 px-2 rounded <?php echo is_active('dashboard.php'); ?>">

                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-speedometer me-2" viewBox="0 0 16 16">
                                <path d="M8 2a.5.5 0 0 1 .5.5V4a.5.5 0 0 1-1 0V2.5A.5.5 0 0 1 8 2M3.732 3.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707M2 8a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 8m9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5m.754-4.246a.39.39 0 0 0-.527-.02L7.547 7.31A.91.91 0 1 0 8.85 8.569l3.434-4.297a.39.39 0 0 0-.029-.518z"/>
                                <path fill-rule="evenodd" d="M6.664 15.889A8 8 0 1 1 9.336.11a8 8 0 0 1-2.672 15.78zm-4.665-4.283A11.95 11.95 0 0 1 8 10c2.186 0 4.236.585 6.001 1.606a7 7 0 1 0-12.002 0"/>
                            </svg>Dashboard
                        </span>
                    </a>

                    <button class="btn btn-sm btn-transparent text-white text-start w-100 d-flex justify-content-between align-items-center py-2 px-2 rounded collapsed" data-bs-toggle="collapse" data-bs-target="#alertsMenu" aria-expanded="false">
                    
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-circle me-2" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
                            </svg>Alerts
                        </span>

                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/>
                        </svg>
                    </button>

                    <div class="collapse <?php echo is_active_section(['alerts.php','alert-details.php','create-alert.php']) ? 'show' : ''; ?> mt-2" id="alertsMenu">
                        <div class="nav flex-column ms-2">
                            <a href="alerts.php" class="nav-link small text-white-50 py-1 <?php echo is_active('alerts.php'); ?>">View All</a>
                            <a href="create-alert.php" class="nav-link small text-white-50 py-1 <?php echo is_active('create-alert.php'); ?>">Create Alert</a>
                        </div>
                    </div>

                    <?php if ($role_id == 1): ?>
                        <a href="users.php" class="nav-link text-white py-2 px-2 rounded <?php echo is_active('users.php'); ?>">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people me-2" viewBox="0 0 16 16">
                                    <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/>
                                </svg>Users
                            </span>
                        </a>
                    <?php endif; ?>

                    <a href="alerts.php?filter=resolved" class="nav-link text-white py-2 px-2 rounded <?php echo is_active('alerts.php') && isset($_GET['filter']) && $_GET['filter'] === 'resolved' ? 'active' : ''; ?>">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-circle me-2" viewBox="0 0 16 16">
                                <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0"/>
                                <path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708-0z"/>
                            </svg>Resolved
                        </span>
                    </a>

                    <a href="reports.php" class="nav-link text-white py-2 px-2 rounded <?php echo is_active('reports.php'); ?>">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-database-check me-2" viewBox="0 0 16 16">
                                <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514"/>
                                <path d="M12.096 6.223A5 5 0 0 0 13 5.698V7c0 .289-.213.654-.753 1.007a4.5 4.5 0 0 1 1.753.25V4c0-1.007-.875-1.755-1.904-2.223C11.022 1.289 9.573 1 8 1s-3.022.289-4.096.777C2.875 2.245 2 2.993 2 4v9c0 1.007.875 1.755 1.904 2.223C4.978 15.71 6.427 16 8 16c.536 0 1.058-.034 1.555-.097a4.5 4.5 0 0 1-.813-.927Q8.378 15 8 15c-1.464 0-2.766-.27-3.682-.687C3.356 13.875 3 13.373 3 13v-1.302c.271.202.58.378.904.525C4.978 12.71 6.427 13 8 13h.027a4.6 4.6 0 0 1 0-1H8c-1.464 0-2.766-.27-3.682-.687C3.356 10.875 3 10.373 3 10V8.698c.271.202.58.378.904.525C4.978 9.71 6.427 10 8 10q.393 0 .774-.024a4.5 4.5 0 0 1 1.102-1.132C9.298 8.944 8.666 9 8 9c-1.464 0-2.766-.27-3.682-.687C3.356 7.875 3 7.373 3 7V5.698c.271.202.58.378.904.525C4.978 6.711 6.427 7 8 7s3.022-.289 4.096-.777M3 4c0-.374.356-.875 1.318-1.313C5.234 2.271 6.536 2 8 2s2.766.27 3.682.687C12.644 3.125 13 3.627 13 4c0 .374-.356.875-1.318 1.313C10.766 5.729 9.464 6 8 6s-2.766-.27-3.682-.687C3.356 4.875 3 4.373 3 4"/>
                            </svg>Reports
                        </span>
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
