FROM trafex/php-nginx:3.4.0

ARG UID=1000
ARG GID=1000

ENV UID=${UID}
ENV GID=${GID}

USER root

RUN apk add --no-cache \
  php82-bcmath \
  php82-soap \
  php82-zip \
  php82-soap \
  php82-pdo \
  php82-pdo_mysql \
  php82-simplexml

COPY assets/default.conf /etc/nginx/conf.d/default.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN addgroup -g ${GID} --system app
RUN adduser -G app --system -D -s /bin/sh -u ${UID} app
RUN chown -R app.app /var/www/html /run /var/lib/nginx /var/log/nginx

COPY --chown=app src/ /var/www/html/

RUN composer install

USER app
