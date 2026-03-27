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

// UPDATED: Table names to Title Case and column names with TablePrefix_
$stmt = $pdo->query('
    SELECT u.Users_id, u.Users_full_name, u.Users_email, u.Users_phone, u.Users_status, r.Roles_name AS role 
    FROM Users u 
    LEFT JOIN Roles r ON u.Users_Roles_id = r.Roles_id 
    ORDER BY u.Users_created_at DESC
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
                    // Note: 'role' key remains because of the 'AS role' alias in your users.php query
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
                                <h6 class="fw-bold mb-0">System Users</h6>
                            </div>

                            <div class="search-wrapper">
                                <input type="text" id="userSearch" class="form-control border-0" placeholder="Search by name, email, or role...">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
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
                                            <!-- UPDATED: Users_full_name and Users_email -->
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($user['Users_full_name']) ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars($user['Users_email']) ?></div>
                                        </td>
                                        <td>
                                            <!-- UPDATED: Users_phone -->
                                            <div class="small text-dark"><?= htmlspecialchars($user['Users_phone'] ?: '--') ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $roleColor ?>-subtle text-<?= $roleColor ?>">
                                                <?= htmlspecialchars($user['role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <!-- UPDATED: Users_status (if mapped directly) -->
                                            <span class="badge bg-success-subtle text-success">
                                                <?= ucfirst($user['Users_status'] ?? 'Active') ?>
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
                                                    <!-- UPDATED: dataset mapping with Users_ prefixed keys -->
                                                    <li><a class="dropdown-item view-user" href="#"
                                                            data-name="<?= htmlspecialchars($user['Users_full_name']) ?>"
                                                            data-email="<?= htmlspecialchars($user['Users_email']) ?>"
                                                            data-phone="<?= htmlspecialchars($user['Users_phone']) ?>"
                                                            data-role="<?= htmlspecialchars($user['role']) ?>"
                                                            data-bs-toggle="modal" data-bs-target="#viewUserModal">View Details</a></li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <!-- UPDATED: User comparison using Users_id -->
                                                    <?php if ($user['Users_id'] != $_SESSION['user_id']): ?>
                                                        <li><a class="dropdown-item text-danger delete-user" href="#" data-id="<?= $user['Users_id'] ?>">Delete User</a></li>
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
                        <!-- UPDATED: Users_full_name -->
                        <input type="text" name="Users_full_name" class="form-control" placeholder="Full Name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Email Address</label>
                            <!-- UPDATED: Users_email -->
                            <input type="email" name="Users_email" class="form-control" placeholder="example@email.com" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Phone Number</label>
                            <!-- UPDATED: Users_phone -->
                            <input type="text" name="Users_phone" class="form-control" placeholder="+254...">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Initial Password</label>
                        <!-- UPDATED: Users_password -->
                        <input type="password" name="Users_password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">System Role</label>
                        <!-- UPDATED: Users_Roles_id -->
                        <select name="Users_Roles_id" class="form-select" required>
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

<!-- Modal: View & Edit User -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">

            <!-- Modal Header -->
            <div class="modal-header border-bottom-0 p-4" style="background-color: #f8fafc;">
                <h5 class="modal-title fw-bold text-dark mb-0">Manage User Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 p-lg-5">
                <!-- Profile Summary Header -->
                <div class="text-center mb-4">
                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center mx-auto mb-2 shadow-sm" style="width: 60px; height: 60px;">
                        <h3 class="mb-0 fw-bold" id="viewInitial">A</h3>
                    </div>
                    <h5 class="fw-bold mb-0" id="headerName">User Name</h5>
                    <span class="badge bg-light text-muted border" id="headerRole">Role</span>
                </div>

                <!-- Update Form -->
                <form id="editUserForm">
                    <!-- UPDATED: Users_id -->
                    <input type="hidden" name="Users_id" id="editUserId">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Full Name</label>
                        <!-- UPDATED: Users_full_name -->
                        <input type="text" name="Users_full_name" id="editName" class="form-control border-slate-200 shadow-sm" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                            <!-- UPDATED: Users_email -->
                            <input type="email" name="Users_email" id="editEmail" class="form-control border-slate-200 shadow-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Phone Number</label>
                            <!-- UPDATED: Users_phone -->
                            <input type="text" name="Users_phone" id="editPhone" class="form-control border-slate-200 shadow-sm">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">System Role</label>
                            <!-- UPDATED: Users_Roles_id -->
                            <select name="Users_Roles_id" id="editRole" class="form-select border-slate-200 shadow-sm fw-bold">
                                <option value="1">Administrator</option>
                                <option value="2">Responder</option>
                                <option value="3">Community</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Account Status</label>
                            <!-- UPDATED: Users_status -->
                            <select name="Users_status" id="editStatus" class="form-select border-slate-200 shadow-sm fw-bold">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive / Suspended</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Action Footer -->
            <div class="modal-footer border-top-0 p-4 d-flex justify-content-between" style="background-color: #f8fafc;">
                <button type="button" class="btn btn-outline-danger border-0 fw-bold px-3" id="modalDeleteBtn">
                    Delete User
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-white border px-4 shadow-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary px-4 shadow" id="saveUserBtn">Save Profile</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Search Functionality (Remains logic-heavy, no naming changes required as it parses innerText)
    document.getElementById("userSearch").addEventListener("keyup", function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll("#usersTable tbody tr");
        let visibleCount = 0;

        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            if (text.includes(filter)) {
                row.style.display = "";
                row.style.opacity = "1";
                visibleCount++;
            } else {
                row.style.opacity = "0";
                setTimeout(() => {
                    if (!row.innerText.toLowerCase().includes(document.getElementById("userSearch").value.toLowerCase())) {
                        row.style.display = "none";
                    }
                }, 200);
            }
        });

        // Handle "No Users Found" state
        let emptyMsg = document.getElementById("noResultsMsg");
        if (visibleCount === 0) {
            if (!emptyMsg) {
                let tr = document.createElement('tr');
                tr.id = "noResultsMsg";
                tr.innerHTML = `<td colspan="6" class="text-center py-5 text-muted">
                <div class="mb-2"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg></div>
                No users match your search criteria.
            </td>`;
                document.querySelector("#usersTable tbody").appendChild(tr);
            }
        } else if (emptyMsg) {
            emptyMsg.remove();
        }
    });

    /**
     * 1. Populate Modal with existing data
     * Updated to handle data- attributes populated from Users_ prefixed columns
     */
    document.querySelectorAll(".view-user").forEach(btn => {
        btn.addEventListener("click", function() {
            const d = this.dataset;

            // Map to Form fields (These IDs correspond to the Modal inputs)
            document.getElementById("editUserId").value = d.id; // Map to Users_id
            document.getElementById("editName").value = d.name; // Map to Users_full_name
            document.getElementById("editEmail").value = d.email; // Map to Users_email
            document.getElementById("editPhone").value = d.phone || ''; // Map to Users_phone
            document.getElementById("editStatus").value = d.status || 'active'; // Map to Users_status

            // Map Role ID (Logic to convert alias name back to Roles_id)
            const roleMap = {
                'Admin': 1,
                'Responder': 2,
                'Community': 3
            };
            document.getElementById("editRole").value = roleMap[d.role] || 3;

            // Map Header Profile UI
            document.getElementById("viewInitial").innerText = d.name.charAt(0).toUpperCase();
            document.getElementById("headerName").innerText = d.name;
            document.getElementById("headerRole").innerText = d.role;

            // Set up Delete button for this specific user
            document.getElementById("modalDeleteBtn").onclick = () => triggerDelete(d.id);
        });
    });

    /**
     * 2. Handle Update (AJAX)
     * FormData will now automatically include Users_full_name, Users_email, etc.
     */
    document.getElementById("saveUserBtn").addEventListener("click", async function() {
        const btn = this;
        const form = document.getElementById("editUserForm");

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        try {
            const response = await fetch("update-user.php", {
                method: "POST",
                body: new FormData(form)
            });
            const result = await response.json();

            if (result.success) {
                Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: result.message,
                        showConfirmButton: false,
                        timer: 1500
                    })
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Network connection failed.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Save Profile';
        }
    });


    /**
     * Global Delete Controller
     */
    document.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.delete-user, .delete-alert, #modalDeleteBtn');

        if (deleteBtn) {
            e.preventDefault();

            const isModalDelete = deleteBtn.id === 'modalDeleteBtn';
            // Accessing Users_id or Alerts_id stored in the 'id' dataset or hidden input
            const id = isModalDelete ? document.getElementById('editUserId').value : deleteBtn.dataset.id;
            const isUser = deleteBtn.classList.contains('delete-user') || isModalDelete;

            const typeLabel = isUser ? 'User Account' : 'Incident Alert';
            const targetUrl = isUser ? 'delete-user.php' : 'delete-alert.php';

            if (!id) return;

            Swal.fire({
                title: `Delete ${typeLabel}?`,
                text: "This action is permanent and cannot be undone.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#ef4444", 
                cancelButtonColor: "#64748b", 
                confirmButtonText: "Yes, Delete Permanently",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {

                    Swal.fire({
                        title: 'Processing Request...',
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const formData = new FormData();
                    formData.append('id', id);

                    fetch(targetUrl, {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: data.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire("Error", data.message, "error");
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire("System Error", "Connection to server failed.", "error");
                        });
                }
            });
        }
    });

    /**
     * 3. Create User Submission
     * FormData will include Users_full_name, Users_email, Users_password, Users_Roles_id
     */
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