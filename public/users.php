<?php

/**
 * users.php - Professional User Management
 * Updated with Indigo/Slate Palette & Soft UI Stats
 */

require_once __DIR__ . '/../includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not authenticated or not admin
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role_id'] != 1) {
    header('Location: ' . ($_SESSION['logged_in'] ? 'dashboard.php' : 'index.php'));
    exit;
}

$pdo = getPDO();
// Optimized query with role names
$stmt = $pdo->query('
    SELECT u.id, u.full_name, u.email, u.phone, u.status, r.name AS role 
    FROM users u 
    LEFT JOIN roles r ON u.role_id = r.id 
    ORDER BY u.created_at DESC
');
$users = $stmt->fetchAll();

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
                        <h1 class="h3 fw-bold mb-1">User Management</h1>
                        <p class="text-muted mb-0">Manage system access levels and user profiles.</p>
                    </div>
                    <button class="btn btn-primary shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                        </svg>
                        Create New User
                    </button>
                </div>

                <!-- Stats Grid -->
                <div class="row g-4 mb-5">
                    <?php
                    $roles_count = [
                        'Total' => ['count' => count($users), 'color' => 'indigo', 'icon' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
                        'Admin' => ['count' => count(array_filter($users, fn($u) => $u['role'] === 'Admin')), 'color' => 'danger', 'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.333 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
                        'Responder' => ['count' => count(array_filter($users, fn($u) => $u['role'] === 'Responder')), 'color' => 'primary', 'icon' => 'M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3'],
                        'Community' => ['count' => count(array_filter($users, fn($u) => $u['role'] === 'Community')), 'color' => 'success', 'icon' => 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z']
                    ];

                    foreach ($roles_count as $title => $data):
                    ?>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm p-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-<?= $data['color'] ?>-subtle text-<?= $data['color'] ?> p-3 rounded-3 me-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="<?= $data['icon'] ?>" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="fw-bold mb-0"><?= $data['count'] ?></h3>
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;"><?= $title ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- User Table Card -->
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <div class="row align-items-center g-3">
                            <div class="col-md-8">
                                <h6 class="fw-bold mb-0">Registered System Users</h6>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                        </svg>
                                    </span>
                                    <input type="text" id="userSearch" class="form-control border-start-0 ps-0" placeholder="Search by name, email or role...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="usersTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4" style="width: 50px;">#</th>
                                    <th>User Information</th>
                                    <th>Contact</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $index => $user):
                                    $roleColor = match (strtolower($user['role'])) {
                                        'admin' => 'danger',
                                        'responder' => 'primary',
                                        'community' => 'success',
                                        default => 'secondary'
                                    };
                                ?>
                                    <tr>
                                        <td class="ps-4 text-muted small"><?= $index + 1 ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($user['full_name']) ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars($user['email']) ?></div>
                                        </td>
                                        <td>
                                            <div class="small text-dark"><?= htmlspecialchars($user['phone'] ?: '--') ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $roleColor ?>-subtle text-<?= $roleColor ?>">
                                                <?= htmlspecialchars($user['role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success">
                                                Active
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-white border px-2 py-1" data-bs-toggle="dropdown">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                        <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z" />
                                                    </svg>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                    <li><a class="dropdown-item view-user" href="#"
                                                            data-name="<?= htmlspecialchars($user['full_name']) ?>"
                                                            data-email="<?= htmlspecialchars($user['email']) ?>"
                                                            data-phone="<?= htmlspecialchars($user['phone']) ?>"
                                                            data-role="<?= htmlspecialchars($user['role']) ?>"
                                                            data-bs-toggle="modal" data-bs-target="#viewUserModal">View Details</a></li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <li><a class="dropdown-item text-danger delete-user" href="#" data-id="<?= $user['id'] ?>">Delete User</a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Footer -->
                    <div class="card-footer bg-white border-top-0 py-3">
                        <nav id="tablePagination"></nav>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<!-- Modal: Create User -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom-0 p-4 pb-0">
                <h5 class="fw-bold mb-0">Create New Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="createUserForm">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name</label>
                        <input type="text" name="full_name" class="form-control" placeholder="John Doe" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+254...">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Initial Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">System Role</label>
                        <select name="role_id" class="form-select" required>
                            <option value="1">Administrator</option>
                            <option value="2">Responder</option>
                            <option value="3">Community Member</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">Create User Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: View User -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-body p-5 text-center">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 64px; height: 64px;">
                    <h3 class="mb-0 fw-bold" id="viewInitial">J</h3>
                </div>
                <h4 class="fw-bold mb-1" id="viewName">John Doe</h4>
                <p class="text-muted mb-4" id="viewRole">Responder</p>

                <div class="row g-2 text-start">
                    <div class="col-12 p-3 bg-light rounded-3 mb-2">
                        <small class="text-muted d-block mb-1">Email Address</small>
                        <div class="fw-bold" id="viewEmail">john@example.com</div>
                    </div>
                    <div class="col-12 p-3 bg-light rounded-3">
                        <small class="text-muted d-block mb-1">Phone Number</small>
                        <div class="fw-bold" id="viewPhone">+254 700 000 000</div>
                    </div>
                </div>
                <button type="button" class="btn btn-dark w-100 mt-4 rounded-pill" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Search Functionality
    document.getElementById("userSearch").addEventListener("keyup", function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll("#usersTable tbody tr");
        rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
        });
    });

    // View Modal Population
    document.querySelectorAll(".view-user").forEach(btn => {
        btn.addEventListener("click", function() {
            const name = this.dataset.name;
            document.getElementById("viewName").innerText = name;
            document.getElementById("viewEmail").innerText = this.dataset.email;
            document.getElementById("viewPhone").innerText = this.dataset.phone || '--';
            document.getElementById("viewRole").innerText = this.dataset.role;
            document.getElementById("viewInitial").innerText = name.charAt(0).toUpperCase();
        });
    });

    // Delete User
    /**
     * Global Delete Handler
     * Works for both Users and Alerts
     */
    document.querySelectorAll(".delete-user, .delete-btn").forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.preventDefault();

            const id = this.dataset.id;
            const row = this.closest('tr'); // Capture the row to remove it later
            const type = this.classList.contains('delete-user') ? 'User' : 'Alert';
            const targetUrl = type === 'User' ? 'delete-user.php' : 'delete-alert.php';

            // Professional Indigo/Slate Confirmation
            Swal.fire({
                title: `Delete ${type}?`,
                text: "This action is permanent and cannot be undone.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#4f46e5", // Indigo Primary
                cancelButtonColor: "#64748b", // Slate Muted
                confirmButtonText: "Yes, delete it",
                background: "#ffffff",
                customClass: {
                    title: 'fw-bold text-dark',
                    popup: 'rounded-4'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.showLoading();

                    // Perform AJAX deletion
                    fetch(targetUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `id=${id}`
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                // Success Feedback
                                Swal.fire({
                                    title: "Deleted!",
                                    text: data.message,
                                    icon: "success",
                                    timer: 2000,
                                    showConfirmButton: false
                                });

                                // Smoothly remove the row from the UI without refresh
                                row.style.transition = "all 0.5s ease";
                                row.style.opacity = "0";
                                row.style.transform = "translateX(20px)";
                                setTimeout(() => row.remove(), 500);

                            } else {
                                Swal.fire("Error", data.message, "error");
                            }
                        })
                        .catch(err => {
                            Swal.fire("System Error", "Could not connect to server.", "error");
                        });
                }
            });
        });
    });

    // Form Submission
    document.getElementById("createUserForm").addEventListener("submit", async function(e) {
        e.preventDefault();
        const response = await fetch("create-user.php", {
            method: "POST",
            body: new FormData(this)
        });
        const result = await response.json();

        if (result.success) {
            Swal.fire("Success", result.message, "success").then(() => window.location.reload());
        } else {
            Swal.fire("Error", result.message, "error");
        }
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>