<?php

require __DIR__ . "/../src/includes/app.php";

$usuario_id = exigir_login();
require __DIR__ . "/../src/includes/hobbies_fluxo.php";
require __DIR__ . "/../src/db/mySkills.php";

const CATEGORIA = "estudo_pratica";
const TABELA = "sessoes_pratica";
const PAGINA = "mySkills.php";

function registros_dados_post()
{
    return [
        "data" => $_POST["data"] ?? "",
        "duracao" => (int) ($_POST["duracao"] ?? 0),
        "descricao_conteudo" => texto_ou_null($_POST["descricao_conteudo"] ?? ""),
        "observacoes" => texto_ou_null($_POST["observacoes"] ?? ""),
    ];
}

function registros_validar($dados)
{
    if(!data_valida($dados["data"]))
        return "Informe uma data válida.";

    if($dados["duracao"] <= 0)
        return "A duração precisa ser maior que zero.";

    return null;
}

$hobby_id = (int) ($_GET["hobby_id"] ?? 0);
$hobby = $hobby_id ? hobby_get($usuario_id, CATEGORIA, $hobby_id) : null;

if($hobby_id && !$hobby)
    redirecionar(PAGINA);

$resposta = hobbies_processar($usuario_id, CATEGORIA, TABELA, PAGINA);
$erro = $resposta["erro"];
$acao = $_POST["acao"] ?? "";
$voltar = PAGINA . "?hobby_id=$hobby_id";

if($hobby && $acao === "registro_del")
{
    registros_del($usuario_id, (int) ($_POST["registro_id"] ?? 0));
    redirecionar($voltar);
}

if($hobby && ($acao === "registro_cad" || $acao === "registro_up"))
{
    $dados = registros_dados_post();
    $erro = registros_validar($dados);

    if(!$erro)
    {
        if($acao === "registro_cad")
            registros_cad($usuario_id, $hobby_id, $dados);
        else
            registros_up($usuario_id, (int) ($_POST["registro_id"] ?? 0), $dados);

        redirecionar($voltar);
    }
}

if(!$hobby)
{
    $explicacao = "Um hobby agrupa suas sessões. Crie um hobby, como Violão ou Inglês, e abra-o para cadastrar as sessões.";
    $dados_tela = hobbies_tela_dados($usuario_id, CATEGORIA, PAGINA, $explicacao);

    exibir(["hobbies/lista"], $dados_tela + [
        "titulo" => "MySkills",
        "erro" => $erro,
        "confirmar" => $resposta["confirmar"],
    ]);
}

$registros = registros_get($usuario_id, $hobby_id);
$editar = null;

foreach($registros as $registro)
    if((int) $registro["id"] === (int) ($_GET["editar"] ?? 0))
        $editar = $registro;

exibir(["mySkills/registros"], [
    "titulo" => "MySkills: " . $hobby["nome"],
    "erro" => $erro,
    "registros" => $registros,
    "editar" => $editar,
    "voltar" => $voltar,
]);

?>
