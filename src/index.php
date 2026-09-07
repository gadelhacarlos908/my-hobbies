<?php

try
{
    $conexao = require_once __DIR__ . "/banco_dados/conectar_banco.php";

    echo "Conectado ao PostgreSQL com sucesso!";
} catch (Exception $e)
{
    echo "Erro ao conectar ao banco de dados!";
}


?>