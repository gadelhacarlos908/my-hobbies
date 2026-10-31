CREATE TABLE registros_atividade (
    id              BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    hobby_id        BIGINT NOT NULL,
    categoria       TEXT NOT NULL DEFAULT 'atividade_fisica' CHECK (categoria = 'atividade_fisica'),
    data            DATE NOT NULL,
    duracao_minutos INTEGER NOT NULL CHECK (duracao_minutos > 0),
    intensidade     TEXT CHECK (intensidade IN ('baixa', 'moderada', 'alta')),
    observacoes     TEXT,
    metricas        JSONB NOT NULL DEFAULT '{}' CHECK (jsonb_typeof(metricas) = 'object'),
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT now(),
    FOREIGN KEY (hobby_id, categoria) REFERENCES hobbies (id, categoria) ON DELETE RESTRICT
);

CREATE INDEX registros_atividade_hobby_data_idx ON registros_atividade (hobby_id, data);
