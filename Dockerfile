FROM php:8.2-cli
 
# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql mysqli
 
# Set working directory
WORKDIR /var/www/html
 
# Copy all project files
COPY . /var/www/html/
 
# Start PHP built-in server on Railway's PORT
CMD php -S 0.0.0.0:${PORT:-80} -t /var/www/html
