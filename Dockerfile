FROM php:8.5-apache

RUN apt-get update \
    && apt-get install -y libpq-dev msmtp \
    && docker-php-ext-install pgsql \
    && rm -rf /var/lib/apt/lists/*

# Só public/ fica acessível pelo navegador. src/ e config/ ficam fora da raiz web.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# O mail() do PHP entrega ao msmtp, que envia ao servidor configurado no .env.
RUN echo 'sendmail_path = "/usr/bin/msmtp -t"' > /usr/local/etc/php/conf.d/email.ini

# Erros vão para tmp/erroDB, fora da pasta pública. A tela mostra só uma mensagem genérica.
RUN printf 'display_errors = Off\ndisplay_startup_errors = Off\nlog_errors = On\nerror_log = /var/www/html/tmp/erroDB\n' \
    > /usr/local/etc/php/conf.d/erros.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
