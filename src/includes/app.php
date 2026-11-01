<?php

require __DIR__ . "/../banco_dados/funcoes_banco_dados.php";

// Fixo até o login da fase 2, quando passará a vir da sessão.
$usuario_id = 1;

function h($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, "UTF-8");
}

function redirecionar($url)
{
    header("Location: $url");
    exit;
}

function texto_ou_null($texto)
{
    $texto = trim((string) $texto);

    return $texto === "" ? null : $texto;
}

function data_br($data)
{
    return $data ? date("d/m/Y", strtotime($data)) : "";
}

function data_valida($data)
{
    $convertida = DateTime::createFromFormat("Y-m-d", (string) $data);

    return $convertida && $convertida->format("Y-m-d") === $data;
}

?>
