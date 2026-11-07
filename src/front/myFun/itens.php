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
<table border="1" cellpadding="6">
    <tr>
        <th>Título</th>
        <?php if(count($secao["tipos"]) > 1): ?><th>Tipo</th><?php endif; ?>
        <th>Situação</th><th>Início</th><th>Nota</th><th>Ações</th>
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
        <?php foreach($historico as $avaliacao): ?>
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
        <p>
            <label>Situação
                <select name="status">
                    <?php foreach($status as $valor => $rotulo): ?>
                        <option value="<?= $valor ?>" <?= ($editar["status"] ?? "") === $valor ? "selected" : "" ?>><?= h($rotulo) ?></option>
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
