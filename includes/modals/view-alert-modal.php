
<!-- Modal to View Alert Details -->
<div class="modal fade" id="viewAlertModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Alert Details</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <form id="alertCrudForm">

                    <input type="hidden" name="alert_id" id="alertId">

                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" id="alertTitle" name="title">
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea class="form-control" id="alertDescription" name="description"></textarea>
                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Alert Type</label>
                            <input type="text" class="form-control" id="alertType" name="type">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Status</label>
                            <select class="form-control" id="alertStatus" name="status">
                                <option value="pending">Pending</option>
                                <option value="verified">Verified</option>
                                <option value="active">Active</option>
                                <option value="resolved">Resolved</option>
                            </select>
                        </div>

                    </div>

                    <div class="mb-3">
                        <label>Assign Respondent</label>
                        <select class="form-control" name="assigned_to" id="alertRespondent">

                            <?php foreach ($responders as $responder): ?>

                                <option value="<?= $responder['id'] ?>">
                                    <?= htmlspecialchars($responder['full_name']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>
                    </div>

                </form>

            </div>

            <div class="modal-footer border-top-0 bg-light p-4">
    <!-- Close button for everyone -->
    <button class="btn btn-white border px-4" data-bs-dismiss="modal">Close</button>
    
    <?php if ($_SESSION['role_id'] == 1): ?>
        <!-- Admin only: Show Verify button if status is pending -->
        <div id="adminActionButtons">
            <!-- This will be shown dynamically via JS -->
        </div>
        
        <!-- Delete Alert -->
        <button class="btn btn-outline-danger px-4" id="deleteAlertBtn">Delete Incident</button>
    <?php endif; ?>
</div>

            <div class="modal-footer">

                <!-- Soft delete: with modal to confirm -->
                <button class="btn btn-danger" id="deleteAlert">
                    Delete
                </button>
                
                <!-- Button to update alert record -->
                <button class="btn btn-warning" id="editAlert">
                    Update
                </button>

                <!-- Button to close the modal -->
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>

            </div>

        </div>
    </div>
</div>


<script>

    // View Alert Details in Modal
    document.querySelectorAll(".view-alert").forEach(btn => {

        btn.addEventListener("click", function(){

        document.getElementById("alertId").value = this.dataset.id
        document.getElementById("alertTitle").value = this.dataset.title
        document.getElementById("alertDescription").value = this.dataset.description
        document.getElementById("alertType").value = this.dataset.type
        document.getElementById("alertStatus").value = this.dataset.status

        })

    })

    // Handle Alert Update
    document.getElementById("editAlert").addEventListener("click",function(){

        let formData = new FormData(document.getElementById("alertCrudForm"))

        fetch("update-alert.php",{
        method:"POST",
        body:formData
        })
        .then(res=>res.json())
        .then(data=>{

        Swal.fire({
        icon:data.success ? "success":"error",
        title:data.message
        }).then(()=>location.reload())

        })

    })

    // Handle Alert Deletion
    document.getElementById("deleteAlert").addEventListener("click",function(){

        let alertId = document.getElementById("alertId").value

        Swal.fire({
        title:"Delete Alert?",
        text:"This cannot be undone",
        icon:"warning",
        showCancelButton:true
        }).then(result=>{

        if(result.isConfirmed){

        fetch("delete-alert.php",{
        method:"POST",
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:"id="+alertId
        })
        .then(res=>res.json())
        .then(data=>{

        Swal.fire({
        icon:data.success ? "success":"error",
        title:data.message
        }).then(()=>location.reload())

        })

        }

        })

    })

    // Modal action buttons for admin (verify/respond)
    // Inside the button click listener that opens the modal:
const status = this.dataset.status;
const alertId = this.dataset.id;
const actionContainer = document.getElementById('adminActionButtons');

if (status === 'pending' && actionContainer) {
    actionContainer.innerHTML = `
        <button class="btn btn-primary px-4 me-2" onclick="verifyAndBroadcast(${alertId})">
            Verify & Broadcast
        </button>
    `;
} else if (actionContainer) {
    actionContainer.innerHTML = ''; // Hide if already verified
}


</script>