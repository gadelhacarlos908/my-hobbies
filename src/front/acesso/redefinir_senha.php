<?php if(!$token_valido): ?>
    <p>Este link é inválido, já foi usado ou expirou.</p>
    <p><a href="index.php?tela=esqueci">Pedir um novo link</a></p>
<?php else: ?>
    <form method="post" action="redefinir_senha.php">
        <?= csrf_campo() ?>
        <input type="hidden" name="acao" value="redefinir">
        <input type="hidden" name="token" value="<?= h($token) ?>">
        <p><label>Nova senha <input type="password" name="senha" minlength="8" required></label></p>
        <p><label>Confirme a senha <input type="password" name="confirmacao" minlength="8" required></label></p>
        <button type="submit">Salvar nova senha</button>
    </form>
<?php endif; ?>
