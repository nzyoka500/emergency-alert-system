<?php
/**
 * alerts.php - Professional Alert Management
 * Updated with Indigo/Slate Palette & Soft UI
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

// Filter Parameters
$status_filter = $_GET['filter'] ?? 'all';
$search_query = trim($_GET['search'] ?? '');
$sort_by = $_GET['sort'] ?? 'recent';

$alerts = [];
$status_counts = ['pending' => 0, 'verified' => 0, 'broadcasted' => 0, 'resolved' => 0, 'all' => 0];

try {
    $pdo = getPDO();

    // Get Counts
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM alerts GROUP BY status");
    while ($row = $stmt->fetch()) {
        if (isset($status_counts[$row['status']])) $status_counts[$row['status']] = (int)$row['count'];
    }
    $status_counts['all'] = array_sum($status_counts);

    // Build Query
    $query = "
        SELECT a.*, at.name as alert_type, u.full_name as creator,
        (SELECT COUNT(*) FROM alert_responses WHERE alert_id = a.id) as response_count
        FROM alerts a
        LEFT JOIN alert_types at ON a.alert_type_id = at.id
        LEFT JOIN users u ON a.created_by = u.id
        WHERE 1=1
    ";

    if ($status_filter !== 'all') {
        $query .= " AND a.status = " . $pdo->quote($status_filter);
    }

    if (!empty($search_query)) {
        $query .= " AND (a.title LIKE " . $pdo->quote("%$search_query%") . " OR at.name LIKE " . $pdo->quote("%$search_query%") . ")";
    }

    $query .= match($sort_by) {
        'oldest' => " ORDER BY a.created_at ASC",
        'status' => " ORDER BY FIELD(a.status, 'pending', 'verified', 'broadcasted', 'resolved'), a.created_at DESC",
        default => " ORDER BY a.created_at DESC"
    };

    $alerts = $pdo->query($query)->fetchAll();
} catch (Exception $e) {
    error_log($e->getMessage());
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Sidebar Navigation -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-lg-10 bg-light min-vh-100">
            <div class="p-4 p-lg-5">

                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="h3 fw-bold mb-1">Alert Management</h1>
                        <p class="text-muted mb-0">Monitor, verify, and respond to emergency incidents.</p>
                    </div>
                    <button class="btn btn-primary shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#createAlertModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Create New Alert
                    </button>
                </div>

                <!-- Status Filter Cards -->
                <div class="row g-3 mb-4">
                    <?php
                    $filter_configs = [
                        'all' => ['label' => 'Total', 'color' => 'secondary', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z'],
                        'pending' => ['label' => 'Pending', 'color' => 'warning', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                        'verified' => ['label' => 'Verified', 'color' => 'info', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                        'broadcasted' => ['label' => 'Broadcasted', 'color' => 'primary', 'icon' => 'M8.288 15.038a5.25 5.25 0 0 1 7.424 0M5.106 11.856a9.75 9.75 0 0 1 13.788 0M1.924 8.674a14.25 14.25 0 0 1 20.152 0M12.53 18.22a.75.75 0 0 1-1.06 0l-1.06-1.06a.75.75 0 1 1 1.06-1.06l1.06 1.06a.75.75 0 0 1 0 1.06Z']
                    ];

                    foreach ($filter_configs as $key => $cfg):
                        $isActive = ($status_filter === $key);
                    ?>
                    <div class="col-lg-3">
                        <a href="?filter=<?= $key ?>" class="text-decoration-none">
                            <div class="card border-0 shadow-sm <?= $isActive ? 'ring-primary' : '' ?>">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-<?= $cfg['color'] ?>-subtle text-<?= $cfg['color'] ?> p-2 rounded-3 me-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="<?= $cfg['icon'] ?>" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="fw-bold mb-0"><?= $status_counts[$key] ?></h4>
                                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;"><?= $cfg['label'] ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Search and Filter Bar -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <form method="GET" class="row g-2">
                            <input type="hidden" name="filter" value="<?= $status_filter ?>">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                                    </span>
                                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by title or category..." value="<?= htmlspecialchars($search_query) ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select name="sort" class="form-select" onchange="this.form.submit()">
                                    <option value="recent" <?= $sort_by === 'recent' ? 'selected' : '' ?>>Newest First</option>
                                    <option value="oldest" <?= $sort_by === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                                    <option value="status" <?= $sort_by === 'status' ? 'selected' : '' ?>>Sort by Status</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-dark w-100">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Alerts Table -->
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Emergency Incident</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Severity</th>
                                    <th>Activity</th>
                                    <th>Timestamp</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($alerts)): foreach ($alerts as $alert): 
                                    $status_meta = match($alert['status']) {
                                        'pending' => ['class' => 'warning', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                                        'verified' => ['class' => 'info', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                                        'broadcasted' => ['class' => 'primary', 'icon' => 'M8.288 15.038a5.25 5.25 0 0 1 7.424 0M12.53 18.22a.75.75 0 0 1-1.06 0l-1.06-1.06a.75.75 0 1 1 1.06-1.06l1.06 1.06a.75.75 0 0 1 0 1.06Z'],
                                        'resolved' => ['class' => 'success', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                                        default => ['class' => 'secondary', 'icon' => '']
                                    };

                                     $severity_color = match($alert['severity']) {
                                        'High' => 'danger',
                                        'Medium' => 'warning',
                                        'Low' => 'success',
                                        default => 'secondary'
                                    };
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($alert['title']) ?></div>
                                        <div class="text-muted small">Reporter: <?= htmlspecialchars($alert['creator'] ?? 'System') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border fw-normal">
                                            <?= htmlspecialchars($alert['alert_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $status_meta['class'] ?>-subtle text-<?= $status_meta['class'] ?> d-inline-flex align-items-center">
                                            <?php if($alert['status'] === 'pending'): ?><span class="status-pulse-pending"></span><?php endif; ?>
                                            <?= ucfirst($alert['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge border border-<?= $severity_color ?> text-<?= $severity_color ?> bg-transparent">
                                            ● <?= $alert['severity'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-<?= $alert['response_count'] > 0 ? 'primary' : 'muted' ?>">
                                            <?= $alert['response_count'] ?> Responses
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-dark"><?= date('M d, Y', strtotime($alert['created_at'])) ?></div>
                                        <div class="small text-muted"><?= date('H:i', strtotime($alert['created_at'])) ?></div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-white border view-alert" 
                                                data-id="<?= $alert['id'] ?>"
                                                data-title="<?= htmlspecialchars($alert['title']) ?>"
                                                data-description="<?= htmlspecialchars($alert['description']) ?>"
                                                data-type="<?= htmlspecialchars($alert['alert_type']) ?>"
                                                data-status="<?= $alert['status'] ?>"
                                                data-bs-toggle="modal" data-bs-target="#viewAlertModal">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                            </button>
                                            
                                            <?php if ($role_id == 2 && ($alert['status'] === 'pending' || $alert['status'] === 'verified')): ?>
                                                <button class="btn btn-sm btn-primary ms-1" 
                                                    onclick="openRespondModal(<?= $alert['id'] ?>, '<?= addslashes($alert['title']) ?>', '...', '<?= $alert['alert_type'] ?>', '<?= $alert['status'] ?>', '<?= $alert['created_at'] ?>')">
                                                    Respond
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="6" class="p-5 text-center text-muted">
                                        <img src="assets/images/empty-state.svg" style="width: 120px; opacity: 0.5;" class="mb-3 d-block mx-auto">
                                        <p class="mb-0">No incidents found matching your criteria.</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<?php 
    include __DIR__ . '/../includes/modals/create-alert-modal.html'; 
    include __DIR__ . '/../includes/modals/view-alert-modal.php';
    if ($role_id == 2) include __DIR__ . '/../includes/modals/respond-alert-modal.html';
    include __DIR__ . '/../includes/footer.php'; 
?>