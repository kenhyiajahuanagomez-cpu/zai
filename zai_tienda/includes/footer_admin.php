        </main>
    </div>
</div>

<!-- ============ TOAST NOTIFICATIONS ============ -->
<div class="toast-container" id="toastContainer"></div>

<!-- Chart.js ya está cargado en el <head> de header_admin.php -->
<script src="<?= SITE_URL ?>/js/admin.js"></script>
<?php if (isset($js_extra)): ?>
    <?php foreach ($js_extra as $js): ?>
        <script src="<?= SITE_URL ?>/js/<?= $js ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
