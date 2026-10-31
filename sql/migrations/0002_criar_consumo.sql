CREATE TABLE itens_consumo (
    id             BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    hobby_id       BIGINT NOT NULL,
    categoria      TEXT NOT NULL DEFAULT 'consumo' CHECK (categoria = 'consumo'),
    titulo         TEXT NOT NULL,
    tipo           TEXT NOT NULL CHECK (tipo IN ('livro', 'filme', 'jogo', 'serie', 'anime')),
    status         TEXT NOT NULL DEFAULT 'quero'
                   CHECK (status IN ('quero', 'em_andamento', 'concluido', 'abandonado')),
    data_inicio    DATE,
    origem_externa TEXT,
    id_externo     TEXT,
    capa_url       TEXT,
    criado_em      TIMESTAMPTZ NOT NULL DEFAULT now(),
    FOREIGN KEY (hobby_id, categoria) REFERENCES hobbies (id, categoria) ON DELETE RESTRICT,
    CHECK ((origem_externa IS NULL) = (id_externo IS NULL)),
    -- NULLs não colidem em UNIQUE, então itens manuais não são afetados.
    UNIQUE (hobby_id, origem_externa, id_externo)
);

-- Histórico alimentado pela opção Atualizar. A nota exibida é a da linha mais recente.
CREATE TABLE avaliacoes_item (
    id             BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    item_id        BIGINT NOT NULL REFERENCES itens_consumo (id) ON DELETE CASCADE,
    nota           NUMERIC(3,1) CHECK (nota BETWEEN 0 AND 10 AND nota * 2 = TRUNC(nota * 2)),
    review         TEXT,
    data_conclusao DATE,
    criado_em      TIMESTAMPTZ NOT NULL DEFAULT now(),
    CHECK (nota IS NOT NULL OR review IS NOT NULL OR data_conclusao IS NOT NULL)
);

CREATE INDEX avaliacoes_item_recentes_idx ON avaliacoes_item (item_id, criado_em DESC, id DESC);

CREATE TABLE sessoes_consumo (
    id              BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    item_id         BIGINT NOT NULL REFERENCES itens_consumo (id) ON DELETE CASCADE,
    data            DATE NOT NULL,
    duracao_minutos INTEGER NOT NULL CHECK (duracao_minutos > 0),
    observacoes     TEXT,
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX sessoes_consumo_item_data_idx ON sessoes_consumo (item_id, data);
