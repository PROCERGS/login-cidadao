FROM php:5.4-apache

RUN apt-get update \
    && apt-get install -y git libssl-dev zlib1g-dev libicu-dev g++ php5-mysql nodejs nodejs-legacy npm \
    && pecl install zip \
    && echo extension=zip.so > /usr/local/etc/php/conf.d/zip.ini \
    && pecl install pdo_mysql \
    && echo extension=pdo_mysql.so > /usr/local/etc/php/conf.d/pdo_mysql.ini \
    && pecl install apcu-beta \
    && echo extension=apcu.so > /usr/local/etc/php/conf.d/apcu.ini \
    && docker-php-ext-install zip mbstring intl pdo_mysql

COPY compose/vhost.conf /etc/apache2/sites-enabled/000-default.conf
COPY compose/php.ini /usr/local/etc/php/php.ini

RUN a2enmod rewrite

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/bin/composer

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY . /var/www/symfony

RUN usermod -u 1000 www-data
RUN chown -R www-data:www-data /var/www/symfony/app/cache
RUN chown -R www-data:www-data /var/www/symfony/app/logs
RUN chown -R 744 /var/www/symfony/app/cache
RUN chown -R 744 /var/www/symfony/app/logs

WORKDIR /var/www/symfony
RUN composer install
CMD /var/www/symfony/entrypoint.sh
