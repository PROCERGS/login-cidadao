FROM php:7.2-apache

RUN a2enmod rewrite

# Instal OS dependencies
RUN apt-get -y update
RUN apt-get install -y zlibc zlib1g-dev libxml2-dev libicu-dev libpq-dev nodejs zip unzip git libz-dev

# Install PHP dependencies
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
 && docker-php-ext-install pdo pdo_pgsql pdo_mysql intl soap \
 && docker-php-ext-enable pdo pdo_mysql pdo_pgsql intl soap

# Create link to nodejs
RUN ln -s /usr/bin/nodejs /usr/bin/node

# Install XDebug
RUN yes | pecl install xdebug

# Configure XDebug
RUN echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
 && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
 && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini

# Configure PHP and Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/web
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
ADD https://curl.haxx.se/ca/cacert.pem /etc/

RUN echo "date.timezone = America/Sao_Paulo" > /usr/local/etc/php/conf.d/php-timezone.ini \
 && echo "memory_limit=256M" > /usr/local/etc/php/conf.d/memory_limit.ini

WORKDIR /var/www/html
# Instal composer
COPY --from=composer:1.5 /usr/bin/composer /usr/bin/composer

# Instal composer dependencies
COPY ./composer.* /var/www/html/
RUN composer config cache-dir
RUN composer install --no-interaction --no-scripts --no-autoloader
COPY . /var/www/html
RUN composer dump-autoload -d /var/www/html
RUN chown -R www-data /var/www/html
# RUN php app/console assets:install \
#  && php app/console assets:install -e prod \
#  && php app/console assetic:dump -e prod
