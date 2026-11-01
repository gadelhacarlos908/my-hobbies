<?php

require __DIR__ . "/includes/app.php";
require __DIR__ . "/includes/hobbies.php";

const CATEGORIA = "estudo_pratica";
const TABELA = "sessoes_pratica";
const PAGINA = "mySkills.php";

function registros_get($usuario_id, $hobby_id)
{
    $query = "SELECT s.id, s.data, s.duracao_minutos, s.descricao_conteudo, s.observacoes
              FROM sessoes_pratica s
              JOIN hobbies h ON h.id = s.hobby_id
              WHERE s.hobby_id = $1 AND h.usuario_id = $2
              ORDER BY s.data DESC, s.id DESC";

    return selectDB($query, [$hobby_id, $usuario_id]);
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

$titulo = $hobby ? "MySkills: " . $hobby["nome"] : "MySkills";
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

<h2>Sessões de prática</h2>
<table border="1" cellpadding="6">
    <tr><th>Data</th><th>Duração</th><th>O que praticou</th><th>Observações</th><th>Ações</th></tr>
    <?php foreach($registros as $registro): ?>
        <tr>
            <td><?= h(data_br($registro["data"])) ?></td>
            <td><?= (int) $registro["duracao_minutos"] ?> min</td>
            <td><?= h($registro["descricao_conteudo"]) ?></td>
            <td><?= h($registro["observacoes"]) ?></td>
            <td>
                <a href="<?= h($voltar) ?>&editar=<?= (int) $registro["id"] ?>">Editar</a>
                <form method="post" action="<?= h($voltar) ?>" style="display:inline"
                      onsubmit="return confirm('Excluir esta sessão?')">
                    <input type="hidden" name="acao" value="registro_del">
                    <input type="hidden" name="registro_id" value="<?= (int) $registro["id"] ?>">
                    <button type="submit">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<h2><?= $editar ? "Editar sessão" : "Nova sessão" ?></h2>
<form method="post" action="<?= h($voltar) ?>">
    <input type="hidden" name="acao" value="<?= $editar ? "registro_up" : "registro_cad" ?>">
    <input type="hidden" name="registro_id" value="<?= (int) ($editar["id"] ?? 0) ?>">
    <p><label>Data <input type="date" name="data" required value="<?= h($editar["data"] ?? date("Y-m-d")) ?>"></label></p>
    <p><label>Duração em minutos <input type="number" name="duracao" min="1" required value="<?= h($editar["duracao_minutos"] ?? "") ?>"></label></p>
    <p><label>O que praticou <input type="text" name="descricao_conteudo" value="<?= h($editar["descricao_conteudo"] ?? "") ?>"></label></p>
    <p><label>Observações <input type="text" name="observacoes" value="<?= h($editar["observacoes"] ?? "") ?>"></label></p>
    <button type="submit">Salvar</button>
    <?php if($editar): ?>
        <a href="<?= h($voltar) ?>">Cancelar</a>
    <?php endif; ?>
</form>
</body>
</html>
