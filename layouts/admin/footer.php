<?php
/**
 * ============================================================
 *  layouts/admin/footer.php — Penutup area admin
 * ============================================================
 *  Menutup <main>, flex container, menambahkan script global,
 *  lalu menutup </body></html>.
 */
?>
            </div><!-- /.max-w-7xl -->
        </main>

    </div><!-- /.flex-1 -->
</div><!-- /.flex -->

<!-- Script global admin -->
<script>
// Konfirmasi hapus generik (dipakai via data-confirm)
document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-confirm]');
    if (!el) return;
    var msg = el.getAttribute('data-confirm') || 'Apakah Anda yakin?';
    if (!confirm(msg)) {
        e.preventDefault();
    }
});
</script>

</body>
</html>
