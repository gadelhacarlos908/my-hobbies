<?php

$config = require __DIR__ . "/../../config/banco.php";

$dados_conexao = "host={$config["host"]} port={$config["port"]} dbname={$config["dbname"]} "
    . "user={$config["user"]} password={$config["password"]}";
$conexao = pg_connect($dados_conexao);

if(!$conexao)
    throw new Exception("Não foi possivel conectar ao banco de dados.");

return $conexao;
?>
