<?php
// alerts.php - View and manage all alerts
// Protected page for logged-in users

require_once __DIR__ . '/../includes/config.php';

// Ensure session is started
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

// Get filter parameters
$status_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'recent';

// Initialize alerts array
$alerts = [];
$total_count = 0;
$status_counts = [
    'pending' => 0,
    'verified' => 0,
    'broadcasted' => 0,
    'resolved' => 0,
    'all' => 0
];

try {
    $pdo = getPDO();

    // Get status counts
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM alerts GROUP BY status");
    $counts = $stmt->fetchAll();
    foreach ($counts as $row) {
        $status_counts[$row['status']] = $row['count'];
    }
    $status_counts['all'] = array_sum($status_counts);

    // Build query based on filters
    $query = "
        SELECT 
            a.id, 
            a.title, 
            a.description, 
            a.status, 
            a.latitude, 
            a.longitude, 
            a.created_at, 
            at.name as alert_type,
            u.full_name as created_by_name,
            COUNT(ar.id) as response_count
        FROM alerts a
        LEFT JOIN alert_types at ON a.alert_type_id = at.id
        LEFT JOIN users u ON a.created_by = u.id
        LEFT JOIN alert_responses ar ON a.id = ar.alert_id
        WHERE 1=1
    ";

    // Add status filter
    if ($status_filter !== 'all' && in_array($status_filter, ['pending', 'verified', 'broadcasted', 'resolved'])) {
        $query .= " AND a.status = '" . $status_filter . "'";
    }

    // Add search filter
    if (!empty($search_query)) {
        $search_term = '%' . $search_query . '%';
        $query .= " AND (a.title LIKE ? OR a.description LIKE ? OR at.name LIKE ?)";
    }

    // Add grouping and sorting
    $query .= " GROUP BY a.id";

    if ($sort_by === 'oldest') {
        $query .= " ORDER BY a.created_at ASC";
    } elseif ($sort_by === 'status') {
        $query .= " ORDER BY FIELD(a.status, 'pending', 'verified', 'broadcasted', 'resolved'), a.created_at DESC";
    } else { // recent
        $query .= " ORDER BY a.created_at DESC";
    }

    // Add limit
    $query .= " LIMIT 50";

    // Execute query
    if (!empty($search_query)) {
        $stmt = $pdo->prepare($query);
        $stmt->execute([$search_term, $search_term, $search_term]);
    } else {
        $stmt = $pdo->query($query);
    }

    $alerts = $stmt->fetchAll();
    $total_count = count($alerts);

} catch (Exception $e) {
    error_log('Alerts page error: ' . $e->getMessage());
}

// Function to get status badge color
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'bg-warning';
        case 'verified':
            return 'bg-info';
        case 'broadcasted':
            return 'bg-primary';
        case 'resolved':
            return 'bg-success';
        default:
            return 'bg-secondary';
    }
}

