    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    const toggle  = document.getElementById('sidebarToggle');
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebarOverlay');

    function openSidebar() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
    }
    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    }

    if (toggle) toggle.addEventListener('click', openSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);

    document.querySelectorAll('.alert-dismissible .btn-close').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('.alert').remove();
        });
    });
})();
</script>
</body>
</html>
