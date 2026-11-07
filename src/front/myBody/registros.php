<p><a href="myBody.php">Voltar aos hobbies</a></p>

<h2>Registros</h2>
<table border="1" cellpadding="6">
    <tr><th>Data</th><th>Duração</th><th>Intensidade</th><th>Métricas</th><th>Observações</th><th>Ações</th></tr>
    <?php foreach($registros as $registro): ?>
        <tr>
            <td><?= h(data_br($registro["data"])) ?></td>
            <td><?= (int) $registro["duracao_minutos"] ?> min</td>
            <td><?= h($registro["intensidade"]) ?></td>
            <td><?= h($registro["metricas"]) ?></td>
            <td><?= h($registro["observacoes"]) ?></td>
            <td>
                <a href="<?= h($voltar) ?>&editar=<?= (int) $registro["id"] ?>">Editar</a>
                <form method="post" action="<?= h($voltar) ?>" style="display:inline"
                      onsubmit="return confirm('Excluir este registro?')">
                    <?= csrf_campo() ?>
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
    <?= csrf_campo() ?>
    <input type="hidden" name="acao" value="<?= $editar ? "registro_up" : "registro_cad" ?>">
    <input type="hidden" name="registro_id" value="<?= (int) ($editar["id"] ?? 0) ?>">
    <p><label>Data <input type="date" name="data" required value="<?= h($editar["data"] ?? date("Y-m-d")) ?>"></label></p>
    <p><label>Duração em minutos <input type="number" name="duracao" min="1" required value="<?= h($editar["duracao_minutos"] ?? "") ?>"></label></p>
    <p>
        <label>Intensidade
            <select name="intensidade">
                <option value="">Não informar</option>
                <?php foreach($intensidades as $opcao): ?>
                    <option value="<?= $opcao ?>" <?= ($editar["intensidade"] ?? "") === $opcao ? "selected" : "" ?>><?= $opcao ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </p>
    <p>
        <label>Métricas
            <input type="text" name="metricas" size="50" value="<?= h($editar["metricas"] ?? "") ?>"
                   placeholder="Exemplo: 5 km em 30 min, supino 3x10 com 60 kg">
        </label>
    </p>
    <p><label>Observações <input type="text" name="observacoes" value="<?= h($editar["observacoes"] ?? "") ?>"></label></p>
    <button type="submit">Salvar</button>
    <?php if($editar): ?>
        <a href="<?= h($voltar) ?>">Cancelar</a>
    <?php endif; ?>
</form>
