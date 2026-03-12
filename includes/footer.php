<?php
/**
 * footer.php - Modern Dashboard Footer
 * Closes layout tags and adds high-polish system info
 */
?>

    <!-- Close the main content column started in sidebar.php/header.php -->
    </main> 
</div> <!-- End row from layout -->
</div> <!-- End container-fluid -->

<!-- Main Dashboard Footer -->
<footer>
    <div class="footer-left">
        <small>&copy; <?php echo date("Y"); ?> <span class="footer-brand">Responda.</span> All rights reserved.</small>
    </div>
    <div class="footer-right d-none d-md-block">
        <small class="text-uppercase" style="font-size: 0.7rem; opacity: 0.8;">
            Emergency Response System <span class="mx-2 text-slate-200">|</span> v1.0.4
        </small>
    </div>
</footer>

<!-- Bootstrap JS Bundle (Includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" 
        crossorigin="anonymous"></script>

<!-- Custom Application Logic -->
<script src="assets/js/script.js"></script>

</body>
</html>