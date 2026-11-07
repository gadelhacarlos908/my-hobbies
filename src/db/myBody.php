<?php

function registros_get($usuario_id, $hobby_id)
{
    $query = "SELECT r.id, r.data, r.duracao_minutos, r.intensidade, r.observacoes,
                     r.metricas->>'texto' AS metricas
              FROM registros_atividade r
              JOIN hobbies h ON h.id = r.hobby_id
              WHERE r.hobby_id = $1 AND h.usuario_id = $2
              ORDER BY r.data DESC, r.id DESC";

    return selectDB($query, [$hobby_id, $usuario_id]);
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
