FROM php:7.4-fpm AS builder

RUN apt-get update && \
    apt-get install -y zip unzip && \
    apt-get clean

COPY --from=composer/composer:latest-bin /composer /usr/local/bin/composer
COPY ./app /opt/phpuush/app
COPY ./composer.json /opt/phpuush/composer.json
COPY ./composer.lock /opt/phpuush/composer.lock
COPY ./index.php /opt/phpuush/index.php
RUN composer install --working-dir /opt/phpuush --no-dev

# --------------------------------------- #

FROM php:7.4-fpm AS web

ENV PHPUUSH_IGNORE_CONFIG_FILE=true
ENV PHPUUSH_DATABASE=/opt/phpuush-data/databases/phpuush.db
ENV PHPUUSH_FILES_DOMAIN=localhost
ENV PHPUUSH_FILES_UPLOAD=/opt/phpuush-data/uploads/

RUN apt-get update && \
    apt-get install -y nginx supervisor && \
    apt-get clean

COPY --from=builder /opt/phpuush /opt/phpuush

COPY <<"EOF" /usr/local/etc/php/conf.d/99-php-overrides.ini
display_errors = Off
log_errors = On
max_execution_time = 600
memory_limit = 64M
file_uploads = On
post_max_size = 64M
upload_max_filesize = 64M
EOF

COPY <<"EOF" /etc/nginx/sites-available/default
server {
    listen 80 default;

    root /var/www/html;

    client_max_body_size 100M;

    location / {
        include fastcgi_params;
        fastcgi_pass localhost:9000;
        fastcgi_param SCRIPT_FILENAME /opt/phpuush/index.php;
    }
}
EOF

COPY <<"EOF" /etc/supervisord.conf
[supervisord]
user=root
nodaemon=true
logfile=/dev/null
logfile_maxbytes=0
pidfile=/tmp/supervisord.pid

[program:php-fpm]
command=php-fpm
stdout_logfile=/dev/fd/1
stdout_logfile_maxbytes=0
redirect_stderr=true

[program:nginx]
command=nginx -g 'daemon off;'
stdout_logfile=/dev/fd/1
stdout_logfile_maxbytes=0
redirect_stderr=true
EOF

EXPOSE 80

ENTRYPOINT ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
