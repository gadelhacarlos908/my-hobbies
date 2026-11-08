<?php

// $filtro: data_de, data_ate, intensidade, texto (métricas ou observações), ordem e dir.
function registros_get($usuario_id, $hobby_id, $filtro)
{
    $parametros = [$hobby_id, $usuario_id];
    $where = "";

    if(data_valida($filtro["data_de"] ?? ""))
        sql_filtro($where, $parametros, "r.data >= {v}::date", $filtro["data_de"]);

    if(data_valida($filtro["data_ate"] ?? ""))
        sql_filtro($where, $parametros, "r.data <= {v}::date", $filtro["data_ate"]);

    if(in_array($filtro["intensidade"] ?? "", ["baixa", "moderada", "alta"], true))
        sql_filtro($where, $parametros, "r.intensidade = {v}", $filtro["intensidade"]);

    if(isset($filtro["texto"]))
        sql_filtro($where, $parametros, "(r.metricas->>'texto' ILIKE {v} OR r.observacoes ILIKE {v})", sql_contem($filtro["texto"]));

    $ordem = match($filtro["ordem"] ?? "")
    {
        "data" => "r.data",
        "duracao" => "r.duracao_minutos",
        "intensidade" => "CASE r.intensidade WHEN 'baixa' THEN 1 WHEN 'moderada' THEN 2 WHEN 'alta' THEN 3 END",
        default => null,
    };

    $query = "SELECT r.id, r.data, r.duracao_minutos, r.intensidade, r.observacoes,
                     r.metricas->>'texto' AS metricas
              FROM registros_atividade r
              JOIN hobbies h ON h.id = r.hobby_id
              WHERE r.hobby_id = $1 AND h.usuario_id = $2$where
              ORDER BY " . sql_ordem($ordem, $filtro, "r.data DESC, r.id DESC");

    return selectDB($query, $parametros);
}

// O INSERT ... SELECT só grava se o hobby for do usuário e da categoria certa.
// As métricas em texto livre ficam na chave "texto" do JSONB, ao lado de campos futuros como "distancia_km".
function registros_cad($usuario_id, $hobby_id, $dados)
{
    $query = "INSERT INTO registros_atividade (hobby_id, data, duracao_minutos, intensidade, observacoes, metricas)
              SELECT id, $1::date, $2::integer, $3, $4, jsonb_strip_nulls(jsonb_build_object('texto', $5::text))
              FROM hobbies
              WHERE id = $6 AND usuario_id = $7 AND categoria = 'atividade_fisica'";

    $parametros = [$dados["data"], $dados["duracao"], $dados["intensidade"], $dados["observacoes"], $dados["metricas"],
        $hobby_id, $usuario_id];

    executarDB($query, $parametros);
}

function registros_up($usuario_id, $registro_id, $dados)
{
    $query = "UPDATE registros_atividade r
              SET data = $1, duracao_minutos = $2, intensidade = $3, observacoes = $4,
                  metricas = CASE WHEN $5::text IS NULL THEN r.metricas - 'texto'
                                  ELSE r.metricas || jsonb_build_object('texto', $5::text) END
              FROM hobbies h
              WHERE r.id = $6 AND h.id = r.hobby_id AND h.usuario_id = $7";

    $parametros = [$dados["data"], $dados["duracao"], $dados["intensidade"], $dados["observacoes"], $dados["metricas"],
        $registro_id, $usuario_id];

    executarDB($query, $parametros);
}

function registros_del($usuario_id, $registro_id)
{
    $query = "DELETE FROM registros_atividade r
              USING hobbies h
              WHERE r.id = $1 AND h.id = r.hobby_id AND h.usuario_id = $2";

    executarDB($query, [$registro_id, $usuario_id]);
}

?>
