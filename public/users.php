<?php
// users.php - View and manage all users
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

// Check if user has admin role (role_id = 1 for Admin)
if ($_SESSION['role_id'] != 1) {
    header('Location: dashboard.php');
    exit;
}

// Fetch all users for admin view
$pdo = getPDO();
$stmt = $pdo->query('SELECT u.id, u.full_name, u.email, r.name AS role FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.created_at DESC');
$users = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
// include __DIR__ . '/../includes/sidebar.php';
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
                    <h1 class="display-6 fw-bold mb-1">User Management</h1>
                    <p class="text-muted mb-0">Manage and monitor system users</p>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-primary shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        + Create User
                    </button>
                </div>
            </div>

            <!-- User stats: to show how many users exist, per role, and active  -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title mb-2 text-muted small mb-1">Total Users</h6>
                            <h3 class="card-text fw-bold mb-0" style="color: #6c757d;"><?php echo count($users); ?></h3>
                            <small class="text-muted">All Users</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title mb-2 text-muted small mb-1">Admins</h6>
                            <h3 class="card-text fw-bold mb-0" style="color: #6c757d;"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'Admin')); ?></h3>
                            <small class="text-muted">System Administrators</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title mb-2 text-muted small mb-1">Responders</h6>
                            <h3 class="card-text fw-bold mb-0" style="color: #6c757d;"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'Responder')); ?></h3>
                            <small class="text-muted">Emergency Responders</small>
                        </div>
                    </div>
                </div>
            </div>


            <!-- User Listing Table -->
            <div class="card border-0 shadow-lg rounded-3 mb-4">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">All Users</h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Username</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Role</th>
                                    <th scope="col" style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($users) > 0): ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <!-- User dropdown menu for actions, view, edit and delete -->

                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $user['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $user['id']; ?>">
                                                        <li><a class="dropdown-item" href="view-user.php?id=<?php echo $user['id']; ?>">View</a></li>
                                                        <li><a class="dropdown-item disabled" href="edit-user.php?id=<?php echo $user['id']; ?>">Edit</a></li>
                                                        <?php if ($user['id'] != $_SESSION['user_id']): // Prevent self-deletion 
                                                        ?>
                                                            <li><a class="dropdown-item disabled" href="delete-user.php?id=<?php echo $user['id']; ?>">Delete</a></li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>



                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No users found.
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</div>


<!-- Modal to create new user -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-semibold" id="createUserModalLabel">Create New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form for creating new user -->
                <form method="POST" action="create-user.php" id="createUserForm">
                    <div class="mb-3">
                        <label for="fullName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="fullName" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role-id" required>
                            <option value="">Select Role</option>
                            <option value="1">Admin</option>
                            <option value="2">Responder</option>
                            <option value="3">Viewer</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
 
 document.getElementById("createUserForm").addEventListener("submit", async function(e){

    e.preventDefault();

    const formData = new FormData(this);

    const response = await fetch("create-user.php", {
        method: "POST",
        body: formData
    });

    const result = await response.json();

    if(result.success){

        alert(result.message);

        window.location.href = result.redirect;

    } else {

        alert(result.message);

    }

});

</script>




<?php include __DIR__ . '/../includes/footer.php'; ?>