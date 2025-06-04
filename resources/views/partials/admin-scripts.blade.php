<!-- resources/views/partials/admin-scripts.blade.php -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("sidebarCollapse").addEventListener("click", function () {
            document.querySelector(".sidebar").classList.toggle("active");
            document.getElementById("content").classList.toggle("active");
        });
    });
</script>