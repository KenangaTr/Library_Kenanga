</div> <!-- End Container -->

<footer class="text-center py-4 mt-5 text-muted">
    <small>&copy; <?php echo date('Y'); ?> Library Management System. All rights reserved.</small>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Confirm delete
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this book?')) {
            window.location.href = 'delete.php?id=' + id;
        }
    }
</script>
</body>
</html>
