FROM ghcr.io/openconext/openconext-basecontainers/php82-apache2-node20-composer2:latest as build
ARG APP_VERSION
ARG GIT_SHA
ARG GIT_COMMIT_TIME
ENV OPENCONEXT_APP_VERSION=${APP_VERSION}
ENV OPENCONEXT_GIT_SHA=${GIT_SHA}
ENV OPENCONEXT_COMMIT_DATE=${GIT_COMMIT_TIME}


COPY . /var/www/html/ 
RUN COMPOSER_ALLOW_SUPERUSER=1 APP_ENV=prod composer install -n --prefer-dist -o --ignore-platform-reqs --no-dev
RUN yarn install && \
  yarn run encore production
RUN composer archive --file=output 

FROM ghcr.io/openconext/openconext-basecontainers/php82-apache2:latest
ARG APP_VERSION
ARG GIT_SHA
ARG GIT_COMMIT_TIME
ENV OPENCONEXT_APP_VERSION=${APP_VERSION}
ENV OPENCONEXT_GIT_SHA=${GIT_SHA}
ENV OPENCONEXT_COMMIT_DATE=${GIT_COMMIT_TIME}
COPY --from=build /var/www/html/output.tar /var/www/html/
COPY ./docker/conf/000-default.conf /etc/apache2/sites-enabled/
COPY ./docker/conf/zz-docker.conf  /usr/local/etc/php/conf.d/zz-docker.ini

RUN tar -xf /var/www/html/output.tar && \
  rm -rf /var/www/html/output.tar && \
  mkdir -p /var/www/html/var/cache && \
  mkdir -p /var/www/html/var/log && \
  mkdir -p /var/www/html/var/sessions && \
  chown -R www-data /var/www/html/var/cache && \
  chown -R www-data /var/www/html/var/log && \
  chown -R www-data /var/www/html/var/sessions && \
  touch /var/www/html/.env

