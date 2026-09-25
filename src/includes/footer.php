<?php if ($user): ?>
    </main>
  </div>
</div>
<?php else: ?>
</main>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if ($user): ?>
<script src="/assets/js/sidebar.js?v=<?= @filemtime(__DIR__ . '/../assets/js/sidebar.js') ?: time() ?>"></script>
<?php endif; ?>
</body>
</html>
