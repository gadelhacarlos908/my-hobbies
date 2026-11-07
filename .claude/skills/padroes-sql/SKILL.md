---
name: padroes-sql
description: Padrões de banco de dados do my-hobbies (PostgreSQL 16). Use ao criar ou alterar tabelas, migrations, índices ou queries SQL.
---

# Padrões SQL do my-hobbies

## Organização

- Todo arquivo SQL fica em `sql/`, na raiz do projeto. Nunca em `public/`, a única pasta acessível pelo navegador.
- Migrations ficam em `sql/migrations/`, numeradas com quatro dígitos: `0001_descricao.sql`.
- Uma migration já aplicada não é editada. Mudanças entram em um novo arquivo, para que o histórico do schema seja reproduzível.
- Migrations são aplicadas com `docker compose exec postgres sh /sql/migrar.sh`. O script registra cada uma em `schema_migrations` e executa apenas as pendentes.
- Os arquivos de migration não têm `BEGIN`/`COMMIT`, porque o script já roda cada um em uma transação.

## Nomes

- Tabelas e colunas em português e em `snake_case`.
- Tabelas no plural: `hobbies`, `itens_consumo`.

## Tipos

| Dado | Tipo | Motivo |
| --- | --- | --- |
| Chave primária | `BIGINT GENERATED ALWAYS AS IDENTITY` | É o padrão SQL atual e impede inserir ids manualmente por engano. |
| Data e hora | `TIMESTAMPTZ` | Guarda o instante exato, sem ambiguidade de fuso. |
| Dia | `DATE` | Serve para datas sem hora, como o dia de um treino. |
| Lista fixa de valores | `TEXT` + `CHECK` | Adicionar ou remover um valor exige apenas trocar a constraint. |

## Integridade

- Toda FK tem índice. O PostgreSQL não cria esse índice sozinho, e sem ele os JOINs e as exclusões ficam mais lentos.
- Tabelas específicas de uma categoria usam FK composta (`hobby_id`, `categoria`) para `hobbies (id, categoria)`. A coluna `categoria` tem valor fixo, garantido por `DEFAULT` e `CHECK`.
- O banco é a última barreira. O PHP valida antes, para não gerar erros de banco desnecessários (ver `padroes-php`).
- FKs para `hobbies` usam `ON DELETE RESTRICT`. O fluxo de exclusão confirmada fica em `padroes-php`.
- FKs para `itens_consumo` usam `ON DELETE CASCADE`. Antes de apagar, o PHP avisa quantos registros existem e pede confirmação.
- Valores calculáveis, como streak e nota atual, não viram coluna. São obtidos por query a partir do histórico.
