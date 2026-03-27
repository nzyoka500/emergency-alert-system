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
        // UPDATED: Table 'Alerts', Column 'Alerts_status'
        $stmt = $pdo->query("
            SELECT
            SUM(Alerts_status='pending') as pending,
            SUM(Alerts_status='verified') as verified,
            SUM(Alerts_status='broadcasted') as broadcasted,
            SUM(Alerts_status='resolved') as resolved
            FROM Alerts
        ");
        $row = $stmt->fetch();
        $stats['active_alerts'] = (int)$row['pending'] + (int)$row['verified'] + (int)$row['broadcasted'];
        $stats['pending_alerts'] = (int)$row['pending'];
        $stats['verified_alerts'] = (int)$row['verified'];
        $stats['resolved_alerts'] = (int)$row['resolved'];

        // UPDATED: Table 'Users', Column 'Users_status'
        $userStats = $pdo->query("SELECT COUNT(*) as total, SUM(Users_status='active') as active FROM Users")->fetch();
        $stats['total_users'] = $userStats['total'];
        $stats['active_users'] = $userStats['active'];

        // UPDATED: Table 'AlertResponses'
        $stats['total_responses'] = $pdo->query("SELECT COUNT(*) FROM AlertResponses")->fetchColumn();

        // UPDATED: Column prefixes and Title Case tables
        $stmt = $pdo->query("
            SELECT a.Alerts_id, a.Alerts_title, a.Alerts_status, a.Alerts_created_at, at.AlertTypes_name as alert_type, u.Users_full_name as creator
            FROM Alerts a 
            LEFT JOIN AlertTypes at ON a.Alerts_AlertTypes_id = at.AlertTypes_id
            LEFT JOIN Users u ON a.Alerts_Users_id = u.Users_id
            ORDER BY a.Alerts_created_at DESC LIMIT 6
        ");
        $recent_alerts = $stmt->fetchAll();
    } else {
        // RESPONDER DATA
        // UPDATED: AlertResponses table, AlertResponses_Users_id column
        $stats['personal_responses'] = $pdo->query("SELECT COUNT(*) FROM AlertResponses WHERE AlertResponses_Users_id = $user_id")->fetchColumn();
        $stats['pending_responses'] = $pdo->query("SELECT COUNT(*) FROM AlertResponses WHERE AlertResponses_Users_id = $user_id AND AlertResponses_status = 'pending'")->fetchColumn();
        $stats['active_alerts'] = $pdo->query("SELECT COUNT(*) FROM Alerts WHERE Alerts_status IN ('pending', 'verified')")->fetchColumn();

        // UPDATED: Joins and column prefixes
        $stmt = $pdo->query("
            SELECT a.Alerts_id, a.Alerts_title, a.Alerts_status, a.Alerts_created_at, at.AlertTypes_name as alert_type
            FROM Alerts a 
            LEFT JOIN AlertTypes at ON a.Alerts_AlertTypes_id = at.AlertTypes_id
            WHERE a.Alerts_status IN ('pending', 'verified')
            ORDER BY a.Alerts_created_at DESC LIMIT 6
        ");
        $recent_alerts = $stmt->fetchAll();
    }

    // Breakdown for Charts
    // UPDATED: Column 'Alerts_status', Table 'Alerts'
    $stmt = $pdo->query("SELECT Alerts_status, COUNT(*) as count FROM Alerts GROUP BY Alerts_status");
    while ($row = $stmt->fetch()) {
        if (isset($status_breakdown[$row['Alerts_status']])) $status_breakdown[$row['Alerts_status']] = (int)$row['count'];
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

                <!-- Top Header Bar -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <!-- Left Side: Page Title -->
                    <div>
                        <h1 class="h3 fw-bold mb-1">Dashboard</h1>
                        <p class="text-muted mb-0 small">Welcome back to the Responda Control Center.</p>
                    </div>

                    <!-- Right Side: Notifications + User Profile -->
                    <div class="d-flex align-items-center">

                        <!-- 1. Notification Bell (Admin Only) -->
                        <?php if ($_SESSION['role_id'] == 1): ?>
                            <div class="dropdown me-3">
                                <button class="btn btn-danger border shadow-sm position-relative rounded-circle p-0 d-flex align-items-center justify-content-center"
                                    type="button" id="notifBell" data-bs-toggle="dropdown" aria-expanded="false"
                                    style="width: 50px; height: 50px; transition: all 0.2s ease;">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#4f46e5" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                    </svg>

                                    <span id="pendingCountBadge" class="position-absolute badge rounded-pill bg-danger d-none border border-white"
                                        style="top: 2px; right: -2px; font-size: 0.6rem; padding: 0.35em 0.5em;">
                                        0
                                    </span>
                                </button>

                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-0 overflow-hidden"
                                    style="width: 320px; border-radius: 12px; margin-top: 10px;">
                                    <li class="p-3 border-bottom bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 fw-bold text-dark">Verification Requests</h6>
                                            <span class="badge bg-primary-subtle text-primary" id="notifCountLabel">0 New</span>
                                        </div>
                                    </li>
                                    <div id="pendingNotifList" style="max-height: 350px; overflow-y: auto;">
                                        <li class="p-4 text-center text-muted small">No pending verifications</li>
                                    </div>
                                    <li class="p-2 border-top text-center bg-slate-50">
                                        <a href="alerts.php?filter=pending" class="text-primary small fw-bold text-decoration-none">View Management Console</a>
                                    </li>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- 2. The User Profile Card -->
                        <div class="bg-white border rounded-3 p-2 shadow-sm" style="min-width: 200px;">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                                    style="width: 36px; height: 36px; font-size: 0.85rem; background: var(--primary); flex-shrink: 0;">
                                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                                </div>
                                <div class="ms-3 overflow-hidden">
                                    <p class="mb-0 text-truncate fw-bold text-dark" style="font-size: 0.9rem; line-height: 1.2;">
                                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                                    </p>
                                    <p class="mb-0 text-truncate text-muted fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                        <?php echo ($role_id == 1) ? 'Administrator' : 'Responder'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="row g-4 mb-5">
                    <?php if ($role_id == 1): ?>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm p-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-subtle text-primary p-3 rounded-3 me-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                        </svg>
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
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
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
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
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
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="fw-bold mb-0"><?= $stats['active_users'] ?>/<?= $stats['total_users'] ?></h3>
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Users Active</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
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
                                <button class="btn btn-sm btn-light border" onclick="location.reload()">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
                                        <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
                                    </svg>
                                </button>
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
                                                    <!-- UPDATED: Alerts_title -->
                                                    <p class="mb-0 fw-bold" style="font-size: 0.9rem;"><?= htmlspecialchars($alert['Alerts_title']) ?></p>
                                                    <!-- UPDATED: alert_type (Alias) and Alerts_created_at -->
                                                    <small class="text-muted"><?= htmlspecialchars($alert['alert_type']) ?> • <?= date('H:i', strtotime($alert['Alerts_created_at'])) ?></small>
                                                </div>
                                                <?php
                                                // UPDATED: Alerts_status
                                                $statusClass = match ($alert['Alerts_status']) {
                                                    'pending' => 'bg-warning-subtle text-warning',
                                                    'verified' => 'bg-info-subtle text-info',
                                                    'resolved' => 'bg-success-subtle text-success',
                                                    default => 'bg-secondary-subtle text-secondary'
                                                };
                                                ?>
                                                <!-- UPDATED: Alerts_status -->
                                                <span class="badge <?= $statusClass ?>"><?= ucfirst($alert['Alerts_status']) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (empty($recent_alerts)): ?>
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
                        <?php if ($role_id == 1): ?>
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
            backgroundColor: ['#ffa200', '#000000', '#ff0000', '#0a5707'],
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
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                }
            }
        }
    });

    // Stats Auto-Refresh
    function updateStats() {
        fetch('get-dashboard-stats.php')
            .then(res => res.json())
            .then(data => {
                if (document.getElementById('stat-active')) {
                    // UPDATED: Assuming API keys match new column naming (e.g. Alerts_count)
                    document.getElementById('stat-active').innerText = data.active_alerts;
                    document.getElementById('stat-pending').innerText = data.pending_alerts;
                }
            });
    }
    setInterval(updateStats, 10000); // 10 seconds

    let lastNotifiedId = 0;

    async function checkNotifications() {
        const apiPath = '../api/check-pending.php';

        try {
            const response = await fetch(`${apiPath}?last_id=${lastNotifiedId}`);
            const data = await response.json();

            if (!data.success) {
                console.error("API Error:", data.message);
                return;
            }

            console.log(`Polling... Total Pending: ${data.total_count}`);

            // 1. Update the Bell Badge
            const badge = document.getElementById('pendingCountBadge');
            if (badge) {
                if (data.total_count > 0) {
                    badge.innerText = data.total_count;
                    badge.classList.remove('d-none');
                } else {
                    badge.classList.add('d-none');
                }
            }

            // 2. Update the Dropdown List
            updateBellDropdown(data.all_pending);

            // 3. Trigger Toasts for BRAND NEW alerts
            if (lastNotifiedId !== 0 && data.new_alerts.length > 0) {
                data.new_alerts.forEach(alert => {
                    showNotificationToast(alert);
                });
            }

            // 4. Update our tracker to the highest ID found
            // UPDATED: Accessing Alerts_id instead of id
            if (data.all_pending.length > 0) {
                lastNotifiedId = data.all_pending[0].Alerts_id;
            } else if (lastNotifiedId === 0) {
                lastNotifiedId = 1;
            }

        } catch (err) {
            console.error("Fetch failed. Check if api/check-pending.php exists.", err);
        }
    }

    function updateBellDropdown(alerts) {
        const list = document.getElementById('pendingNotifList');
        if (!list) return;

        if (alerts.length === 0) {
            list.innerHTML = '<li class="p-4 text-center text-muted small">No pending verifications</li>';
            return;
        }

        let html = '';
        alerts.forEach(alert => {
            // UPDATED: Using Alerts_severity, Alerts_title, and Users_full_name
            const sevColor = alert.Alerts_severity === 'High' ? 'text-danger' : 'text-warning';
            html += `
        <li class="dropdown-item p-3 border-bottom d-flex align-items-start" style="white-space: normal; cursor: pointer;" onclick="location.href='alerts.php?filter=pending'">
            <div class="bg-light p-2 rounded-3 me-3">
                <svg class="text-primary" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold small text-dark">${alert.Alerts_title}</div>
                <div class="text-muted" style="font-size: 0.7rem;">
                    <span class="${sevColor} fw-bold">● ${alert.Alerts_severity}</span> • ${alert.Users_full_name || 'System'}
                </div>
            </div>
        </li>`;
        });
        list.innerHTML = html;
    }

    function showNotificationToast(alert) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'warning',
            title: 'New Verification Alert',
            // UPDATED: Alerts_title
            text: alert.Alerts_title,
            showConfirmButton: false,
            timer: 8000,
            timerProgressBar: true
        });

        // Play sound
        const audio = new Audio('../assets/sounds/notification.mp3');
        audio.play().catch(() => console.log("Sound blocked until user clicks page."));
    }

    // Start polling immediately, then every 10 seconds
    document.addEventListener('DOMContentLoaded', () => {
        checkNotifications();
        setInterval(checkNotifications, 10000);
    });
</script>

<?php
include __DIR__ . '/../includes/modals/create-alert-modal.html';
include __DIR__ . '/../includes/footer.php';
?>