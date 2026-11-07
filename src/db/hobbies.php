<?php

function hobbies_get($usuario_id, $categoria)
{
    $query = "SELECT id, nome, descricao, ativo
              FROM hobbies
              WHERE usuario_id = $1 AND categoria = $2
              ORDER BY ativo DESC, nome";

    return selectDB($query, [$usuario_id, $categoria]);
}

function hobby_get($usuario_id, $categoria, $hobby_id)
{
    $query = "SELECT id, nome, descricao, ativo
              FROM hobbies
              WHERE id = $1 AND usuario_id = $2 AND categoria = $3";

    $hobbies = selectDB($query, [$hobby_id, $usuario_id, $categoria]);

    return $hobbies[0] ?? null;
}

function hobbies_nome_existe($usuario_id, $nome, $ignorar_id)
{
    $query = "SELECT 1 FROM hobbies WHERE usuario_id = $1 AND nome = $2 AND id <> $3";

    return !empty(selectDB($query, [$usuario_id, $nome, $ignorar_id]));
}

function hobbies_cad($usuario_id, $categoria, $nome, $descricao)
{
    $query = "INSERT INTO hobbies (usuario_id, nome, categoria, descricao) VALUES ($1, $2, $3, $4)";

    executarDB($query, [$usuario_id, $nome, $categoria, $descricao]);
}

function hobbies_up($usuario_id, $categoria, $hobby_id, $nome, $descricao, $ativo)
{
    $query = "UPDATE hobbies SET nome = $1, descricao = $2, ativo = $3
              WHERE id = $4 AND usuario_id = $5 AND categoria = $6";

    executarDB($query, [$nome, $descricao, $ativo, $hobby_id, $usuario_id, $categoria]);
}

// $tabela vem sempre de uma constante da página, nunca do usuário.
function hobbies_total_registros($tabela, $hobby_id)
{
    $query = "SELECT (SELECT COUNT(*) FROM $tabela WHERE hobby_id = $1)
                   + (SELECT COUNT(*) FROM metas WHERE hobby_id = $1) AS total";

    $total = selectDB($query, [$hobby_id]);

    return (int) $total[0]["total"];
}

function hobbies_del($usuario_id, $categoria, $tabela, $hobby_id)
{
    executarDB("BEGIN");

    try
    {
        $query = "DELETE FROM $tabela WHERE hobby_id = $1";
        executarDB($query, [$hobby_id]);

        $query = "DELETE FROM metas WHERE hobby_id = $1";
        executarDB($query, [$hobby_id]);

        $query = "DELETE FROM hobbies WHERE id = $1 AND usuario_id = $2 AND categoria = $3";
        executarDB($query, [$hobby_id, $usuario_id, $categoria]);

        executarDB("COMMIT");
    } catch (Exception $e)
    {
        executarDB("ROLLBACK");
        throw $e;
    }
}

?>
