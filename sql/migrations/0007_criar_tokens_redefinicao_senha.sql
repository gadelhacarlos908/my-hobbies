-- Guarda só o hash do token. Quem ler o banco não consegue usar o link do e-mail.
CREATE TABLE tokens_redefinicao_senha (
    id         BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id BIGINT NOT NULL REFERENCES usuarios (id) ON DELETE CASCADE,
    token_hash TEXT NOT NULL UNIQUE,
    expira_em  TIMESTAMPTZ NOT NULL,
    usado_em   TIMESTAMPTZ,
    criado_em  TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX tokens_redefinicao_senha_usuario_id_idx ON tokens_redefinicao_senha (usuario_id);
