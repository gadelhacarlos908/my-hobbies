<?php

require __DIR__ . "/../db/hobbies.php";

// Trata os formulários de hobby, que são iguais no MyBody e no MySkills.
function hobbies_processar($usuario_id, $categoria, $tabela, $pagina)
{
    $acao = $_POST["acao"] ?? "";
    $hobby_id = (int) ($_POST["hobby_id"] ?? 0);
    $resposta = ["erro" => null, "confirmar" => null];

    if($acao === "hobby_del")
    {
        $hobby = hobby_get($usuario_id, $categoria, $hobby_id);
        if(!$hobby)
            redirecionar($pagina);

        $total = hobbies_total_registros($tabela, $hobby_id);
        if($total > 0 && !isset($_POST["confirmado"]))
        {
            $hobby["total"] = $total;
            $resposta["confirmar"] = $hobby;
            return $resposta;
        }

        hobbies_del($usuario_id, $categoria, $tabela, $hobby_id);
        redirecionar($pagina);
    }

    if($acao === "hobby_cad" || $acao === "hobby_up")
    {
        $nome = trim($_POST["nome"] ?? "");
        $descricao = texto_ou_null($_POST["descricao"] ?? "");

        if($nome === "")
            $resposta["erro"] = "Informe o nome do hobby.";
        elseif(hobbies_nome_existe($usuario_id, $nome, $hobby_id))
            $resposta["erro"] = "Você já tem um hobby com esse nome.";
        else
        {
            if($acao === "hobby_cad")
                hobbies_cad($usuario_id, $categoria, $nome, $descricao);
            else
                hobbies_up($usuario_id, $categoria, $hobby_id, $nome, $descricao, isset($_POST["ativo"]));

            redirecionar($pagina);
        }
    }

    return $resposta;
}

// Dados da tela de lista de hobbies, usados quando nenhum hobby está aberto.
function hobbies_tela_dados($usuario_id, $categoria, $pagina, $explicacao)
{
    return [
        "pagina" => $pagina,
        "explicacao" => $explicacao,
        "hobbies" => hobbies_get($usuario_id, $categoria),
        "editar_hobby" => isset($_GET["editar_hobby"])
            ? hobby_get($usuario_id, $categoria, (int) $_GET["editar_hobby"])
            : null,
    ];
}

?>
