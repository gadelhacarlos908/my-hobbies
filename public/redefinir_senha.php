<?php

require __DIR__ . "/../src/includes/app.php";
require __DIR__ . "/../src/db/usuarios.php";

$token = $_GET["token"] ?? $_POST["token"] ?? "";
$registro = is_string($token) && $token !== "" ? tokens_get_valido($token) : null;
$erro = null;

if($registro && ($_POST["acao"] ?? "") === "redefinir")
{
    $senha = $_POST["senha"] ?? "";
    $erro = senha_validar($senha, $_POST["confirmacao"] ?? "");

    if(!$erro)
    {
        usuarios_senha_up((int) $registro["usuario_id"], $senha);

        // Quem estava logado em outra conta neste navegador sai, para entrar com a nova senha.
        $_SESSION = [];
        session_regenerate_id(true);
        aviso_definir("Senha alterada. Entre com a nova senha.");
        redirecionar("index.php");
    }
}

exibir(["acesso/redefinir_senha"], [
    "titulo" => "Nova senha",
    "erro" => $erro,
    "token_valido" => (bool) $registro,
    "token" => $token,
    "scripts" => ["js/senha.js"],
]);

?>
