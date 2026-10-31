#!/bin/sh
# Aplica as migrations pendentes, em ordem.
# Uso: docker compose exec postgres sh /sql/migrar.sh
set -eu

PSQL="psql -q -v ON_ERROR_STOP=1 -U $POSTGRES_USER -d $POSTGRES_DB"

$PSQL -c "CREATE TABLE IF NOT EXISTS schema_migrations (
    versao      TEXT PRIMARY KEY,
    aplicada_em TIMESTAMPTZ NOT NULL DEFAULT now()
);"

for arquivo in /sql/migrations/*.sql; do
    versao=$(basename "$arquivo")

    if [ -n "$($PSQL -tAc "SELECT 1 FROM schema_migrations WHERE versao = '$versao'")" ]; then
        echo "Já aplicada: $versao"
        continue
    fi

    # --single-transaction garante que a migration e o seu registro entram juntos.
    $PSQL --single-transaction -f "$arquivo" \
        -c "INSERT INTO schema_migrations (versao) VALUES ('$versao');"
    echo "Aplicada: $versao"
done
