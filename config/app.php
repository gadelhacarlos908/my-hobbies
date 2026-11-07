<?php

// O endereço dos links vem do .env, nunca do cabeçalho Host da requisição,
// que pode ser falsificado para enviar e-mails com links de outro site.
return [
    "url" => rtrim(getenv("APP_URL") ?: "http://localhost:8080", "/"),
    "email_remetente" => getenv("SMTP_FROM") ?: "nao-responda@myhobbies.local",
];

?>
