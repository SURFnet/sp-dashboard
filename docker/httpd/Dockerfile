FROM alpine
RUN mkdir -p /var/www/html/
COPY . /var/www/html/
WORKDIR /var/www/html/

RUN apk add --no-cache yarn && \
    yarn install && \
    yarn run encore production

FROM httpd:2.4-alpine
MAINTAINER Bart Geesink (bart.geesink@surfnet.nl)
RUN mkdir -p /var/www/html/
COPY --from=0 /var/www/html/web/. /var/www/html/web/
COPY ./docker/httpd/conf/000-default.conf /usr/local/apache2/conf/
COPY ./docker/httpd/conf/httpd.conf /usr/local/apache2/conf/

EXPOSE 80

CMD ["httpd", "-D", "FOREGROUND"]
