<?php

// Funções usadas pelas páginas e templates. Este arquivo só declara funções, não executa nada.

function erro_generico()
{
    if(!headers_sent())
        http_response_code(500);

    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8"><title>Erro | MyHobbies</title></head>'
        . '<body><h1>Algo deu errado</h1><p>Tente novamente em alguns minutos.</p>'
        . '<p><a href="index.php">Voltar ao início</a></p></body></html>';
    exit;
}

function h($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, "UTF-8");
}

function redirecionar($url)
{
    header("Location: $url");
    exit;
}

// Monta a página com os templates de src/front/. Cada template só enxerga as variáveis de $dados.
function exibir($telas, $dados)
{
    extract($dados);

    require __DIR__ . "/../front/layout/cabecalho.php";

    foreach($telas as $arquivo_tela)
        require __DIR__ . "/../front/$arquivo_tela.php";

    require __DIR__ . "/../front/layout/rodape.php";
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

function config_app()
{
    static $config = null;

    if($config === null)
        $config = require __DIR__ . "/../../config/app.php";

    return $config;
}

function csrf_token()
{
    if(empty($_SESSION["csrf"]))
        $_SESSION["csrf"] = bin2hex(random_bytes(32));

    return $_SESSION["csrf"];
}

function csrf_campo()
{
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

function csrf_valido()
{
    return isset($_SESSION["csrf"], $_POST["csrf"])
        && is_string($_POST["csrf"])
        && hash_equals($_SESSION["csrf"], $_POST["csrf"]);
}

// Um novo ID de sessão no login impede que alguém reaproveite um ID conhecido antes dele.
function usuario_entrar($usuario)
{
    session_regenerate_id(true);
    unset($_SESSION["csrf"]);

    $_SESSION["usuario_id"] = (int) $usuario["id"];
    $_SESSION["usuario_nome"] = $usuario["nome"];
}

function usuario_logado()
{
    return isset($_SESSION["usuario_id"]);
}

function exigir_login()
{
    if(!usuario_logado())
        redirecionar("index.php");

    return (int) $_SESSION["usuario_id"];
}

function senha_validar($senha, $confirmacao)
{
    if(mb_strlen($senha) < 8)
        return "A senha precisa ter pelo menos 8 caracteres.";

    if($senha !== $confirmacao)
        return "A confirmação não é igual à senha.";

    return null;
}

// Mensagem que sobrevive a um redirecionamento e aparece uma única vez.
function aviso_definir($texto)
{
    $_SESSION["aviso"] = $texto;
}

function aviso_pegar()
{
    $aviso = $_SESSION["aviso"] ?? null;
    unset($_SESSION["aviso"]);

    return $aviso;
}

?>
