<?php

require_once __DIR__ . "/../conf/dados_psql.php";

$dados_conexao = "host=$host port=$port dbname=$dbname user=$user password=$password";
$conexao = pg_connect($dados_conexao);

if(!$conexao)
    throw new Exception("Não foi possivel conectar ao banco de dados.");
?>