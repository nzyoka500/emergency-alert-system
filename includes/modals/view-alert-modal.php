<?php
/**
 * view-alert-modal.php - Production Grade Detailed View
 * Standardized for Admin (Management) and Responders (Viewing)
 */
?>

<!-- Modal to View Alert Details -->
<div class="modal fade" id="viewAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">

            <!-- Modal Header -->
            <div class="modal-header border-bottom-0 p-4" style="background-color: #f8fafc;">
                <div class="d-flex align-items-center">
                    <div class="bg-indigo-subtle text-primary p-2 rounded-3 me-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>
                    <h5 class="modal-title fw-bold text-dark mb-0" style="letter-spacing: -0.5px;">Incident Report Details</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 p-lg-5">
                <form id="alertCrudForm">
                    <!-- Hidden input for ID -->
                    <input type="hidden" name="alert_id" id="alertId">

                    <!-- Basic Info Section -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase text-muted" style="letter-spacing: 0.5px;">Alert Title</label>
                        <input type="text" class="form-control border-slate-200 shadow-sm fw-semibold" id="alertTitle" name="title" 
                               <?= $_SESSION['role_id'] != 1 ? 'readonly' : '' ?>>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase text-muted" style="letter-spacing: 0.5px;">Situation Description</label>
                        <textarea class="form-control border-slate-200 shadow-sm" id="alertDescription" name="description" rows="5" 
                                  <?= $_SESSION['role_id'] != 1 ? 'readonly' : '' ?>></textarea>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-uppercase text-muted" style="letter-spacing: 0.5px;">Alert Category</label>
                            <input type="text" class="form-control border-slate-200 shadow-sm bg-light" id="alertType" name="type" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-uppercase text-muted" style="letter-spacing: 0.5px;">Alert Status</label>
                            <?php if ($_SESSION['role_id'] == 1): ?>
                                <select class="form-select border-slate-200 shadow-sm fw-bold" id="alertStatus" name="status">
                                    <option value="pending text-warning">Pending</option>
                                    <option value="verified text-info">Verified</option>
                                    <option value="broadcasted text-primary">Broadcasted</option>
                                    <option value="resolved text-success">Resolved</option>
                                </select>
                            <?php else: ?>
                                <input type="text" class="form-control border-slate-200 shadow-sm bg-light fw-bold" id="alertStatusView" readonly>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-uppercase text-muted">Incident Severity</label>
                            <?php if ($_SESSION['role_id'] == 1): ?>
                                <select class="form-select border-slate-200 shadow-sm fw-bold" id="alertSeverity" name="severity">
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                </select>
                            <?php else: ?>
                                <input type="text" class="form-control bg-light fw-bold" id="alertSeverityView" readonly>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($_SESSION['role_id'] == 1): ?>
                    <!-- Assignment Section (Admin Only) -->
                    <div class="p-4 rounded-4 bg-slate-50 border border-slate-200">
                        <label class="form-label small fw-bold text-uppercase text-muted" style="letter-spacing: 0.5px;">Assign Responder</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0zM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </span>
                            <select class="form-select border-start-0 ps-0" name="assigned_to" id="alertRespondent">
                                <option value="">No responder assigned</option>
                                <?php 
                                    // Assuming $responders is pre-fetched in the main page
                                    if(isset($responders)) {
                                        foreach ($responders as $resp): 
                                ?>
                                    <option value="<?= $resp['id'] ?>"><?= htmlspecialchars($resp['full_name']) ?></option>
                                <?php endforeach; } ?>
                            </select>
                        </div>
                        <small class="form-text text-muted mt-2 d-block">Select a primary responder to handle this incident.</small>
                    </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Unified Action Footer -->
            <div class="modal-footer border-top-0 p-4 d-flex justify-content-between" style="background-color: #f8fafc;">
                <div>
                    <?php if ($_SESSION['role_id'] == 1): ?>
                        <button type="button" class="btn btn-outline-danger border-0 fw-bold px-3" id="deleteAlert">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Delete
                        </button>
                    <?php endif; ?>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-white border shadow-sm px-4" data-bs-dismiss="modal">Close</button>
                    
                    <?php if ($_SESSION['role_id'] == 1): ?>
                        <div id="adminActionButtons" class="d-inline-block">
                            <!-- Injected via JavaScript -->
                        </div>
                        <button type="button" class="btn btn-primary shadow px-4" id="editAlert">Update Details</button>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
/**
 * Initialization and Data Mapping
 */
document.querySelectorAll(".view-alert").forEach(btn => {
    btn.addEventListener("click", function() {
        const data = this.dataset;
        
        // Map data to fields
        document.getElementById("alertId").value = data.id;
        document.getElementById("alertTitle").value = data.title;
        document.getElementById("alertDescription").value = data.description;
        document.getElementById("alertType").value = data.type;
        
        const statusField = document.getElementById("alertStatus");
        const statusViewField = document.getElementById("alertStatusView");

        if (statusField) statusField.value = data.status;
        if (statusViewField) statusViewField.value = data.status.charAt(0).toUpperCase() + data.status.slice(1);

        // Handle Admin Verification Buttons
        const actionContainer = document.getElementById('adminActionButtons');
        if (actionContainer) {
            if (data.status === 'pending') {
                actionContainer.innerHTML = `
                    <button type="button" class="btn btn-success shadow-sm px-4 me-2 d-flex align-items-center" 
                            onclick="verifyAndBroadcast(${data.id})">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                        Verify & Broadcast
                    </button>`;
            } else {
                actionContainer.innerHTML = '';
            }
        }
    });
});

/**
 * Update Handler
 */
const editBtn = document.getElementById("editAlert");
if (editBtn) {
    editBtn.addEventListener("click", function() {
        const form = document.getElementById("alertCrudForm");
        const formData = new FormData(form);
        
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        fetch("update-alert.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            Swal.fire({
                icon: data.success ? "success" : "error",
                title: data.success ? "Success" : "Update Failed",
                text: data.message,
                confirmButtonColor: "#4f46e5"
            }).then(() => { if(data.success) location.reload(); });
        })
        .catch(() => Swal.fire("Error", "Network error. Try again.", "error"))
        .finally(() => {
            this.disabled = false;
            this.innerHTML = "Update Details";
        });
    });
}

/**
 * Delete Handler (Integrated with the Smooth Delete logic)
 */
const deleteBtn = document.getElementById("deleteAlert");
if (deleteBtn) {
    deleteBtn.addEventListener("click", function() {
        const alertId = document.getElementById("alertId").value;

        Swal.fire({
            title: "Purge Incident Record?",
            text: "This action is permanent and will remove all associated responder logs.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#ef4444",
            cancelButtonColor: "#64748b",
            confirmButtonText: "Yes, delete permanently"
        }).then(result => {
            if (result.isConfirmed) {
                fetch("delete-alert.php", {
                    method: "POST",
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: "id=" + alertId
                })
                .then(res => res.json())
                .then(data => {
                    Swal.fire({
                        icon: data.success ? "success" : "error",
                        title: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => { if(data.success) location.reload(); });
                });
            }
        });
    });
}
</script>