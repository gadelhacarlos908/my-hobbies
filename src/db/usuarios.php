<?php

function usuarios_get_por_email($email)
{
    $query = "SELECT id, nome, email, senha_hash FROM usuarios WHERE lower(email) = lower($1)";
    $usuarios = selectDB($query, [$email]);

    return $usuarios[0] ?? null;
}

// A mesma resposta para e-mail inexistente e senha errada não revela quem tem conta.
function usuarios_login($email, $senha)
{
    $usuario = usuarios_get_por_email($email);

    if($usuario && password_verify($senha, $usuario["senha_hash"]))
        return $usuario;

    return null;
}

// Falhas do e-mail na janela e quantos minutos faltam para a mais antiga sair dela.
function tentativas_get($email, $janela_minutos)
{
    $query = "SELECT COUNT(*) AS total,
                     CEIL(EXTRACT(EPOCH FROM MIN(criado_em) + make_interval(mins => $2) - now()) / 60) AS minutos
              FROM tentativas_login
              WHERE email = lower($1) AND criado_em > now() - make_interval(mins => $2)";

    $tentativas = selectDB($query, [$email, $janela_minutos]);

    return ["total" => (int) $tentativas[0]["total"], "minutos" => max(1, (int) $tentativas[0]["minutos"])];
}

function tentativas_cad($email, $ip)
{
    $query = "INSERT INTO tentativas_login (email, ip) VALUES (lower($1), $2)";
    executarDB($query, [$email, $ip]);

    $query = "DELETE FROM tentativas_login WHERE criado_em < now() - interval '1 day'";
    executarDB($query);
}

function tentativas_del($email)
{
    $query = "DELETE FROM tentativas_login WHERE email = lower($1)";
    executarDB($query, [$email]);
}

function usuarios_cad($nome, $email, $senha)
{
    $query = "INSERT INTO usuarios (nome, email, senha_hash) VALUES ($1, $2, $3) RETURNING id, nome";
    $usuario = selectDB($query, [$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);

    return $usuario[0];
}

// Invalida os links anteriores e cria um novo. Devolve o token; o banco guarda só o hash dele.
function tokens_cad($usuario_id)
{
    $token = bin2hex(random_bytes(32));

    executarDB("BEGIN");

    try
    {
        $query = "UPDATE tokens_redefinicao_senha SET usado_em = now() WHERE usuario_id = $1 AND usado_em IS NULL";
        executarDB($query, [$usuario_id]);

        $query = "INSERT INTO tokens_redefinicao_senha (usuario_id, token_hash, expira_em)
                  VALUES ($1, $2, now() + interval '1 hour')";
        executarDB($query, [$usuario_id, hash("sha256", $token)]);

        executarDB("COMMIT");
    } catch (Exception $e)
    {
        executarDB("ROLLBACK");
        throw $e;
    }

    return $token;
}

function tokens_get_valido($token)
{
    $query = "SELECT id, usuario_id
              FROM tokens_redefinicao_senha
              WHERE token_hash = $1 AND usado_em IS NULL AND expira_em > now()";
    $tokens = selectDB($query, [hash("sha256", $token)]);

    return $tokens[0] ?? null;
}

// Troca a senha e invalida todos os links pendentes do usuário na mesma transação.
function usuarios_senha_up($usuario_id, $senha)
{
    executarDB("BEGIN");

    try
    {
        $query = "UPDATE usuarios SET senha_hash = $1 WHERE id = $2";
        executarDB($query, [password_hash($senha, PASSWORD_DEFAULT), $usuario_id]);

        $query = "UPDATE tokens_redefinicao_senha SET usado_em = now() WHERE usuario_id = $1 AND usado_em IS NULL";
        executarDB($query, [$usuario_id]);

        executarDB("COMMIT");
    } catch (Exception $e)
    {
        executarDB("ROLLBACK");
        throw $e;
    }
}

?>
