FROM httpd:2.4-alpine

MAINTAINER Bart Geesink (bart.geesink@surfnet.nl)
RUN mkdir -p /var/www/html/
COPY --from=sp-dashboard_php-fpm /var/www/html/web/. /var/www/html/web/
COPY ./conf/000-default.conf /usr/local/apache2/conf/
COPY ./conf/httpd.conf /usr/local/apache2/conf/


EXPOSE 80

CMD ["httpd", "-D", "FOREGROUND"]


