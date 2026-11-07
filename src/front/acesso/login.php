<form method="post" action="index.php">
    <?= csrf_campo() ?>
    <input type="hidden" name="acao" value="login">
    <p><label>E-mail <input type="email" name="email" required value="<?= h($email) ?>"></label></p>
    <p><label>Senha <input type="password" name="senha" required></label></p>
    <button type="submit">Entrar</button>
</form>
