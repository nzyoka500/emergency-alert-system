<?php
// register.php - registration form for new users
include '../includes/header.php';

// show any flash error
$flashError = '';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['error'])) {
    $flashError = $_SESSION['error'];
    unset($_SESSION['error']);
}

?>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg p-4" style="width:100%; max-width:560px; border-radius:12px;">
        <h3 class="mb-3 text-center">Register for Responda</h3>
        <form action="register-process.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Full name</label>
                <input type="text" name="full_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirm" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">Create account</button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php">Back to login</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<?php if (!empty($flashError)): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var modalHtml = `
            <div class="modal fade" id="flashErrorModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Error</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body"><p class="mb-0"><?php echo htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?></p></div>
                  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                </div>
              </div>
            </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        var myModal = new bootstrap.Modal(document.getElementById('flashErrorModal'));
        myModal.show();
    });
    </script>
<?php endif; ?>
