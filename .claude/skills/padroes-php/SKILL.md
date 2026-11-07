---
name: padroes-php
description: Padrões de código PHP do my-hobbies (PHP sem framework com a extensão pgsql). Use ao criar ou alterar código PHP, principalmente acesso ao banco, validações e saída HTML.
---

# Padrões PHP do my-hobbies

## Forma de trabalho

- Pedir permissão antes de alterar arquivos e explicar de forma didática o que será feito.
- Claude não executa operações de Git, como commit, branch ou push. O versionamento é feito pelo dono do projeto.

## Acesso ao banco

- As funções acessam o banco por `src/db/funcoes_banco_dados.php`: `selectDB` para leituras e `executarDB` para escritas. Só esse arquivo chama `pg_query_params`.
- A query fica em uma variável `$query`, com marcadores `$1`, `$2`, e é enviada à função junto com os parâmetros. Listas longas ficam em `$parametros`.
- Nunca concatene valores na query, para evitar SQL Injection. Nomes de tabela só entram por constantes da página.
- Toda query de dados do usuário filtra por `usuario_id`, para que uma pessoa nunca veja dados de outra. O valor vem de `exigir_login()`, nunca do formulário.

## Organização em três camadas

Cada página é dividida em três arquivos com o mesmo nome:

| Camada | Onde | Pode | Não pode |
| --- | --- | --- | --- |
| Fluxo | `public/myBody.php` | Ler `$_GET`/`$_POST`, validar, chamar o CRUD, redirecionar e chamar `exibir()` | SQL e HTML |
| CRUD | `src/db/myBody.php` | SQL por `selectDB`/`executarDB` | `$_GET`, `$_POST`, redirecionamento e HTML |
| Tela | `src/front/myBody/registros.php` | HTML com `<?= h(...) ?>`, laços e condições de exibição | Consultas ao banco e regras de negócio |

- O fluxo termina com `exibir(["pasta/tela"], [...])`. O template só enxerga as variáveis passadas nesse array.
- Cabeçalho e rodapé ficam em `src/front/layout/`. Scripts entram pela chave `scripts` do array.
- Funções CRUD de registros: `registros_get`, `registros_cad`, `registros_up`, `registros_del`. Funções de validação e leitura do formulário (`registros_validar`, `registros_dados_post`) ficam no fluxo.
- O CRUD de hobbies fica em `src/db/hobbies.php`, e o fluxo compartilhado de hobbies do MyBody e do MySkills, em `src/includes/hobbies_fluxo.php`.
- O MyFun não cadastra hobbies: tem categorias fixas (Games, Filmes, Livros, Mangás/HQs, Séries e Músicas), definidas em `SECOES` em `public/myFun.php`. Cada uma vira um hobby da categoria `consumo`, criado no primeiro cadastro de item.
- Arquivos que o navegador baixa direto, como JavaScript e futuramente CSS, ficam em `public/`.

## Saída

- Todo dado exibido em HTML passa por `htmlspecialchars`, para evitar XSS.

## Validação

- O PHP valida antes de gravar. Isso inclui conferir se o hobby pertence à categoria do registro.
- As constraints do banco continuam existindo como garantia final (ver `padroes-sql`).

## Exclusão de hobbies

1. Um hobby sem registros pode ser excluído direto.
2. Um hobby com registros não é excluído direto. O sistema avisa que há registros e pergunta se a pessoa não prefere alterá-los.
3. Só depois da confirmação os registros e o hobby são apagados, em uma única transação (`BEGIN`/`COMMIT`). Se algo falhar, nada é apagado.

## Estrutura e credenciais

- `public/` é a raiz do Apache e guarda só o fluxo das páginas e arquivos estáticos. Código interno fica em `src/`, e configurações em `config/`.
- Senhas ficam apenas no `.env`, que não é versionado. O código lê os valores com `getenv`, e o `.env.example` mostra o formato.

## Autenticação e sessão

- Toda página interna começa com `$usuario_id = exigir_login();`, logo após incluir `app.php`. Só `index.php`, `redefinir_senha.php` e `sair.php` são públicas.
- Senhas usam `password_hash` e `password_verify`, com no mínimo 8 caracteres (`senha_validar`).
- O login chama `usuario_entrar()`, que regenera o ID da sessão.
- Todo formulário POST inclui `<?= csrf_campo() ?>`. O `app.php` recusa com 400 qualquer POST sem token válido.
- Mensagens de login e de "esqueci a senha" não revelam se um e-mail tem conta.
- Tokens de redefinição: o link leva o token, e o banco guarda só o hash SHA-256, com validade de 1 hora e uso único.
- Links em e-mails usam `APP_URL` do `.env`, nunca o cabeçalho Host da requisição.
- E-mails saem por `email_enviar()` (`mail()` e `msmtp`). O destino SMTP é configurado no `.env`: Mailpit no ambiente local e servidor real em produção.
- Login: 5 senhas erradas para o mesmo e-mail em 15 minutos bloqueiam esse e-mail por 15 minutos (tabela `tentativas_login`). O bloqueio é conferido antes da senha, e um login certo apaga as falhas.

## Erros

- O usuário nunca vê detalhes técnicos: `display_errors` fica desligado, e exceções não tratadas mostram só "Algo deu errado" com status 500 (`erro_generico()` em `app.php`).
- Todos os erros do PHP vão para `tmp/erroDB`, fora da pasta pública e ignorado pelo Git. Para investigar um problema, leia esse arquivo.
- Para registrar algo no log, use `error_log()`. Nunca use `echo` ou `var_dump` com dados de erro.
