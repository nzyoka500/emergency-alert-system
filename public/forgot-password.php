<?php
    /**
     * forgot-password.php - Responda Password Recovery
     * Updated to match new database naming conventions
     */
    include '../includes/header.php';

    // Capture flash messages
    $flashError = '';
    $flashSuccess = '';
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    if (!empty($_SESSION['error'])) {
        $flashError = $_SESSION['error'];
        unset($_SESSION['error']);
    }
    if (!empty($_SESSION['success'])) {
        $flashSuccess = $_SESSION['success'];
        unset($_SESSION['success']);
    }
?>

<style>
    /* Specific styles for the Recovery Page */
    body {
        background: radial-gradient(circle at top right, #1e293b, #0f172a);
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        margin-top: 12em;
        overflow: hidden;
    }

    .recovery-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        flex: 1;
        padding: 20px;
    }

    .recovery-card {
        width: 100%;
        max-width: 420px;
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        padding: 3rem;
    }

    .brand-icon-container {
        width: 64px;
        height: 64px;
        background-color: var(--primary);
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
        color: white;
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

    .btn-recovery {
        background-color: var(--primary) !important;
        color: white !important;
        border: none !important;
        padding: 0.8rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.5px;
        border-radius: 10px !important;
        transition: all 0.3s ease;
    }

    .btn-recovery:hover {
        background-color: var(--primary-hover) !important;
        transform: translateY(-1px);
        box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4) !important;
    }

    footer {
        color: #94a3b8;
        font-size: 0.85rem;
        margin-top: 2rem;
        text-align: center;
        padding-bottom: 20px;
    }
</style>

<div class="recovery-wrapper">
    <div class="recovery-card text-center">

        <!-- Branded Icon Container -->
        <div class="brand-icon-container">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
        </div>

        <h3 class="fw-bold mb-1" style="color: #0f172a; letter-spacing: -0.5px;">Reset Password</h3>
        <p class="text-muted mb-4 small">Enter your email address and we'll send you instructions to reset your password.</p>

        <?php if (!empty($flashSuccess)): ?>
            <div class="alert bg-success-subtle text-success border-0 small py-2 mb-4 text-start">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill me-2" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
                <?= htmlspecialchars($flashSuccess) ?>
            </div>
        <?php endif; ?>

        <form action="forgot-password-process.php" method="POST" class="text-start">
            <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <!-- UPDATED: name attribute to Users_email to match database column prefixing -->
                <input type="email" id="email" name="Users_email" class="form-control" placeholder="yourname@example.com" required>
            </div>

            <button type="submit" class="btn btn-recovery w-100 mb-3">
                Send Reset Link
            </button>
        </form>

        <div class="mt-4 pt-3 border-top">
            <p class="mb-0 small text-muted">
                Wait, I remember it! 
                <a href="index.php" class="text-decoration-none text-primary fw-bold">Back to Login</a>
            </p>
        </div>
    </div>
</div>

<!-- Modernized Flash Error Modal -->
<?php if (!empty($flashError)): ?>
    <div class="modal fade" id="flashErrorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#ef4444" class="bi bi-exclamation-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                        </svg>
                    </div>
                    <h5 class="fw-bold text-dark">Error</h5>
                    <p class="text-muted small mb-4"><?php echo htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?></p>
                    <button type="button" class="btn btn-dark w-100 rounded-pill" data-bs-dismiss="modal">Dismiss</button>
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