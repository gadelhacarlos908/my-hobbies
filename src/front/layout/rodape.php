<?php foreach($scripts ?? [] as $script): ?>
<script src="<?= h($script) ?>"></script>
<?php endforeach; ?>
</body>
</html>
