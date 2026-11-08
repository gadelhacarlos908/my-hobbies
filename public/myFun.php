<?php

require __DIR__ . "/../src/includes/app.php";

$usuario_id = exigir_login();
require __DIR__ . "/../src/db/myFun.php";

const PAGINA = "myFun.php";

// Categorias fixas do MyFun. Cada uma é um hobby criado no primeiro cadastro.
const SECOES = [
    "games" => ["nome" => "Games", "tipos" => ["jogo" => "Jogo"]],
    "filmes" => ["nome" => "Filmes", "tipos" => ["filme" => "Filme"]],
    "livros" => ["nome" => "Livros", "tipos" => ["livro" => "Livro"]],
    "mangas_hqs" => ["nome" => "Mangás/HQs", "tipos" => ["manga_hq" => "Mangá/HQ"]],
    "series" => ["nome" => "Séries", "tipos" => ["serie" => "Série", "anime" => "Anime"]],
    "musicas" => ["nome" => "Músicas", "tipos" => ["musica" => "Música"]],
];

const STATUS = [
    "quero" => "Quero",
    "em_andamento" => "Em andamento",
    "concluido" => "Concluído",
    "abandonado" => "Abandonado",
];

function registros_dados_post($secao)
{
    $tipos = array_keys($secao["tipos"]);

    return [
        "titulo" => trim($_POST["titulo"] ?? ""),
        "tipo" => count($tipos) === 1 ? $tipos[0] : ($_POST["tipo"] ?? ""),
        "status" => $_POST["status"] ?? "",
        "data_inicio" => texto_ou_null($_POST["data_inicio"] ?? ""),
    ];
}

// A situação só é escolhida no cadastro. Depois, muda pelo Atualizar.
function registros_validar($secao, $dados, $acao)
{
    if($dados["titulo"] === "")
        return "Informe o título.";

    if(!isset($secao["tipos"][$dados["tipo"]]))
        return "Escolha um tipo válido.";

    if($acao === "registro_cad" && !isset(STATUS[$dados["status"]]))
        return "Escolha uma situação válida.";

    if($dados["data_inicio"] !== null && !data_valida($dados["data_inicio"]))
        return "Informe uma data de início válida.";

    return null;
}

function avaliacoes_dados_post()
{
    $nota = texto_ou_null($_POST["nota"] ?? "");

    return [
        "nota" => $nota === null ? null : str_replace(",", ".", $nota),
        "review" => texto_ou_null($_POST["review"] ?? ""),
        "data_conclusao" => texto_ou_null($_POST["data_conclusao"] ?? ""),
    ];
}

function avaliacoes_vazia($dados)
{
    return $dados["nota"] === null && $dados["review"] === null && $dados["data_conclusao"] === null;
}

function avaliacoes_validar($dados)
{
    if(avaliacoes_vazia($dados))
        return "Informe a nota, o review ou a data de conclusão.";

    if($dados["nota"] !== null)
    {
        $nota = is_numeric($dados["nota"]) ? (float) $dados["nota"] : -1;

        if($nota < 0 || $nota > 10 || fmod($nota * 2, 1) !== 0.0)
            return "A nota vai de 0 a 10, de meio em meio ponto.";
    }

    if($dados["data_conclusao"] !== null && !data_valida($dados["data_conclusao"]))
        return "Informe uma data de conclusão válida.";

    return null;
}

$chave = $_GET["secao"] ?? "";
$secao = SECOES[$chave] ?? null;

if(!$secao)
{
    exibir(["myFun/secoes"], [
        "titulo" => "MyFun",
        "secoes" => SECOES,
        "totais" => secoes_totais($usuario_id),
    ]);
}

$hobby_id = secao_hobby_id($usuario_id, $secao["nome"], false);
$acao = $_POST["acao"] ?? "";
$voltar = PAGINA . "?secao=$chave";
$erro = null;
$confirmar_item = null;

if($hobby_id && $acao === "registro_del")
{
    $registro_id = (int) ($_POST["registro_id"] ?? 0);
    $total = registros_total_dependentes($usuario_id, $registro_id);

    if($total > 0 && !isset($_POST["confirmado"]))
        $confirmar_item = ["id" => $registro_id, "total" => $total];
    else
    {
        registros_del($usuario_id, $hobby_id, $registro_id);
        redirecionar($voltar);
    }
}

if($acao === "registro_cad" || ($hobby_id && $acao === "registro_up"))
{
    $dados = registros_dados_post($secao);
    $avaliacao = avaliacoes_dados_post();
    $erro = registros_validar($secao, $dados, $acao);

    if(!$erro && $acao === "registro_cad" && !avaliacoes_vazia($avaliacao))
        $erro = avaliacoes_validar($avaliacao);

    if(!$erro && $acao === "registro_cad")
    {
        $hobby_id = secao_hobby_id($usuario_id, $secao["nome"], true);

        if(!$hobby_id)
            $erro = "Já existe um hobby chamado " . $secao["nome"] . " em outra categoria.";
    }

    if(!$erro)
    {
        if($acao === "registro_cad")
            registros_cad($usuario_id, $hobby_id, $dados, avaliacoes_vazia($avaliacao) ? null : $avaliacao);
        else
            registros_up($usuario_id, $hobby_id, (int) ($_POST["registro_id"] ?? 0), $dados);

        redirecionar($voltar);
    }
}

// Atualizar: nova situação e, opcionalmente, uma linha no histórico (nota, review, conclusão).
if($hobby_id && $acao === "registro_atualizar")
{
    $item_id = (int) ($_POST["item_id"] ?? 0);
    $status_atual = registros_status_get($usuario_id, $hobby_id, $item_id);
    $status = $_POST["status"] ?? "";
    $avaliacao = avaliacoes_dados_post();

    if($status_atual === null)
        redirecionar($voltar);

    if(!isset(STATUS[$status]))
        $erro = "Escolha uma situação válida.";
    elseif(avaliacoes_vazia($avaliacao) && $status === $status_atual)
        $erro = "Mude a situação ou informe a nota, o review ou a data de conclusão.";
    elseif(!avaliacoes_vazia($avaliacao))
        $erro = avaliacoes_validar($avaliacao);

    if(!$erro)
    {
        registros_atualizar($usuario_id, $hobby_id, $item_id, $status, avaliacoes_vazia($avaliacao) ? null : $avaliacao);
        redirecionar("$voltar&ver=$item_id");
    }
}

$filtro = filtros_processar("my_fun", $chave, $voltar);
$registros = $hobby_id ? registros_get($usuario_id, $hobby_id, $filtro) : [];
$editar = null;
$ver = null;
$ver_id = (int) ($_GET["ver"] ?? $_POST["item_id"] ?? 0);

foreach($registros as $registro)
{
    if((int) $registro["id"] === (int) ($_GET["editar"] ?? 0))
        $editar = $registro;

    if((int) $registro["id"] === $ver_id)
        $ver = $registro;
}

exibir(["myFun/itens"], [
    "titulo" => "MyFun: " . $secao["nome"],
    "erro" => $erro,
    "secao" => $secao,
    "status" => STATUS,
    "registros" => $registros,
    "editar" => $editar,
    "form_aberto" => $editar || ($erro && in_array($acao, ["registro_cad", "registro_up"], true)),
    "ver" => $ver,
    "historico" => $ver ? avaliacoes_get($usuario_id, (int) $ver["id"]) : [],
    "voltar" => $voltar,
    "confirmar_item" => $confirmar_item,
    "lista_url" => $voltar,
    "menu" => "my_fun",
    "filtro" => $filtro,
]);

?>
