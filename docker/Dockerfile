FROM ghcr.io/openconext/openconext-containers/openconext-phpfpm-dev:81 AS node-build
COPY . /var/www/html/
WORKDIR /var/www/html/
ENV APP_ENV=prod
RUN composer install -n --prefer-dist -o --ignore-platform-reqs --no-dev

FROM node:14 AS js-build
COPY . /var/www/html/
WORKDIR /var/www/html/
RUN yarn install && \
  yarn run encore production

FROM ghcr.io/openconext/openconext-containers/openconext-httpd:latest AS httpd-build
LABEL maintainer Bart Geesink (bart.geesink@surf.nl)
LABEL org.opencontainers.image.source=https://github.com/surfnet/sp-dashboard
RUN mkdir -p /var/www/html/
COPY --from=node-build /var/www/html/public/. /var/www/html/public/
COPY --from=js-build /var/www/html/public/build/. /var/www/html/public/build/
COPY ./docker/conf/000-default.conf /usr/local/apache2/conf/
COPY ./docker/conf/httpd.conf /usr/local/apache2/conf/

EXPOSE 80

CMD ["httpd", "-D", "FOREGROUND"]

FROM ghcr.io/openconext/openconext-containers/openconext-phpfpm:81 AS phpfpm-build
COPY --from=node-build /var/www/html/public/. /var/www/html/public/
COPY --from=node-build /var/www/html/vendor/. /var/www/html/vendor/
COPY --from=node-build /var/www/html/var/. /var/www/html/var/
COPY --from=node-build /var/www/html/src/. /var/www/html/src/
COPY --from=node-build /var/www/html/bin/. /var/www/html/bin/
COPY --from=node-build /var/www/html/config/. /var/www/html/config/
COPY --from=node-build /var/www/html/assets/. /var/www/html/assets/
COPY --from=node-build /var/www/html/templates/. /var/www/html/templates/
COPY --from=node-build /var/www/html/translations/. /var/www/html/translations/
COPY --from=node-build /var/www/html/migrations/. /var/www/html/migrations/
COPY --from=node-build /var/www/html/composer.json /var/www/html/
COPY --from=js-build /var/www/html/public/build/. /var/www/html/public/build/
COPY --from=js-build /var/www/html/node_modules/. /var/www/html/node_modules/

LABEL maintainer Bart Geesink (bart.geesink@surf.nl)
LABEL org.opencontainers.image.source=https://github.com/surfnet/sp-dashboard

RUN mkdir -p /var/www/html/var/cache && \
  mkdir -p /var/www/html/var/log && \
  mkdir -p /var/www/html/var/sessions

RUN chown -R www-data /var/www/html/var/cache && \
  chown -R www-data /var/www/html/var/log && \
  chown -R www-data /var/www/html/var/sessions

COPY ./docker/conf/zz-docker.conf /usr/local/etc/php-fpm.d
COPY ./docker/conf/10-docker-opcache-openconext.conf /usr/local/etc/php/conf.d/
COPY ./docker/conf/10-override-php.ini /usr/local/etc/php/conf.d/
RUN mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
RUN touch /var/www/html/.env
CMD ["/usr/local/sbin/php-fpm" , "-F"]
