<?php

function secoes_totais($usuario_id)
{
    $query = "SELECT h.nome, COUNT(i.id) AS total
              FROM hobbies h
              LEFT JOIN itens_consumo i ON i.hobby_id = h.id
              WHERE h.usuario_id = $1 AND h.categoria = 'consumo'
              GROUP BY h.nome";

    return array_column(selectDB($query, [$usuario_id]), "total", "nome");
}

// Devolve o hobby da categoria. Com $criar, cria se ainda não existir.
function secao_hobby_id($usuario_id, $nome, $criar)
{
    $query = "SELECT id FROM hobbies WHERE usuario_id = $1 AND nome = $2 AND categoria = 'consumo'";
    $hobby = selectDB($query, [$usuario_id, $nome]);

    if(!$hobby && $criar)
    {
        $query = "INSERT INTO hobbies (usuario_id, nome, categoria) VALUES ($1, $2, 'consumo')
                  ON CONFLICT (usuario_id, nome) DO NOTHING
                  RETURNING id";
        $hobby = selectDB($query, [$usuario_id, $nome]);
    }

    return $hobby ? (int) $hobby[0]["id"] : null;
}

// $filtro: titulo (contém), situacao, tipo, nota_min, ordem e dir.
// O LATERAL busca a última nota de cada item uma vez, e "n.nota" pode ser usada no filtro e na ordem.
function registros_get($usuario_id, $hobby_id, $filtro)
{
    $parametros = [$hobby_id, $usuario_id];
    $where = "";

    if(isset($filtro["titulo"]))
        sql_filtro($where, $parametros, "i.titulo ILIKE {v}", sql_contem($filtro["titulo"]));

    if(isset($filtro["situacao"]))
        sql_filtro($where, $parametros, "i.status = {v}", $filtro["situacao"]);

    if(isset($filtro["tipo"]))
        sql_filtro($where, $parametros, "i.tipo = {v}", $filtro["tipo"]);

    if(is_numeric($filtro["nota_min"] ?? ""))
        sql_filtro($where, $parametros, "n.nota >= {v}::numeric", $filtro["nota_min"]);

    $ordem = match($filtro["ordem"] ?? "")
    {
        "titulo" => "i.titulo",
        "tipo" => "i.tipo",
        "situacao" => "CASE i.status WHEN 'quero' THEN 1 WHEN 'em_andamento' THEN 2 WHEN 'concluido' THEN 3 WHEN 'abandonado' THEN 4 END",
        "inicio" => "i.data_inicio",
        "nota" => "n.nota",
        default => null,
    };

    $query = "SELECT i.id, i.titulo, i.tipo, i.status, i.data_inicio, n.nota
              FROM itens_consumo i
              JOIN hobbies h ON h.id = i.hobby_id
              LEFT JOIN LATERAL (
                  SELECT a.nota
                  FROM avaliacoes_item a
                  WHERE a.item_id = i.id AND a.nota IS NOT NULL
                  ORDER BY a.criado_em DESC, a.id DESC
                  LIMIT 1
              ) n ON true
              WHERE i.hobby_id = $1 AND h.usuario_id = $2$where
              ORDER BY " . sql_ordem($ordem, $filtro, "i.titulo, i.id");

    return selectDB($query, $parametros);
}

// O INSERT ... SELECT só grava se o hobby for do usuário e da categoria certa.
// Item e primeira avaliação ($avaliacao, ou null) entram juntos, para o histórico já nascer completo.
function registros_cad($usuario_id, $hobby_id, $dados, $avaliacao)
{
    $query = "INSERT INTO itens_consumo (hobby_id, titulo, tipo, status, data_inicio)
              SELECT id, $1, $2, $3, $4::date
              FROM hobbies
              WHERE id = $5 AND usuario_id = $6 AND categoria = 'consumo'
              RETURNING id";

    $parametros = [$dados["titulo"], $dados["tipo"], $dados["status"], $dados["data_inicio"],
        $hobby_id, $usuario_id];

    executarDB("BEGIN");

    try
    {
        $item = selectDB($query, $parametros);

        if($item && $avaliacao)
            avaliacoes_cad($usuario_id, (int) $item[0]["id"], $avaliacao);

        executarDB("COMMIT");
    } catch (Exception $e)
    {
        executarDB("ROLLBACK");
        throw $e;
    }
}

