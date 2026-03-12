<?php

/**
 * index.php - Responda Login Page
 * Updated to match the Indigo/Slate Production UI
 */
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
    /* Specific styles for the Login Page to override global Slate-50 background */
    body {
        background: radial-gradient(circle at top right, #1e293b, #0f172a);
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        overflow-y: hidden;
        margin-top: 12em;
    }

    .login-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        flex: 1;
        padding: 20px;
    }

    .login-card {
        width: 100%;
        max-width: 420px;
        background: #ffffff;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        padding: 3rem;
    }

    .login-logo-container {
        width: 64px;
        height: 64px;
        background-color: var(--primary);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
    }

    .form-control {
        background-color: #f8fafc !important;
        border: 1.5px solid #e2e8f0 !important;
        padding: 0.75rem 1rem !important;
        height: auto !important;
    }

    .form-control:focus {
        background-color: #ffffff !important;
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1) !important;
    }

    .login-footer {
        color: #94a3b8;
        font-size: 0.85rem;
        margin-top: 2rem;
    }

    /* Override button for full width and indigo style */
    .btn-login {
        background-color: var(--primary) !important;
        color: white !important;
        border: none !important;
        padding: 0.8rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.5px;
        border-radius: 10px !important;
        transition: all 0.3s ease;
    }

    .btn-login:hover {
        background-color: var(--primary-hover) !important;
        transform: translateY(-1px);
        box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4) !important;
    }
</style>

<div class="login-wrapper">
    <div class="login-card text-center">

        <!-- Branded Logo Container -->
        <div class="login-logo-container">
            <img src="assets/images/logo-white.png" alt="Responda Logo" style="width: 32px; height: 32px;">
        </div>

        <h3 class="fw-bold mb-1" style="color: #0f172a; letter-spacing: -0.5px;">Welcome Back</h3>
        <p class="text-muted mb-4 small">Enter your credentials to access the system</p>

        <form action="login-process.php" method="POST" class="text-start">
            <div class="mb-3">
                <label for="username" class="form-label">Username / Email</label>
                <div class="input-group">
                    <input type="text" id="username" name="username" class="form-control" placeholder="admin@responda.com" required>
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between">
                    <label for="password" class="form-label">Password</label>
                    <a href="forgot-password.php" class="text-decoration-none small text-primary fw-semibold">Forgot?</a>
                </div>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-login w-100 mb-3">
                Sign In
            </button>
        </form>

        <div class="mt-4 pt-3 border-top">
            <p class="mb-0 small text-muted">
                New to the community?
                <a href="register.php" class="text-decoration-none text-primary fw-bold">Create an account</a>
            </p>
        </div>
    </div>
</div>



<?php // include '../includes/footer.php'; ?>

<!-- Modernized Flash Error Modal -->
<?php if (!empty($flashError)): ?>
    <div class="modal fade" id="flashErrorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#ef4444" class="bi bi-exclamation-octagon" viewBox="0 0 16 16">
                            <path d="M4.54.146A.5.5 0 0 1 4.893 0h6.214a.5.5 0 0 1 .353.146l4.394 4.394a.5.5 0 0 1 .146.353v6.214a.5.5 0 0 1-.146.353l-4.394 4.394a.5.5 0 0 1-.353.146H4.893a.5.5 0 0 1-.353-.146L.146 11.46A.5.5 0 0 1 0 11.107V4.893a.5.5 0 0 1 .146-.353L4.54.146zM5.1 1 1 5.1v5.8L5.1 15h5.8l4.1-4.1V5.1L10.9 1H5.1z" />
                            <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0l-.35-3.507z" />
                        </svg>
                    </div>
                    <h5 class="fw-bold text-dark">Login Failed</h5>
                    <p class="text-muted small mb-4"><?php echo htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?></p>
                    <button type="button" class="btn btn-dark w-100 rounded-pill" data-bs-dismiss="modal">Try Again</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = new bootstrap.Modal(document.getElementById('flashErrorModal'));
            myModal.show();
        });
    </script>
<?php endif; ?>