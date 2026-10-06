# Nyumbani website: PHP 8.2 on Apache with the mysqli extension.
FROM php:8.2-apache

RUN docker-php-ext-install mysqli \
    && a2enmod rewrite headers \
    && printf 'upload_max_filesize=12M\npost_max_size=14M\n' > /usr/local/etc/php/conf.d/uploads.ini

COPY . /var/www/html/
RUN rm -rf /var/www/html/tests /var/www/html/docs \
    && chown -R www-data:www-data /var/www/html/uploads

# uploads/.htaccess blocks script execution, so Apache must honour .htaccess files.
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

EXPOSE 80
