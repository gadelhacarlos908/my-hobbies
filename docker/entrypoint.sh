#!/bin/sh
# Gera a configuração do msmtp a partir do .env e inicia o Apache.
# Sem SMTP_USER, envia sem autenticação, como o Mailpit espera.
set -eu

{
    echo "account default"
    echo "host ${SMTP_HOST:-mailpit}"
    echo "port ${SMTP_PORT:-1025}"
    echo "from ${SMTP_FROM:-nao-responda@myhobbies.local}"
    echo "tls ${SMTP_TLS:-off}"

    if [ "${SMTP_TLS:-off}" = "on" ]; then
        echo "tls_trust_file /etc/ssl/certs/ca-certificates.crt"
    fi

    if [ -n "${SMTP_USER:-}" ]; then
        echo "auth on"
        echo "user ${SMTP_USER}"
        echo "password ${SMTP_PASSWORD:-}"
    fi
} > /etc/msmtprc

chown root:www-data /etc/msmtprc
chmod 640 /etc/msmtprc

exec docker-php-entrypoint "$@"
