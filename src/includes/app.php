<?php

require __DIR__ . "/../funcoes/funcoes_banco_dados.php";
require __DIR__ . "/../funcoes/uteis.php";
require __DIR__ . "/../funcoes/filtros.php";

// Erros nunca aparecem para o usuário: os detalhes vão para tmp/erroDB (configurado no Dockerfile)
// e a pessoa vê só uma mensagem genérica.
set_exception_handler(function($e)
{
    error_log(get_class($e) . ": " . $e->getMessage() . " em " . $e->getFile() . ":" . $e->getLine()
        . "\n" . $e->getTraceAsString());
    erro_generico();
});

register_shutdown_function(function()
{
    $erro = error_get_last();

    if($erro && in_array($erro["type"], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true))
        erro_generico();
});

// O cookie da sessão não fica visível ao JavaScript e não é enviado por outros sites em POSTs.
session_set_cookie_params([
    "httponly" => true,
    "samesite" => "Lax",
    "secure" => !empty($_SERVER["HTTPS"]),
]);
session_start();

// Todo POST precisa trazer o token CSRF da sessão.
if($_SERVER["REQUEST_METHOD"] === "POST" && !csrf_valido())
{
    http_response_code(400);
    exit("Formulário expirado ou inválido. Volte, recarregue a página e tente de novo.");
}

?>
