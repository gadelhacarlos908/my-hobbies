<p><a href="myBody.php">Voltar aos hobbies</a></p>

<h2>Registros</h2>
<div class="barra-acoes">
    <details name="barra-acoes">
        <summary><?= h(filtros_rotulo($filtro)) ?></summary>
        <form id="filtros" method="post" action="<?= h($lista_url) ?>">
            <?= csrf_campo() ?>
            <input type="hidden" name="pagina" value="<?= h($menu) ?>">
            <label>De <input type="date" name="filtro[data_de]" value="<?= h($filtro["data_de"] ?? "") ?>"></label>
            <label>Até <input type="date" name="filtro[data_ate]" value="<?= h($filtro["data_ate"] ?? "") ?>"></label>
            <label>Intensidade
                <select name="filtro[intensidade]">
                    <option value="">Todas</option>
                    <?php foreach($intensidades as $opcao): ?>
                        <option value="<?= $opcao ?>" <?= ($filtro["intensidade"] ?? "") === $opcao ? "selected" : "" ?>><?= $opcao ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Métricas ou observações contêm <input type="text" name="filtro[texto]" value="<?= h($filtro["texto"] ?? "") ?>"></label>
            <button type="submit">Aplicar</button>
            <button type="submit" name="acao" value="filtro_limpar" formnovalidate>Limpar</button>
        </form>
    </details>

    <details name="barra-acoes" <?= $form_aberto ? "open" : "" ?>>
        <summary><?= $editar ? "Editar registro" : "+ Novo Registro" ?></summary>
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
    </details>
</div>

<p><?= count($registros) ?> registro(s)</p>
<table border="1" cellpadding="6">
    <tr>
        <th><button type="submit" form="filtros" name="filtro[ordem]" value="data">Data<?= ordem_seta("data", $filtro) ?></button></th>
        <th><button type="submit" form="filtros" name="filtro[ordem]" value="duracao">Duração<?= ordem_seta("duracao", $filtro) ?></button></th>
        <th><button type="submit" form="filtros" name="filtro[ordem]" value="intensidade">Intensidade<?= ordem_seta("intensidade", $filtro) ?></button></th>
        <th>Métricas</th><th>Observações</th><th>Ações</th>
    </tr>
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
