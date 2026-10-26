FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers

RUN { echo 'upload_max_filesize=50M'; echo 'post_max_size=55M'; } > /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html
COPY . /var/www/html/
RUN mkdir -p /opt/pawpaws && mv /var/www/html/docker/init_db.php /opt/pawpaws/ \
    && mv /var/www/html/database /opt/pawpaws/database \
    && rm -rf /var/www/html/docker /var/www/html/_backup_chat \
    && mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENV PORT=10000
EXPOSE 10000
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
