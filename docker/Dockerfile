FROM php:7.2-fpm-alpine AS node-build
COPY --from=composer:1 /usr/bin/composer /usr/bin/composer
COPY . /var/www/html/
WORKDIR /var/www/html/
ENV SYMFONY_ENV=prod
RUN composer install -n --prefer-dist -o --ignore-platform-reqs --no-dev && \
    composer run-script symfony-scripts && \
    bin/console assets:install && \
    apk add --no-cache yarn && \
    yarn install && \
    yarn run encore production

FROM httpd:2.4-alpine AS httpd-build
MAINTAINER Bart Geesink (bart.geesink@surf.nl)
RUN mkdir -p /var/www/html/
COPY --from=node-build /var/www/html/web/. /var/www/html/web/
COPY ./docker/conf/000-default.conf /usr/local/apache2/conf/
COPY ./docker/conf/httpd.conf /usr/local/apache2/conf/

EXPOSE 80

CMD ["httpd", "-D", "FOREGROUND"]

FROM php:7.2-fpm-alpine AS phpfpm-build
COPY --from=node-build /var/www/html/app/. /var/www/html/app/
COPY --from=node-build /var/www/html/web/. /var/www/html/web/
COPY --from=node-build /var/www/html/vendor/. /var/www/html/vendor/
COPY --from=node-build /var/www/html/var/. /var/www/html/var/
COPY --from=node-build /var/www/html/src/. /var/www/html/src/
COPY --from=node-build /var/www/html/bin/. /var/www/html/bin/
COPY --from=node-build /var/www/html/composer.json /var/www/html/

LABEL maintainer "Bart Geesink" <bart.geesink@surf.nl>

RUN apk add --no-cache libxml2-dev \
    freetype-dev && \
    docker-php-ext-install soap gd pdo_mysql && \
    chown -R www-data /var/www/html/var/cache && \
    chown -R www-data /var/www/html/var/logs && \
    chown -R www-data /var/www/html/var/sessions && \
    rm -rf /var/www/html/var/cache/prod/

COPY ./docker/conf/zz-docker.conf /usr/local/etc/php-fpm.d

CMD ["/usr/local/sbin/php-fpm" , "-F"]
