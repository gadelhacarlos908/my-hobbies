<p><a href="mySkills.php">Voltar aos hobbies</a></p>

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
                    <?= csrf_campo() ?>
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
    <?= csrf_campo() ?>
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
