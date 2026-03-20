    </main>
    <footer class="admin-footer">
        <p>&copy; <?= date('Y') ?> Admin Panel &mdash; All rights reserved</p>
    </footer>
</div><!-- /.main-wrapper -->

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.querySelector('.main-wrapper').classList.toggle('shifted');
}
setTimeout(function() {
    var a = document.querySelector('.alert');
    if (a) { a.style.transition='opacity 0.5s'; a.style.opacity='0'; setTimeout(()=>a.remove(),500); }
}, 4000);
</script>
</body>
</html>
