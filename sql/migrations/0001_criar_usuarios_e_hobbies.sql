CREATE TABLE usuarios (
    id         BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nome       TEXT NOT NULL,
    email      TEXT NOT NULL,
    senha_hash TEXT NOT NULL,
    criado_em  TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- O índice em lower(email) impede 'Ana@x.com' e 'ana@x.com' como contas separadas.
CREATE UNIQUE INDEX usuarios_email_unico ON usuarios (lower(email));

CREATE TABLE hobbies (
    id         BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id BIGINT NOT NULL REFERENCES usuarios (id) ON DELETE RESTRICT,
    nome       TEXT NOT NULL,
    categoria  TEXT NOT NULL CHECK (categoria IN ('consumo', 'atividade_fisica', 'estudo_pratica')),
    descricao  TEXT,
    ativo      BOOLEAN NOT NULL DEFAULT true,
    criado_em  TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (usuario_id, nome),
    -- Alvo das FKs compostas das tabelas de cada categoria.
    UNIQUE (id, categoria)
);

CREATE TABLE tags (
    id         BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id BIGINT NOT NULL REFERENCES usuarios (id) ON DELETE RESTRICT,
    nome       TEXT NOT NULL,
    UNIQUE (usuario_id, nome)
);

CREATE TABLE hobby_tags (
    hobby_id BIGINT NOT NULL REFERENCES hobbies (id) ON DELETE CASCADE,
    tag_id   BIGINT NOT NULL REFERENCES tags (id) ON DELETE CASCADE,
    PRIMARY KEY (hobby_id, tag_id)
);

CREATE INDEX hobby_tags_tag_id_idx ON hobby_tags (tag_id);

CREATE TABLE metas (
    id           BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    hobby_id     BIGINT NOT NULL REFERENCES hobbies (id) ON DELETE RESTRICT,
    descricao    TEXT NOT NULL,
    periodo      TEXT NOT NULL CHECK (periodo IN ('diaria', 'semanal', 'mensal', 'unica')),
    unidade      TEXT NOT NULL CHECK (unidade IN ('minutos', 'sessoes', 'itens')),
    valor_alvo   INTEGER NOT NULL CHECK (valor_alvo > 0),
    data_inicio  DATE NOT NULL DEFAULT CURRENT_DATE,
    data_fim     DATE,
    concluida_em TIMESTAMPTZ,
    ativa        BOOLEAN NOT NULL DEFAULT true,
    criado_em    TIMESTAMPTZ NOT NULL DEFAULT now(),
    CHECK (data_fim IS NULL OR data_fim >= data_inicio)
);

CREATE INDEX metas_hobby_id_idx ON metas (hobby_id);