function registros_status_get($usuario_id, $hobby_id, $registro_id)
{
    $query = "SELECT i.status
              FROM itens_consumo i
              JOIN hobbies h ON h.id = i.hobby_id
              WHERE i.id = $1 AND i.hobby_id = $2 AND h.usuario_id = $3";

    $item = selectDB($query, [$registro_id, $hobby_id, $usuario_id]);

    return $item[0]["status"] ?? null;
}

// Editar corrige os dados do item. A situação muda só pelo Atualizar (registros_atualizar).
function registros_up($usuario_id, $hobby_id, $registro_id, $dados)
{
    $query = "UPDATE itens_consumo i
              SET titulo = $1, tipo = $2, data_inicio = $3
              FROM hobbies h
              WHERE i.id = $4 AND i.hobby_id = $5 AND h.id = i.hobby_id AND h.usuario_id = $6";

    $parametros = [$dados["titulo"], $dados["tipo"], $dados["data_inicio"], $registro_id, $hobby_id, $usuario_id];

    executarDB($query, $parametros);
}

// Atualizar: muda a situação e, se houver nota, review ou conclusão ($avaliacao), soma uma linha
// ao histórico. As duas gravações entram juntas.
function registros_atualizar($usuario_id, $hobby_id, $registro_id, $status, $avaliacao)
{
    $query = "UPDATE itens_consumo i
              SET status = $1
              FROM hobbies h
              WHERE i.id = $2 AND i.hobby_id = $3 AND h.id = i.hobby_id AND h.usuario_id = $4";

    executarDB("BEGIN");

    try
    {
        executarDB($query, [$status, $registro_id, $hobby_id, $usuario_id]);

        if($avaliacao)
            avaliacoes_cad($usuario_id, $registro_id, $avaliacao);

        executarDB("COMMIT");
    } catch (Exception $e)
    {
        executarDB("ROLLBACK");
        throw $e;
    }
}

// O CASCADE do banco apaga junto as avaliações e as sessões do item.
function registros_del($usuario_id, $hobby_id, $registro_id)
{
    $query = "DELETE FROM itens_consumo i
              USING hobbies h
              WHERE i.id = $1 AND i.hobby_id = $2 AND h.id = i.hobby_id AND h.usuario_id = $3";

    executarDB($query, [$registro_id, $hobby_id, $usuario_id]);
}

function registros_total_dependentes($usuario_id, $registro_id)
{
    $query = "SELECT (SELECT COUNT(*) FROM avaliacoes_item a WHERE a.item_id = i.id)
                   + (SELECT COUNT(*) FROM sessoes_consumo s WHERE s.item_id = i.id) AS total
              FROM itens_consumo i
              JOIN hobbies h ON h.id = i.hobby_id
              WHERE i.id = $1 AND h.usuario_id = $2";

    $total = selectDB($query, [$registro_id, $usuario_id]);

    return (int) ($total[0]["total"] ?? 0);
}

function avaliacoes_get($usuario_id, $item_id)
{
    $query = "SELECT a.nota, a.review, a.data_conclusao, a.criado_em
              FROM avaliacoes_item a
              JOIN itens_consumo i ON i.id = a.item_id
              JOIN hobbies h ON h.id = i.hobby_id
              WHERE a.item_id = $1 AND h.usuario_id = $2
              ORDER BY a.criado_em DESC, a.id DESC";

    return selectDB($query, [$item_id, $usuario_id]);
}

// A opção Atualizar sempre adiciona uma linha ao histórico.
function avaliacoes_cad($usuario_id, $item_id, $dados)
{
    $query = "INSERT INTO avaliacoes_item (item_id, nota, review, data_conclusao)
              SELECT i.id, $1::numeric, $2, $3::date
              FROM itens_consumo i
              JOIN hobbies h ON h.id = i.hobby_id
              WHERE i.id = $4 AND h.usuario_id = $5";

    executarDB($query, [$dados["nota"], $dados["review"], $dados["data_conclusao"], $item_id, $usuario_id]);
}

?>
