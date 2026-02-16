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

<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #e7d0ff 100%);
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }

    .login-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        flex: 1;
        padding: 40px 20px;
        width: 100%;
        margin-top: 8rem;
    }

    footer {
        text-align: center;
        padding: 20px;
        margin-top: auto;
        width: 100%;
    }
</style>

<div class="login-wrapper">
    <div class="card shadow-lg p-5" style="width: 100%; max-width: 420px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.2); background: white; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">

        <!-- App Logo -->
        <div class="text-center mb-3">
            <img src="assets/images/logo-full.png" alt="Responda Logo" class="mb-3" style="width: auto; height: 80px;">
        </div>

        <h3 class="text-center fw-bold mb-2" style="color: #2d3748;">Welcome Back</h3>
        <p class="text-center text-muted mb-4" style="font-size: 14px;">Emergency Alert & Response System</p>

        <form action="login-process.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label fw-600" style="color: #2d3748;">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required style="padding: 12px 14px; border: 2px solid #e8ebf2; border-radius: 8px; font-size: 14px; transition: all 0.3s ease;">
            </div>
            <div class="mb-4">
                <label for="password" class="form-label fw-600" style="color: #2d3748;">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required style="padding: 12px 14px; border: 2px solid #e8ebf2; border-radius: 8px; font-size: 14px; transition: all 0.3s ease;">
            </div>
            <button type="submit" class="btn btn-primary w-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px; font-weight: 600; border-radius: 8px; transition: all 0.3s ease;">Login</button>
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


