FROM php:7.4-apache

RUN a2enmod rewrite

COPY ./source/app/ /var/www/html/
RUN rm -f /var/www/html/.env.local
RUN touch /var/www/html/.env.local
RUN mkdir -p /var/www/html/var
RUN chmod -R 0777 /var/www/html/var

ENV APACHE_DOCUMENT_ROOT /var/www/html/public/

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
