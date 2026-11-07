<?php

// Os valores vêm do .env, carregado pelo docker-compose. Nenhuma senha fica no código.
return [
    "host" => "postgres",
    "port" => "5432",
    "dbname" => getenv("POSTGRES_DB"),
    "user" => getenv("POSTGRES_USER"),
    "password" => getenv("POSTGRES_PASSWORD"),
];

?>
