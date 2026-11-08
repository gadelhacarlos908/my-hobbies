<?php

// Filtros e ordenação das listas.
// O formulário envia $_POST["pagina"] (o menu, como "my_fun") e $_POST["filtro"] (campos e ordem).
// O filtro fica na sessão, em uma chave por lista, e a página redireciona para o próprio endereço
// (POST, sessão, redirecionamento). Quem monta o SQL é a função de listagem em src/db/: ela só
// reconhece os campos que conhece e usa parâmetros ($3, $4...), então nada do POST vira SQL.

// Trata o envio do formulário de filtro e devolve o filtro atual da lista.
function filtros_processar($pagina, $lista, $url)
{
    $chave = "$pagina:$lista";
    $atual = $_SESSION["filtros"][$chave] ?? [];

    if(($_POST["pagina"] ?? "") === $pagina)
    {
        if(($_POST["acao"] ?? "") === "filtro_limpar")
        {
            // Limpar apaga os filtros e mantém a ordem escolhida.
            $_SESSION["filtros"][$chave] = array_intersect_key($atual, ["ordem" => 1, "dir" => 1]);
            redirecionar($url);
        }

        $novo = filtros_limpar_entrada($_POST["filtro"] ?? []);

        // Sem "ordem" no envio, mantém a atual. Clicar de novo na mesma coluna inverte a direção.
        if(!isset($novo["ordem"]))
            $novo = array_merge($novo, array_intersect_key($atual, ["ordem" => 1, "dir" => 1]));
        elseif(!isset($novo["dir"]))
            $novo["dir"] = ($atual["ordem"] ?? "") === $novo["ordem"] && ($atual["dir"] ?? "") === "asc" ? "desc" : "asc";

        $_SESSION["filtros"][$chave] = $novo;
        redirecionar($url);
    }

    return $atual;
}

// Guarda só textos curtos e não vazios. Listas e outros formatos são descartados.
function filtros_limpar_entrada($entrada)
{
    $limpo = [];

    if(!is_array($entrada))
        return $limpo;

    foreach($entrada as $campo => $valor)
    {
        $valor = is_string($valor) ? trim($valor) : "";

        if(is_string($campo) && $valor !== "" && mb_strlen($valor) <= 100)
            $limpo[$campo] = $valor;
    }

    return $limpo;
}

// Acrescenta " AND trecho" ao WHERE e o valor aos parâmetros. {v} vira o número do parâmetro.
function sql_filtro(&$where, &$parametros, $trecho, $valor)
{
    $parametros[] = $valor;
    $where .= " AND " . str_replace("{v}", "$" . count($parametros), $trecho);
}

// Para "contém": % e _ digitados valem como texto comum, não como curinga do ILIKE.
function sql_contem($texto)
{
    return "%" . addcslashes($texto, "%_\\") . "%";
}

// $expressao vem de um match na função de listagem; null usa só a ordem padrão, que também desempata.
function sql_ordem($expressao, $filtro, $padrao)
{
    if(!$expressao)
        return $padrao;

    return $expressao . (($filtro["dir"] ?? "") === "desc" ? " DESC" : " ASC") . " NULLS LAST, " . $padrao;
}

// Texto do botão Filtrar: mostra quantos filtros estão ativos, sem contar a ordenação.
function filtros_rotulo($filtro)
{
    $ativos = count(array_diff_key($filtro, ["ordem" => 1, "dir" => 1]));

    return $ativos ? "Filtrar ($ativos)" : "Filtrar";
}

function ordem_seta($campo, $filtro)
{
    if(($filtro["ordem"] ?? "") !== $campo)
        return "";

    return ($filtro["dir"] ?? "") === "desc" ? " ▼" : " ▲";
}

?>
