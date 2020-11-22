FROM php:7.4-apache

# configure apache
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT /var/www/html/public/
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# create clean copy of app
COPY ./source/app/ /var/www/html/
RUN rm -f /var/www/html/.env.local && touch /var/www/html/.env.local
RUN mkdir -p /var/www/html/var && chmod -R 0777 /var/www/html/var
RUN rm -rf /var/www/html/vendor

# install dependencies using composer
RUN apt-get update && apt-get install git unzip -y
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN cd /var/www/html
RUN composer self-update --1
RUN composer install --no-dev --no-scripts
