<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title><?= h($titulo) ?> | MyHobbies</title>
</head>
<body>
    <p><a href="index.php">MyHobbies</a></p>
    <h1><?= h($titulo) ?></h1>
    <?php if(!empty($erro)): ?>
        <p><strong><?= h($erro) ?></strong></p>
    <?php endif; ?>
