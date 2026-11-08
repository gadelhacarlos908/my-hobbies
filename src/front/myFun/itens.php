<p><a href="myFun.php">Voltar ao MyFun</a></p>

<?php if($confirmar_item): ?>
    <h2>Excluir este item?</h2>
    <p>Ele tem <?= (int) $confirmar_item["total"] ?> avaliação(ões) ou sessão(ões), que serão apagadas junto.</p>
    <form method="post" action="<?= h($voltar) ?>">
        <?= csrf_campo() ?>
        <input type="hidden" name="acao" value="registro_del">
        <input type="hidden" name="registro_id" value="<?= (int) $confirmar_item["id"] ?>">
        <input type="hidden" name="confirmado" value="1">
        <button type="submit">Sim, excluir tudo</button>
        <a href="<?= h($voltar) ?>">Cancelar</a>
    </form>
<?php endif; ?>

<h2>Itens</h2>
<div class="barra-acoes">
    <details name="barra-acoes">
        <summary><?= h(filtros_rotulo($filtro)) ?></summary>
        <form id="filtros" method="post" action="<?= h($lista_url) ?>">
            <?= csrf_campo() ?>
            <input type="hidden" name="pagina" value="<?= h($menu) ?>">
            <label>Título contém <input type="text" name="filtro[titulo]" value="<?= h($filtro["titulo"] ?? "") ?>"></label>
            <label>Situação
                <select name="filtro[situacao]">
                    <option value="">Todas</option>
                    <?php foreach($status as $valor => $rotulo): ?>
                        <option value="<?= $valor ?>" <?= ($filtro["situacao"] ?? "") === $valor ? "selected" : "" ?>><?= h($rotulo) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php if(count($secao["tipos"]) > 1): ?>
                <label>Tipo
                    <select name="filtro[tipo]">
                        <option value="">Todos</option>
                        <?php foreach($secao["tipos"] as $valor => $rotulo): ?>
                            <option value="<?= $valor ?>" <?= ($filtro["tipo"] ?? "") === $valor ? "selected" : "" ?>><?= h($rotulo) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endif; ?>
            <label>Nota mínima <input type="number" name="filtro[nota_min]" step="0.5" min="0" max="10" value="<?= h($filtro["nota_min"] ?? "") ?>"></label>
            <button type="submit">Aplicar</button>
            <button type="submit" name="acao" value="filtro_limpar" formnovalidate>Limpar</button>
        </form>
    </details>

    <details name="barra-acoes" <?= $form_aberto ? "open" : "" ?>>
        <summary><?= $editar ? "Editar item" : "+ Novo Item" ?></summary>
        <form method="post" action="<?= h($voltar) ?>">
            <?= csrf_campo() ?>
            <input type="hidden" name="acao" value="<?= $editar ? "registro_up" : "registro_cad" ?>">
            <input type="hidden" name="registro_id" value="<?= (int) ($editar["id"] ?? 0) ?>">
            <p><label>Título <input type="text" name="titulo" required value="<?= h($editar["titulo"] ?? "") ?>"></label></p>
            <?php if(count($secao["tipos"]) > 1): ?>
                <p>
                    <label>Tipo
                        <select name="tipo">
                            <?php foreach($secao["tipos"] as $valor => $rotulo): ?>
                                <option value="<?= $valor ?>" <?= ($editar["tipo"] ?? "") === $valor ? "selected" : "" ?>><?= h($rotulo) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </p>
            <?php endif; ?>
            <?php if(!$editar): ?>
                <p>
                    <label>Situação
                        <select name="status">
                            <?php foreach($status as $valor => $rotulo): ?>
                                <option value="<?= $valor ?>"><?= h($rotulo) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </p>
            <?php endif; ?>
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
    </details>
</div>

<p><?= count($registros) ?> item(ns)</p>
<table border="1" cellpadding="6">
    <tr>
        <th><button type="submit" form="filtros" name="filtro[ordem]" value="titulo">Título<?= ordem_seta("titulo", $filtro) ?></button></th>
        <?php if(count($secao["tipos"]) > 1): ?>
            <th><button type="submit" form="filtros" name="filtro[ordem]" value="tipo">Tipo<?= ordem_seta("tipo", $filtro) ?></button></th>
        <?php endif; ?>
        <th><button type="submit" form="filtros" name="filtro[ordem]" value="situacao">Situação<?= ordem_seta("situacao", $filtro) ?></button></th>
        <th><button type="submit" form="filtros" name="filtro[ordem]" value="inicio">Início<?= ordem_seta("inicio", $filtro) ?></button></th>
        <th><button type="submit" form="filtros" name="filtro[ordem]" value="nota">Nota<?= ordem_seta("nota", $filtro) ?></button></th>
        <th>Ações</th>
    </tr>
    <?php foreach($registros as $registro): ?>
        <tr>
            <td><?= h($registro["titulo"]) ?></td>
            <?php if(count($secao["tipos"]) > 1): ?>
                <td><?= h($secao["tipos"][$registro["tipo"]] ?? $registro["tipo"]) ?></td>
            <?php endif; ?>
            <td><?= h($status[$registro["status"]] ?? $registro["status"]) ?></td>
            <td><?= h(data_br($registro["data_inicio"])) ?></td>
            <td><?= h($registro["nota"]) ?></td>
            <td>
                <a href="<?= h($voltar) ?>&ver=<?= (int) $registro["id"] ?>">Atualizar</a>
                <a href="<?= h($voltar) ?>&editar=<?= (int) $registro["id"] ?>">Editar</a>
                <form method="post" action="<?= h($voltar) ?>" style="display:inline">
                    <?= csrf_campo() ?>
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
        <?= csrf_campo() ?>
        <input type="hidden" name="acao" value="registro_atualizar">
        <input type="hidden" name="item_id" value="<?= (int) $ver["id"] ?>">
        <p>
            <label>Situação
                <select name="status">
                    <?php foreach($status as $valor => $rotulo): ?>
                        <option value="<?= $valor ?>" <?= $ver["status"] === $valor ? "selected" : "" ?>><?= h($rotulo) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </p>
        <p>Para registrar no histórico, preencha também a nota, o review ou a data de conclusão.</p>
        <p><label>Nota <input type="number" name="nota" min="0" max="10" step="0.5"></label></p>
        <p><label>Review <input type="text" name="review" size="50"></label></p>
        <p><label>Data de conclusão <input type="date" name="data_conclusao"></label></p>
        <button type="submit">Salvar</button>
        <a href="<?= h($voltar) ?>">Cancelar</a>
    </form>

    <h3>Histórico</h3>
    <table border="1" cellpadding="6">
        <tr><th>Cadastrado em</th><th>Nota</th><th>Review</th><th>Conclusão</th></tr>
        <?php foreach($historico as $avaliacao): ?>
            <tr>
                <td><?= h(data_br($avaliacao["criado_em"])) ?></td>
                <td><?= h($avaliacao["nota"]) ?></td>
                <td><?= h($avaliacao["review"]) ?></td>
                <td><?= h(data_br($avaliacao["data_conclusao"])) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