// Function to get status badge icon
function getStatusIcon($status) {
    switch ($status) {
        case 'pending':
            return '⏳';
        case 'verified':
            return '✓';
        case 'broadcasted':
            return '📡';
        case 'resolved':
            return '✓✓';
        default:
            return '•';
    }
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
            <div class="row mb-4 align-items-center border-bottom pb-3">
                <div class="col-md-6">
                    <h1 class="display-6 fw-bold">All Alerts</h1>
                    <p class="text-muted">Manage and monitor emergency alerts</p>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAlertModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-plus-circle me-2" viewBox="0 0 16 16" style="display: inline;">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                        </svg>
                        Create Alert
                    </button>
                </div>
            </div>

            <!-- Status Filter Cards -->
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-md-6">
                    <a href="?filter=all" class="text-decoration-none">
                        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6c757d; cursor: pointer; <?php echo ($status_filter === 'all') ? 'background: linear-gradient(135deg, #f8f9fd 0%, #f3f4f9 100%);' : ''; ?>">
                            <div class="card-body">
                                <p class="text-muted small mb-1">Total Alerts</p>
                                <h3 class="fw-bold mb-0" style="color: #6c757d;"><?php echo $status_counts['all']; ?></h3>
                                <small class="text-muted">All statuses</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <a href="?filter=pending" class="text-decoration-none">
                        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107; cursor: pointer; <?php echo ($status_filter === 'pending') ? 'background: linear-gradient(135deg, #f8f9fd 0%, #f3f4f9 100%);' : ''; ?>">
                            <div class="card-body">
                                <p class="text-muted small mb-1">Pending</p>
                                <h3 class="fw-bold mb-0" style="color: #ffc107;"><?php echo $status_counts['pending']; ?></h3>
                                <small class="text-muted">Awaiting review</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <a href="?filter=verified" class="text-decoration-none">
                        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0dcaf0; cursor: pointer; <?php echo ($status_filter === 'verified') ? 'background: linear-gradient(135deg, #f8f9fd 0%, #f3f4f9 100%);' : ''; ?>">
                            <div class="card-body">
                                <p class="text-muted small mb-1">Verified</p>
                                <h3 class="fw-bold mb-0" style="color: #0dcaf0;"><?php echo $status_counts['verified']; ?></h3>
                                <small class="text-muted">Confirmed alerts</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <a href="?filter=broadcasted" class="text-decoration-none">
                        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd; cursor: pointer; <?php echo ($status_filter === 'broadcasted') ? 'background: linear-gradient(135deg, #f8f9fd 0%, #f3f4f9 100%);' : ''; ?>">
                            <div class="card-body">
                                <p class="text-muted small mb-1">Broadcasted</p>
                                <h3 class="fw-bold mb-0" style="color: #0d6efd;"><?php echo $status_counts['broadcasted']; ?></h3>
                                <small class="text-muted">Sent to community</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Search and Sort Bar -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control" placeholder="Search by title, description, or type..." value="<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="sort" class="form-control">
                                <option value="recent" <?php echo ($sort_by === 'recent') ? 'selected' : ''; ?>>Most Recent</option>
                                <option value="oldest" <?php echo ($sort_by === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                                <option value="status" <?php echo ($sort_by === 'status') ? 'selected' : ''; ?>>By Status</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                        <?php if (!empty($search_query) || $status_filter !== 'all' || $sort_by !== 'recent'): ?>
                            <div class="col-12">
                                <a href="alerts.php" class="btn btn-outline-secondary btn-sm">Clear Filters</a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Alerts List -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <?php 
                            if ($status_filter !== 'all') {
                                echo ucfirst($status_filter) . ' Alerts';
                            } else {
                                echo 'All Alerts';
                            }
                            ?>
                            <span class="badge bg-light text-dark ms-2"><?php echo $total_count; ?></span>
                        </h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (count($alerts) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr style="background: #f8f9fd;">
                                        <th style="width: 30%;">Alert Title</th>
                                        <th style="width: 15%;">Type</th>
                                        <th style="width: 15%;">Status</th>
                                        <th style="width: 12%;">Responses</th>
                                        <th style="width: 18%;">Created</th>
                                        <th style="width: 10%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alerts as $alert): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-600" style="color: #2d3748;">
                                                    <?php echo htmlspecialchars(strlen($alert['title']) > 40 ? substr($alert['title'], 0, 40) . '...' : $alert['title'], ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <small class="text-muted">Created by: <?php echo htmlspecialchars($alert['created_by_name'] ?? 'System', ENT_QUOTES, 'UTF-8'); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark" style="font-size: 12px;">
                                                    <?php echo htmlspecialchars($alert['alert_type'], ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo getStatusBadgeClass($alert['status']); ?>" style="font-size: 12px; padding: 8px 12px;">
                                                    <?php echo getStatusIcon($alert['status']) . ' ' . ucfirst($alert['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info" style="font-size: 12px;">
                                                    <?php echo $alert['response_count']; ?> response<?php echo $alert['response_count'] != 1 ? 's' : ''; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y H:i', strtotime($alert['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <a href="alert-details.php?id=<?php echo $alert['id']; ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                                    </svg>
                                                </a>
                                                <?php if ($role_id == 2 && ($alert['status'] === 'pending' || $alert['status'] === 'verified')): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-info ms-1" title="Respond to Alert" 
                                                        onclick="openRespondModal(<?php echo $alert['id']; ?>, '<?php echo htmlspecialchars($alert['title'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars(substr($alert['description'], 0, 100), ENT_QUOTES); ?>...', '<?php echo htmlspecialchars($alert['alert_type'], ENT_QUOTES); ?>', '<?php echo $alert['status']; ?>', '<?php echo $alert['created_at']; ?>')">
                                                        <i class="fas fa-reply"></i> Respond
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-5 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#cbd5e1" class="mb-3" viewBox="0 0 16 16">
                                <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                            </svg>
                            <h5 class="text-muted">No Alerts Found</h5>
                            <p class="text-muted mb-3">
                                <?php 
                                if (!empty($search_query)) {
                                    echo "No alerts match your search. <a href='alerts.php'>Clear search</a>";
                                } elseif ($status_filter !== 'all') {
                                    echo "No " . $status_filter . " alerts found.";
                                } else {
                                    echo "Create your first alert to get started. <a href='create-alert.php'>Create Alert</a>";
                                }
                                ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info Footer -->
            <div class="mt-4 p-3 bg-light rounded" style="border-left: 4px solid #667eea;">
                <small class="text-muted">
                    <strong>Tip:</strong> Click on any alert to view full details, responses, and take actions. 
                    You can also filter by status or search for specific keywords.
                </small>
            </div>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/modals/create-alert-modal.html'; ?>
<?php if ($role_id == 2): include __DIR__ . '/../includes/modals/respond-alert-modal.html'; endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
