<?php

require __DIR__ . "/includes/app.php";
require __DIR__ . "/includes/hobbies.php";

const CATEGORIA = "atividade_fisica";
const TABELA = "registros_atividade";
const PAGINA = "myBody.php";

function registros_get($usuario_id, $hobby_id)
{
    $query = "SELECT r.id, r.data, r.duracao_minutos, r.intensidade, r.observacoes, r.metricas
              FROM registros_atividade r
              JOIN hobbies h ON h.id = r.hobby_id
              WHERE r.hobby_id = $1 AND h.usuario_id = $2
              ORDER BY r.data DESC, r.id DESC";

    return selectDB($query, [$hobby_id, $usuario_id]);
}

// O INSERT ... SELECT só grava se o hobby for do usuário e da categoria certa.
function registros_cad($usuario_id, $hobby_id, $dados)
{
    $query = "INSERT INTO registros_atividade (hobby_id, data, duracao_minutos, intensidade, observacoes, metricas)
              SELECT id, $1::date, $2::integer, $3, $4, $5::jsonb
              FROM hobbies
              WHERE id = $6 AND usuario_id = $7 AND categoria = 'atividade_fisica'";

    $parametros = [$dados["data"], $dados["duracao"], $dados["intensidade"], $dados["observacoes"], $dados["metricas"],
        $hobby_id, $usuario_id];

    executarDB($query, $parametros);
}

function registros_up($usuario_id, $registro_id, $dados)
{
    $query = "UPDATE registros_atividade r
              SET data = $1, duracao_minutos = $2, intensidade = $3, observacoes = $4, metricas = $5
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

function registros_dados_post()
{
    $metricas = trim($_POST["metricas"] ?? "");

    return [
        "data" => $_POST["data"] ?? "",
        "duracao" => (int) ($_POST["duracao"] ?? 0),
        "intensidade" => texto_ou_null($_POST["intensidade"] ?? ""),
        "observacoes" => texto_ou_null($_POST["observacoes"] ?? ""),
        "metricas" => $metricas === "" ? "{}" : $metricas,
    ];
}

function registros_validar($dados)
{
    $data = DateTime::createFromFormat("Y-m-d", $dados["data"]);
    if(!$data || $data->format("Y-m-d") !== $dados["data"])
        return "Informe uma data válida.";

    if($dados["duracao"] <= 0)
        return "A duração precisa ser maior que zero.";

    if(!in_array($dados["intensidade"], [null, "baixa", "moderada", "alta"], true))
        return "Escolha uma intensidade válida.";

    if(!is_array(json_decode($dados["metricas"], true)) || $dados["metricas"][0] !== "{")
        return "As métricas precisam ser um JSON como {\"distancia_km\": 5.2}.";

    return null;
}

$hobby_id = (int) ($_GET["hobby_id"] ?? 0);
$hobby = $hobby_id ? hobby_get($usuario_id, CATEGORIA, $hobby_id) : null;

if($hobby_id && !$hobby)
    redirecionar(PAGINA);

$resposta = hobbies_processar($usuario_id, CATEGORIA, TABELA, PAGINA);
$erro = $resposta["erro"];
$confirmar = $resposta["confirmar"];
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

$titulo = $hobby ? "MyBody: " . $hobby["nome"] : "MyBody";
require __DIR__ . "/includes/cabecalho.php";

if(!$hobby)
{
    $pagina = PAGINA;
    $hobbies = hobbies_get($usuario_id, CATEGORIA);
    $editar_hobby = isset($_GET["editar_hobby"])
        ? hobby_get($usuario_id, CATEGORIA, (int) $_GET["editar_hobby"])
        : null;

    require __DIR__ . "/includes/hobbies_lista.php";
    echo "</body></html>";
    exit;
}

$registros = registros_get($usuario_id, $hobby_id);
$editar = null;

foreach($registros as $registro)
    if((int) $registro["id"] === (int) ($_GET["editar"] ?? 0))
        $editar = $registro;

?>
<p><a href="<?= PAGINA ?>">Voltar aos hobbies</a></p>

<h2>Registros</h2>
<table border="1" cellpadding="6">
    <tr><th>Data</th><th>Duração</th><th>Intensidade</th><th>Métricas</th><th>Observações</th><th>Ações</th></tr>
    <?php foreach($registros as $registro): ?>
        <tr>
            <td><?= h(data_br($registro["data"])) ?></td>
            <td><?= (int) $registro["duracao_minutos"] ?> min</td>
            <td><?= h($registro["intensidade"]) ?></td>
            <td><code><?= h($registro["metricas"]) ?></code></td>
            <td><?= h($registro["observacoes"]) ?></td>
            <td>
                <a href="<?= h($voltar) ?>&editar=<?= (int) $registro["id"] ?>">Editar</a>
                <form method="post" action="<?= h($voltar) ?>" style="display:inline"
                      onsubmit="return confirm('Excluir este registro?')">
                    <input type="hidden" name="acao" value="registro_del">
                    <input type="hidden" name="registro_id" value="<?= (int) $registro["id"] ?>">
                    <button type="submit">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<h2><?= $editar ? "Editar registro" : "Novo registro" ?></h2>
<form method="post" action="<?= h($voltar) ?>">
    <input type="hidden" name="acao" value="<?= $editar ? "registro_up" : "registro_cad" ?>">
    <input type="hidden" name="registro_id" value="<?= (int) ($editar["id"] ?? 0) ?>">
    <p><label>Data <input type="date" name="data" required value="<?= h($editar["data"] ?? date("Y-m-d")) ?>"></label></p>
    <p><label>Duração em minutos <input type="number" name="duracao" min="1" required value="<?= h($editar["duracao_minutos"] ?? "") ?>"></label></p>
    <p>
        <label>Intensidade
            <select name="intensidade">
                <option value="">Não informar</option>
                <?php foreach(["baixa", "moderada", "alta"] as $opcao): ?>
                    <option value="<?= $opcao ?>" <?= ($editar["intensidade"] ?? "") === $opcao ? "selected" : "" ?>><?= $opcao ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </p>
    <p><label>Métricas em JSON <input type="text" name="metricas" size="50" value="<?= h($editar["metricas"] ?? "") ?>"></label></p>
    <p><label>Observações <input type="text" name="observacoes" value="<?= h($editar["observacoes"] ?? "") ?>"></label></p>
    <button type="submit">Salvar</button>
    <?php if($editar): ?>
        <a href="<?= h($voltar) ?>">Cancelar</a>
    <?php endif; ?>
</form>
</body>
</html>
