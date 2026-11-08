<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title><?= h($titulo) ?> | MyHobbies</title>
    <link rel="stylesheet" href="css/app.css">
</head>
<body>
    <?php if(usuario_logado()): ?>
        <div>
            <a href="myHub.php">MyHobbies</a>
            | Olá, <?= h($_SESSION["usuario_nome"]) ?>
            <form method="post" action="sair.php" style="display:inline">
                <?= csrf_campo() ?>
                <button type="submit">Sair</button>
            </form>
        </div>
    <?php endif; ?>
    <h1><?= h($titulo) ?></h1>
    <?php $aviso = aviso_pegar(); ?>
    <?php if($aviso): ?>
        <p><?= h($aviso) ?></p>
    <?php endif; ?>
    <?php if(!empty($erro)): ?>
        <p role="alert" style="color:#b00020; background:#fdecee; border:1px solid #b00020; padding:8px">
            <strong><?= h($erro) ?></strong>
        </p>
    <?php endif; ?>
