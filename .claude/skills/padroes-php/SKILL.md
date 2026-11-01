---
name: padroes-php
description: Padrões de código PHP do my-hobbies (PHP sem framework com a extensão pgsql). Use ao criar ou alterar código PHP, principalmente acesso ao banco, validações e saída HTML.
---

# Padrões PHP do my-hobbies

## Forma de trabalho

- Pedir permissão antes de alterar arquivos e explicar de forma didática o que será feito.
- Claude não executa operações de Git, como commit, branch ou push. O versionamento é feito pelo dono do projeto.

## Acesso ao banco

- As funções acessam o banco por `banco_dados/funcoes_banco_dados.php`: `selectDB` para leituras e `executarDB` para escritas. Só esse arquivo chama `pg_query_params`.
- A query fica em uma variável `$query`, com marcadores `$1`, `$2`, e é enviada à função junto com os parâmetros. Listas longas ficam em `$parametros`.
- Nunca concatene valores na query, para evitar SQL Injection. Nomes de tabela só entram por constantes da página.
- Toda query de dados do usuário filtra por `usuario_id`, para que uma pessoa nunca veja dados de outra.

## Organização

- Cada categoria tem sua página (`myFun.php`, `myBody.php`, `mySkills.php`), com o CRUD em funções dentro dela: `registros_get`, `registros_cad`, `registros_up`, `registros_del`.
- O CRUD de hobbies é igual nas três páginas e fica em `includes/hobbies.php`.

## Saída

- Todo dado exibido em HTML passa por `htmlspecialchars`, para evitar XSS.

## Validação

- O PHP valida antes de gravar. Isso inclui conferir se o hobby pertence à categoria do registro.
- As constraints do banco continuam existindo como garantia final (ver `padroes-sql`).

## Exclusão de hobbies

1. Um hobby sem registros pode ser excluído direto.
2. Um hobby com registros não é excluído direto. O sistema avisa que há registros e pergunta se a pessoa não prefere alterá-los.
3. Só depois da confirmação os registros e o hobby são apagados, em uma única transação (`BEGIN`/`COMMIT`). Se algo falhar, nada é apagado.
