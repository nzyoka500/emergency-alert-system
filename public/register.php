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

    .register-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        flex: 1;
        padding: 40px 20px;
        width: 100%;
           margin-top: 2rem;
    }

    footer {
        text-align: center;
        padding: 20px;
        margin-top: auto;
        width: 100%;
    }
</style>

<div class="register-wrapper">
    <div class="card shadow-lg p-5" style="width:100%; max-width:500px; border-radius:16px; border: 1px solid rgba(255,255,255,0.2); background: white; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
        <h3 class="mb-2 text-center fw-bold" style="color: #2d3748;">Create Account</h3>
        <p class="text-center text-muted mb-4" style="font-size: 13px;">Join Responda Emergency System</p>
        
        <form action="register-process.php" method="POST">
            <div class="mb-3">
                <label class="form-label fw-600" style="color: #2d3748;">Full Name</label>
                <input type="text" name="full_name" class="form-control" placeholder="Enter your full name" required style="padding: 12px 14px; border: 2px solid #e8ebf2; border-radius: 8px; transition: all 0.3s ease;">
            </div>
            <div class="mb-3">
                <label class="form-label fw-600" style="color: #2d3748;">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required style="padding: 12px 14px; border: 2px solid #e8ebf2; border-radius: 8px; transition: all 0.3s ease;">
            </div>
            <div class="mb-3">
                <label class="form-label fw-600" style="color: #2d3748;">Phone</label>
                <input type="text" name="phone" class="form-control" placeholder="Enter your phone number" style="padding: 12px 14px; border: 2px solid #e8ebf2; border-radius: 8px; transition: all 0.3s ease;">
            </div>
            <div class="mb-3">
                <label class="form-label fw-600" style="color: #2d3748;">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Create a password" required style="padding: 12px 14px; border: 2px solid #e8ebf2; border-radius: 8px; transition: all 0.3s ease;">
            </div>
            <div class="mb-4">
                <label class="form-label fw-600" style="color: #2d3748;">Confirm Password</label>
                <input type="password" name="password_confirm" class="form-control" placeholder="Confirm your password" required style="padding: 12px 14px; border: 2px solid #e8ebf2; border-radius: 8px; transition: all 0.3s ease;">
            </div>
            <button type="submit" class="btn btn-primary w-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px; font-weight: 600; border-radius: 8px; transition: all 0.3s ease;">Create Account</button>
        </form>
        <div class="text-center mt-4 pt-4 border-top">
            <p class="text-muted mb-0" style="font-size: 13px;">Already have an account? <a href="index.php" class="text-primary fw-600 text-decoration-none">Login here</a></p>
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
