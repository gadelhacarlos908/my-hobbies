CREATE TABLE sessoes_pratica (
    id                 BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    hobby_id           BIGINT NOT NULL,
    categoria          TEXT NOT NULL DEFAULT 'estudo_pratica' CHECK (categoria = 'estudo_pratica'),
    data               DATE NOT NULL,
    duracao_minutos    INTEGER NOT NULL CHECK (duracao_minutos > 0),
    descricao_conteudo TEXT,
    observacoes        TEXT,
    criado_em          TIMESTAMPTZ NOT NULL DEFAULT now(),
    FOREIGN KEY (hobby_id, categoria) REFERENCES hobbies (id, categoria) ON DELETE RESTRICT
);

CREATE INDEX sessoes_pratica_hobby_data_idx ON sessoes_pratica (hobby_id, data);
