<?php

require __DIR__ . "/../src/includes/app.php";
require __DIR__ . "/../src/funcoes/email.php";
require __DIR__ . "/../src/db/usuarios.php";

const PAGINA = "index.php";
const TELAS = ["login" => "Entrar", "cadastro" => "Criar conta", "esqueci" => "Esqueci a senha"];
const LOGIN_MAX_TENTATIVAS = 5;
const LOGIN_JANELA_MINUTOS = 15;

function login_bloqueado_msg($minutos)
{
    return "Muitas tentativas com este e-mail. Tente de novo em $minutos minuto(s) ou use \"Esqueci a senha\".";
}

function usuarios_validar_cadastro($dados)
{
    if($dados["nome"] === "")
        return "Informe seu nome.";

    if(!filter_var($dados["email"], FILTER_VALIDATE_EMAIL))
        return "Informe um e-mail válido.";

    $erro_senha = senha_validar($dados["senha"], $dados["confirmacao"]);
    if($erro_senha)
        return $erro_senha;

    if(usuarios_get_por_email($dados["email"]))
        return "Já existe uma conta com este e-mail. Entre ou use \"Esqueci a senha\".";

    return null;
}

// O link leva o token; o banco guarda só o hash dele, com validade de 1 hora e uso único.
function senha_esqueci($email)
{
    $usuario = usuarios_get_por_email($email);

    if(!$usuario)
        return;

    $token = tokens_cad($usuario["id"]);
    $link = config_app()["url"] . "/redefinir_senha.php?token=$token";
    $texto = "Olá, {$usuario["nome"]}.\n\n"
        . "Para criar uma nova senha no MyHobbies, abra o link abaixo. Ele vale por 1 hora e só pode ser usado uma vez.\n\n"
        . "$link\n\n"
        . "Se você não pediu a troca, ignore este e-mail. Sua senha continua a mesma.";

    email_enviar($usuario["email"], "Redefinição de senha do MyHobbies", $texto);
}

if(usuario_logado())
    redirecionar("myHub.php");

$tela = isset(TELAS[$_GET["tela"] ?? ""]) ? $_GET["tela"] : "login";
$acao = $_POST["acao"] ?? "";
$erro = null;
$email = trim($_POST["email"] ?? "");
$nome = trim($_POST["nome"] ?? "");

// O bloqueio é conferido antes da senha: enquanto durar, nem a senha certa entra.
if($acao === "login")
{
    $tentativas = tentativas_get($email, LOGIN_JANELA_MINUTOS);

    if($tentativas["total"] >= LOGIN_MAX_TENTATIVAS)
        $erro = login_bloqueado_msg($tentativas["minutos"]);
    else
    {
        $usuario = usuarios_login($email, $_POST["senha"] ?? "");

        if($usuario)
        {
            tentativas_del($email);
            usuario_entrar($usuario);
            redirecionar("myHub.php");
        }

        tentativas_cad($email, $_SERVER["REMOTE_ADDR"] ?? null);
        $erro = "E-mail ou senha inválidos.";

        if($tentativas["total"] + 1 >= LOGIN_MAX_TENTATIVAS)
            $erro = login_bloqueado_msg(LOGIN_JANELA_MINUTOS);
    }
}

if($acao === "cadastro")
{
    $dados = [
        "nome" => $nome,
        "email" => $email,
        "senha" => $_POST["senha"] ?? "",
        "confirmacao" => $_POST["confirmacao"] ?? "",
    ];
    $erro = usuarios_validar_cadastro($dados);

    if(!$erro)
    {
        usuario_entrar(usuarios_cad($dados["nome"], $dados["email"], $dados["senha"]));
        redirecionar("myHub.php");
    }
}

if($acao === "esqueci")
{
    if(!filter_var($email, FILTER_VALIDATE_EMAIL))
        $erro = "Informe um e-mail válido.";
    else
    {
        senha_esqueci($email);
        aviso_definir("Se existir uma conta com este e-mail, enviamos um link para criar uma nova senha.");
        redirecionar(PAGINA);
    }
}

exibir(["acesso/$tela", "acesso/links"], [
    "titulo" => TELAS[$tela],
    "erro" => $erro,
    "tela" => $tela,
    "telas_acesso" => TELAS,
    "email" => $email,
    "nome" => $nome,
    "scripts" => ["js/senha.js"],
]);

?>
