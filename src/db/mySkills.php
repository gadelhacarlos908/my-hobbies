<?php

// $filtro: data_de, data_ate, texto (o que praticou ou observações), ordem e dir.
function registros_get($usuario_id, $hobby_id, $filtro)
{
    $parametros = [$hobby_id, $usuario_id];
    $where = "";

    if(data_valida($filtro["data_de"] ?? ""))
        sql_filtro($where, $parametros, "s.data >= {v}::date", $filtro["data_de"]);

    if(data_valida($filtro["data_ate"] ?? ""))
        sql_filtro($where, $parametros, "s.data <= {v}::date", $filtro["data_ate"]);

    if(isset($filtro["texto"]))
        sql_filtro($where, $parametros, "(s.descricao_conteudo ILIKE {v} OR s.observacoes ILIKE {v})", sql_contem($filtro["texto"]));

    $ordem = match($filtro["ordem"] ?? "")
    {
        "data" => "s.data",
        "duracao" => "s.duracao_minutos",
        default => null,
    };

    $query = "SELECT s.id, s.data, s.duracao_minutos, s.descricao_conteudo, s.observacoes
              FROM sessoes_pratica s
              JOIN hobbies h ON h.id = s.hobby_id
              WHERE s.hobby_id = $1 AND h.usuario_id = $2$where
              ORDER BY " . sql_ordem($ordem, $filtro, "s.data DESC, s.id DESC");

    return selectDB($query, $parametros);
}

// O INSERT ... SELECT só grava se o hobby for do usuário e da categoria certa.
function registros_cad($usuario_id, $hobby_id, $dados)
{
    $query = "INSERT INTO sessoes_pratica (hobby_id, data, duracao_minutos, descricao_conteudo, observacoes)
              SELECT id, $1::date, $2::integer, $3, $4
              FROM hobbies
              WHERE id = $5 AND usuario_id = $6 AND categoria = 'estudo_pratica'";

    $parametros = [$dados["data"], $dados["duracao"], $dados["descricao_conteudo"], $dados["observacoes"],
        $hobby_id, $usuario_id];

    executarDB($query, $parametros);
}

function registros_up($usuario_id, $registro_id, $dados)
{
    $query = "UPDATE sessoes_pratica s
              SET data = $1, duracao_minutos = $2, descricao_conteudo = $3, observacoes = $4
              FROM hobbies h
              WHERE s.id = $5 AND h.id = s.hobby_id AND h.usuario_id = $6";

    $parametros = [$dados["data"], $dados["duracao"], $dados["descricao_conteudo"], $dados["observacoes"],
        $registro_id, $usuario_id];

    executarDB($query, $parametros);
}

function registros_del($usuario_id, $registro_id)
{
    $query = "DELETE FROM sessoes_pratica s
              USING hobbies h
              WHERE s.id = $1 AND h.id = s.hobby_id AND h.usuario_id = $2";

    executarDB($query, [$registro_id, $usuario_id]);
}

?>
