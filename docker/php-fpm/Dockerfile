FROM php:7.2-fpm-alpine
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY . /var/www/html/
WORKDIR /var/www/html/
ENV SYMFONY_ENV=prod
RUN composer install -n --prefer-dist -o --ignore-platform-reqs --no-dev && \
    apk add --no-cache yarn && \
    yarn install && \
    yarn run encore production

FROM php:7.2-fpm-alpine
COPY --from=0 /var/www/html/app/. /var/www/html/app/
COPY --from=0 /var/www/html/web/. /var/www/html/web/
COPY --from=0 /var/www/html/vendor/. /var/www/html/vendor/
COPY --from=0 /var/www/html/var/. /var/www/html/var/
COPY --from=0 /var/www/html/src/. /var/www/html/src/
COPY --from=0 /var/www/html/bin/. /var/www/html/bin/

LABEL maintainer "Bart Geesink" <bart.geesink@surfnet.nl>

RUN apk add --no-cache libxml2-dev \
    freetype-dev && \
    docker-php-ext-install soap gd pdo_mysql && \
    chown -R www-data /var/www/html/var/cache && \
    chown -R www-data /var/www/html/var/logs && \
    chown -R www-data /var/www/html/var/sessions

CMD ["/usr/local/sbin/php-fpm" , "-F"]
