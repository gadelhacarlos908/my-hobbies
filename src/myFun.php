<?php

require __DIR__ . "/includes/app.php";
require __DIR__ . "/includes/hobbies.php";

const CATEGORIA = "consumo";
const TABELA = "itens_consumo";
const PAGINA = "myFun.php";
const TIPOS = ["livro", "filme", "jogo", "serie", "anime"];
const STATUS = ["quero", "em_andamento", "concluido", "abandonado"];

function registros_get($usuario_id, $hobby_id)
{
    $query = "SELECT i.id, i.titulo, i.tipo, i.status, i.data_inicio,
                     (SELECT a.nota
                      FROM avaliacoes_item a
                      WHERE a.item_id = i.id AND a.nota IS NOT NULL
                      ORDER BY a.criado_em DESC, a.id DESC
                      LIMIT 1) AS nota
              FROM itens_consumo i
              JOIN hobbies h ON h.id = i.hobby_id
              WHERE i.hobby_id = $1 AND h.usuario_id = $2
              ORDER BY i.titulo, i.id";

    return selectDB($query, [$hobby_id, $usuario_id]);
}

// O INSERT ... SELECT só grava se o hobby for do usuário e da categoria certa.
// Item e primeira avaliação entram juntos, para o histórico já nascer completo.
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

        if($item && !avaliacoes_vazia($avaliacao))
            avaliacoes_cad($usuario_id, (int) $item[0]["id"], $avaliacao);

        executarDB("COMMIT");
    } catch (Exception $e)
    {
        executarDB("ROLLBACK");
        throw $e;
    }
}

function registros_up($usuario_id, $registro_id, $dados)
{
    $query = "UPDATE itens_consumo i
              SET titulo = $1, tipo = $2, status = $3, data_inicio = $4
              FROM hobbies h
              WHERE i.id = $5 AND h.id = i.hobby_id AND h.usuario_id = $6";

    $parametros = [$dados["titulo"], $dados["tipo"], $dados["status"], $dados["data_inicio"],
        $registro_id, $usuario_id];

    executarDB($query, $parametros);
}

