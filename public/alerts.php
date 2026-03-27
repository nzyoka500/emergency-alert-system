<?php
/**
 * alerts.php - Production Grade Alert Management
 * High-fidelity UI with integrated CRUD logic
 */

require_once __DIR__ . '/../includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$username = htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES, 'UTF-8');
$user_id = $_SESSION['user_id'] ?? null;
$role_id = $_SESSION['role_id'] ?? null;

// Filter Parameters
$status_filter = $_GET['filter'] ?? 'all';
$search_query = trim($_GET['search'] ?? '');
$sort_by = $_GET['sort'] ?? 'recent';

$alerts = [];
$status_counts = ['pending' => 0, 'verified' => 0, 'broadcasted' => 0, 'resolved' => 0, 'all' => 0];

try {
    $pdo = getPDO();

    // 1. Fetch Summary Data 
    // UPDATED: Table 'Alerts', Column 'Alerts_status'
    $stmt = $pdo->query("SELECT Alerts_status, COUNT(*) as count FROM Alerts GROUP BY Alerts_status");
    while ($row = $stmt->fetch()) {
        if (isset($status_counts[$row['Alerts_status']])) {
            $status_counts[$row['Alerts_status']] = (int)$row['count'];
        }
    }
    $status_counts['all'] = array_sum($status_counts);

    // 2. Build Intelligent Query
    // UPDATED: Prefixed columns and Title Case tables
    $query = "
        SELECT a.*, at.AlertTypes_name as alert_type, u.Users_full_name as creator,
        (SELECT COUNT(*) FROM AlertResponses WHERE AlertResponses_Alerts_id = a.Alerts_id) as response_count
        FROM Alerts a
        LEFT JOIN AlertTypes at ON a.Alerts_AlertTypes_id = at.AlertTypes_id
        LEFT JOIN Users u ON a.Alerts_Users_id = u.Users_id
        WHERE 1=1
    ";

    if ($status_filter !== 'all') {
        $query .= " AND a.Alerts_status = " . $pdo->quote($status_filter);
    }

    if (!empty($search_query)) {
        $search_term = "%$search_query%";
        // UPDATED: Search against Alerts_title and AlertTypes_name
        $query .= " AND (a.Alerts_title LIKE " . $pdo->quote($search_term) . " OR at.AlertTypes_name LIKE " . $pdo->quote($search_term) . ")";
    }

    // UPDATED: Sort using Alerts_created_at and Alerts_status
    $query .= match($sort_by) {
        'oldest' => " ORDER BY a.Alerts_created_at ASC",
        'status' => " ORDER BY FIELD(a.Alerts_status, 'pending', 'verified', 'broadcasted', 'resolved'), a.Alerts_created_at DESC",
        default => " ORDER BY a.Alerts_created_at DESC"
    };

    $alerts = $pdo->query($query)->fetchAll();
} catch (Exception $e) {
    error_log($e->getMessage());
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid p-0">
    <div class="row g-0">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-lg-10 bg-light min-vh-100">
            <div class="p-4 p-lg-5">

                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="h3 fw-bold mb-1">Alert Management</h1>
                        <p class="text-muted mb-0 small">Real-time emergency tracking and response.</p>
                    </div>

                    <div class="d-flex align-items-center">
                        <!-- Notification Bell -->
                        <div class="dropdown me-3">
                            <button class="btn btn-white border shadow-sm position-relative rounded-circle p-0 d-flex align-items-center justify-content-center" type="button" id="notifBell" data-bs-toggle="dropdown" style="width: 42px; height: 42px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#4f46e5" stroke-width="2">
                                    <path d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                </svg>
                                <span id="pendingCountBadge" class="position-absolute badge rounded-pill bg-danger d-none border border-white" style="top: 2px; right: -2px; font-size: 0.6rem;">0</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-0" style="width: 320px; border-radius: 12px;">
                                <li class="p-3 border-bottom bg-light fw-bold small text-uppercase text-muted">Pending Verification</li>
                                <div id="pendingNotifList" style="max-height: 350px; overflow-y: auto;">
                                    <li class="p-4 text-center text-muted small">Searching for alerts...</li>
                                </div>
                            </ul>
                        </div>

                        <!-- User Card -->
                        <div class="bg-white border rounded-3 p-2 shadow-sm d-flex align-items-center" style="min-width: 180px;">
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold me-2 shadow-sm" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                <?= strtoupper(substr($username, 0, 1)) ?>
                            </div>
                            <div class="overflow-hidden">
                                <p class="mb-0 text-truncate fw-bold text-dark" style="font-size: 0.9rem; line-height: 1.2;"><?= $username ?></p>
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;"><?= ($role_id == 1) ? 'Administrator' : 'Responder' ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Grid -->
                <div class="row g-3 mb-5">
                    <?php
                    $filter_configs = [
                        'all' => ['label' => 'Total alerts', 'color' => 'secondary'],
                        'pending' => ['label' => 'Pending', 'color' => 'warning'],
                        'verified' => ['label' => 'Verified', 'color' => 'info'],
                        'broadcasted' => ['label' => 'Broadcasted', 'color' => 'primary'],
                        'resolved' => ['label' => 'Resolved', 'color' => 'success']
                    ];
                    foreach ($filter_configs as $key => $cfg):
                        $isActive = ($status_filter === $key);
                    ?>
                    <div class="col-lg">
                        <a href="?filter=<?= $key ?>" class="text-decoration-none">
                            <div class="card border-0 shadow-sm transition-all h-100 <?= $isActive ? 'border-start border-primary border-4' : '' ?>">
                                <div class="card-body p-3 text-center">
                                    <h4 class="fw-bold mb-0 text-dark"><?= $status_counts[$key] ?></h4>
                                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.6rem; letter-spacing: 0.5px;"><?= $cfg['label'] ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Actions & Search Row -->
                <div class="row g-3 mb-4 align-items-center">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-2">
                                <form method="GET" class="row g-2">
                                    <input type="hidden" name="filter" value="<?= $status_filter ?>">
                                    <div class="col-md-7">
                                        <div class="input-group input-group-sm border-0">
                                            <span class="input-group-text bg-transparent border-0 text-muted ps-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                                            </span>
                                            <input type="text" name="search" class="form-control border-0 bg-transparent" placeholder="Filter by title, type..." value="<?= htmlspecialchars($search_query) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="sort" class="form-select form-select-sm border-0 bg-light fw-semibold" onchange="this.form.submit()">
                                            <option value="recent" <?= $sort_by === 'recent' ? 'selected' : '' ?>>Newest First</option>
                                            <option value="oldest" <?= $sort_by === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                                            <option value="status" <?= $sort_by === 'status' ? 'selected' : '' ?>>By Status</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">Apply</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <button class="btn btn-primary shadow-sm px-4 fw-bold w-100 w-lg-auto" data-bs-toggle="modal" data-bs-target="#createAlertModal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" class="me-2"><path d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Create Alert
                        </button>
                    </div>
                </div>

                <!-- Main Table Card -->
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="alertsTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Emergency Incident</th>
                                    <th>Status</th>
                                    <th>Severity</th>
                                    <th>Activity</th>
                                    <th>Timestamp</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($alerts)): ?>
                                    <tr><td colspan="6" class="text-center p-5 text-muted small">No incidents found matching your current filters.</td></tr>
                                <?php else: foreach ($alerts as $alert): 
                                    // UPDATED: Alerts_severity and Alerts_status prefixes
                                    $sev_color = match($alert['Alerts_severity']) { 'High' => 'danger', 'Medium' => 'warning', 'Low' => 'success', default => 'secondary' };
                                    $stat_color = match($alert['Alerts_status']) { 'pending' => 'warning', 'verified' => 'info', 'broadcasted' => 'primary', 'resolved' => 'success', default => 'secondary' };
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <!-- UPDATED: Alerts_title prefix -->
                                        <div class="fw-bold text-dark" style="font-size: 0.95rem;"><?= htmlspecialchars($alert['Alerts_title']) ?></div>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">
                                            <span class="fw-bold text-uppercase"><?= htmlspecialchars($alert['alert_type']) ?></span> • By <?= htmlspecialchars($alert['creator'] ?? 'System') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <!-- UPDATED: Alerts_status prefix -->
                                        <span class="badge bg-<?= $stat_color ?>-subtle text-<?= $stat_color ?> px-2 py-1">
                                            <?php if($alert['Alerts_status'] === 'pending'): ?><span class="status-pulse-pending"></span><?php endif; ?>
                                            <?= ucfirst($alert['Alerts_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- UPDATED: Alerts_severity prefix -->
                                        <span class="badge border border-<?= $sev_color ?> text-<?= $sev_color ?> bg-white fw-bold" style="font-size: 0.65rem;">
                                            ● <?= strtoupper($alert['Alerts_severity']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small fw-bold text-<?= $alert['response_count'] > 0 ? 'primary' : 'muted' ?>">
                                            <?= $alert['response_count'] ?> Response<?= $alert['response_count'] != 1 ? 's' : '' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <!-- UPDATED: Alerts_created_at prefix -->
                                        <div class="small text-dark fw-semibold"><?= date('M d, Y', strtotime($alert['Alerts_created_at'])) ?></div>
                                        <div class="small text-muted" style="font-size: 0.7rem;"><?= date('H:i', strtotime($alert['Alerts_created_at'])) ?></div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <!-- UPDATED: data- attributes to match new column names (Alerts_desc, Alerts_id, etc.) -->
                                            <button class="btn btn-sm btn-white border shadow-sm view-alert me-3" 
                                                data-id="<?= $alert['Alerts_id'] ?>"
                                                data-title="<?= htmlspecialchars($alert['Alerts_title']) ?>"
                                                data-description="<?= htmlspecialchars($alert['Alerts_desc']) ?>"
                                                data-type="<?= htmlspecialchars($alert['alert_type']) ?>"
                                                data-status="<?= $alert['Alerts_status'] ?>"
                                                data-severity="<?= $alert['Alerts_severity'] ?>"
                                                data-latitude="<?= $alert['Alerts_latitude'] ?>"
                                                data-longitude="<?= $alert['Alerts_longitude'] ?>"
                                                data-bs-toggle="modal" data-bs-target="#viewAlertModal">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                            </button>
                                            <?php if($role_id == 1): ?>
                                            <!-- UPDATED: Alerts_id prefix -->
                                            <button class="btn btn-sm btn-white border text-danger shadow-sm delete-alert" data-id="<?= $alert['Alerts_id'] ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/**
 * CRUD Event Handling
 */
document.addEventListener('click', function (e) {
    // 1. Modal View Population
    // These 'd' (dataset) keys correspond to the 'data-' attributes in the HTML template
    const viewBtn = e.target.closest('.view-alert');
    if (viewBtn) {
        const d = viewBtn.dataset;
        
        // Populate inputs (Ensure these element IDs match your modal form IDs)
        if(document.getElementById("alertId")) document.getElementById("alertId").value = d.id; // Alerts_id
        if(document.getElementById("alertTitle")) document.getElementById("alertTitle").value = d.title; // Alerts_title
        if(document.getElementById("alertDescription")) document.getElementById("alertDescription").value = d.description; // Alerts_desc
        if(document.getElementById("alertType")) document.getElementById("alertType").value = d.type; // AlertTypes_name
        
        if(document.getElementById("alertSeverity")) document.getElementById("alertSeverity").value = d.severity; // Alerts_severity
        if(document.getElementById("alertSeverityView")) document.getElementById("alertSeverityView").value = d.severity;
        
        if(document.getElementById("alertStatus")) document.getElementById("alertStatus").value = d.status; // Alerts_status
        if(document.getElementById("alertStatusView")) document.getElementById("alertStatusView").value = d.status;
        
        // Handle Action Buttons visibility (Verify button)
        const actionContainer = document.getElementById('adminActionButtons');
        if (actionContainer) {
            // Status check matches new 'pending' logic
            actionContainer.innerHTML = d.status === 'pending' ? 
                `<button class="btn btn-success btn-sm px-4 me-2 shadow-sm" onclick="verifyAndBroadcast(${d.id})">Verify & Broadcast</button>` : '';
        }
    }

    // 2. Deletion Logic
    const deleteBtn = e.target.closest('.delete-alert, #deleteAlert');
    if (deleteBtn) {
        // Grab the ID from either the modal hidden field or the button's data attribute
        const id = deleteBtn.id === 'deleteAlert' ? document.getElementById('alertId').value : deleteBtn.dataset.id;
        
        Swal.fire({
            title: 'Delete record?',
            text: "This operation cannot be reversed.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, Delete Permanently'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData(); 
                fd.append('id', id); // Sends the Alerts_id
                
                fetch('delete-alert.php', { method: 'POST', body: fd })
                .then(res => res.json())
                .then(data => {
                    if(data.success) location.reload();
                    else Swal.fire('Error', data.message, 'error');
                });
            }
        });
    }
});

// 3. Update Logic
const updateBtn = document.getElementById('editAlert');
if(updateBtn) {
    updateBtn.addEventListener('click', function() {
        const form = document.getElementById('alertCrudForm');
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        // Ensure update-alert.php is updated to handle prefixed column names
        fetch('update-alert.php', { method: 'POST', body: new FormData(form) })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                Swal.fire('Success', data.message, 'success').then(() => location.reload());
            } else { 
                Swal.fire('Error', data.message, 'error');
                this.disabled = false;
                this.innerHTML = 'Update Details';
            }
        });
    });
}
</script>


<?php 
    include __DIR__ . '/../includes/modals/create-alert-modal.html'; 
    include __DIR__ . '/../includes/modals/view-alert-modal.php';
    if ($role_id == 2) include __DIR__ . '/../includes/modals/respond-alert-modal.html';
    include __DIR__ . '/../includes/footer.php'; 
?>