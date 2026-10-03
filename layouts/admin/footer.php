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

<?php require_once __DIR__ . '/modal_confirm.php'; ?>

<script src="<?= url('assets/js/custom-confirm.js') ?>"></script>
<script src="<?= url('assets/js/custom-select.js') ?>"></script>

</body>
</html>
