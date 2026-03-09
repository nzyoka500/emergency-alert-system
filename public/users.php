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
$stmt = $pdo->query('SELECT u.id, u.full_name, u.email, u.phone, r.name AS role FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.created_at DESC');
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
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title mb-2 text-muted small mb-1">Total Users</h6>
                            <h3 class="card-text fw-bold mb-0" style="color: #6c757d;"><?php echo count($users); ?></h3>
                            <small class="text-muted">All Users</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title mb-2 text-muted small mb-1">Admins</h6>
                            <h3 class="card-text fw-bold mb-0" style="color: #6c757d;"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'Admin')); ?></h3>
                            <small class="text-muted">System Administrators</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title mb-2 text-muted small mb-1">Responders</h6>
                            <h3 class="card-text fw-bold mb-0" style="color: #6c757d;"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'Responder')); ?></h3>
                            <small class="text-muted">Emergency Responders</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title mb-2 text-muted small mb-1">Public</h6>
                            <h3 class="card-text fw-bold mb-0" style="color: #6c757d;"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'Community')); ?></h3>
                            <small class="text-muted">Community/ Public Users</small>
                        </div>
                    </div>
                </div>
            </div>


            <!-- User Listing Table -->

            <!-- Users Table Card -->
            <div class="card border-0 shadow-lg rounded-3 mb-4">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <h5 class="fw-semibold mb-0">System Users</h5>

                    <!-- Search -->
                    <input
                        type="text"
                        id="userSearch"
                        class="form-control w-25"
                        placeholder="Search users...">

                </div>


                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-hover table-striped align-middle" id="usersTable">

                            <thead class="table-light">
                                <tr>
                                    <th>Sn#</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if (!empty($users) && count($users) > 0): ?>

                                    <?php foreach ($users as $index => $user): ?>

                                        <tr>

                                            <td><?= $index + 1 ?></td>

                                            <td><?= htmlspecialchars($user['full_name']) ?></td>

                                            <td><?= htmlspecialchars($user['email']) ?></td>

                                            <td><?= htmlspecialchars($user['phone']) ?></td>

                                            <!-- Role Badge -->
                                            <td>

                                                <?php
                                                $roleName = strtolower($user['role'] ?? 'unknown');

                                                $roleStyles = [
                                                    'admin' => 'danger',
                                                    'responder' => 'primary',
                                                    'community' => 'secondary'
                                                ];

                                                $badge = $roleStyles[$roleName] ?? 'dark';
                                                ?>

                                                <span class="badge bg-<?= $badge ?>">
                                                    <?= htmlspecialchars($user['role'] ?? 'Unknown') ?>
                                                </span>

                                            </td>

                                            <!-- Status -->
                                            <td>
                                                <span class="badge bg-success">Active</span>
                                            </td>

                                            <!-- Actions -->
                                            <td>

                                                <div class="dropdown">

                                                    <button class="btn btn-sm btn-light border dropdown-toggle"
                                                        type="button"
                                                        id="dropdownMenuButton<?= $user['id']; ?>"
                                                        data-bs-toggle="dropdown">

                                                        Actions

                                                    </button>

                                                    <ul class="dropdown-menu">

                                                        <li>
                                                            <a class="dropdown-item view-user"
                                                                href="#"
                                                                data-id="<?= $user['id']; ?>"
                                                                data-name="<?= htmlspecialchars($user['full_name']); ?>"
                                                                data-email="<?= htmlspecialchars($user['email']); ?>"
                                                                data-phone="<?= htmlspecialchars($user['phone']); ?>"
                                                                data-role="<?= htmlspecialchars($user['role']); ?>"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewUserModal">

                                                                View
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item disabled"
                                                                href="edit-user.php?id=<?= $user['id']; ?>">
                                                                Edit
                                                            </a>
                                                        </li>

                                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>

                                                            <li>
                                                                <a class="dropdown-item text-danger delete-user"
                                                                    href="#"
                                                                    data-id="<?= $user['id']; ?>">
                                                                    Delete
                                                                </a>
                                                            </li>

                                                        <?php endif; ?>

                                                    </ul>

                                                </div>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            No users found.
                                        </td>
                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>


                <!-- Pagination -->
                <div class="card-footer text-center">

                    <nav>

                        <ul class="pagination justify-content-center" id="tablePagination"></ul>

                    </nav>

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
                 <form id="createUserForm">

                    <input type="text" name="full_name" class="form-control mb-2" placeholder="Full Name" required>

                    <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>

                    <input type="text" name="phone" class="form-control mb-2" placeholder="Phone">

                    <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>

                    <select name="role_id" class="form-control mb-3">
                    <option value="1">Admin</option>
                    <option value="2">Responder</option>
                    <option value="3">Community</option>
                    </select>

                    <button type="submit" class="btn btn-primary">Create User</button>

                </form>




               
            </div>
        </div>
    </div>
