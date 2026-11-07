<form method="post" action="index.php?tela=cadastro">
    <?= csrf_campo() ?>
    <input type="hidden" name="acao" value="cadastro">
    <p><label>Nome <input type="text" name="nome" required value="<?= h($nome) ?>"></label></p>
    <p><label>E-mail <input type="email" name="email" required value="<?= h($email) ?>"></label></p>
    <p><label>Senha <input type="password" name="senha" minlength="8" required></label></p>
    <p><label>Confirme a senha <input type="password" name="confirmacao" minlength="8" required></label></p>
    <button type="submit">Criar conta</button>
</form>
