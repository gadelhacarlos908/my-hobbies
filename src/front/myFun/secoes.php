<ul>
    <?php foreach($secoes as $chave_secao => $item): ?>
        <li>
            <a href="myFun.php?secao=<?= $chave_secao ?>"><?= h($item["nome"]) ?></a>
            (<?= (int) ($totais[$item["nome"]] ?? 0) ?>)
        </li>
    <?php endforeach; ?>
</ul>
