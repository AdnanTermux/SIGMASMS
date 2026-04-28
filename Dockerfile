FROM php:8.2-cli

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql mysqli

WORKDIR /var/www/html

COPY . /var/www/html/

# Start PHP built-in server using router.php for static file support
CMD php -S 0.0.0.0:${PORT:-80} -t /var/www/html /var/www/html/router.php
