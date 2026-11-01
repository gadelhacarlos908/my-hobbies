-- Usuário de teste até o login da fase 2. Não é migration porque é dado, não estrutura.
INSERT INTO usuarios (nome, email, senha_hash)
VALUES ('Usuário de desenvolvimento', 'dev@myhobbies.local', 'login-ainda-nao-implementado')
ON CONFLICT (lower(email)) DO NOTHING;
