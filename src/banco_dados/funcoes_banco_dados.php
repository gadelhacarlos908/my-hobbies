<?php

// Mantém uma única conexão por requisição.
function conexaoDB()
{
    static $conexao = null;

    if($conexao === null)
        $conexao = require __DIR__ . "/conectar_banco.php";

    return $conexao;
}

function tratarParametrosDB($parametros)
{
    return array_map(function($valor)
    {
        if(is_bool($valor))
            return $valor ? "true" : "false";

        return $valor;
    }, $parametros);
}

// Os valores viajam separados do SQL, então nunca são interpretados como comando.
function consultaDB($query, $parametros)
{
    $resultado = pg_query_params(conexaoDB(), $query, tratarParametrosDB($parametros));

    if($resultado === false)
        throw new Exception("Erro ao executar a query: " . pg_last_error(conexaoDB()));

    return $resultado;
}

function selectDB($query, $parametros = [])
{
    return pg_fetch_all(consultaDB($query, $parametros));
}

function executarDB($query, $parametros = [])
{
    return pg_affected_rows(consultaDB($query, $parametros));
}

?>