</div>

<!-- Modal to view user details -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-semibold" id="viewUserModalLabel">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- User details will be populated here -->
                <table class="table table-bordered">
                    <tr>
                        <th>Full Name</th>
                        <td id="viewName"></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td id="viewEmail"></td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td id="viewPhone"></td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td id="viewRole"></td>
                    </tr>
            </div>
        </div>
    </div>
</div>


<script>
    // Table search functionality
    // Search users
    document.getElementById("userSearch").addEventListener("keyup", function() {

        let filter = this.value.toLowerCase();

        let rows = document.querySelectorAll("#usersTable tbody tr");

        rows.forEach(row => {

            let text = row.innerText.toLowerCase();

            row.style.display = text.includes(filter) ? "" : "none";

        });

    });

    // Pagination functionality (client-side)
    const rowsPerPage = 5;
    const rows = document.querySelectorAll("#usersTable tbody tr");
    const pagination = document.getElementById("tablePagination");

    let currentPage = 1;

    function displayRows() {

        rows.forEach((row, index) => {

            row.style.display =
                (index >= (currentPage - 1) * rowsPerPage &&
                    index < currentPage * rowsPerPage) ?
                "" :
                "none";

        });

    }

    function setupPagination() {

        const pageCount = Math.ceil(rows.length / rowsPerPage);

        pagination.innerHTML = "";

        for (let i = 1; i <= pageCount; i++) {

            const li = document.createElement("li");
            li.className = "page-item";

            const btn = document.createElement("button");
            btn.className = "page-link";
            btn.innerText = i;

            btn.addEventListener("click", () => {

                currentPage = i;
                displayRows();

            });

            li.appendChild(btn);
            pagination.appendChild(li);
        }

    }

    displayRows();
    setupPagination();

    // View user details in modal
    document.querySelectorAll(".view-user").forEach(button => {

        button.addEventListener("click", function() {

            document.getElementById("viewName").textContent = this.dataset.name;
            document.getElementById("viewEmail").textContent = this.dataset.email;
            document.getElementById("viewPhone").textContent = this.dataset.phone;
            document.getElementById("viewRole").textContent = this.dataset.role;

        });

    });

    // Delete user functionality
    document.querySelectorAll(".delete-user").forEach(button => {

        button.addEventListener("click", function() {

            const userId = this.dataset.id;

            Swal.fire({
                title: "Delete User?",
                text: "This action cannot be undone.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, delete"
            }).then((result) => {

                if (result.isConfirmed) {

                    window.location.href = "delete-user.php?id=" + userId;

                }

            });

        });

    });






    document.getElementById("createUserForm").addEventListener("submit", async function(e) {

        e.preventDefault();

        const formData = new FormData(this);

        const response = await fetch("create-user.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (result.success) {

            alert(result.message);

            window.location.href = result.redirect;

        } else {

            alert(result.message);

        }

    });


// Handle create user form submission
document.getElementById("createUserForm").addEventListener("submit", function(e){

    e.preventDefault();

    let formData = new FormData(this);

    fetch("create-user.php",{
        method:"POST",
        body:formData
    })
    .then(res => res.json())
    .then(data => {

        if(data.success){

            Swal.fire({
                icon:"success",
                title:"Success",
                text:data.message
            }).then(()=>{
                location.reload();
            });

        }else{

            Swal.fire({
                icon:"error",
                title:"Error",
                text:data.message
            });

        }

    });

});

</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<?php include __DIR__ . '/../includes/footer.php'; ?>