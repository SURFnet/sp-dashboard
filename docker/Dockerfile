FROM ghcr.io/openconext/openconext-containers/openconext-phpfpm-dev:latest AS node-build
COPY . /var/www/html/
WORKDIR /var/www/html/
ENV SYMFONY_ENV=prod
RUN composer install -n --prefer-dist -o --ignore-platform-reqs --no-dev && \
    composer run-script symfony-scripts

FROM node:14 AS js-build
COPY . /var/www/html/
WORKDIR /var/www/html/
RUN yarn install && \
    yarn run encore production

FROM ghcr.io/openconext/openconext-containers/openconext-httpd:latest AS httpd-build
MAINTAINER Bart Geesink (bart.geesink@surf.nl)
RUN mkdir -p /var/www/html/
COPY --from=node-build /var/www/html/public/. /var/www/html/public/
COPY --from=js-build /var/www/html/public/build/. /var/www/html/web/public/
COPY ./docker/conf/000-default.conf /usr/local/apache2/conf/
COPY ./docker/conf/httpd.conf /usr/local/apache2/conf/

EXPOSE 80

CMD ["httpd", "-D", "FOREGROUND"]

FROM ghcr.io/openconext/openconext-containers/openconext-phpfpm:latest AS phpfpm-build
COPY --from=node-build /var/www/html/public/. /var/www/html/public/
COPY --from=node-build /var/www/html/vendor/. /var/www/html/vendor/
COPY --from=node-build /var/www/html/var/. /var/www/html/var/
COPY --from=node-build /var/www/html/src/. /var/www/html/src/
COPY --from=node-build /var/www/html/bin/. /var/www/html/bin/
COPY --from=node-build /var/www/html/composer.json /var/www/html/
COPY --from=js-build /var/www/html/public/build/. /var/www/html/public/build/
COPY --from=js-build /var/www/html/node_modules/. /var/www/html/node_modules/

LABEL maintainer "Bart Geesink" <bart.geesink@surf.nl>

RUN chown -R www-data /var/www/html/var/cache && \
    chown -R www-data /var/www/html/var/log && \
    chown -R www-data /var/www/html/var/sessions && \
    rm -rf /var/www/html/var/cache/prod/

COPY ./docker/conf/zz-docker.conf /usr/local/etc/php-fpm.d
COPY ./docker/conf/10-docker-opcache-openconext.conf /usr/local/etc/php/conf.d/
COPY ./docker/conf/10-timezone-openconext.ini /usr/local/etc/php/conf.d/

CMD ["/usr/local/sbin/php-fpm" , "-F"]
