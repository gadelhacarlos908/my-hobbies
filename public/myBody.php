<?php

require __DIR__ . "/../src/includes/app.php";

$usuario_id = exigir_login();
require __DIR__ . "/../src/includes/hobbies_fluxo.php";
require __DIR__ . "/../src/db/myBody.php";

const CATEGORIA = "atividade_fisica";
const TABELA = "registros_atividade";
const PAGINA = "myBody.php";
const INTENSIDADES = ["baixa", "moderada", "alta"];

function registros_dados_post()
{
    return [
        "data" => $_POST["data"] ?? "",
        "duracao" => (int) ($_POST["duracao"] ?? 0),
        "intensidade" => texto_ou_null($_POST["intensidade"] ?? ""),
        "observacoes" => texto_ou_null($_POST["observacoes"] ?? ""),
        "metricas" => texto_ou_null($_POST["metricas"] ?? ""),
    ];
}

function registros_validar($dados)
{
    if(!data_valida($dados["data"]))
        return "Informe uma data válida.";

    if($dados["duracao"] <= 0)
        return "A duração precisa ser maior que zero.";

    if($dados["intensidade"] !== null && !in_array($dados["intensidade"], INTENSIDADES, true))
        return "Escolha uma intensidade válida.";

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
    $explicacao = "Um hobby agrupa seus treinos. Crie um hobby, como Musculação ou Corrida, e abra-o para cadastrar os treinos.";
    $dados_tela = hobbies_tela_dados($usuario_id, CATEGORIA, PAGINA, $explicacao);

    exibir(["hobbies/lista"], $dados_tela + [
        "titulo" => "MyBody",
        "erro" => $erro,
        "confirmar" => $resposta["confirmar"],
    ]);
}

$registros = registros_get($usuario_id, $hobby_id);
$editar = null;

foreach($registros as $registro)
    if((int) $registro["id"] === (int) ($_GET["editar"] ?? 0))
        $editar = $registro;

exibir(["myBody/registros"], [
    "titulo" => "MyBody: " . $hobby["nome"],
    "erro" => $erro,
    "registros" => $registros,
    "editar" => $editar,
    "voltar" => $voltar,
    "intensidades" => INTENSIDADES,
]);

?>
