<?php if($confirmar): ?>
    <h2>Excluir <?= h($confirmar["nome"]) ?>?</h2>
    <p>
        Este hobby tem <?= (int) $confirmar["total"] ?> registro(s) ou meta(s).
        Você não prefere alterar os registros em vez de excluir?
    </p>
    <form method="post" action="<?= h($pagina) ?>">
        <?= csrf_campo() ?>
        <input type="hidden" name="acao" value="hobby_del">
        <input type="hidden" name="hobby_id" value="<?= (int) $confirmar["id"] ?>">
        <input type="hidden" name="confirmado" value="1">
        <button type="submit">Sim, excluir tudo</button>
        <a href="<?= h($pagina) ?>">Cancelar</a>
    </form>
<?php endif; ?>

<h2>Hobbies</h2>
<p><?= h($explicacao) ?></p>
<div class="barra-acoes">
    <details name="barra-acoes">
        <summary><?= h(filtros_rotulo($filtro)) ?></summary>
        <form id="filtros" method="post" action="<?= h($lista_url) ?>">
            <?= csrf_campo() ?>
            <input type="hidden" name="pagina" value="<?= h($menu) ?>">
            <label>Situação
                <select name="filtro[situacao]">
                    <option value="">Todas</option>
                    <option value="ativo" <?= ($filtro["situacao"] ?? "") === "ativo" ? "selected" : "" ?>>Ativo</option>
                    <option value="arquivado" <?= ($filtro["situacao"] ?? "") === "arquivado" ? "selected" : "" ?>>Arquivado</option>
                </select>
            </label>
            <button type="submit">Aplicar</button>
            <button type="submit" name="acao" value="filtro_limpar" formnovalidate>Limpar</button>
        </form>
    </details>

    <details name="barra-acoes" <?= $form_aberto ? "open" : "" ?>>
        <summary><?= $editar_hobby ? "Editar hobby" : "+ Novo Hobby" ?></summary>
        <form method="post" action="<?= h($pagina) ?>">
            <?= csrf_campo() ?>
            <input type="hidden" name="acao" value="<?= $editar_hobby ? "hobby_up" : "hobby_cad" ?>">
            <input type="hidden" name="hobby_id" value="<?= (int) ($editar_hobby["id"] ?? 0) ?>">
            <p><label>Nome <input type="text" name="nome" required value="<?= h($editar_hobby["nome"] ?? "") ?>"></label></p>
            <p><label>Descrição <input type="text" name="descricao" value="<?= h($editar_hobby["descricao"] ?? "") ?>"></label></p>
            <?php if($editar_hobby): ?>
                <p><label><input type="checkbox" name="ativo" <?= $editar_hobby["ativo"] === "t" ? "checked" : "" ?>> Ativo</label></p>
            <?php endif; ?>
            <button type="submit">Salvar</button>
            <?php if($editar_hobby): ?>
                <a href="<?= h($pagina) ?>">Cancelar</a>
            <?php endif; ?>
        </form>
    </details>
</div>

<p><?= count($hobbies) ?> hobby(s)</p>
<table border="1" cellpadding="6">
    <tr>
        <th><button type="submit" form="filtros" name="filtro[ordem]" value="nome">Nome<?= ordem_seta("nome", $filtro) ?></button></th>
        <th>Descrição</th>
        <th><button type="submit" form="filtros" name="filtro[ordem]" value="situacao">Situação<?= ordem_seta("situacao", $filtro) ?></button></th>
        <th>Ações</th>
    </tr>
    <?php foreach($hobbies as $item): ?>
        <tr>
            <td><a href="<?= h($pagina) ?>?hobby_id=<?= (int) $item["id"] ?>"><?= h($item["nome"]) ?></a></td>
            <td><?= h($item["descricao"]) ?></td>
            <td><?= $item["ativo"] === "t" ? "Ativo" : "Arquivado" ?></td>
            <td>
                <a href="<?= h($pagina) ?>?hobby_id=<?= (int) $item["id"] ?>">Abrir</a>
                <a href="<?= h($pagina) ?>?editar_hobby=<?= (int) $item["id"] ?>">Editar</a>
                <form method="post" action="<?= h($pagina) ?>" style="display:inline">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="acao" value="hobby_del">
                    <input type="hidden" name="hobby_id" value="<?= (int) $item["id"] ?>">
                    <button type="submit">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
