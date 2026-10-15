FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ARG APACHE_PORT=10000
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s/Listen 80/Listen ${APACHE_PORT}/" /etc/apache2/ports.conf \
    && sed -ri -e "s/<VirtualHost \*:80>/<VirtualHost *:${APACHE_PORT}>/" /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY . /var/www/html/
RUN if [ ! -f /var/www/html/src/config/config.php ] && [ -f /var/www/html/src/config/config.php.example ]; then cp /var/www/html/src/config/config.php.example /var/www/html/src/config/config.php; fi
COPY docker-entrypoint.sh /usr/local/bin/tourism-entrypoint
RUN chmod +x /usr/local/bin/tourism-entrypoint \
    && mkdir -p /var/www/html/logs \
    && chown -R www-data:www-data /var/www/html/logs \
    && chmod -R 755 /var/www/html

EXPOSE 10000

ENTRYPOINT ["tourism-entrypoint"]
CMD ["apache2-foreground"]
