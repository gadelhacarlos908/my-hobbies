<p>Informe o e-mail da sua conta. Vamos enviar um link para você criar uma nova senha.</p>
<form method="post" action="index.php?tela=esqueci">
    <?= csrf_campo() ?>
    <input type="hidden" name="acao" value="esqueci">
    <p><label>E-mail <input type="email" name="email" required value="<?= h($email) ?>"></label></p>
    <button type="submit">Enviar link</button>
</form>
