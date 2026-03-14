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
                        <input type="text" name="full_name" class="form-control" placeholder="Full Name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="example@email.com" required>
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
                    <input type="hidden" name="user_id" id="editUserId">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Full Name</label>
                        <input type="text" name="full_name" id="editName" class="form-control border-slate-200 shadow-sm" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                            <input type="email" name="email" id="editEmail" class="form-control border-slate-200 shadow-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Phone Number</label>
                            <input type="text" name="phone" id="editPhone" class="form-control border-slate-200 shadow-sm">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">System Role</label>
                            <select name="role_id" id="editRole" class="form-select border-slate-200 shadow-sm fw-bold">
                                <option value="1">Administrator</option>
                                <option value="2">Responder</option>
                                <option value="3">Community</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Account Status</label>
                            <select name="status" id="editStatus" class="form-select border-slate-200 shadow-sm fw-bold">
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-1">
                        <path d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
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
    // Search Functionality
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
     */
    document.querySelectorAll(".view-user").forEach(btn => {
        btn.addEventListener("click", function() {
            const d = this.dataset;

            // Map to Form
            document.getElementById("editUserId").value = d.id;
            document.getElementById("editName").value = d.name;
            document.getElementById("editEmail").value = d.email;
            document.getElementById("editPhone").value = d.phone || '';
            document.getElementById("editStatus").value = d.status || 'active';

            // Map Role ID (Logic to convert name back to ID)
            const roleMap = {
                'Admin': 1,
                'Responder': 2,
                'Community': 3
            };
            document.getElementById("editRole").value = roleMap[d.role] || 3;

            // Map Header Profile
            document.getElementById("viewInitial").innerText = d.name.charAt(0).toUpperCase();
            document.getElementById("headerName").innerText = d.name;
            document.getElementById("headerRole").innerText = d.role;

            // Set up Delete button for this specific user
            document.getElementById("modalDeleteBtn").onclick = () => triggerDelete(d.id);
        });
    });

    /**
     * 2. Handle Update (AJAX)
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
     * Uses Event Delegation to ensure it works on dynamic elements and modals
     */
    document.addEventListener('click', function(e) {
        // Look for elements with .delete-user or .delete-alert class (or closest parent)
        const deleteBtn = e.target.closest('.delete-user, .delete-alert, #modalDeleteBtn');

        if (deleteBtn) {
            e.preventDefault();

            // 1. Determine Type and ID
            // If it's the modal button, we get ID from a hidden input, otherwise from dataset
            const isModalDelete = deleteBtn.id === 'modalDeleteBtn';
            const id = isModalDelete ? document.getElementById('editUserId').value : deleteBtn.dataset.id;
            const isUser = deleteBtn.classList.contains('delete-user') || isModalDelete;

            const typeLabel = isUser ? 'User' : 'Alert';
            const targetUrl = isUser ? 'delete-user.php' : 'delete-alert.php';

            if (!id) return;

            // 2. SweetAlert Confirmation
            Swal.fire({
                title: `Delete ${typeLabel}?`,
                text: "This action is permanent and cannot be undone.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#ef4444", // Red
                cancelButtonColor: "#64748b", // Slate
                confirmButtonText: "Yes, Delete Permanently",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {

                    // Show loading state
                    Swal.fire({
                        title: 'Processing...',
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // 3. AJAX Request
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
                                    title: 'Deleted!',
                                    text: data.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    // If we deleted from a modal, we must refresh or remove the row
                                    // Simply reloading is safest to update all counters
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