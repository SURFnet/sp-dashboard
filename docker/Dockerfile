FROM ghcr.io/openconext/openconext-basecontainers/php82-apache2-node20-composer2:latest as build
COPY . /var/www/html/ 
RUN composer install -n --prefer-dist -o --ignore-platform-reqs --no-dev
RUN yarn install && \
  yarn run encore production
RUN composer archive --file=output 

FROM ghcr.io/openconext/openconext-basecontainers/php82-apache2:latest
COPY --from=build /var/www/html/output.tar /var/www/html/
COPY ./docker/conf/000-default.conf /etc/apache2/sites-enabled/

RUN tar -xf /var/www/html/output.tar && \
  rm -rf /var/www/html/output.tar && \
  mkdir -p /var/www/html/var/cache && \
  mkdir -p /var/www/html/var/log && \
  mkdir -p /var/www/html/var/sessions && \
  chown -R www-data /var/www/html/var/cache && \
  chown -R www-data /var/www/html/var/log && \
  chown -R www-data /var/www/html/var/sessions && \
  touch /var/www/html/.env

