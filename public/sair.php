<?php

require __DIR__ . "/../src/includes/app.php";

// Só aceita POST com token CSRF, para que outro site não consiga deslogar o usuário por um link.
if($_SERVER["REQUEST_METHOD"] !== "POST")
    redirecionar("myHub.php");

$_SESSION = [];
session_regenerate_id(true);
session_destroy();

redirecionar("index.php");

?>
