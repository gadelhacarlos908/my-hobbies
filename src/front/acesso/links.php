<p>
    <?php foreach($telas_acesso as $chave => $rotulo): ?>
        <?php if($chave !== $tela): ?>
            <a href="index.php?tela=<?= $chave ?>"><?= h($rotulo) ?></a>
        <?php endif; ?>
    <?php endforeach; ?>
</p>
