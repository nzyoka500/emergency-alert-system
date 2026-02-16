<?php
    // import files
    include '../includes/header.php';

    // Capture and clear flash error if present
    $flashError = '';
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['error'])) {
        $flashError = $_SESSION['error'];
        unset($_SESSION['error']);
    }

?>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg p-5" style="width: 100%; max-width: 400px; border-radius: 12px;">

        <!-- App Logo -->
        <div class="text-center mb-0">
            <img src="../assets/images/logo-full.png" alt="Responda Logo" class="mb-3" style="width: auto; height: 80px;">
        </div>

        <form action="login-process.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label fw-700">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label fw-700">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px; font-weight: 600;">Login</button>
        </form>

        <div class="d-flex justify-content-center align-items-center gap-3 mt-4 pt-4 border-top">
            <a href="forgot-password.php" class="text-decoration-none text-primary fw-500 small">Forgot Password?</a>
            <span class="text-muted">|</span>
            <a href="register.php" class="text-decoration-none text-primary fw-500 small">Register</a>
        </div>
    </div>
</div>


<?php include '../includes/footer.php'; ?>

<!-- Flash Error Modal -->
<?php if (!empty($flashError)): ?>
        <div class="modal fade" id="flashErrorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Error Message</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0"><?php echo htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
                var myModal = new bootstrap.Modal(document.getElementById('flashErrorModal'));
                myModal.show();
        });
        </script>
<?php endif; ?>


