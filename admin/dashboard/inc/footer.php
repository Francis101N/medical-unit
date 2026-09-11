<?php if (strtolower($_SESSION['role'] ?? '') !== 'adhoc-user'): ?>
<footer>
    <div class="footer mb-0 text-muted text-center">
        <p>2026 &copy; Medicals Management System </p>
    </div>
</footer>
<?php endif; ?>