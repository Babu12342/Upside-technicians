FROM php:8.2-apache
COPY Uni_Mobile_Repairs/ /var/www/html/
RUN chmod -R 755 /var/www/html
RUN docker-php-ext-install mysqli pdo pdo_mysql && a2enmod rewrite
