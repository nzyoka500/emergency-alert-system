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
            <div class="row mb-4 align-items-center">
                <div class="col-md-6">
                    <h1 class="display-5 fw-bold">User Management</h1>
                    <p class="text-muted">Manage and monitor user accounts</p>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-plus-circle me-2" viewBox="0 0 16 16" style="display: inline;">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" />
                        </svg>
                        Create User
                    </button>
                </div>
            </div>

            <!-- User stats -->


            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">User Management</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Username</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Role</th>
                                    <th scope="col" style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <!-- User dropdown menu for actions, view, edit and delete -->

                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $user['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
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
            <div class="modal-header">
                <h5 class="modal-title" id="createUserModalLabel">Create User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form for creating new user -->
                <form id="createUserForm">
                    <div class="mb-3">
                        <label for="fullName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="fullName" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role_id" required>
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




<?php include __DIR__ . '/../includes/footer.php'; ?>