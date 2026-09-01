<?php

require_once __DIR__ . "/../conf/dados_psql.php";

try 
{
    $pdo = new PDO
    (
        "pgsql:host=$host;dbname=$dbname",
        $user,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;

} catch(PDOException $e) 
{
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

?>