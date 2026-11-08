<?php

// O mail() entrega ao msmtp, que envia ao servidor definido no .env (Mailpit no ambiente local).
function email_enviar($para, $assunto, $texto)
{
    $cabecalhos = [
        "From" => config_app()["email_remetente"],
        "MIME-Version" => "1.0",
        "Content-Type" => "text/plain; charset=UTF-8",
    ];

    $enviado = mail($para, mb_encode_mimeheader($assunto, "UTF-8"), $texto, $cabecalhos);

    if(!$enviado)
        error_log("Falha ao enviar e-mail para $para");

    return $enviado;
}

?>