// O CASCADE do banco apaga junto as avaliações e as sessões do item.
function registros_del($usuario_id, $registro_id)
{
    $query = "DELETE FROM itens_consumo i
              USING hobbies h
              WHERE i.id = $1 AND h.id = i.hobby_id AND h.usuario_id = $2";

    executarDB($query, [$registro_id, $usuario_id]);
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

function registros_dados_post()
{
    return [
        "titulo" => trim($_POST["titulo"] ?? ""),
        "tipo" => $_POST["tipo"] ?? "",
        "status" => $_POST["status"] ?? "",
        "data_inicio" => texto_ou_null($_POST["data_inicio"] ?? ""),
    ];
}

function registros_validar($dados)
{
    if($dados["titulo"] === "")
        return "Informe o título.";

    if(!in_array($dados["tipo"], TIPOS, true))
        return "Escolha um tipo válido.";

    if(!in_array($dados["status"], STATUS, true))
        return "Escolha uma situação válida.";

    if($dados["data_inicio"] !== null && !data_valida($dados["data_inicio"]))
        return "Informe uma data de início válida.";

    return null;
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

$hobby_id = (int) ($_GET["hobby_id"] ?? 0);
$hobby = $hobby_id ? hobby_get($usuario_id, CATEGORIA, $hobby_id) : null;

if($hobby_id && !$hobby)
    redirecionar(PAGINA);

$resposta = hobbies_processar($usuario_id, CATEGORIA, TABELA, PAGINA);
$erro = $resposta["erro"];
$confirmar = $resposta["confirmar"];
$acao = $_POST["acao"] ?? "";
$voltar = PAGINA . "?hobby_id=$hobby_id";
$confirmar_item = null;

if($hobby && $acao === "registro_del")
{
    $registro_id = (int) ($_POST["registro_id"] ?? 0);
    $total = registros_total_dependentes($usuario_id, $registro_id);

    if($total > 0 && !isset($_POST["confirmado"]))
        $confirmar_item = ["id" => $registro_id, "total" => $total];
    else
    {
        registros_del($usuario_id, $registro_id);
        redirecionar($voltar);
    }
}

if($hobby && ($acao === "registro_cad" || $acao === "registro_up"))
{
    $dados = registros_dados_post();
    $avaliacao = avaliacoes_dados_post();
    $erro = registros_validar($dados);

    if(!$erro && $acao === "registro_cad" && !avaliacoes_vazia($avaliacao))
        $erro = avaliacoes_validar($avaliacao);

    if(!$erro)
    {
        if($acao === "registro_cad")
            registros_cad($usuario_id, $hobby_id, $dados, $avaliacao);
        else
            registros_up($usuario_id, (int) ($_POST["registro_id"] ?? 0), $dados);

        redirecionar($voltar);
    }
}

if($hobby && $acao === "avaliacao_cad")
{
    $item_id = (int) ($_POST["item_id"] ?? 0);
    $dados = avaliacoes_dados_post();
    $erro = avaliacoes_validar($dados);

    if(!$erro)
    {
        avaliacoes_cad($usuario_id, $item_id, $dados);
        redirecionar("$voltar&ver=$item_id");
    }
}

$titulo = $hobby ? "MyFun: " . $hobby["nome"] : "MyFun";
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
$ver = null;
$ver_id = (int) ($_GET["ver"] ?? $_POST["item_id"] ?? 0);

foreach($registros as $registro)
{
    if((int) $registro["id"] === (int) ($_GET["editar"] ?? 0))
        $editar = $registro;

    if((int) $registro["id"] === $ver_id)
        $ver = $registro;
}

?>
<p><a href="<?= PAGINA ?>">Voltar aos hobbies</a></p>

<?php if($confirmar_item): ?>
    <h2>Excluir este item?</h2>
    <p>Ele tem <?= (int) $confirmar_item["total"] ?> avaliação(ões) ou sessão(ões), que serão apagadas junto.</p>
    <form method="post" action="<?= h($voltar) ?>">
        <input type="hidden" name="acao" value="registro_del">
        <input type="hidden" name="registro_id" value="<?= (int) $confirmar_item["id"] ?>">
        <input type="hidden" name="confirmado" value="1">
        <button type="submit">Sim, excluir tudo</button>
        <a href="<?= h($voltar) ?>">Cancelar</a>
    </form>
<?php endif; ?>

<h2>Itens</h2>
<table border="1" cellpadding="6">
    <tr><th>Título</th><th>Tipo</th><th>Situação</th><th>Início</th><th>Nota</th><th>Ações</th></tr>
    <?php foreach($registros as $registro): ?>
        <tr>
            <td><?= h($registro["titulo"]) ?></td>
            <td><?= h($registro["tipo"]) ?></td>
            <td><?= h($registro["status"]) ?></td>
            <td><?= h(data_br($registro["data_inicio"])) ?></td>
            <td><?= h($registro["nota"]) ?></td>
            <td>
                <a href="<?= h($voltar) ?>&ver=<?= (int) $registro["id"] ?>">Atualizar</a>
                <a href="<?= h($voltar) ?>&editar=<?= (int) $registro["id"] ?>">Editar</a>
                <form method="post" action="<?= h($voltar) ?>" style="display:inline">
                    <input type="hidden" name="acao" value="registro_del">
                    <input type="hidden" name="registro_id" value="<?= (int) $registro["id"] ?>">
                    <button type="submit">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php if($ver): ?>
    <h2>Atualizar: <?= h($ver["titulo"]) ?></h2>
    <form method="post" action="<?= h($voltar) ?>">
        <input type="hidden" name="acao" value="avaliacao_cad">
        <input type="hidden" name="item_id" value="<?= (int) $ver["id"] ?>">
        <p><label>Nota <input type="number" name="nota" min="0" max="10" step="0.5"></label></p>
        <p><label>Review <input type="text" name="review" size="50"></label></p>
        <p><label>Data de conclusão <input type="date" name="data_conclusao"></label></p>
        <button type="submit">Salvar no histórico</button>
        <a href="<?= h($voltar) ?>">Cancelar</a>
    </form>

    <h3>Histórico</h3>
    <table border="1" cellpadding="6">
        <tr><th>Cadastrado em</th><th>Nota</th><th>Review</th><th>Conclusão</th></tr>
        <?php foreach(avaliacoes_get($usuario_id, (int) $ver["id"]) as $avaliacao): ?>
            <tr>
                <td><?= h(data_br($avaliacao["criado_em"])) ?></td>
                <td><?= h($avaliacao["nota"]) ?></td>
                <td><?= h($avaliacao["review"]) ?></td>
                <td><?= h(data_br($avaliacao["data_conclusao"])) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <h2><?= $editar ? "Editar item" : "Novo item" ?></h2>
    <form method="post" action="<?= h($voltar) ?>">
        <input type="hidden" name="acao" value="<?= $editar ? "registro_up" : "registro_cad" ?>">
        <input type="hidden" name="registro_id" value="<?= (int) ($editar["id"] ?? 0) ?>">
        <p><label>Título <input type="text" name="titulo" required value="<?= h($editar["titulo"] ?? "") ?>"></label></p>
        <p>
            <label>Tipo
                <select name="tipo">
                    <?php foreach(TIPOS as $opcao): ?>
                        <option value="<?= $opcao ?>" <?= ($editar["tipo"] ?? "") === $opcao ? "selected" : "" ?>><?= $opcao ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </p>
        <p>
            <label>Situação
                <select name="status">
                    <?php foreach(STATUS as $opcao): ?>
                        <option value="<?= $opcao ?>" <?= ($editar["status"] ?? "") === $opcao ? "selected" : "" ?>><?= $opcao ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </p>
        <p><label>Data de início <input type="date" name="data_inicio" value="<?= h($editar["data_inicio"] ?? "") ?>"></label></p>
        <?php if(!$editar): ?>
            <p><label>Data de conclusão <input type="date" name="data_conclusao"></label></p>
            <p><label>Nota <input type="number" name="nota" min="0" max="10" step="0.5"></label></p>
            <p><label>Review <input type="text" name="review" size="50"></label></p>
        <?php endif; ?>
        <button type="submit">Salvar</button>
        <?php if($editar): ?>
            <a href="<?= h($voltar) ?>">Cancelar</a>
        <?php endif; ?>
    </form>
<?php endif; ?>
</body>
</html>
