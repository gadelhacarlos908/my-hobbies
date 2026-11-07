-- Cada linha é uma senha errada. Serve para bloquear o e-mail após várias falhas seguidas.
CREATE TABLE tentativas_login (
    id        BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    email     TEXT NOT NULL,
    ip        TEXT,
    criado_em TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX tentativas_login_email_criado_em_idx ON tentativas_login (email, criado_em);
